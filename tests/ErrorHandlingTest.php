<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Support\Facades\Http;
use SantosDave\JamboJet\Contracts\ApoInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;

/**
 * What a caller gets back: a uniform envelope on success, typed exceptions on failure.
 */
class ErrorHandlingTest extends TestCase
{
    private function fakeApo(int $status, array $body, array $headers = []): void
    {
        Http::fake([
            '*/api/auth/v1/token/user' => self::tokenResponses('token-1'),
            '*/api/nsk/v1/apo' => Http::response($body, $status, $headers),
        ]);
    }

    public function test_success_comes_back_in_the_envelope(): void
    {
        $this->fakeApo(200, ['data' => ['options' => ['A']]]);

        $result = app(ApoInterface::class)->getAncillaryPricingOptions();

        $this->assertTrue($result['success']);
        $this->assertSame(['data' => ['options' => ['A']]], $result['data']);
        $this->assertSame(200, $result['meta']['status_code']);
    }

    public function test_a_validation_error_keeps_its_status(): void
    {
        $this->fakeApo(400, ['message' => 'Bad request']);

        try {
            app(ApoInterface::class)->getAncillaryPricingOptions();
            $this->fail('Expected an exception');
        } catch (JamboJetApiException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('Bad request', $e->getMessage());
        }
    }

    public function test_a_rate_limit_says_when_to_retry(): void
    {
        $this->fakeApo(429, ['message' => 'Too many'], ['Retry-After' => '30']);

        try {
            app(ApoInterface::class)->getAncillaryPricingOptions();
            $this->fail('Expected an exception');
        } catch (JamboJetApiException $e) {
            $this->assertStringContainsString('Retry after 30 seconds', $e->getMessage());
        }
    }
}
