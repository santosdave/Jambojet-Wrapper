<?php

namespace SantosDave\JamboJet;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use SantosDave\JamboJet\Contracts\AccountInterface;
use SantosDave\JamboJet\Contracts\AddOnsInterface;
use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Contracts\AvailabilityInterface;
use SantosDave\JamboJet\Contracts\BoardingPassInterface;
use SantosDave\JamboJet\Contracts\BookingInterface;
use SantosDave\JamboJet\Contracts\BundleInterface;
use SantosDave\JamboJet\Contracts\CoreInterface;
use SantosDave\JamboJet\Contracts\LoyaltyProgramInterface;
use SantosDave\JamboJet\Contracts\ManifestInterface;
use SantosDave\JamboJet\Contracts\MessageInterface;
use SantosDave\JamboJet\Contracts\NavigationInterface;
use SantosDave\JamboJet\Contracts\OrganizationInterface;
use SantosDave\JamboJet\Contracts\PaymentInterface;
use SantosDave\JamboJet\Contracts\ResourcesInterface;
use SantosDave\JamboJet\Contracts\SeatInterface;
use SantosDave\JamboJet\Contracts\SettingsInterface;
use SantosDave\JamboJet\Contracts\TokenManagerInterface;
use SantosDave\JamboJet\Contracts\TripInterface;
use SantosDave\JamboJet\Contracts\UserInterface;
use SantosDave\JamboJet\Contracts\VoucherInterface;
use SantosDave\JamboJet\Services\AccountService;
use SantosDave\JamboJet\Services\AddOnsService;
use SantosDave\JamboJet\Services\AuthenticationService;
use SantosDave\JamboJet\Services\AvailabilityService;
use SantosDave\JamboJet\Services\BoardingPassService;
use SantosDave\JamboJet\Services\BookingService;
use SantosDave\JamboJet\Services\BundleService;
use SantosDave\JamboJet\Services\CoreService;
use SantosDave\JamboJet\Services\JamboJetClient;
use SantosDave\JamboJet\Services\LoyaltyProgramService;
use SantosDave\JamboJet\Services\ManifestService;
use SantosDave\JamboJet\Services\MessageService;
use SantosDave\JamboJet\Services\NavigationService;
use SantosDave\JamboJet\Services\OrganizationService;
use SantosDave\JamboJet\Services\PaymentService;
use SantosDave\JamboJet\Services\ResourcesService;
use SantosDave\JamboJet\Services\SeatService;
use SantosDave\JamboJet\Services\SettingsService;
use SantosDave\JamboJet\Services\TokenManager;
use SantosDave\JamboJet\Services\TripService;
use SantosDave\JamboJet\Services\UserService;
use SantosDave\JamboJet\Services\VoucherService;

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
            __DIR__ . '/../Config/jambojet.php' => config_path('jambojet.php'),
        ], 'jambojet-config');

        $this->validateConfiguration();
    }

    private function registerServices()
    {
        // Authentication & Token Service
        $this->app->bind(
            AuthenticationInterface::class,
            AuthenticationService::class
        );

        // Availability & Search Service  
        $this->app->bind(
            AvailabilityInterface::class,
            AvailabilityService::class
        );

        // Booking Service
        $this->app->bind(
            BookingInterface::class,
            BookingService::class
        );

        // Payment Service
        $this->app->bind(
            PaymentInterface::class,
            PaymentService::class
        );

        // User Management Service
        $this->app->bind(
            UserInterface::class,
            UserService::class
        );

        // Account Service
        $this->app->bind(
            AccountInterface::class,
            AccountService::class
        );

        // Add-ons Service
        $this->app->bind(
            AddOnsInterface::class,
            AddOnsService::class
        );

        // Resources Service
        $this->app->bind(
            ResourcesInterface::class,
            ResourcesService::class
        );

        // Organization Management Service
        $this->app->bind(
            OrganizationInterface::class,
            OrganizationService::class
        );

        // Loyalty Program Service
        $this->app->bind(
            LoyaltyProgramInterface::class,
            LoyaltyProgramService::class
        );

        // Navigation & Workflow Service
        $this->app->bind(
            NavigationInterface::class,
            NavigationService::class
        );

        // Seat Management Service
        $this->app->bind(
            SeatInterface::class,
            SeatService::class
        );

        // Bundle & Package Service
        $this->app->bind(
            BundleInterface::class,
            BundleService::class
        );

        // Boarding Pass Service
        $this->app->bind(
            BoardingPassInterface::class,
            BoardingPassService::class
        );

        // Token Manager Service
        $this->app->bind(
            TokenManagerInterface::class,
            TokenManager::class
        );
        // Core System Operations Service
        $this->app->bind(
            CoreInterface::class,
            CoreService::class
        );

        // Message Service
        $this->app->bind(
            MessageInterface::class,
            MessageService::class
        );

        //Trip Service
        $this->app->bind(
            TripInterface::class,
            TripService::class
        );

        //Manifest Service
        $this->app->bind(
            ManifestInterface::class,
            ManifestService::class
        );

        // Voucher Service
        $this->app->bind(
            VoucherInterface::class,
            VoucherService::class
        );

        // Settings Service
        $this->app->bind(
            SettingsInterface::class,
            SettingsService::class
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
            AuthenticationInterface::class,
            AvailabilityInterface::class,
            BookingInterface::class,
            PaymentInterface::class,
            UserInterface::class,
            AccountInterface::class,
            AddOnsInterface::class,
            ResourcesInterface::class,
            OrganizationInterface::class,
            LoyaltyProgramInterface::class,
            NavigationInterface::class,
            SeatInterface::class,
            BundleInterface::class,
            BoardingPassInterface::class,
            TokenManagerInterface::class,
            CoreInterface::class,
            MessageInterface::class,
            TripInterface::class,
            ManifestInterface::class,
            VoucherInterface::class,
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