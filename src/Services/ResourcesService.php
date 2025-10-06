<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\ResourcesInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Resources Service for JamboJet NSK API
 * 
 * Handles all static/reference data including airports, countries, currencies, 
 * configurations, and other system resources
 * Base endpoints: /api/nsk/v{version}/resources, /api/nsk/v{version}/settings
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/resources/stations - Get airports/stations
 * - GET /api/nsk/v1/resources/stations/{stationCode} - Get specific station
 * - GET /api/nsk/v1/resources/stations/{stationCode}/details - Get station details
 * - GET /api/nsk/v1/resources/stations/category/{categoryCode} - Get stations by category
 * - GET /api/nsk/v1/resources/countries - Get countries (v1 - deprecated)
 * - GET /api/nsk/v2/resources/countries - Get countries (v2 - recommended)
 * - GET /api/nsk/v2/resources/countries/{countryCode} - Get specific country
 * - GET /api/nsk/v1/resources/cultures - Get cultures/languages
 * - GET /api/nsk/v1/resources/currencies - Get currencies
 * - GET /api/nsk/v1/resources/equipmentTypes - Get equipment types
 * - GET /api/nsk/v1/resources/fareTypes - Get fare types
 * - GET /api/nsk/v1/resources/passengerTypes - Get passenger types
 * - GET /api/nsk/v1/resources/stationTypes - Get station types
 * - GET /api/nsk/v1/resources/addressTypes - Get address types
 * - GET /api/nsk/v1/resources/bundles/{bundleCode} - Get bundle configuration
 * - GET /api/nsk/v1/resources/bundles/applications - Get bundle applications
 * - GET /api/nsk/v1/resources/bundles/rules - Get bundle rules
 * - GET /api/nsk/v1/settings/user/customerCreation - Get customer creation settings
 * 
 * @package SantosDave\JamboJet\Services
 */
