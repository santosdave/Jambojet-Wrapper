<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;

/**
 * A failed call is only sent again when that cannot do something twice: reads, and calls
 * JamboJet refused outright (429). A write that failed or timed out may already have been
 * applied (a payment taken, a booking committed), so it is reported, never resent.
 */
class RetryPolicyTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('jambojet.retry_attempts', 3);
        $app['config']->set('jambojet.cache.enabled', false);
    }

    private static function api(): object
    {
        return new class
        {
            use HandlesApiRequests;

            public function call(string $method, string $endpoint, array $data = []): array
            {
                return $this->makeRequest($method, $endpoint, $data);
            }
        };
    }

    private static function sent(string $path): int
    {
        return count(Http::recorded(fn (Request $r) => str_contains($r->url(), $path)));
    }

    private function fake(string $path, $response): void
    {
        Http::fake([
            '*/api/auth/v1/token/user' => self::tokenResponses('token-1'),
            '*'.$path => $response,
        ]);
    }

    public function test_a_failed_payment_is_never_sent_again(): void
    {
        $this->fake('api/nsk/v5/booking/payments', Http::response(['message' => 'Gateway timeout'], 504));

        $this->expectException(JamboJetApiException::class);
        try {
            self::api()->call('POST', 'api/nsk/v5/booking/payments', ['paymentMethodCode' => 'AG', 'amount' => 100]);
        } finally {
            $this->assertSame(1, self::sent('api/nsk/v5/booking/payments'));
        }
    }

    public function test_a_commit_that_timed_out_is_never_sent_again(): void
    {
        $attempts = 0;
        $this->fake('api/nsk/v3/booking', function () use (&$attempts) {
            $attempts++;
            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $this->expectException(JamboJetApiException::class);
        try {
            self::api()->call('POST', 'api/nsk/v3/booking', ['receivedBy' => 'test']);
        } finally {
            $this->assertSame(1, $attempts);
        }
    }

    public function test_a_read_that_failed_is_tried_again(): void
    {
        $this->fake('api/nsk/v1/bookings/ABC123', Http::sequence()->push(['message' => 'busy'], 503)->push(['data' => ['recordLocator' => 'ABC123']], 200));

        $result = self::api()->call('GET', 'api/nsk/v1/bookings/ABC123');

        $this->assertSame('ABC123', $result['data']['data']['recordLocator']);
        $this->assertSame(2, self::sent('api/nsk/v1/bookings/ABC123'));
    }

    public function test_an_availability_search_is_a_read_even_though_it_is_a_post(): void
    {
        $this->fake('api/nsk/v4/availability/search/simple', Http::sequence()->push(['message' => 'busy'], 503)->push(['data' => ['results' => []]], 200));

        self::api()->call('POST', 'api/nsk/v4/availability/search/simple', ['origin' => 'NBO']);

        $this->assertSame(2, self::sent('api/nsk/v4/availability/search/simple'));
    }

    public function test_a_write_jambojet_refused_as_too_many_requests_is_tried_again(): void
    {
        $this->fake('api/nsk/v5/trip', Http::sequence()->push(['message' => 'slow down'], 429)->push(['data' => ['ok' => true]], 200));

        self::api()->call('POST', 'api/nsk/v5/trip', ['journeys' => []]);

        $this->assertSame(2, self::sent('api/nsk/v5/trip'));
    }

    public function test_a_rejected_request_is_not_repeated(): void
    {
        $this->fake('api/nsk/v1/bookings/BAD', Http::response(['message' => 'Invalid record locator'], 400));

        $this->expectException(JamboJetApiException::class);
        try {
            self::api()->call('GET', 'api/nsk/v1/bookings/BAD');
        } finally {
            $this->assertSame(1, self::sent('api/nsk/v1/bookings/BAD'));
        }
    }
}
