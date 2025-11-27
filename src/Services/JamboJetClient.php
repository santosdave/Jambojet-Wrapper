<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Contracts\AvailabilityInterface;
use SantosDave\JamboJet\Contracts\BookingInterface;
use SantosDave\JamboJet\Contracts\PaymentInterface;
use SantosDave\JamboJet\Contracts\UserInterface;
use SantosDave\JamboJet\Contracts\AccountInterface;
use SantosDave\JamboJet\Contracts\AddOnsInterface;
use SantosDave\JamboJet\Contracts\ApoInterface;
use SantosDave\JamboJet\Contracts\ResourcesInterface;
use SantosDave\JamboJet\Contracts\OrganizationInterface;
use SantosDave\JamboJet\Contracts\LoyaltyProgramInterface;
use SantosDave\JamboJet\Contracts\NavigationInterface;
use SantosDave\JamboJet\Contracts\SeatInterface;
use SantosDave\JamboJet\Contracts\BundleInterface;
use SantosDave\JamboJet\Contracts\BoardingPassInterface;
use SantosDave\JamboJet\Contracts\CollectionInterface;
use SantosDave\JamboJet\Contracts\TokenManagerInterface;
use SantosDave\JamboJet\Contracts\CoreInterface;
use SantosDave\JamboJet\Contracts\CurrencyInterface;
use SantosDave\JamboJet\Contracts\EquipmentInterface;
use SantosDave\JamboJet\Contracts\ETicketInterface;
use SantosDave\JamboJet\Contracts\SettingsInterface;
use SantosDave\JamboJet\Contracts\InventoryInterface;
use SantosDave\JamboJet\Contracts\ManifestInterface;
use SantosDave\JamboJet\Contracts\MessageInterface;
use SantosDave\JamboJet\Contracts\OneTimeTravelNotificationInterface;
use SantosDave\JamboJet\Contracts\PersonInterface;
use SantosDave\JamboJet\Contracts\QueueInterface;
use SantosDave\JamboJet\Contracts\TripInterface;
use SantosDave\JamboJet\Contracts\VoucherInterface;

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
    // EXTENDED API MODULES 
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

    /**
     * Inventory Management Operations
     * Handles: /api/dcs/v1/inventory, /api/dcs/v2/inventory endpoints
     * 
     * @return InventoryInterface
     */
    public function inventory(): InventoryInterface
    {
        return app(InventoryInterface::class);
    }

    /**
     * Booking Queue Operations
     * Handles: /api/nsk/v1/queues/bookings, /api/nsk/v2/queues/bookings endpoints
     * 
     * @return QueueInterface
     */
    public function queue(): QueueInterface
    {
        return app(QueueInterface::class);
    }

    /**
     * Trip Management Operations
     * Handles: /api/nsk/v1/trips endpoints
     * 
     * @return TripInterface
     */
    public function trip(): TripInterface
    {
        return app(TripInterface::class);
    }

    /**
     * Manifest Operations
     * Handles: /api/nsk/v1/manifest, /api/nsk/v2/manifest endpoints
     * 
     * @return ManifestInterface
     */
    public function manifest(): ManifestInterface
    {
        return app(ManifestInterface::class);
    }

    /**
     * Voucher Operations
     * Handles: /api/nsk/v1/vouchers endpoints
     * 
     * @return VoucherInterface
     */
    public function voucher(): VoucherInterface
    {
        return app(VoucherInterface::class);
    }

    /**
     * Settings Operations
     * Handles: /api/nsk/v1/settings, /api/nsk/v2/settings endpoints
     * 
     * @return SettingsInterface
     */
    public function settings(): SettingsInterface
    {
        return app(SettingsInterface::class);
    }

    /**
     * Token Manager Operations
     * Handles token lifecycle management and refresh operations
     * 
     * @return TokenManagerInterface
     */
    public function tokenManager(): TokenManagerInterface
    {
        return app(TokenManagerInterface::class);
    }

    /**
     * Currency Operations
     * Handles: /api/nsk/v1/currency endpoints
     * 
     * @return CurrencyInterface
     */
    public function currency(): CurrencyInterface
    {
        return app(CurrencyInterface::class);
    }

    /**
     * E-Ticket Operations
     * Handles: /api/nsk/v1/etickets endpoints
     * 
     * @return ETicketInterface
     */
    public function eTicket(): ETicketInterface
    {
        return app(ETicketInterface::class);
    }

    /**
     * Equipment Operations
     * Handles: /api/nsk/v1/equipment endpoints
     * 
     * @return EquipmentInterface
     */
    public function equipment(): EquipmentInterface
    {
        return app(EquipmentInterface::class);
    }

    /**
     * Collection Operations
     * Handles: /api/nsk/v1/collections endpoints
     * 
     * @return CollectionInterface
     */
    public function collection(): CollectionInterface
    {
        return app(CollectionInterface::class);
    }

    // APO 
    public function apo(): ApoInterface
    {
        return app(ApoInterface::class);
    }

    // One-Time Travel Notification Service
    public function oneTimeTravelNotification(): OneTimeTravelNotificationInterface
    {
        return app(OneTimeTravelNotificationInterface::class);
    }

    // Person Service
    public function person(): PersonInterface
    {
        return app(PersonInterface::class);
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
            'boardingpass',
            'message',
            'inventory',
            'queue',
            'trip',
            'manifest',
            'voucher',
            'settings',

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
