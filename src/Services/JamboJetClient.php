<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Contracts\AvailabilityInterface;
use SantosDave\JamboJet\Contracts\BookingInterface;
use SantosDave\JamboJet\Contracts\PaymentInterface;
use SantosDave\JamboJet\Contracts\UserInterface;
use SantosDave\JamboJet\Contracts\AccountInterface;
use SantosDave\JamboJet\Contracts\AddOnsInterface;
use SantosDave\JamboJet\Contracts\ResourcesInterface;
use SantosDave\JamboJet\Contracts\OrganizationInterface;
use SantosDave\JamboJet\Contracts\LoyaltyProgramInterface;
use SantosDave\JamboJet\Contracts\NavigationInterface;
use SantosDave\JamboJet\Contracts\SeatInterface;
use SantosDave\JamboJet\Contracts\BundleInterface;
use SantosDave\JamboJet\Contracts\BoardingPassInterface;
use SantosDave\JamboJet\Contracts\CoreInterface;
use SantosDave\JamboJet\Contracts\MessageInterface;

/**
 * Main JamboJet API Client
 * 
 * Provides access to all JamboJet NSK API modules
 * Base URL: https://jmtest.booking.jambojet.com/jm/dotrez/
 * System: New Skies 4.2.1.252
 * 
 * Complete service coverage for all NSK API endpoints:
 * - Core System Operations (Redis, GraphQL, Pricing, Promotions)
 * - Authentication & Token Management
 * - Availability & Search Operations
 * - Booking & Reservation Management
 * - Payment Processing
 * - User & Account Management
 * - Add-ons & Ancillary Services
 * - Resources & Configuration
 * - Organization Management
 * - Loyalty Program Operations
 * - Booking Navigation & Workflow
 * - Seat Management & Assignment
 * - Bundle & Package Management
 * - Boarding Pass Operations
 */
