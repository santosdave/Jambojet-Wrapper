# JamboJet Laravel API Wrapper

A comprehensive Laravel package for integrating with JamboJet's NSK API (New Skies 4.2.1.252). This package provides a clean, Laravel-friendly interface for flight booking, payment processing, and account management.

## Features

- ✅ Complete flight search and availability checking
- ✅ Booking creation, modification, and cancellation
- ✅ Payment processing with multiple payment methods
- ✅ User and account management
- ✅ Add-on services (seats, baggage, insurance)
- ✅ Automatic token management and caching
- ✅ Comprehensive validation and error handling
- ✅ Support for Laravel 9, 10, and 11

## Installation

Install via Composer:

```bash
composer require santosdave/jambojet-laravel
```

## Configuration

### 1. Publish Configuration File

```bash
php artisan vendor:publish --provider="SantosDave\JamboJet\JamboJetServiceProvider"
```

### 2. Configure Environment Variables

Add these to your `.env` file:

```env
JAMBOJET_BASE_URL=https://jmtest.booking.jambojet.com/jm/dotrez/
JAMBOJET_SUBSCRIPTION_KEY=your-subscription-key-here
JAMBOJET_TIMEOUT=30
JAMBOJET_RETRY_ATTEMPTS=3
JAMBOJET_CACHE_ENABLED=true
JAMBOJET_LOG_REQUESTS=false
```

### 3. Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Quick Start

### Authentication

```php
use SantosDave\JamboJet\Facades\JamboJet;

// Create authentication token
$response = JamboJet::auth()->createToken([
    'userName' => 'your-username',
    'password' => 'your-password',
    'domain' => 'your-domain',
    'channelType' => 'Api'
]);

// Token is automatically stored and used for subsequent requests
```

### Search Flights

```php
$flights = JamboJet::availability()->searchSimple([
    'origin' => 'NBO',
    'destination' => 'MBA',
    'beginDate' => '2025-04-15T00:00:00',
    'passengers' => [
        'types' => [
            ['type' => 'ADT', 'count' => 2]
        ],
        'residentCountry' => 'KE'
    ],
    'codes' => [
        'currencyCode' => 'KES'
    ]
]);
```

### Create Booking

```php
$booking = JamboJet::booking()->create([
    'journeys' => [
        'keys' => [
            [
                'journeyKey' => 'journey-key-from-search',
                'fareAvailabilityKey' => 'fare-key-from-search'
            ]
        ]
    ],
    'passengers' => [
        [
            'passenger' => [
                'passengerTypeCode' => 'ADT',
                'name' => [
                    'first' => 'JOHN',
                    'last' => 'DOE',
                    'title' => 'MR'
                ],
                'info' => [
                    'gender' => 'Male',
                    'dateOfBirth' => '1990-01-01'
                ]
            ]
        ]
    ],
    'contact' => [
        'emailAddress' => 'john.doe@example.com',
        'phoneNumbers' => [
            ['type' => 'Mobile', 'number' => '+254712345678']
        ]
    ],
    'currencyCode' => 'KES'
]);
```

### Process Payment

```php
$payment = JamboJet::payment()->processPayment([
    'paymentMethodCode' => 'AG',
    'amount' => 15000.00,
    'currencyCode' => 'KES',
    'paymentFields' => [
        'ACCTNO' => 'your-account-number'
    ]
]);
```

## Available Services

### Authentication Service

```php
JamboJet::auth()->createToken($credentials);
JamboJet::auth()->refreshToken();
JamboJet::auth()->getTokenInfo();
```

### Availability Service

```php
JamboJet::availability()->search($criteria);
JamboJet::availability()->searchSimple($criteria);
JamboJet::availability()->getLowestFares($criteria);
```

### Booking Service

```php
JamboJet::booking()->create($data);
JamboJet::booking()->get();
JamboJet::booking()->commit($options);
JamboJet::booking()->cancel($recordLocator);
```

### Payment Service

```php
JamboJet::payment()->processPayment($data);
JamboJet::payment()->getPaymentMethods();
JamboJet::payment()->processRefund($data);
```

### Add-ons Service

```php
JamboJet::addOns()->addSeatAssignment($data);
JamboJet::addOns()->addBaggage($data);
JamboJet::addOns()->addInsurance($data);
```

### Resources Service

```php
JamboJet::resources()->getCities();
JamboJet::resources()->getCountries();
JamboJet::resources()->getAirports();
```

### User Service

```php
JamboJet::user()->createUser($data);
JamboJet::user()->getCurrentUser();
JamboJet::user()->updateUser($data);
```

### Account Service

```php
JamboJet::account()->getAccount($accountNumber);
JamboJet::account()->getCustomerCredit();
JamboJet::account()->addTransaction($data);
```

## Error Handling

The package provides custom exceptions for different error scenarios:

```php
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetAuthenticationException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

try {
    $flights = JamboJet::availability()->searchSimple($criteria);
} catch (JamboJetAuthenticationException $e) {
    // Handle authentication errors (401)
    Log::error('Authentication failed: ' . $e->getMessage());
} catch (JamboJetValidationException $e) {
    // Handle validation errors (400)
    Log::error('Validation failed: ' . $e->getMessage());
    $errors = $e->getValidationErrors();
} catch (JamboJetApiException $e) {
    // Handle general API errors
    Log::error('API error: ' . $e->getMessage());
}
```

## Configuration Options

The `config/jambojet.php` file provides these options:

```php
return [
    'base_url' => env('JAMBOJET_BASE_URL'),
    'subscription_key' => env('JAMBOJET_SUBSCRIPTION_KEY'),
    'timeout' => env('JAMBOJET_TIMEOUT', 30),
    'retry_attempts' => env('JAMBOJET_RETRY_ATTEMPTS', 3),
    'environment' => env('JAMBOJET_ENVIRONMENT', 'test'),

    'cache' => [
        'enabled' => env('JAMBOJET_CACHE_ENABLED', true),
        'ttl' => env('JAMBOJET_CACHE_TTL', 3600),
        'prefix' => env('JAMBOJET_CACHE_PREFIX', 'jambojet_'),
    ],

    'logging' => [
        'enabled' => env('JAMBOJET_LOG_REQUESTS', false),
        'channel' => env('JAMBOJET_LOG_CHANNEL', 'stack'),
    ],
];
```

## Testing

The package includes comprehensive validation for all API requests. To test your integration:

```bash
# Create a test route
php artisan make:controller JamboJetTestController

# Add test methods
# Run your tests
```

## Requirements

- PHP 8.0 or higher
- Laravel 9.0, 10.0, or 11.0
- GuzzleHTTP 7.0+
- Valid JamboJet API credentials

## Support

For issues, questions, or feature requests:

- GitHub Issues: [https://github.com/santosdave/jambojet-laravel/issues](https://github.com/santosdave/jambojet-laravel/issues)
- Documentation: [Link to full documentation]

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the MIT license.

## Credits

Developed and maintained by Santos Dave.

## Security

If you discover any security-related issues, please email santosdave86@gmail.com instead of using the issue tracker.
