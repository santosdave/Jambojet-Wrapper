<?php

namespace SantosDave\JamboJet\Tests;

use Carbon\Carbon;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionProperty;
use SantosDave\JamboJet\JamboJetServiceProvider;
use SantosDave\JamboJet\Services\TokenManager;

/**
 * A Laravel app with the package, fake credentials and no real network: every test fakes
 * the JamboJet API with Http::fake.
 */
abstract class TestCase extends BaseTestCase
{
    protected const BASE_URL = 'https://jambojet.test/jm/dotrez/';

    protected function getPackageProviders($app): array
    {
        return [JamboJetServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('jambojet.base_url', self::BASE_URL);
        $app['config']->set('jambojet.subscription_key', 'test-subscription-key');
        $app['config']->set('jambojet.retry_attempts', 1);
        $app['config']->set('jambojet.logging.enabled', false);
        $app['config']->set('jambojet.logging.channel', 'null');
        $app['config']->set('jambojet.auth', [
            'username' => 'test-user',
            'password' => 'test-password',
            'domain' => 'WWW',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-05 08:00:00');
        self::forgetProcessToken();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        self::forgetProcessToken();
        parent::tearDown();
    }

    /**
     * The token manager keeps the token in static properties: what a fresh PHP process
     * (another queue worker, the next web request) starts without.
     */
    protected static function forgetProcessToken(): void
    {
        foreach (['tokens'] as $property) {
            (new ReflectionProperty(TokenManager::class, $property))->setValue(null, []);
        }
    }

    /**
     * The token endpoint answering with the given tokens in turn, each valid for 20 minutes.
     */
    protected static function tokenResponses(string ...$tokens): ResponseSequence
    {
        $sequence = Http::sequence();
        foreach ($tokens as $token) {
            $sequence->push(['data' => ['token' => $token, 'expires' => now()->addMinutes(20)->toIso8601String()]], 201);
        }

        return $sequence;
    }

    protected static function tokenRequests(): int
    {
        return count(Http::recorded(static fn ($request) => str_contains($request->url(), 'api/auth/v1/token/user')));
    }
}