class JamboJetClient
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }



    // ==========================================
    // CORE API MODULES
    // ==========================================

    /**
     * Authentication & Token Management
     * Handles: /api/auth/v1/token/user endpoints
     */
    public function auth(): AuthenticationInterface
    {
        return app(AuthenticationInterface::class);
    }

    /**
     * Availability & Search Operations  
     * Handles: /api/nsk/v{1-4}/availability endpoints
     */
    public function availability(): AvailabilityInterface
    {
        return app(AvailabilityInterface::class);
    }

    /**
     * Booking Management
     * Handles: /api/nsk/v{1-3}/booking, /api/nsk/v{1-2}/bookings endpoints
     */
    public function booking(): BookingInterface
    {
        return app(BookingInterface::class);
    }

    /**
     * Payment Processing
     * Handles: /api/nsk/v{1-6}/booking/payments endpoints
     */
    public function payment(): PaymentInterface
    {
        return app(PaymentInterface::class);
    }

    /**
     * User Management
     * Handles: /api/nsk/v{1-2}/user, /api/nsk/v1/users endpoints
     */
    public function user(): UserInterface
    {
        return app(UserInterface::class);
    }

    /**
     * Account Management
     * Handles: /api/nsk/v{1-3}/account endpoints  
     */
    public function account(): AccountInterface
    {
        return app(AccountInterface::class);
    }

    /**
     * Add-ons & Ancillary Services
     * Handles: /api/nsk/v{1-2}/addOns endpoints
     */
    public function addOns(): AddOnsInterface
    {
        return app(AddOnsInterface::class);
    }

    /**
     * Resources & Configuration
     * Handles: /api/nsk/v{1-2}/resources endpoints
     */
    public function resources(): ResourcesInterface
    {
        return app(ResourcesInterface::class);
    }

    // ==========================================
    // EXTENDED API MODULES (Phase 2 Additions)
    // ==========================================

    /**
     * Organization Management
     * Handles: /api/nsk/v{1-3}/organizations endpoints
     */
    public function organization(): OrganizationInterface
    {
        return app(OrganizationInterface::class);
    }

    /**
     * Loyalty Program Operations
     * Handles: /api/nsk/v{1-2}/loyaltyPrograms endpoints
     */
    public function loyaltyProgram(): LoyaltyProgramInterface
    {
        return app(LoyaltyProgramInterface::class);
    }

    /**
     * Booking Navigation & Workflow
     * Handles: /api/nsk/v1/booking/navigation endpoints
     */
    public function navigation(): NavigationInterface
    {
        return app(NavigationInterface::class);
    }

    /**
     * Seat Management & Assignment
     * Handles: /api/nsk/v1/booking/seat endpoints
     */
    public function seat(): SeatInterface
    {
        return app(SeatInterface::class);
    }

    /**
     * Bundle & Package Management
     * Handles: /api/nsk/v1/booking/bundle, /api/nsk/v1/resources/bundles endpoints
     */
    public function bundle(): BundleInterface
    {
        return app(BundleInterface::class);
    }

    /**
     * Boarding Pass Operations
     * Handles: /api/nsk/v3/booking/boardingpasses endpoints
     */
    public function boardingPass(): BoardingPassInterface
    {
        return app(BoardingPassInterface::class);
    }

    // ==========================================
    // CORE SYSTEM MODULE (NEW)
    // ==========================================

    /**
     * Core System Operations
     * Handles: /api/v1/redis, /api/nsk/v1/graph, /api/apo/v1/pricing, /api/nsk/v1/promotions endpoints
     */
    public function core(): CoreInterface
    {
        return app(CoreInterface::class);
    }

    /**
     * Message Queue Operations
     * Handles: /api/nsk/v1/messages, /api/nsk/v2/messages endpoints
     * 
     * @return MessageInterface
     */
    public function message(): MessageInterface
    {
        return app(MessageInterface::class);
    }

    // ==========================================
    // UTILITY METHODS
    // ==========================================

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get API base URL from configuration
     */
    public function getBaseUrl(): string
    {
        return $this->config['base_url'] ?? 'https://jmtest.booking.jambojet.com/jm/dotrez/';
    }

    /**
     * Get API version for specific module
     * 
     * @param string $module Module name (availability, booking, payment, etc.)
     * @return string API version for the module
     */
    public function getApiVersion(string $module): string
    {
        $versions = [
            'core' => 'v1',
            'authentication' => 'v1',
            'availability' => 'v4',    // Latest for search
            'booking' => 'v3',         // Stateful operations
            'payment' => 'v6',         // Latest
            'user' => 'v2',           // Creation operations
            'account' => 'v1',
            'addons' => 'v2',         // Latest
            'resources' => 'v1',      // Most endpoints
            'organization' => 'v1',
            'loyaltyprogram' => 'v1',
            'navigation' => 'v1',
            'seat' => 'v1',
            'bundle' => 'v1',
            'boardingpass' => 'v3'    // Latest stable
        ];

        return $versions[strtolower($module)] ?? 'v1';
    }

    /**
     * Check if a specific service is available
     * 
     * @param string $service Service name
     * @return bool True if service is available
     */
    public function hasService(string $service): bool
    {
        $availableServices = [
            'core',
            'auth',
            'availability',
            'booking',
            'payment',
            'user',
            'account',
            'addons',
            'resources',
            'organization',
            'loyaltyprogram',
            'navigation',
            'seat',
            'bundle',
            'boardingpass'
        ];

        return in_array(strtolower($service), $availableServices);
    }

    /**
     * Get all available services
     * 
     * @return array List of available service names
     */
    public function getAvailableServices(): array
    {
        return [
            'core' => 'Core System Operations',
            'auth' => 'Authentication & Token Management',
            'availability' => 'Availability & Search Operations',
            'booking' => 'Booking Management',
            'payment' => 'Payment Processing',
            'user' => 'User Management',
            'account' => 'Account Management',
            'addons' => 'Add-ons & Ancillary Services',
            'resources' => 'Resources & Configuration',
            'organization' => 'Organization Management',
            'loyaltyprogram' => 'Loyalty Program Operations',
            'navigation' => 'Booking Navigation & Workflow',
            'seat' => 'Seat Management & Assignment',
            'bundle' => 'Bundle & Package Management',
            'boardingpass' => 'Boarding Pass Operations'
        ];
    }
}