class ResourcesService implements ResourcesInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - Core Resources
    // =================================================================

    /**
     * Get airports
     * 
     * GET /api/nsk/v1/resources/stations
     * Retrieves all airports/stations with optional filtering
     * 
     * @param array $criteria Optional filtering criteria (ActiveOnly, CultureCode, etc.)
     * @return array Collection of airports/stations
     * @throws JamboJetApiException
     */
    public function getAirports(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/stations', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get airports: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get countries
     * 
     * GET /api/nsk/v2/resources/countries
     * Retrieves all countries (recommended v2 endpoint)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of countries
     * @throws JamboJetApiException
     */
    public function getCountries(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/countries', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get countries: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get currencies
     * 
     * GET /api/nsk/v1/resources/currencies
     * Retrieves all available currencies
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of currencies
     * @throws JamboJetApiException
     */
    public function getCurrencies(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/currencies', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get currencies: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific airport/station by code
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}
     * Retrieves detailed information for a specific airport/station
     * 
     * @param string $stationCode Airport/station code (3-letter IATA code)
     * @param array $criteria Optional additional criteria
     * @return array Station details
     * @throws JamboJetApiException
     */
    public function getAirportByCode(string $stationCode, array $criteria = []): array
    {
        $this->validateStationCode($stationCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get airport details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get detailed station information
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}/details
     * Retrieves comprehensive details for a specific station
     * 
     * @param string $stationCode Airport/station code
     * @param array $criteria Optional criteria
     * @return array Detailed station information
     * @throws JamboJetApiException
     */
    public function getStationDetails(string $stationCode, array $criteria = []): array
    {
        $this->validateStationCode($stationCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}/details", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get stations by category
     * 
     * GET /api/nsk/v1/resources/stations/category/{categoryCode}
     * Retrieves stations filtered by category
     * 
     * @param string $categoryCode Station category code
     * @param array $criteria Optional criteria
     * @return array Stations in category
     * @throws JamboJetApiException
     */
    public function getStationsByCategory(string $categoryCode, array $criteria = []): array
    {
        $this->validateCategoryCode($categoryCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/stations/category/{$categoryCode}", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get stations by category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific country by code
     * 
     * GET /api/nsk/v2/resources/countries/{countryCode}
     * Retrieves detailed information for a specific country
     * 
     * @param string $countryCode Country code (2-letter ISO code)
     * @param array $criteria Optional criteria
     * @return array Country details
     * @throws JamboJetApiException
     */
    public function getCountryByCode(string $countryCode, array $criteria = []): array
    {
        $this->validateCountryCode($countryCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v2/resources/countries/{$countryCode}", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get country details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get equipment types
     * 
     * GET /api/nsk/v1/resources/equipmentTypes
     * Retrieves aircraft equipment type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of equipment types
     * @throws JamboJetApiException
     */
    public function getEquipmentTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/equipmentTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get equipment types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare types
     * 
     * GET /api/nsk/v1/resources/fareTypes
     * Retrieves available fare type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of fare types
     * @throws JamboJetApiException
     */
    public function getFareTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/fareTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get passenger types
     * 
     * GET /api/nsk/v1/resources/passengerTypes
     * Retrieves available passenger type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of passenger types
     * @throws JamboJetApiException
     */
    public function getPassengerTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/passengerTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get station types
     * 
     * GET /api/nsk/v1/resources/stationTypes
     * Retrieves station type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of station types
     * @throws JamboJetApiException
     */
    public function getStationTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/stationTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get bundle configuration
     * 
     * GET /api/nsk/v1/resources/bundles/{bundleCode}
     * Retrieves configuration for a specific bundle
     * 
     * @param string $bundleCode Bundle identifier code
     * @param array $criteria Optional criteria
     * @return array Bundle configuration
     * @throws JamboJetApiException
     */
    public function getBundleConfiguration(string $bundleCode, array $criteria = []): array
    {
        $this->validateBundleCode($bundleCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/bundles/{$bundleCode}", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bundle configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get bundle applications
     * 
     * GET /api/nsk/v1/resources/bundles/applications
     * Retrieves available bundle applications
     * 
     * @param array $criteria Optional criteria
     * @return array Bundle applications
     * @throws JamboJetApiException
     */
    public function getBundleApplications(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/bundles/applications', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bundle applications: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Get bundle rules
     * 
     * GET /api/nsk/v1/resources/bundles/rules
     * Retrieves bundle rule configurations
     * 
     * @param array $criteria Optional criteria
     * @return array Bundle rules
     * @throws JamboJetApiException
     */
    public function getBundleRules(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/bundles/rules', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bundle rules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get cultures/languages
     * 
     * GET /api/nsk/v1/resources/cultures
     * Retrieves available cultures and language configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of cultures
     * @throws JamboJetApiException
     */
    public function getCultures(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/cultures', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get cultures: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get customer creation settings
     * 
     * GET /api/nsk/v1/settings/user/customerCreation
     * Retrieves customer creation configuration settings
     * 
     * @param array $criteria Optional criteria
     * @return array Customer creation settings
     * @throws JamboJetApiException
     */
    public function getCustomerCreationSettings(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/settings/user/customerCreation', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get customer creation settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // EXTENDED RESOURCES METHODS - Specific Resource Operations
    // =================================================================

    /**
     * Get specific airport/station
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}
     * Retrieves details for a specific airport/station
     * 
     * @param string $stationCode The station code (IATA code)
     * @param array $params Optional parameters (cultureCode, eTag)
     * @return array Station information
     * @throws JamboJetApiException
     */
    public function getAirport(string $stationCode, array $params = []): array
    {
        if (empty($stationCode)) {
            throw new JamboJetValidationException('Station code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get airport: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get airport/station details
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}/details
     * Retrieves detailed information for a specific airport/station
     * Note: This endpoint is not cached like other resource endpoints
     * 
     * @param string $stationCode The station code (IATA code)
     * @return array Station detailed information
     * @throws JamboJetApiException
     */
    public function getAirportDetails(string $stationCode): array
    {
        if (empty($stationCode)) {
            throw new JamboJetValidationException('Station code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}/details");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get airport details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get airports by category
     * 
     * GET /api/nsk/v1/resources/stations/category/{stationCategoryCode}
     * Retrieves airports filtered by category
     * Note: This endpoint is resource intensive until cached
     * 
     * @param string $categoryCode The station category code
     * @param array $params Optional parameters
     * @return array Stations filtered by category
     * @throws JamboJetApiException
     */
    public function getAirportsByCategory(string $categoryCode, array $params = []): array
    {
        if (empty($categoryCode)) {
            throw new JamboJetValidationException('Category code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/stations/category/{$categoryCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get airports by category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific country
     * 
     * GET /api/nsk/v2/resources/countries/{countryCode} (Recommended)
     * GET /api/nsk/v1/resources/Countries/{countryCode} (Deprecated)
     * Retrieves details for a specific country
     * 
     * @param string $countryCode The country code
     * @param array $params Optional parameters (cultureCode)
     * @param bool $useV2 Whether to use v2 endpoint (recommended)
     * @return array Country information
     * @throws JamboJetApiException
     */
    public function getCountry(string $countryCode, array $params = [], bool $useV2 = true): array
    {
        if (empty($countryCode)) {
            throw new JamboJetValidationException('Country code is required');
        }

        try {
            $endpoint = $useV2
                ? "api/nsk/v2/resources/countries/{$countryCode}"
                : "api/nsk/v1/resources/Countries/{$countryCode}";

            return $this->get($endpoint, $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get country: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific passenger type
     * 
     * GET /api/nsk/v1/resources/PassengerTypes/{passengerTypeCode}
     * Retrieves details for a specific passenger type
     * 
     * @param string $passengerTypeCode The passenger type code
     * @param array $params Optional parameters (cultureCode)
     * @return array Passenger type information
     * @throws JamboJetApiException
     */
    public function getPassengerType(string $passengerTypeCode, array $params = []): array
    {
        if (empty($passengerTypeCode)) {
            throw new JamboJetValidationException('Passenger type code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/PassengerTypes/{$passengerTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // ADDITIONAL RESOURCES METHODS
    // =================================================================

    /**
     * Get address types
     * 
     * GET /api/nsk/v1/resources/addressTypes
     * Retrieves address type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of address types
     * @throws JamboJetApiException
     */
    public function getAddressTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/addressTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get address types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get cities for a country
     * 
     * GET /api/nsk/v1/resources/countries/{countryCode}/cities
     * Retrieves cities for a specific country
     * 
     * @param string $countryCode The country code
     * @param array $params Optional parameters
     * @return array Collection of cities
     * @throws JamboJetApiException
     */
    public function getCitiesForCountry(string $countryCode, array $params = []): array
    {
        if (empty($countryCode)) {
            throw new JamboJetValidationException('Country code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/countries/{$countryCode}/cities", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get cities for country: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get province/states for a country
     * 
     * GET /api/nsk/v1/resources/countries/{countryCode}/provinceStates
     * Retrieves province/states for a specific country
     * 
     * @param string $countryCode The country code
     * @param array $params Optional parameters
     * @return array Collection of province/states
     * @throws JamboJetApiException
     */
    public function getProvinceStatesForCountry(string $countryCode, array $params = []): array
    {
        if (empty($countryCode)) {
            throw new JamboJetValidationException('Country code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/countries/{$countryCode}/provinceStates", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get province/states for country: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fees
     * 
     * GET /api/nsk/v1/resources/fees
     * Retrieves collection of fees
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of fees
     * @throws JamboJetApiException
     */
    public function getFees(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v1/resources/fees', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fees: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get markets
     * 
     * GET /api/nsk/v1/resources/markets
     * Retrieves collection of markets
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of markets
     * @throws JamboJetApiException
     */
    public function getMarkets(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v1/resources/markets', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get markets: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get time zones
     * 
     * GET /api/nsk/v1/resources/timeZones
     * Retrieves collection of time zones
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of time zones
     * @throws JamboJetApiException
     */
    public function getTimeZones(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v1/resources/timeZones', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get time zones: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // SETTINGS METHODS
    // =================================================================

    /**
     * Get general settings
     * 
     * GET /api/nsk/v1/settings/general
     * Retrieves general system settings
     * 
     * @param array $params Optional parameters
     * @return array General settings
     * @throws JamboJetApiException
     */
    public function getGeneralSettings(array $params = []): array
    {
        try {
            return $this->get('api/nsk/v1/settings/general', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get general settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking settings
     * 
     * GET /api/nsk/v1/settings/booking
     * Retrieves booking-related settings
     * 
     * @param array $params Optional parameters
     * @return array Booking settings
     * @throws JamboJetApiException
     */
    public function getBookingSettings(array $params = []): array
    {
        try {
            return $this->get('api/nsk/v1/settings/booking', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment settings
     * 
     * GET /api/nsk/v1/settings/payment
     * Retrieves payment-related settings
     * 
     * @param array $params Optional parameters
     * @return array Payment settings
     * @throws JamboJetApiException
     */
    public function getPaymentSettings(array $params = []): array
    {
        try {
            return $this->get('api/nsk/v1/settings/payment', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // CACHING HELPER METHODS
    // =================================================================

    /**
     * Check resource cache status using ETag
     * 
     * @param string $endpoint The resource endpoint
     * @param string $eTag The cache eTag
     * @param array $params Additional parameters
     * @return array Resource data or cache status
     * @throws JamboJetApiException
     */
    public function checkResourceCache(string $endpoint, string $eTag, array $params = []): array
    {
        if (empty($endpoint)) {
            throw new JamboJetValidationException('Endpoint is required');
        }

        if (!empty($eTag)) {
            $params['ETag'] = $eTag;
        }

        try {
            return $this->get($endpoint, $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to check resource cache: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get paginated resources
     * 
     * Helper method for resources that support pagination
     * 
     * @param string $endpoint The resource endpoint
     * @param int $startIndex Start index for pagination
     * @param int $itemCount Number of items to retrieve
     * @param array $additionalParams Additional parameters
     * @return array Paginated resource data
     * @throws JamboJetApiException
     */
    public function getPaginatedResources(string $endpoint, int $startIndex = 0, int $itemCount = 100, array $additionalParams = []): array
    {
        $params = array_merge($additionalParams, [
            'StartIndex' => $startIndex,
            'ItemCount' => $itemCount
        ]);

        try {
            return $this->get($endpoint, $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get paginated resources: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Get class of services
     * 
     * GET /api/nsk/v1/resources/classOfServices
     * Retrieves class of service configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of class of services
     * @throws JamboJetApiException
     */
    public function getClassOfServices(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/classOfServices', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get class of services: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific class of service
     * 
     * GET /api/nsk/v1/resources/classOfServices/{classOfServiceCode}
     * Retrieves specific class of service configuration
     * 
     * @param string $classOfServiceCode Class of service code
     * @param array $criteria Optional criteria
     * @return array Class of service details
     * @throws JamboJetApiException
     */
    public function getClassOfServiceByCode(string $classOfServiceCode, array $criteria = []): array
    {
        $this->validateClassOfServiceCode($classOfServiceCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/classOfServices/{$classOfServiceCode}", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get class of service details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get customer programs
     * 
     * GET /api/nsk/v1/resources/customerPrograms
     * Retrieves customer loyalty program configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Collection of customer programs
     * @throws JamboJetApiException
     */
    public function getCustomerPrograms(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/customerPrograms', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get customer programs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get customer program levels
     * 
     * GET /api/nsk/v1/resources/customerPrograms/{programCode}/levels
     * Retrieves levels for a specific customer program
     * 
     * @param string $programCode Program code
     * @param array $criteria Optional criteria
     * @return array Program levels
     * @throws JamboJetApiException
     */
    public function getCustomerProgramLevels(string $programCode, array $criteria = []): array
    {
        $this->validateProgramCode($programCode);
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/resources/customerPrograms/{$programCode}/levels", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get customer program levels: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate resource criteria for filtering and pagination
     * 
     * @param array $criteria Resource criteria
     * @throws JamboJetValidationException
     */
    private function validateResourceCriteria(array $criteria): void
    {
        // Validate common pagination parameters
        if (isset($criteria['StartIndex'])) {
            $this->validateNumericRanges($criteria, ['StartIndex' => ['min' => 0, 'max' => 100000]]);

            if (!is_int($criteria['StartIndex'])) {
                throw new JamboJetValidationException(
                    'StartIndex must be an integer',
                    400
                );
            }
        }

        if (isset($criteria['ItemCount'])) {
            $this->validateNumericRanges($criteria, ['ItemCount' => ['min' => 1, 'max' => 1000]]);

            if (!is_int($criteria['ItemCount'])) {
                throw new JamboJetValidationException(
                    'ItemCount must be an integer',
                    400
                );
            }
        }

        // Validate active filter
        if (isset($criteria['ActiveOnly'])) {
            if (!is_bool($criteria['ActiveOnly'])) {
                throw new JamboJetValidationException(
                    'ActiveOnly must be a boolean value',
                    400
                );
            }
        }

        // Validate culture code
        if (isset($criteria['CultureCode'])) {
            $this->validateCultureCodeFormat($criteria['CultureCode']);
        }

        // Validate ETag
        if (isset($criteria['ETag'])) {
            $this->validateStringLengths($criteria, ['ETag' => ['max' => 100]]);
        }

        // Validate date filters
        if (isset($criteria['modifiedAfter'])) {
            $this->validateFormats($criteria, ['modifiedAfter' => 'datetime']);
        }

        if (isset($criteria['modifiedBefore'])) {
            $this->validateFormats($criteria, ['modifiedBefore' => 'datetime']);

            // If both dates provided, ensure logical order
            if (isset($criteria['modifiedAfter'])) {
                $after = new \DateTime($criteria['modifiedAfter']);
                $before = new \DateTime($criteria['modifiedBefore']);

                if ($before <= $after) {
                    throw new JamboJetValidationException(
                        'modifiedBefore must be after modifiedAfter',
                        400
                    );
                }
            }
        }

        // Validate search filters
        if (isset($criteria['searchText'])) {
            $this->validateStringLengths($criteria, ['searchText' => ['min' => 2, 'max' => 100]]);
        }

        if (isset($criteria['nameFilter'])) {
            $this->validateStringLengths($criteria, ['nameFilter' => ['min' => 2, 'max' => 50]]);
        }

        // Validate sorting parameters
        if (isset($criteria['sortBy'])) {
            $this->validateSortField($criteria['sortBy']);
        }

        if (isset($criteria['sortOrder'])) {
            $this->validateSortOrder($criteria['sortOrder']);
        }

        // Validate region filters
        if (isset($criteria['regionCode'])) {
            $this->validateRegionCode($criteria['regionCode']);
        }

        if (isset($criteria['countryCode'])) {
            $this->validateFormats($criteria, ['countryCode' => 'country_code']);
        }

        // Validate type filters
        if (isset($criteria['typeFilter'])) {
            $this->validateTypeFilter($criteria['typeFilter']);
        }

        // Validate status filters
        if (isset($criteria['statusFilter'])) {
            $this->validateStatusFilter($criteria['statusFilter']);
        }
    }

    /**
     * Validate station/airport code format
     * 
     * @param string $stationCode Station code
     * @throws JamboJetValidationException
     */
    private function validateStationCode(string $stationCode): void
    {
        if (empty(trim($stationCode))) {
            throw new JamboJetValidationException(
                'Station code is required',
                400
            );
        }

        // Station codes are typically 3-character IATA codes
        if (!preg_match('/^[A-Z]{3}$/', $stationCode)) {
            throw new JamboJetValidationException(
                'Invalid station code format. Expected 3-letter IATA code',
                400
            );
        }
    }

    /**
     * Validate country code format
     * 
     * @param string $countryCode Country code
     * @throws JamboJetValidationException
     */
    private function validateCountryCode(string $countryCode): void
    {
        if (empty(trim($countryCode))) {
            throw new JamboJetValidationException(
                'Country code is required',
                400
            );
        }

        // Country codes can be 2 or 3 characters (ISO)
        if (!preg_match('/^[A-Z]{2,3}$/', $countryCode)) {
            throw new JamboJetValidationException(
                'Invalid country code format. Expected 2-3 letter ISO code',
                400
            );
        }
    }

    /**
     * Validate category code format
     * 
     * @param string $categoryCode Category code
     * @throws JamboJetValidationException
     */
    private function validateCategoryCode(string $categoryCode): void
    {
        if (empty(trim($categoryCode))) {
            throw new JamboJetValidationException(
                'Category code is required',
                400
            );
        }

        // Category codes are typically short alphanumeric codes
        if (!preg_match('/^[A-Z0-9]{1,10}$/', $categoryCode)) {
            throw new JamboJetValidationException(
                'Invalid category code format. Expected 1-10 character alphanumeric code',
                400
            );
        }
    }

    /**
     * Validate bundle code format
     * 
     * @param string $bundleCode Bundle code
     * @throws JamboJetValidationException
     */
    private function validateBundleCode(string $bundleCode): void
    {
        if (empty(trim($bundleCode))) {
            throw new JamboJetValidationException(
                'Bundle code is required',
                400
            );
        }

        // Bundle codes are typically alphanumeric
        if (!preg_match('/^[A-Z0-9_]{1,20}$/', $bundleCode)) {
            throw new JamboJetValidationException(
                'Invalid bundle code format. Expected 1-20 character alphanumeric code',
                400
            );
        }
    }

    /**
     * Validate class of service code format
     * 
     * @param string $classOfServiceCode Class of service code
     * @throws JamboJetValidationException
     */
    private function validateClassOfServiceCode(string $classOfServiceCode): void
    {
        if (empty(trim($classOfServiceCode))) {
            throw new JamboJetValidationException(
                'Class of service code is required',
                400
            );
        }

        // Class of service codes are typically 1-2 characters
        if (!preg_match('/^[A-Z]{1,2}$/', $classOfServiceCode)) {
            throw new JamboJetValidationException(
                'Invalid class of service code format. Expected 1-2 letter code',
                400
            );
        }
    }

    /**
     * Validate program code format
     * 
     * @param string $programCode Program code
     * @throws JamboJetValidationException
     */
    private function validateProgramCode(string $programCode): void
    {
        if (empty(trim($programCode))) {
            throw new JamboJetValidationException(
                'Program code is required',
                400
            );
        }

        // Program codes are typically short alphanumeric codes
        if (!preg_match('/^[A-Z0-9]{1,10}$/', $programCode)) {
            throw new JamboJetValidationException(
                'Invalid program code format. Expected 1-10 character alphanumeric code',
                400
            );
        }
    }

    /**
     * Validate culture code format
     * 
     * @param string $cultureCode Culture code
     * @throws JamboJetValidationException
     */
    private function validateCultureCodeFormat(string $cultureCode): void
    {
        // Culture codes can be various formats: en, en-US, en-GB, etc.
        if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?(-[A-Z0-9]{1,8})?$/', $cultureCode)) {
            throw new JamboJetValidationException(
                'Invalid culture code format. Expected format: en, en-US, or en-US-variant',
                400
            );
        }

        // Validate length
        if (strlen($cultureCode) > 17) {
            throw new JamboJetValidationException(
                'Culture code cannot exceed 17 characters',
                400
            );
        }
    }

    /**
     * Validate sort field
     * 
     * @param string $sortField Sort field
     * @throws JamboJetValidationException
     */
    private function validateSortField(string $sortField): void
    {
        $validSortFields = [
            'code',
            'name',
            'description',
            'type',
            'status',
            'category',
            'createdDate',
            'modifiedDate',
            'rank',
            'sequence'
        ];

        if (!in_array($sortField, $validSortFields)) {
            throw new JamboJetValidationException(
                'Invalid sort field. Expected one of: ' . implode(', ', $validSortFields),
                400
            );
        }
    }

    /**
     * Validate sort order
     * 
     * @param string $sortOrder Sort order
     * @throws JamboJetValidationException
     */
    private function validateSortOrder(string $sortOrder): void
    {
        $validOrders = ['asc', 'desc', 'ASC', 'DESC'];

        if (!in_array($sortOrder, $validOrders)) {
            throw new JamboJetValidationException(
                'Invalid sort order. Expected: asc, desc, ASC, or DESC',
                400
            );
        }
    }

    /**
     * Validate region code
     * 
     * @param string $regionCode Region code
     * @throws JamboJetValidationException
     */
    private function validateRegionCode(string $regionCode): void
    {
        // Region codes are typically short alphanumeric codes
        if (!preg_match('/^[A-Z0-9]{1,10}$/', $regionCode)) {
            throw new JamboJetValidationException(
                'Invalid region code format. Expected 1-10 character alphanumeric code',
                400
            );
        }
    }

    /**
     * Validate type filter
     * 
     * @param string $typeFilter Type filter
     * @throws JamboJetValidationException
     */
    private function validateTypeFilter(string $typeFilter): void
    {
        $validTypes = [
            'Airport',
            'Station',
            'City',
            'Country',
            'Region',
            'Terminal',
            'Domestic',
            'International',
            'Hub',
            'Focus',
            'Cargo',
            'Military'
        ];

        if (!in_array($typeFilter, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid type filter. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate status filter
     * 
     * @param string $statusFilter Status filter
     * @throws JamboJetValidationException
     */
    private function validateStatusFilter(string $statusFilter): void
    {
        $validStatuses = [
            'Active',
            'Inactive',
            'Pending',
            'Suspended',
            'Deprecated',
            'Available',
            'Unavailable',
            'Maintenance',
            'Restricted'
        ];

        if (!in_array($statusFilter, $validStatuses)) {
            throw new JamboJetValidationException(
                'Invalid status filter. Expected one of: ' . implode(', ', $validStatuses),
                400
            );
        }
    }

    // =================================================================
    // SPECIALIZED VALIDATION METHODS
    // =================================================================

    /**
     * Validate equipment type criteria
     * 
     * @param array $criteria Equipment type criteria
     * @throws JamboJetValidationException
     */
    private function validateEquipmentTypeCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for equipment-specific filters
        if (isset($criteria['manufacturer'])) {
            $validManufacturers = ['Boeing', 'Airbus', 'Embraer', 'Bombardier', 'ATR', 'Other'];
            if (!in_array($criteria['manufacturer'], $validManufacturers)) {
                throw new JamboJetValidationException(
                    'Invalid manufacturer filter. Expected one of: ' . implode(', ', $validManufacturers),
                    400
                );
            }
        }

        if (isset($criteria['capacity'])) {
            $this->validateNumericRanges($criteria, ['capacity' => ['min' => 1, 'max' => 1000]]);
        }

        if (isset($criteria['range'])) {
            $this->validateNumericRanges($criteria, ['range' => ['min' => 100, 'max' => 20000]]);
        }
    }

    /**
     * Validate passenger type criteria
     * 
     * @param array $criteria Passenger type criteria
     * @throws JamboJetValidationException
     */
    private function validatePassengerTypeCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for passenger type filters
        if (isset($criteria['ageGroup'])) {
            $validAgeGroups = ['Adult', 'Child', 'Infant', 'Senior', 'Youth'];
            if (!in_array($criteria['ageGroup'], $validAgeGroups)) {
                throw new JamboJetValidationException(
                    'Invalid age group filter. Expected one of: ' . implode(', ', $validAgeGroups),
                    400
                );
            }
        }

        if (isset($criteria['discountEligible'])) {
            if (!is_bool($criteria['discountEligible'])) {
                throw new JamboJetValidationException(
                    'discountEligible must be a boolean value',
                    400
                );
            }
        }
    }

    /**
     * Validate fare type criteria
     * 
     * @param array $criteria Fare type criteria
     * @throws JamboJetValidationException
     */
    private function validateFareTypeCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for fare type filters
        if (isset($criteria['fareCategory'])) {
            $validCategories = ['Published', 'Private', 'Corporate', 'Promotional', 'Group'];
            if (!in_array($criteria['fareCategory'], $validCategories)) {
                throw new JamboJetValidationException(
                    'Invalid fare category filter. Expected one of: ' . implode(', ', $validCategories),
                    400
                );
            }
        }

        if (isset($criteria['refundable'])) {
            if (!is_bool($criteria['refundable'])) {
                throw new JamboJetValidationException(
                    'refundable must be a boolean value',
                    400
                );
            }
        }

        if (isset($criteria['changeable'])) {
            if (!is_bool($criteria['changeable'])) {
                throw new JamboJetValidationException(
                    'changeable must be a boolean value',
                    400
                );
            }
        }
    }

    /**
     * Validate currency criteria
     * 
     * @param array $criteria Currency criteria
     * @throws JamboJetValidationException
     */
    private function validateCurrencyCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for currency filters
        if (isset($criteria['majorCurrencies'])) {
            if (!is_bool($criteria['majorCurrencies'])) {
                throw new JamboJetValidationException(
                    'majorCurrencies must be a boolean value',
                    400
                );
            }
        }

        if (isset($criteria['cryptoCurrency'])) {
            if (!is_bool($criteria['cryptoCurrency'])) {
                throw new JamboJetValidationException(
                    'cryptoCurrency must be a boolean value',
                    400
                );
            }
        }

        if (isset($criteria['baseCurrency'])) {
            $this->validateFormats($criteria, ['baseCurrency' => 'currency_code']);
        }
    }

    /**
     * Validate station criteria
     * 
     * @param array $criteria Station criteria
     * @throws JamboJetValidationException
     */
    private function validateStationCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for station filters
        if (isset($criteria['stationType'])) {
            $validStationTypes = ['Airport', 'Seaport', 'Railway', 'Bus', 'Multi'];
            if (!in_array($criteria['stationType'], $validStationTypes)) {
                throw new JamboJetValidationException(
                    'Invalid station type filter. Expected one of: ' . implode(', ', $validStationTypes),
                    400
                );
            }
        }

        if (isset($criteria['hasCustoms'])) {
            if (!is_bool($criteria['hasCustoms'])) {
                throw new JamboJetValidationException(
                    'hasCustoms must be a boolean value',
                    400
                );
            }
        }

        if (isset($criteria['hasImmigration'])) {
            if (!is_bool($criteria['hasImmigration'])) {
                throw new JamboJetValidationException(
                    'hasImmigration must be a boolean value',
                    400
                );
            }
        }

        if (isset($criteria['timezone'])) {
            $validTimezones = timezone_identifiers_list();
            if (!in_array($criteria['timezone'], $validTimezones)) {
                throw new JamboJetValidationException(
                    'Invalid timezone',
                    400
                );
            }
        }
    }

    /**
     * Validate bundle criteria
     * 
     * @param array $criteria Bundle criteria
     * @throws JamboJetValidationException
     */
    private function validateBundleCriteria(array $criteria): void
    {
        $this->validateResourceCriteria($criteria);

        // Additional validation for bundle filters
        if (isset($criteria['bundleType'])) {
            $validBundleTypes = ['Fare', 'Service', 'Product', 'Ancillary', 'Package'];
            if (!in_array($criteria['bundleType'], $validBundleTypes)) {
                throw new JamboJetValidationException(
                    'Invalid bundle type filter. Expected one of: ' . implode(', ', $validBundleTypes),
                    400
                );
            }
        }

        if (isset($criteria['applicableRoutes'])) {
            if (!is_array($criteria['applicableRoutes'])) {
                throw new JamboJetValidationException(
                    'applicableRoutes must be an array',
                    400
                );
            }

            foreach ($criteria['applicableRoutes'] as $route) {
                if (!preg_match('/^[A-Z]{3}-[A-Z]{3}$/', $route)) {
                    throw new JamboJetValidationException(
                        'Invalid route format in applicableRoutes. Expected format: XXX-YYY',
                        400
                    );
                }
            }
        }

        if (isset($criteria['effective'])) {
            if (!is_bool($criteria['effective'])) {
                throw new JamboJetValidationException(
                    'effective must be a boolean value',
                    400
                );
            }
        }
    }
}
