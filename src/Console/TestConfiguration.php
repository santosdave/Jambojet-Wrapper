<?php

namespace SantosDave\JamboJet\Console;

use Illuminate\Console\Command;
use SantosDave\JamboJet\Facades\JamboJet;

class TestConfiguration extends Command
{
    protected $signature = 'jambojet:test-config';
    protected $description = 'Test JamboJet API configuration';

    public function handle()
    {
        $this->info('Testing JamboJet Configuration...');
        $this->newLine();

        $config = config('jambojet');

        // Test 1: Base URL
        $this->checkConfig('Base URL', $config['base_url'] ?? null);

        // Test 2: Subscription Key
        $subscriptionKey = $config['subscription_key'] ?? null;
        if ($subscriptionKey) {
            $masked = substr($subscriptionKey, 0, 8) . '...' . substr($subscriptionKey, -4);
            $this->info("✓ Subscription Key: {$masked}");
        } else {
            $this->error("✗ Subscription Key: NOT SET");
        }

        // Test 3: Timeout
        $this->checkConfig('Timeout', $config['timeout'] ?? null);

        // Test 4: Retry Attempts
        $this->checkConfig('Retry Attempts', $config['retry_attempts'] ?? null);

        // Test 5: Environment
        $this->checkConfig('Environment', $config['environment'] ?? null);

        // Test 6: Cache Configuration
        $this->newLine();
        $this->info('Cache Configuration:');
        if (isset($config['cache']) && is_array($config['cache'])) {
            $this->info("  ✓ Cache Enabled: " . ($config['cache']['enabled'] ? 'Yes' : 'No'));
            $this->info("  ✓ Cache TTL: {$config['cache']['ttl']} seconds");
            $this->info("  ✓ Cache Prefix: {$config['cache']['prefix']}");
        } else {
            $this->error("  ✗ Cache configuration is missing!");
        }

        // Test 7: Logging Configuration
        $this->newLine();
        $this->info('Logging Configuration:');
        if (isset($config['logging']) && is_array($config['logging'])) {
            $this->info("  ✓ Logging Enabled: " . ($config['logging']['enabled'] ? 'Yes' : 'No'));
            $this->info("  ✓ Log Channel: {$config['logging']['channel']}");
        } else {
            $this->error("  ✗ Logging configuration is missing!");
        }

        // Test 8: Try to instantiate services
        $this->newLine();
        $this->info('Testing Service Instantiation:');

        try {
            $auth = JamboJet::auth();
            $this->info("  ✓ Authentication Service: OK");
        } catch (\Exception $e) {
            $this->error("  ✗ Authentication Service: FAILED - " . $e->getMessage());
        }

        try {
            $availability = JamboJet::availability();
            $this->info("  ✓ Availability Service: OK");
        } catch (\Exception $e) {
            $this->error("  ✗ Availability Service: FAILED - " . $e->getMessage());
        }

        try {
            $booking = JamboJet::booking();
            $this->info("  ✓ Booking Service: OK");
        } catch (\Exception $e) {
            $this->error("  ✗ Booking Service: FAILED - " . $e->getMessage());
        }

        // Summary
        $this->newLine();
        if (empty($config['base_url']) || empty($config['subscription_key'])) {
            $this->error('Configuration is incomplete. Please check your .env file.');
            return 1;
        }

        $this->info('✓ Configuration looks good!');
        $this->newLine();
        $this->comment('Next step: Test authentication with:');
        $this->line('   php artisan jambojet:test-auth');

        return 0;
    }

    private function checkConfig(string $label, $value)
    {
        if ($value !== null && $value !== '') {
            $this->info("✓ {$label}: {$value}");
        } else {
            $this->error("✗ {$label}: NOT SET");
        }
    }
}
