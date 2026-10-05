<?php

namespace SantosDave\JamboJet\Tests;

use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SantosDave\JamboJet\Contracts\ApoInterface;
use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Contracts\TokenManagerInterface;

/**
 * How the package gets and keeps its API token: authenticate once, then reuse the token
 * until it is about to expire. Requesting a token on every call is what an upgrade can
 * silently break (Carbon 3 made diffInSeconds signed), so it is pinned here.
 */
class TokenLifecycleTest extends TestCase
{
    private function fakeApi(string ...$tokens): void
    {
        Http::fake([
            // Spare tokens, so an unwanted extra token request is answered and counted
            // instead of failing quietly inside the package's own error handling.
            '*/api/auth/v1/token/user' => self::tokenResponses(...($tokens ?: ['token-1', 'token-2', 'token-3', 'token-4'])),
            '*/api/nsk/v1/apo' => Http::response(['data' => ['options' => []]]),
        ]);
    }

    public function test_it_authenticates_once_and_reuses_the_token(): void
    {
        $this->fakeApi();

        app(ApoInterface::class)->getAncillaryPricingOptions();
        app(ApoInterface::class)->getAncillaryPricingOptions();
        app(ApoInterface::class)->preserveSession(false)->getAncillaryPricingOptions();

        $this->assertSame(1, self::tokenRequests());
    }

    public function test_it_sends_the_subscription_key_and_the_token(): void
    {
        $this->fakeApi('token-1');

        app(ApoInterface::class)->getAncillaryPricingOptions();

        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'api/nsk/v1/apo')
            && $request->hasHeader('Ocp-Apim-Subscription-Key', 'test-subscription-key')
            && $request->hasHeader('Authorization', 'Bearer token-1'));
        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'api/auth/v1/token/user')
            && $request['credentials']['userName'] === 'test-user'
            && $request['credentials']['domain'] === 'WWW');
    }

    public function test_a_new_process_reuses_the_cached_token(): void
    {
        $this->fakeApi();
        app(ApoInterface::class)->getAncillaryPricingOptions();

        self::forgetProcessToken(); // the next request, in another PHP process
        app(ApoInterface::class)->getAncillaryPricingOptions();

        $this->assertSame(1, self::tokenRequests());
    }

    public function test_it_knows_how_long_the_token_has_left(): void
    {
        $this->fakeApi();
        $auth = app(AuthenticationInterface::class);
        $auth->autoAuthenticate();

        $this->assertSame(1200, (int) $auth->tokenExpiresIn());
        $this->assertFalse($auth->isTokenExpiringSoon(120));
        $this->assertFalse($auth->isTokenExpired());

        Carbon::setTestNow(now()->addMinutes(19)); // one minute left
        $this->assertTrue($auth->isTokenExpiringSoon(120));
    }

    public function test_the_shared_token_manager_reports_the_remaining_time(): void
    {
        $this->fakeApi();
        app(ApoInterface::class)->getAncillaryPricingOptions();

        $manager = app(TokenManagerInterface::class);
        $this->assertTrue($manager->hasValidToken());
        $this->assertSame(1200, $manager->getRemainingSeconds());

        Carbon::setTestNow(now()->addMinutes(21));
        $this->assertFalse($manager->hasValidToken());
    }

    public function test_it_authenticates_again_once_the_token_is_about_to_expire(): void
    {
        $this->fakeApi('token-1', 'token-2', 'token-3', 'token-4');
        app(ApoInterface::class)->getAncillaryPricingOptions();

        Carbon::setTestNow(now()->addMinutes(19)); // inside the two-minute margin
        app(ApoInterface::class)->getAncillaryPricingOptions();

        $this->assertSame(2, self::tokenRequests());
    }
}
