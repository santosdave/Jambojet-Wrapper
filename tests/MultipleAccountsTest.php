<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SantosDave\JamboJet\Services\JamboJetClient;

/**
 * One application, several JamboJet accounts (agencies on one platform): each client logs
 * in with its own credentials and keeps its own token. The config-file setup is unchanged.
 */
class MultipleAccountsTest extends TestCase
{
    private function fakeApi(): void
    {
        // Every call reaches the API (repeated reads would come from the response cache).
        config(['jambojet.cache.enabled' => false]);
        // A token per user name, so the test can see whose token a call carries.
        Http::fake([
            '*/api/auth/v1/token/user' => fn (Request $request) => Http::response(['data' => [
                'token' => 'token-of-'.$request['credentials']['userName'],
                'expires' => now()->addMinutes(20)->toIso8601String(),
            ]], 201),
            '*/api/nsk/v1/apo' => Http::response(['data' => ['options' => []]]),
        ]);
    }

    private static function account(string $user): JamboJetClient
    {
        return new JamboJetClient(['auth' => ['username' => $user, 'password' => 'secret-'.$user, 'domain' => 'WWW']]);
    }

    /**
     * @return list<string> user names that requested a token, in order
     */
    private static function logins(): array
    {
        return Http::recorded(fn (Request $r) => str_contains($r->url(), 'api/auth/v1/token/user'))
            ->map(fn ($pair) => $pair[0]['credentials']['userName'])->values()->all();
    }

    private static function tokensSentToApo(): array
    {
        return Http::recorded(fn (Request $r) => str_contains($r->url(), 'api/nsk/v1/apo'))
            ->map(fn ($pair) => $pair[0]->header('Authorization')[0])->values()->all();
    }

    public function test_each_account_logs_in_with_its_own_credentials_and_keeps_its_own_token(): void
    {
        $this->fakeApi();
        $agencyA = self::account('agency-a');
        $agencyB = self::account('agency-b');

        $agencyA->apo()->getAncillaryPricingOptions();
        $agencyB->apo()->getAncillaryPricingOptions();
        $agencyA->apo()->getAncillaryPricingOptions();
        $agencyB->apo()->getAncillaryPricingOptions();

        $this->assertSame(['agency-a', 'agency-b'], self::logins());
        $this->assertSame([
            'Bearer token-of-agency-a', 'Bearer token-of-agency-b',
            'Bearer token-of-agency-a', 'Bearer token-of-agency-b',
        ], self::tokensSentToApo());
    }

    public function test_an_account_recovers_from_a_rejected_token_with_its_own_credentials(): void
    {
        config(['jambojet.cache.enabled' => false]);
        Http::fake([
            '*/api/auth/v1/token/user' => fn (Request $request) => Http::response(['data' => [
                'token' => 'token-of-'.$request['credentials']['userName'].'-'.uniqid(),
                'expires' => now()->addMinutes(20)->toIso8601String(),
            ]], 201),
            // fine, then the token is rejected once, then fine again
            '*/api/nsk/v1/apo' => Http::sequence()->push(['data' => []], 200)->push(['message' => 'expired'], 401)->push(['data' => []], 200),
        ]);
        $agencyB = self::account('agency-b');

        $agencyB->apo()->getAncillaryPricingOptions();
        $result = $agencyB->apo()->getAncillaryPricingOptions();

        $this->assertTrue($result['success']);
        $this->assertSame(['agency-b', 'agency-b'], self::logins());
    }

    public function test_the_config_file_setup_is_unchanged(): void
    {
        $this->fakeApi();

        app('jambojet')->apo()->getAncillaryPricingOptions();
        (new JamboJetClient(['timeout' => 30]))->apo()->getAncillaryPricingOptions(); // a partial config, as hosts pass today

        $this->assertSame(['test-user'], self::logins());
        $this->assertSame(['Bearer token-of-test-user', 'Bearer token-of-test-user'], self::tokensSentToApo());
    }
}
