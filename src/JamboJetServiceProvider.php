<?php

namespace SantosDave\JamboJet;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use SantosDave\JamboJet\Services\JamboJetClient;

class JamboJetServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/jambojet.php',
            'jambojet'
        );

        $this->app->singleton('jambojet', function ($app) {
            return new JamboJetClient($app['config']['jambojet']);
        });

        // Register all service modules
        $this->registerServices();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/jambojet.php' => config_path('jambojet.php'),
        ], 'jambojet-config');

        $this->validateConfiguration();
    }

    private function registerServices()
    {
        // Authentication & Token Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\AuthenticationInterface::class,
            \SantosDave\JamboJet\Services\AuthenticationService::class
        );

        // Availability & Search Service  
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\AvailabilityInterface::class,
            \SantosDave\JamboJet\Services\AvailabilityService::class
        );

        // Booking Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\BookingInterface::class,
            \SantosDave\JamboJet\Services\BookingService::class
        );

        // Payment Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\PaymentInterface::class,
            \SantosDave\JamboJet\Services\PaymentService::class
        );

        // User Management Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\UserInterface::class,
            \SantosDave\JamboJet\Services\UserService::class
        );

        // Account Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\AccountInterface::class,
            \SantosDave\JamboJet\Services\AccountService::class
        );

        // Add-ons Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\AddOnsInterface::class,
            \SantosDave\JamboJet\Services\AddOnsService::class
        );

        // Resources Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\ResourcesInterface::class,
            \SantosDave\JamboJet\Services\ResourcesService::class
        );

        // Organization Management Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\OrganizationInterface::class,
            \SantosDave\JamboJet\Services\OrganizationService::class
        );

        // Loyalty Program Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\LoyaltyProgramInterface::class,
            \SantosDave\JamboJet\Services\LoyaltyProgramService::class
        );

        // Navigation & Workflow Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\NavigationInterface::class,
            \SantosDave\JamboJet\Services\NavigationService::class
        );

        // Seat Management Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\SeatInterface::class,
            \SantosDave\JamboJet\Services\SeatService::class
        );

        // Bundle & Package Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\BundleInterface::class,
            \SantosDave\JamboJet\Services\BundleService::class
        );

        // Boarding Pass Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\BoardingPassInterface::class,
            \SantosDave\JamboJet\Services\BoardingPassService::class
        );

        // Token Manager Service
        $this->app->bind(
            \SantosDave\JamboJet\Contracts\TokenManagerInterface::class,
            \SantosDave\JamboJet\Services\TokenManager::class
        );
    }

    /**
     * Get services configuration
     * 
     * @return array List of available services and their descriptions
     */
    public function provides()
    {
        return [
            'jambojet',
            \SantosDave\JamboJet\Contracts\AuthenticationInterface::class,
            \SantosDave\JamboJet\Contracts\AvailabilityInterface::class,
            \SantosDave\JamboJet\Contracts\BookingInterface::class,
            \SantosDave\JamboJet\Contracts\PaymentInterface::class,
            \SantosDave\JamboJet\Contracts\UserInterface::class,
            \SantosDave\JamboJet\Contracts\AccountInterface::class,
            \SantosDave\JamboJet\Contracts\AddOnsInterface::class,
            \SantosDave\JamboJet\Contracts\ResourcesInterface::class,
            \SantosDave\JamboJet\Contracts\OrganizationInterface::class,
            \SantosDave\JamboJet\Contracts\LoyaltyProgramInterface::class,
            \SantosDave\JamboJet\Contracts\NavigationInterface::class,
            \SantosDave\JamboJet\Contracts\SeatInterface::class,
            \SantosDave\JamboJet\Contracts\BundleInterface::class,
            \SantosDave\JamboJet\Contracts\BoardingPassInterface::class,
        ];
    }

    /**
     * Get service coverage status
     * 
     * @return array Coverage summary
     */
    public function getServiceCoverage(): array
    {
        return [
            'core_services' => 8,
            'extended_services' => 6,
            'total_services' => 14,
            'api_endpoints_covered' => '80+',
            'nsk_api_version' => '4.2.1.252',
            'coverage_status' => 'Complete'
        ];
    }

    /**
     * Validate JamboJet configuration
     */
    protected function validateConfiguration(): void
    {
        $config = config('jambojet');

        // Check required configuration
        if (empty($config['base_url'])) {
            throw new \RuntimeException(
                'JamboJet configuration error: JAMBOJET_BASE_URL is required in .env file'
            );
        }

        if (empty($config['subscription_key'])) {
            Log::warning(
                'JamboJet configuration warning: JAMBOJET_SUBSCRIPTION_KEY is not set. ' .
                    'API requests will fail without a valid subscription key.'
            );
        }

        // Validate base URL format
        if (!filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            throw new \RuntimeException(
                'JamboJet configuration error: JAMBOJET_BASE_URL must be a valid URL'
            );
        }

        // Validate cache configuration
        if (!isset($config['cache']) || !is_array($config['cache'])) {
            throw new \RuntimeException(
                'JamboJet configuration error: cache configuration is missing or invalid'
            );
        }

        // Validate logging configuration
        if (!isset($config['logging']) || !is_array($config['logging'])) {
            throw new \RuntimeException(
                'JamboJet configuration error: logging configuration is missing or invalid'
            );
        }

        // Log configuration status (excluding sensitive data)
        Log::debug('JamboJet configuration loaded', [
            'base_url' => $config['base_url'],
            'environment' => $config['environment'] ?? 'not set',
            'timeout' => $config['timeout'] ?? 'not set',
            'cache_enabled' => $config['cache']['enabled'] ?? false,
            'logging_enabled' => $config['logging']['enabled'] ?? false,
        ]);
    }
}
