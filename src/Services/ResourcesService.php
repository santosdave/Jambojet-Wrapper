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
//  SSR RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get all SSRs (Special Service Requests)
     * 
     * GET /api/nsk/v1/resources/Ssrs
     * Retrieves collection of SSR (Special Service Request) configurations
     * Used for baggage, meals, seats, assistance, and other ancillary services
     * 
     * @param array $criteria Optional filtering (ActiveOnly, CultureCode, ETag, StartIndex, ItemCount)
     * @return array SSR collection
     * @throws JamboJetApiException
     */
    public function getSsrs(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/Ssrs', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSRs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR by code
     * 
     * GET /api/nsk/v1/resources/Ssrs/{ssrCode}
     * Retrieves details for a specific SSR including fee codes, limits, and restrictions
     * 
     * @param string $ssrCode SSR code (4 chars max, e.g., 'BAGX', 'WCHR', 'PETC')
     * @param string|null $cultureCode Optional culture code for localization
     * @return array SSR details
     * @throws JamboJetApiException
     */
    public function getSsr(string $ssrCode, ?string $cultureCode = null): array
    {
        $this->validateSsrCode($ssrCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/Ssrs/{$ssrCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all SSR groups
     * 
     * GET /api/nsk/v1/resources/SsrGroups
     * Retrieves SSR groups (e.g., Baggage, Meals, Special Assistance)
     * Used for categorizing and organizing SSRs
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR groups collection
     * @throws JamboJetApiException
     */
    public function getSsrGroups(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrGroups', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR groups: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR group
     * 
     * GET /api/nsk/v1/resources/SsrGroups/{ssrGroupCode}
     * Retrieves details for a specific SSR group
     * 
     * @param string $ssrGroupCode SSR group code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR group details
     * @throws JamboJetApiException
     */
    public function getSsrGroup(string $ssrGroupCode, ?string $cultureCode = null): array
    {
        if (empty($ssrGroupCode)) {
            throw new JamboJetValidationException('SSR group code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/SsrGroups/{$ssrGroupCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all SSR nests
     * 
     * GET /api/nsk/v1/resources/SsrNests
     * SSR nests define inventory limits for SSRs (max quantity per flight)
     * Critical for managing capacity of services like extra baggage, special meals
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR nests collection
     * @throws JamboJetApiException
     */
    public function getSsrNests(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrNests', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR nests: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR nest
     * 
     * GET /api/nsk/v1/resources/SsrNests/{ssrNestCode}
     * Retrieves capacity and inventory configuration for a specific SSR nest
     * 
     * @param string $ssrNestCode SSR nest code (4 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array SSR nest details including LID (inventory limits)
     * @throws JamboJetApiException
     */
    public function getSsrNest(string $ssrNestCode, ?string $cultureCode = null): array
    {
        $this->validateSsrNestCode($ssrNestCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/SsrNests/{$ssrNestCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR nest: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR restriction results
     * 
     * GET /api/nsk/v1/resources/SsrRestrictionResults
     * Defines what happens when SSR restrictions are evaluated
     * (e.g., Allow, Deny, Warning messages)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR restriction results collection
     * @throws JamboJetApiException
     */
    public function getSsrRestrictionResults(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrRestrictionResults', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR restriction results: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR restriction result
     * 
     * GET /api/nsk/v1/resources/SsrRestrictionResults/{ssrRestrictionResultCode}
     * 
     * @param string $ssrRestrictionResultCode Restriction result code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR restriction result details
     * @throws JamboJetApiException
     */
    public function getSsrRestrictionResult(string $ssrRestrictionResultCode, ?string $cultureCode = null): array
    {
        if (empty($ssrRestrictionResultCode)) {
            throw new JamboJetValidationException('SSR restriction result code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/SsrRestrictionResults/{$ssrRestrictionResultCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR restriction result: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR types
     * 
     * GET /api/nsk/v1/resources/SsrTypes
     * Retrieves SSR type classifications (e.g., Service, Merchandise, Seat)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR types collection
     * @throws JamboJetApiException
     */
    public function getSsrTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR availability types
     * 
     * GET /api/nsk/v1/resources/SsrAvailabilityTypes
     * Defines how SSR availability is calculated (e.g., By Flight, By Nest)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR availability types collection
     * @throws JamboJetApiException
     */
    public function getSsrAvailabilityTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrAvailabilityTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR availability types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR leg restrictions
     * 
     * GET /api/nsk/v1/resources/SsrLegRestrictions
     * Retrieves leg-level SSR restrictions (route-specific rules)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR leg restrictions collection
     * @throws JamboJetApiException
     */
    public function getSsrLegRestrictions(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SsrLegRestrictions', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR leg restrictions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSRs (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/ssrs
     * V2 version with enhanced data structure
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSRs collection (v2)
     * @throws JamboJetApiException
     */
    public function getSsrsV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/ssrs', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSRs (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/ssrs/{ssrCode}
     * 
     * @param string $ssrCode SSR code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR details (v2)
     * @throws JamboJetApiException
     */
    public function getSsrV2(string $ssrCode, ?string $cultureCode = null): array
    {
        $this->validateSsrCode($ssrCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/ssrs/{$ssrCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR groups (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/ssrGroups
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR groups collection (v2)
     * @throws JamboJetApiException
     */
    public function getSsrGroupsV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/ssrGroups', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR groups (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR group (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/ssrGroups/{ssrGroupCode}
     * 
     * @param string $ssrGroupCode SSR group code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR group details (v2)
     * @throws JamboJetApiException
     */
    public function getSsrGroupV2(string $ssrGroupCode, ?string $cultureCode = null): array
    {
        if (empty($ssrGroupCode)) {
            throw new JamboJetValidationException('SSR group code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/ssrGroups/{$ssrGroupCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR group (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  CARRIER & EXTERNAL SYSTEM RESOURCES
// =================================================================

    /**
     * Get carriers
     * 
     * GET /api/nsk/v1/resources/carriers
     * Retrieves all airline carrier configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Carriers collection
     * @throws JamboJetApiException
     */
    public function getCarriers(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/carriers', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get carriers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific carrier
     * 
     * GET /api/nsk/v1/resources/carriers/{carrierCode}
     * Retrieves details for a specific airline carrier
     * 
     * @param string $carrierCode Carrier code (2 chars, IATA airline code)
     * @param string|null $cultureCode Optional culture code
     * @return array Carrier details
     * @throws JamboJetApiException
     */
    public function getCarrier(string $carrierCode, ?string $cultureCode = null): array
    {
        $this->validateCarrierCode($carrierCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/carriers/{$carrierCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get carrier: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get external systems
     * 
     * GET /api/nsk/v1/resources/ExternalSystems
     * Retrieves external system integration configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array External systems collection
     * @throws JamboJetApiException
     */
    public function getExternalSystems(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/ExternalSystems', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get external systems: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific external system
     * 
     * GET /api/nsk/v1/resources/ExternalSystems/{externalSystemCode}
     * Retrieves details for a specific external system integration
     * 
     * @param string $externalSystemCode External system code
     * @param string|null $cultureCode Optional culture code
     * @return array External system details
     * @throws JamboJetApiException
     */
    public function getExternalSystem(string $externalSystemCode, ?string $cultureCode = null): array
    {
        if (empty($externalSystemCode)) {
            throw new JamboJetValidationException('External system code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/ExternalSystems/{$externalSystemCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get external system: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR types (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/ssrTypes
     * V2 version with enhanced SSR type data
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR types collection (v2)
     * @throws JamboJetApiException
     */
    public function getSsrTypesV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/ssrTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR types (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
//  PERSON & COMMUNICATION RESOURCES
// =================================================================

    /**
     * Get person information types
     * 
     * GET /api/nsk/v1/resources/PersonInformationTypes
     * Retrieves types of person information that can be collected
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Person information types collection
     * @throws JamboJetApiException
     */
    public function getPersonInformationTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/PersonInformationTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person information types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get notification delivery methods
     * 
     * GET /api/nsk/v1/resources/NotificationDeliveryMethods
     * Retrieves available notification delivery methods (Email, SMS, etc.)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Notification delivery methods collection
     * @throws JamboJetApiException
     */
    public function getNotificationDeliveryMethods(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/NotificationDeliveryMethods', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get notification delivery methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get gender localizations
     * 
     * GET /api/nsk/v1/resources/genders
     * Retrieves localized gender display names
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Gender localizations
     * @throws JamboJetApiException
     */
    public function getGenders(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/genders', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get genders: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get message types
     * 
     * GET /api/nsk/v1/resources/MessageTypes
     * Retrieves all message type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Message types collection
     * @throws JamboJetApiException
     */
    public function getMessageTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/MessageTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get message types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific message type
     * 
     * GET /api/nsk/v1/resources/MessageTypes/{messageTypeCode}
     * Retrieves details for a specific message type
     * 
     * @param string $messageTypeCode Message type code
     * @param string|null $cultureCode Optional culture code
     * @return array Message type details
     * @throws JamboJetApiException
     */
    public function getMessageType(string $messageTypeCode, ?string $cultureCode = null): array
    {
        if (empty($messageTypeCode)) {
            throw new JamboJetValidationException('Message type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/MessageTypes/{$messageTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get message type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment methods
     * 
     * GET /api/nsk/v2/resources/paymentMethods
     * Retrieves available payment method configurations
     * 
     * @param array $params Query parameters (PaymentMethodType, CurrencyCode, CultureCode, ETag, etc.)
     * @return array Payment methods collection
     * @throws JamboJetApiException
     */
    public function getPaymentMethods(array $params = []): array
    {
        try {
            return $this->get('api/nsk/v2/resources/paymentMethods', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific payment method
     * 
     * GET /api/nsk/v2/resources/paymentMethods/{paymentMethodCode}
     * Retrieves details for a specific payment method
     * 
     * @param string $paymentMethodCode Payment method code
     * @param array $params Query parameters (PaymentMethodType, CurrencyCode, CultureCode)
     * @return array Payment method details
     * @throws JamboJetApiException
     */
    public function getPaymentMethod(string $paymentMethodCode, array $params = []): array
    {
        if (empty($paymentMethodCode)) {
            throw new JamboJetValidationException('Payment method code is required');
        }

        try {
            return $this->get("api/nsk/v2/resources/paymentMethods/{$paymentMethodCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment method: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR type
     * 
     * GET /api/nsk/v1/resources/SsrTypes/{ssrTypeCode}
     * Retrieves details for a specific SSR type
     * 
     * @param string $ssrTypeCode SSR type code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR type details
     * @throws JamboJetApiException
     */
    public function getSsrType(string $ssrTypeCode, ?string $cultureCode = null): array
    {
        if (empty($ssrTypeCode)) {
            throw new JamboJetValidationException('SSR type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/SsrTypes/{$ssrTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
//  STATUS RESOURCES
// =================================================================

    /**
     * Get segment statuses
     * 
     * GET /api/nsk/v1/resources/SegmentStatuses
     * Retrieves all segment status codes and descriptions
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Segment statuses collection
     * @throws JamboJetApiException
     */
    public function getSegmentStatuses(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/SegmentStatuses', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get segment statuses: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific segment status
     * 
     * GET /api/nsk/v1/resources/SegmentStatuses/{segmentStatus}
     * Retrieves details for a specific segment status
     * 
     * @param int $segmentStatus Segment status code
     * @param string|null $cultureCode Optional culture code
     * @return array Segment status details
     * @throws JamboJetApiException
     */
    public function getSegmentStatus(int $segmentStatus, ?string $cultureCode = null): array
    {
        if ($segmentStatus < 0) {
            throw new JamboJetValidationException('Segment status must be a non-negative integer');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/SegmentStatuses/{$segmentStatus}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get segment status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get passenger statuses
     * 
     * GET /api/nsk/v1/resources/PassengerStatuses
     * Retrieves all passenger status codes and descriptions
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Passenger statuses collection
     * @throws JamboJetApiException
     */
    public function getPassengerStatuses(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/PassengerStatuses', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger statuses: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific passenger status
     * 
     * GET /api/nsk/v1/resources/PassengerStatuses/{passengerStatus}
     * Retrieves details for a specific passenger status
     * 
     * @param int $passengerStatus Passenger status code
     * @param string|null $cultureCode Optional culture code
     * @return array Passenger status details
     * @throws JamboJetApiException
     */
    public function getPassengerStatus(int $passengerStatus, ?string $cultureCode = null): array
    {
        if ($passengerStatus < 0) {
            throw new JamboJetValidationException('Passenger status must be a non-negative integer');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/PassengerStatuses/{$passengerStatus}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get standby priorities
     * 
     * GET /api/nsk/v1/resources/StandByPriorities
     * Retrieves standby priority configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Standby priorities collection
     * @throws JamboJetApiException
     */
    public function getStandbyPriorities(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/StandByPriorities', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get standby priorities: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific standby priority
     * 
     * GET /api/nsk/v1/resources/StandByPriorities/{priorityCode}
     * Retrieves details for a specific standby priority
     * 
     * @param string $priorityCode Standby priority code
     * @param string|null $cultureCode Optional culture code
     * @return array Standby priority details
     * @throws JamboJetApiException
     */
    public function getStandbyPriority(string $priorityCode, ?string $cultureCode = null): array
    {
        if (empty($priorityCode)) {
            throw new JamboJetValidationException('Priority code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/StandByPriorities/{$priorityCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get standby priority: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking statuses
     * 
     * GET /api/nsk/v1/resources/bookingStatuses
     * Retrieves booking status codes and descriptions
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Booking statuses collection
     * @throws JamboJetApiException
     */
    public function getBookingStatuses(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/bookingStatuses', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking statuses: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get journey statuses
     * 
     * GET /api/nsk/v1/resources/journeyStatuses
     * Retrieves journey status codes and descriptions
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Journey statuses collection
     * @throws JamboJetApiException
     */
    public function getJourneyStatuses(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/journeyStatuses', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get journey statuses: ' . $e->getMessage(),
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
// FEE RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get specific fee by code
     * 
     * GET /api/nsk/v1/resources/fees/{feeCode}
     * Retrieves detailed configuration for a specific fee
     * Includes pricing, limits, applicability rules
     * 
     * @param string $feeCode Fee code (6 chars max)
     * @param string|null $cultureCode Optional culture code for localization
     * @return array Fee details
     * @throws JamboJetApiException
     */
    public function getFee(string $feeCode, ?string $cultureCode = null): array
    {
        $this->validateFeeCode($feeCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/fees/{$feeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fee details (not cached)
     * 
     * GET /api/nsk/v1/resources/fees/{feeCode}/details
     * Retrieves detailed fee options and pricing rules
     * Note: This endpoint is NOT cached, always returns fresh data
     * 
     * @param string $feeCode Fee code
     * @return array Fee details with pricing options
     * @throws JamboJetApiException
     */
    public function getFeeDetails(string $feeCode): array
    {
        $this->validateFeeCode($feeCode);

        try {
            return $this->get("api/nsk/v1/resources/fees/{$feeCode}/details");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fee types
     * 
     * GET /api/nsk/v1/resources/feeTypes
     * Retrieves all fee type classifications
     * (e.g., Service Fee, Change Fee, Cancellation Fee)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fee types collection
     * @throws JamboJetApiException
     */
    public function getFeeTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/feeTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fee type
     * 
     * GET /api/nsk/v1/resources/feeTypes/{feeTypeCode}
     * Retrieves details for a specific fee type classification
     * 
     * @param string $feeTypeCode Fee type code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee type details
     * @throws JamboJetApiException
     */
    public function getFeeType(string $feeTypeCode, ?string $cultureCode = null): array
    {
        if (empty($feeTypeCode)) {
            throw new JamboJetValidationException('Fee type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/feeTypes/{$feeTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fee categories
     * 
     * GET /api/nsk/v1/resources/feeCategories
     * Retrieves fee category groupings for organizational purposes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fee categories collection
     * @throws JamboJetApiException
     */
    public function getFeeCategories(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/feeCategories', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee categories: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fee category
     * 
     * GET /api/nsk/v1/resources/feeCategories/{feeCategoryCode}
     * Retrieves details for a specific fee category
     * 
     * @param string $feeCategoryCode Fee category code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee category details
     * @throws JamboJetApiException
     */
    public function getFeeCategory(string $feeCategoryCode, ?string $cultureCode = null): array
    {
        if (empty($feeCategoryCode)) {
            throw new JamboJetValidationException('Fee category code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/feeCategories/{$feeCategoryCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fees (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/fees
     * V2 version with enhanced data structure
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fees collection (v2)
     * @throws JamboJetApiException
     */
    public function getFeesV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/fees', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fees (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fee (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/fees/{feeCode}
     * 
     * @param string $feeCode Fee code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee details (v2)
     * @throws JamboJetApiException
     */
    public function getFeeV2(string $feeCode, ?string $cultureCode = null): array
    {
        $this->validateFeeCode($feeCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/fees/{$feeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fee (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare basis codes
     * 
     * GET /api/nsk/v1/resources/fareBasisCodes
     * Retrieves list of fare basis codes used for fare classification
     * Returns simple string array of codes
     * 
     * @param string|null $eTag Optional ETag for caching
     * @return array Fare basis codes list (array of strings)
     * @throws JamboJetApiException
     */
    public function getFareBasisCodes(?string $eTag = null): array
    {
        $params = $eTag ? ['eTag' => $eTag] : [];

        try {
            return $this->get('api/nsk/v1/resources/fareBasisCodes', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare basis codes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare discounts
     * 
     * GET /api/nsk/v1/resources/fareDiscounts
     * Retrieves available fare discount configurations
     * (e.g., Military, Senior, Student discounts)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fare discounts collection
     * @throws JamboJetApiException
     */
    public function getFareDiscounts(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/fareDiscounts', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare discounts: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fare discount
     * 
     * GET /api/nsk/v1/resources/fareDiscounts/{fareDiscountCode}
     * Retrieves details for a specific fare discount
     * 
     * @param string $fareDiscountCode Fare discount code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare discount details
     * @throws JamboJetApiException
     */
    public function getFareDiscount(string $fareDiscountCode, ?string $cultureCode = null): array
    {
        if (empty($fareDiscountCode)) {
            throw new JamboJetValidationException('Fare discount code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/fareDiscounts/{$fareDiscountCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare discount: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare surcharges
     * 
     * GET /api/nsk/v1/resources/fareSurcharges
     * Retrieves fare surcharge configurations
     * (additional charges applied to base fares)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fare surcharges collection
     * @throws JamboJetApiException
     */
    public function getFareSurcharges(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/fareSurcharges', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare surcharges: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fare surcharge
     * 
     * GET /api/nsk/v1/resources/fareSurcharges/{fareSurchargeCode}
     * Retrieves details for a specific fare surcharge
     * 
     * @param string $fareSurchargeCode Fare surcharge code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare surcharge details
     * @throws JamboJetApiException
     */
    public function getFareSurcharge(string $fareSurchargeCode, ?string $cultureCode = null): array
    {
        if (empty($fareSurchargeCode)) {
            throw new JamboJetValidationException('Fare surcharge code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/fareSurcharges/{$fareSurchargeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare surcharge: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  STATION RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get station categories
     * 
     * GET /api/nsk/v1/resources/stationCategories
     * Retrieves station category classifications (e.g., International, Domestic, Regional)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Station categories collection
     * @throws JamboJetApiException
     */
    public function getStationCategories(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/stationCategories', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station categories: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific station category
     * 
     * GET /api/nsk/v1/resources/stationCategories/{stationCategoryCode}
     * Retrieves details for a specific station category
     * 
     * @param string $stationCategoryCode Station category code
     * @param string|null $cultureCode Optional culture code
     * @return array Station category details
     * @throws JamboJetApiException
     */
    public function getStationCategory(string $stationCategoryCode, ?string $cultureCode = null): array
    {
        if (empty($stationCategoryCode)) {
            throw new JamboJetValidationException('Station category code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/stationCategories/{$stationCategoryCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific station type
     * 
     * GET /api/nsk/v1/resources/stationTypes/{stationTypeCode}
     * Retrieves details for a specific station type
     * 
     * @param string $stationTypeCode Station type code
     * @param string|null $cultureCode Optional culture code
     * @return array Station type details
     * @throws JamboJetApiException
     */
    public function getStationType(string $stationTypeCode, ?string $cultureCode = null): array
    {
        if (empty($stationTypeCode)) {
            throw new JamboJetValidationException('Station type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/stationTypes/{$stationTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get stations summary
     * 
     * GET /api/nsk/v1/resources/stations/summary
     * Retrieves summarized station information (lightweight version)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Stations summary collection
     * @throws JamboJetApiException
     */
    public function getStationsSummary(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/stations/summary', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get stations summary: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get stations (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/stations
     * V2 version with enhanced station data
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Stations collection (v2)
     * @throws JamboJetApiException
     */
    public function getStationsV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/stations', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get stations (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific station (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/stations/{stationCode}
     * 
     * @param string $stationCode Station code (3-letter IATA)
     * @param string|null $cultureCode Optional culture code
     * @return array Station details (v2)
     * @throws JamboJetApiException
     */
    public function getStationV2(string $stationCode, ?string $cultureCode = null): array
    {
        $this->validateStationCode($stationCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/stations/{$stationCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get station details (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/stations/{stationCode}/details
     * Enhanced detailed information for a station (not cached)
     * 
     * @param string $stationCode Station code
     * @return array Station detailed information (v2)
     * @throws JamboJetApiException
     */
    public function getStationDetailsV2(string $stationCode): array
    {
        $this->validateStationCode($stationCode);

        try {
            return $this->get("api/nsk/v2/resources/stations/{$stationCode}/details");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station details (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get station city
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}/city
     * Retrieves city information for a station
     * 
     * @param string $stationCode Station code
     * @return array City information for station
     * @throws JamboJetApiException
     */
    public function getStationCity(string $stationCode): array
    {
        $this->validateStationCode($stationCode);

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}/city");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station city: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get station timezone
     * 
     * GET /api/nsk/v1/resources/stations/{stationCode}/timezone
     * Retrieves timezone information for a station
     * Important for scheduling and time calculations
     * 
     * @param string $stationCode Station code
     * @return array Timezone information for station
     * @throws JamboJetApiException
     */
    public function getStationTimezone(string $stationCode): array
    {
        $this->validateStationCode($stationCode);

        try {
            return $this->get("api/nsk/v1/resources/stations/{$stationCode}/timezone");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get station timezone: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific fare type by code
     * 
     * GET /api/nsk/v1/resources/FareTypes/{fareTypeCode}
     * Retrieves details for a specific fare type
     * 
     * @param string $fareTypeCode Fare type code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare type details
     * @throws JamboJetApiException
     */
    public function getFareType(string $fareTypeCode, ?string $cultureCode = null): array
    {
        if (empty($fareTypeCode)) {
            throw new JamboJetValidationException('Fare type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/FareTypes/{$fareTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  DOCUMENT TYPE RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get document types
     * 
     * GET /api/nsk/v1/resources/DocumentTypes
     * Retrieves all document type configurations
     * (Passports, Visas, National IDs, Travel Documents, etc.)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document types collection
     * @throws JamboJetApiException
     */
    public function getDocumentTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/DocumentTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific document type
     * 
     * GET /api/nsk/v1/resources/DocumentTypes/{documentTypeCode}
     * Retrieves details for a specific document type
     * Includes validation rules, required fields, expiry requirements
     * 
     * @param string $documentTypeCode Document type code (4 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array Document type details
     * @throws JamboJetApiException
     */
    public function getDocumentType(string $documentTypeCode, ?string $cultureCode = null): array
    {
        $this->validateDocumentTypeCode($documentTypeCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/DocumentTypes/{$documentTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get document type groups
     * 
     * GET /api/nsk/v1/resources/DocumentTypeGroups
     * Retrieves document type groupings for categorization
     * (e.g., TravelVisa, RedressNumber, KnownTravelerId)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document type groups collection
     * @throws JamboJetApiException
     */
    public function getDocumentTypeGroups(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/DocumentTypeGroups', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document type groups: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get document type requirements
     * 
     * GET /api/nsk/v1/resources/DocumentTypeRequirements
     * Retrieves requirements for different document types
     * (mandatory fields, validation rules, country-specific requirements)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document requirements collection
     * @throws JamboJetApiException
     */
    public function getDocumentTypeRequirements(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/DocumentTypeRequirements', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document type requirements: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get document types (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/documentTypes
     * V2 version with enhanced document type data
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document types collection (v2)
     * @throws JamboJetApiException
     */
    public function getDocumentTypesV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/documentTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document types (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific document type (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/documentTypes/{documentTypeCode}
     * 
     * @param string $documentTypeCode Document type code
     * @param string|null $cultureCode Optional culture code
     * @return array Document type details (v2)
     * @throws JamboJetApiException
     */
    public function getDocumentTypeV2(string $documentTypeCode, ?string $cultureCode = null): array
    {
        $this->validateDocumentTypeCode($documentTypeCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/documentTypes/{$documentTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get document type (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  MARKET & ROUTE RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get markets
     * 
     * GET /api/nsk/v2/resources/markets
     * Retrieves all market configurations (city pairs served)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Markets collection
     * @throws JamboJetApiException
     */
    public function getMarkets(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/markets', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get markets: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get markets by location
     * 
     * GET /api/nsk/v2/resources/markets/{locationCode}
     * Retrieves all markets departing from or arriving at a location
     * 
     * @param string $locationCode Location code (3 chars)
     * @param string|null $cultureCode Optional culture code
     * @return array Markets for location
     * @throws JamboJetApiException
     */
    public function getMarketsByLocation(string $locationCode, ?string $cultureCode = null): array
    {
        $this->validateLocationCode($locationCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/markets/{$locationCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get markets by location: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific market
     * 
     * GET /api/nsk/v2/resources/markets/{locationCode}/{travelLocationCode}
     * Retrieves market details for a specific origin-destination pair
     * 
     * @param string $locationCode Origin location code
     * @param string $travelLocationCode Destination location code
     * @param string|null $cultureCode Optional culture code
     * @return array Market details
     * @throws JamboJetApiException
     */
    public function getMarket(string $locationCode, string $travelLocationCode, ?string $cultureCode = null): array
    {
        $this->validateLocationCode($locationCode);
        $this->validateLocationCode($travelLocationCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/markets/{$locationCode}/{$travelLocationCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get market: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get market routes
     * 
     * GET /api/nsk/v1/resources/marketRoutes
     * Retrieves connection routes with segment information
     * Useful for multi-leg journey planning
     * 
     * @param array $params Query parameters (DepartureStation, ArrivalStation)
     * @return array Market routes collection with connection segments
     * @throws JamboJetApiException
     */
    public function getMarketRoutes(array $params = []): array
    {
        // Validate parameters
        if (isset($params['DepartureStation'])) {
            $this->validateStationCode($params['DepartureStation']);
        }

        if (isset($params['ArrivalStation'])) {
            $this->validateStationCode($params['ArrivalStation']);
        }

        try {
            return $this->get('api/nsk/v1/resources/marketRoutes', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get market routes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get cities
     * 
     * GET /api/nsk/v1/resources/Cities
     * Retrieves all city configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Cities collection
     * @throws JamboJetApiException
     */
    public function getCities(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/Cities', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get cities: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific city
     * 
     * GET /api/nsk/v1/resources/Cities/{cityCode}
     * Retrieves details for a specific city
     * 
     * @param string $cityCode City code (3 chars)
     * @param string|null $cultureCode Optional culture code
     * @return array City details
     * @throws JamboJetApiException
     */
    public function getCity(string $cityCode, ?string $cultureCode = null): array
    {
        $this->validateCityCode($cityCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/Cities/{$cityCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get city: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get locations
     * 
     * GET /api/nsk/v1/resources/Locations
     * Retrieves all location configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Locations collection
     * @throws JamboJetApiException
     */
    public function getLocations(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/Locations', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get locations: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific location
     * 
     * GET /api/nsk/v1/resources/Locations/{locationCode}
     * Retrieves details for a specific location
     * 
     * @param string $locationCode Location code
     * @param string|null $cultureCode Optional culture code
     * @return array Location details
     * @throws JamboJetApiException
     */
    public function getLocation(string $locationCode, ?string $cultureCode = null): array
    {
        $this->validateLocationCode($locationCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/Locations/{$locationCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get location: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get MACs (Metropolitan Area Codes)
     * 
     * GET /api/nsk/v2/resources/macs
     * Retrieves all MAC configurations
     * MACs group nearby airports (e.g., NYC includes JFK, LGA, EWR)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array MACs collection
     * @throws JamboJetApiException
     */
    public function getMacs(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/macs', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MACs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific MAC
     * 
     * GET /api/nsk/v2/resources/macs/{macCode}
     * Retrieves details for a specific Metropolitan Area Code
     * 
     * @param string $macCode MAC code
     * @param string|null $cultureCode Optional culture code
     * @return array MAC details with associated airports
     * @throws JamboJetApiException
     */
    public function getMac(string $macCode, ?string $cultureCode = null): array
    {
        if (empty($macCode)) {
            throw new JamboJetValidationException('MAC code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/macs/{$macCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MAC: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get subzones for a zone
     * 
     * GET /api/nsk/v1/resources/zones/{zoneCode}/subZones
     * Retrieves all subzones within a geographic zone
     * Note: This endpoint does not cache subzone resources
     * 
     * @param string $zoneCode Zone code
     * @return array Subzones collection
     * @throws JamboJetApiException
     */
    public function getSubZones(string $zoneCode): array
    {
        if (empty($zoneCode)) {
            throw new JamboJetValidationException('Zone code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/zones/{$zoneCode}/subZones");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get subzones: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific subzone
     * 
     * GET /api/nsk/v1/resources/zones/{zoneCode}/subZones/{subZoneCode}
     * Retrieves details for a specific subzone within a zone
     * Note: This endpoint does not retrieve from cache
     * 
     * @param string $zoneCode Zone code
     * @param string $subZoneCode Subzone code
     * @return array Subzone details
     * @throws JamboJetApiException
     */
    public function getSubZone(string $zoneCode, string $subZoneCode): array
    {
        if (empty($zoneCode)) {
            throw new JamboJetValidationException('Zone code is required');
        }

        if (empty($subZoneCode)) {
            throw new JamboJetValidationException('Subzone code is required');
        }

        try {
            return $this->get("api/nsk/v1/resources/zones/{$zoneCode}/subZones/{$subZoneCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get subzone: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  TAX RESOURCES IMPLEMENTATION
// =================================================================

    /**
     * Get tax codes
     * 
     * GET /api/nsk/v1/resources/TaxCodes
     * Retrieves all tax code configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax codes collection
     * @throws JamboJetApiException
     */
    public function getTaxCodes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/TaxCodes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax codes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific tax code
     * 
     * GET /api/nsk/v1/resources/TaxCodes/{taxCode}
     * Retrieves details for a specific tax code
     * 
     * @param string $taxCode Tax code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax code details
     * @throws JamboJetApiException
     */
    public function getTaxCode(string $taxCode, ?string $cultureCode = null): array
    {
        if (empty($taxCode)) {
            throw new JamboJetValidationException('Tax code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/TaxCodes/{$taxCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get tax types
     * 
     * GET /api/nsk/v1/resources/TaxTypes
     * Retrieves all tax type classifications
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax types collection
     * @throws JamboJetApiException
     */
    public function getTaxTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/TaxTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific tax type
     * 
     * GET /api/nsk/v1/resources/TaxTypes/{taxTypeCode}
     * Retrieves details for a specific tax type
     * 
     * @param string $taxTypeCode Tax type code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax type details
     * @throws JamboJetApiException
     */
    public function getTaxType(string $taxTypeCode, ?string $cultureCode = null): array
    {
        if (empty($taxTypeCode)) {
            throw new JamboJetValidationException('Tax type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/TaxTypes/{$taxTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get taxes (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/taxes
     * V2 version with enhanced tax data
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Taxes collection (v2)
     * @throws JamboJetApiException
     */
    public function getTaxesV2(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/resources/taxes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get taxes (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific tax (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/taxes/{taxCode}
     * 
     * @param string $taxCode Tax code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax details (v2)
     * @throws JamboJetApiException
     */
    public function getTaxV2(string $taxCode, ?string $cultureCode = null): array
    {
        if (empty($taxCode)) {
            throw new JamboJetValidationException('Tax code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v2/resources/taxes/{$taxCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get tax categories
     * 
     * GET /api/nsk/v1/resources/TaxCategories
     * Retrieves tax category groupings
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax categories collection
     * @throws JamboJetApiException
     */
    public function getTaxCategories(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/TaxCategories', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax categories: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific tax category
     * 
     * GET /api/nsk/v1/resources/TaxCategories/{taxCategoryCode}
     * Retrieves details for a specific tax category
     * 
     * @param string $taxCategoryCode Tax category code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax category details
     * @throws JamboJetApiException
     */
    public function getTaxCategory(string $taxCategoryCode, ?string $cultureCode = null): array
    {
        if (empty($taxCategoryCode)) {
            throw new JamboJetValidationException('Tax category code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/TaxCategories/{$taxCategoryCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get tax category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
//  ROLE & PERMISSION RESOURCES
// =================================================================

    /**
     * Get roles
     * 
     * GET /api/nsk/v1/resources/roles
     * Retrieves all user role configurations
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Roles collection
     * @throws JamboJetApiException
     */
    public function getRoles(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/roles', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get roles: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific role (v2 endpoint)
     * 
     * GET /api/nsk/v2/resources/roles/{roleCode}
     * Retrieves details for a specific role
     * 
     * @param string $roleCode Role code
     * @return array Role details
     * @throws JamboJetApiException
     */
    public function getRole(string $roleCode): array
    {
        if (empty($roleCode)) {
            throw new JamboJetValidationException('Role code is required');
        }

        try {
            return $this->get("api/nsk/v2/resources/roles/{$roleCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete role (BETA)
     * 
     * DELETE /api/nsk/v1/resources/roles/{roleCode}
     * BETA endpoint - may be removed in future updates
     * Not available in all labs or for all customers
     * 
     * @param string $roleCode Role code
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deleteRole(string $roleCode): array
    {
        if (empty($roleCode)) {
            throw new JamboJetValidationException('Role code is required');
        }

        try {
            return $this->delete("api/nsk/v1/resources/roles/{$roleCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization types
     * 
     * GET /api/nsk/v1/resources/organizationTypes
     * Retrieves organization type classifications
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Organization types collection
     * @throws JamboJetApiException
     */
    public function getOrganizationTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/organizationTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific organization type
     * 
     * GET /api/nsk/v1/resources/organizationTypes/{organizationTypeCode}
     * Retrieves details for a specific organization type
     * 
     * @param string $organizationTypeCode Organization type code
     * @param string|null $cultureCode Optional culture code
     * @return array Organization type details
     * @throws JamboJetApiException
     */
    public function getOrganizationType(string $organizationTypeCode, ?string $cultureCode = null): array
    {
        if (empty($organizationTypeCode)) {
            throw new JamboJetValidationException('Organization type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/organizationTypes/{$organizationTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
//  CONTENT RESOURCES
// =================================================================

    /**
     * Get all content items
     * 
     * GET /api/nsk/v1/resources/contents
     * Retrieves content items with filtering
     * 
     * @param array $params Query parameters (ContentType, ActiveOnly, StartDate, EndDate, etc.)
     * @return array Content items collection
     * @throws JamboJetApiException
     */
    public function getContents(array $params = []): array
    {
        try {
            return $this->get('api/nsk/v1/resources/contents', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get contents: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create content item
     * 
     * POST /api/nsk/v1/resources/contents
     * Creates a new content item
     * 
     * @param array $contentData Content creation data
     * @return array Created content item
     * @throws JamboJetApiException
     */
    public function createContent(array $contentData): array
    {
        if (empty($contentData)) {
            throw new JamboJetValidationException('Content data is required');
        }

        try {
            return $this->post('api/nsk/v1/resources/contents', $contentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create content: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific content item
     * 
     * GET /api/nsk/v1/resources/contents/{contentId}
     * Retrieves a specific content item
     * 
     * @param int $contentId Content ID
     * @param bool $convertRtfToHtml Convert RTF to HTML
     * @param string|null $eTag ETag for caching
     * @return array Content item details
     * @throws JamboJetApiException
     */
    public function getContent(int $contentId, bool $convertRtfToHtml = false, ?string $eTag = null): array
    {
        if ($contentId <= 0) {
            throw new JamboJetValidationException('Content ID must be a positive integer');
        }

        $params = array_filter([
            'convertRtfToHtml' => $convertRtfToHtml,
            'eTag' => $eTag
        ], fn($value) => $value !== null && $value !== false);

        try {
            return $this->get("api/nsk/v1/resources/contents/{$contentId}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get content: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete content item
     * 
     * DELETE /api/nsk/v1/resources/contents/{contentId}
     * Currently only supports deleting news content items
     * 
     * @param int $contentId Content ID
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deleteContent(int $contentId): array
    {
        if ($contentId <= 0) {
            throw new JamboJetValidationException('Content ID must be a positive integer');
        }

        try {
            return $this->delete("api/nsk/v1/resources/contents/{$contentId}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete content: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get resource settings categories
     * 
     * GET /api/nsk/v1/resources/settings/categories
     * Retrieves settings category hierarchy
     * 
     * @param string|null $eTag ETag for caching
     * @return array Settings categories collection
     * @throws JamboJetApiException
     */
    public function getSettingsCategories(?string $eTag = null): array
    {
        $params = $eTag ? ['eTag' => $eTag] : [];

        try {
            return $this->get('api/nsk/v1/resources/settings/categories', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get settings categories: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get resource settings data
     * 
     * POST /api/nsk/v1/resources/settings/data
     * Retrieves resource setting data based on accept content type
     * 
     * @param array $settingsRequest Settings request data
     * @param string|null $eTag ETag for caching
     * @return array Settings data
     * @throws JamboJetApiException
     */
    public function getSettingsData(array $settingsRequest, ?string $eTag = null): array
    {
        if (empty($settingsRequest)) {
            throw new JamboJetValidationException('Settings request data is required');
        }

        $params = $eTag ? ['eTag' => $eTag] : [];

        try {
            return $this->post('api/nsk/v1/resources/settings/data', $settingsRequest, $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get settings data: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific settings category
     * 
     * GET /api/nsk/v1/resources/settings/categories/{categoryPath}
     * Retrieves a specific settings category
     * 
     * @param string $categoryPath Category path
     * @param string|null $eTag ETag for caching
     * @return array Category details
     * @throws JamboJetApiException
     */
    public function getSettingsCategory(string $categoryPath, ?string $eTag = null): array
    {
        if (empty($categoryPath)) {
            throw new JamboJetValidationException('Category path is required');
        }

        $params = $eTag ? ['eTag' => $eTag] : [];

        try {
            return $this->get("api/nsk/v1/resources/settings/categories/{$categoryPath}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get settings category: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get contact types
     * 
     * GET /api/nsk/v1/resources/contactTypes
     * Retrieves contact type classifications
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Contact types collection
     * @throws JamboJetApiException
     */
    public function getContactTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/contactTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get contact types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific contact type
     * 
     * GET /api/nsk/v1/resources/contactTypes/{contactTypeCode}
     * Retrieves details for a specific contact type
     * 
     * @param string $contactTypeCode Contact type code
     * @param string|null $cultureCode Optional culture code
     * @return array Contact type details
     * @throws JamboJetApiException
     */
    public function getContactType(string $contactTypeCode, ?string $cultureCode = null): array
    {
        if (empty($contactTypeCode)) {
            throw new JamboJetValidationException('Contact type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/contactTypes/{$contactTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get contact type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get titles (salutations)
     * 
     * GET /api/nsk/v1/resources/titles
     * Retrieves title/salutation options (Mr, Mrs, Ms, Dr, etc.)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Titles collection
     * @throws JamboJetApiException
     */
    public function getTitles(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/titles', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get titles: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get change reasons
     * 
     * GET /api/nsk/v1/resources/changeReasons
     * Retrieves change reason codes (for booking modifications)
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Change reasons collection
     * @throws JamboJetApiException
     */
    public function getChangeReasons(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/changeReasons', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get change reasons: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific change reason
     * 
     * GET /api/nsk/v1/resources/changeReasons/{changeReasonCode}
     * 
     * @param string $changeReasonCode Change reason code
     * @param string|null $cultureCode Optional culture code
     * @return array Change reason details
     * @throws JamboJetApiException
     */
    public function getChangeReason(string $changeReasonCode, ?string $cultureCode = null): array
    {
        if (empty($changeReasonCode)) {
            throw new JamboJetValidationException('Change reason code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/changeReasons/{$changeReasonCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get change reason: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get comment types
     * 
     * GET /api/nsk/v1/resources/commentTypes
     * Retrieves comment type classifications
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Comment types collection
     * @throws JamboJetApiException
     */
    public function getCommentTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/commentTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get comment types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific comment type
     * 
     * GET /api/nsk/v1/resources/commentTypes/{commentTypeCode}
     * 
     * @param string $commentTypeCode Comment type code
     * @param string|null $cultureCode Optional culture code
     * @return array Comment type details
     * @throws JamboJetApiException
     */
    public function getCommentType(string $commentTypeCode, ?string $cultureCode = null): array
    {
        if (empty($commentTypeCode)) {
            throw new JamboJetValidationException('Comment type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/commentTypes/{$commentTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get comment type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get currency codes
     * 
     * GET /api/nsk/v1/resources/currencyCodes
     * Retrieves all currency code configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Currency codes collection
     * @throws JamboJetApiException
     */
    public function getCurrencyCodes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/currencyCodes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get currency codes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific currency code
     * 
     * GET /api/nsk/v1/resources/currencyCodes/{currencyCode}
     * 
     * @param string $currencyCode Currency code (3 chars, e.g., USD, EUR, KES)
     * @param string|null $cultureCode Optional culture code
     * @return array Currency code details
     * @throws JamboJetApiException
     */
    public function getCurrencyCode(string $currencyCode, ?string $cultureCode = null): array
    {
        $this->validateCurrencyCode($currencyCode);

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/currencyCodes/{$currencyCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get currency code: ' . $e->getMessage(),
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
     * @return array Equipment types collection
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
     * Get specific equipment type
     * 
     * GET /api/nsk/v1/resources/equipmentTypes/{equipmentTypeCode}
     * 
     * @param string $equipmentTypeCode Equipment type code
     * @param string|null $cultureCode Optional culture code
     * @return array Equipment type details
     * @throws JamboJetApiException
     */
    public function getEquipmentType(string $equipmentTypeCode, ?string $cultureCode = null): array
    {
        if (empty($equipmentTypeCode)) {
            throw new JamboJetValidationException('Equipment type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/equipmentTypes/{$equipmentTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get equipment type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get identification types
     * 
     * GET /api/nsk/v1/resources/identificationTypes
     * Retrieves identification type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Identification types collection
     * @throws JamboJetApiException
     */
    public function getIdentificationTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/identificationTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get identification types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific identification type
     * 
     * GET /api/nsk/v1/resources/identificationTypes/{identificationTypeCode}
     * 
     * @param string $identificationTypeCode Identification type code
     * @param string|null $cultureCode Optional culture code
     * @return array Identification type details
     * @throws JamboJetApiException
     */
    public function getIdentificationType(string $identificationTypeCode, ?string $cultureCode = null): array
    {
        if (empty($identificationTypeCode)) {
            throw new JamboJetValidationException('Identification type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/identificationTypes/{$identificationTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get identification type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get phone number types
     * 
     * GET /api/nsk/v1/resources/phoneNumberTypes
     * Retrieves phone number type classifications
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Phone number types collection
     * @throws JamboJetApiException
     */
    public function getPhoneNumberTypes(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/phoneNumberTypes', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get phone number types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get supplier types
     * 
     * GET /api/nsk/v1/resources/supplierTypes
     * Retrieves supplier type classifications
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Supplier types collection
     * @throws JamboJetApiException
     */
    public function getSupplierTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/supplierTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get supplier types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific supplier type
     * 
     * GET /api/nsk/v1/resources/supplierTypes/{supplierTypeCode}
     * 
     * @param string $supplierTypeCode Supplier type code
     * @param string|null $cultureCode Optional culture code
     * @return array Supplier type details
     * @throws JamboJetApiException
     */
    public function getSupplierType(string $supplierTypeCode, ?string $cultureCode = null): array
    {
        if (empty($supplierTypeCode)) {
            throw new JamboJetValidationException('Supplier type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/supplierTypes/{$supplierTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get supplier type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get time periods
     * 
     * GET /api/nsk/v1/resources/timePeriods
     * Retrieves time period configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Time periods collection
     * @throws JamboJetApiException
     */
    public function getTimePeriods(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/timePeriods', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get time periods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific time period
     * 
     * GET /api/nsk/v1/resources/timePeriods/{timePeriodCode}
     * 
     * @param string $timePeriodCode Time period code
     * @param string|null $cultureCode Optional culture code
     * @return array Time period details
     * @throws JamboJetApiException
     */
    public function getTimePeriod(string $timePeriodCode, ?string $cultureCode = null): array
    {
        if (empty($timePeriodCode)) {
            throw new JamboJetValidationException('Time period code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/timePeriods/{$timePeriodCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get time period: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get travel document requirement types
     * 
     * GET /api/nsk/v1/resources/travelDocumentRequirementTypes
     * Retrieves travel document requirement type configurations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Travel document requirement types collection
     * @throws JamboJetApiException
     */
    public function getTravelDocumentRequirementTypes(array $criteria = []): array
    {
        $this->validateResourceCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/resources/travelDocumentRequirementTypes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get travel document requirement types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific travel document requirement type
     * 
     * GET /api/nsk/v1/resources/travelDocumentRequirementTypes/{requirementTypeCode}
     * 
     * @param string $requirementTypeCode Requirement type code
     * @param string|null $cultureCode Optional culture code
     * @return array Requirement type details
     * @throws JamboJetApiException
     */
    public function getTravelDocumentRequirementType(string $requirementTypeCode, ?string $cultureCode = null): array
    {
        if (empty($requirementTypeCode)) {
            throw new JamboJetValidationException('Requirement type code is required');
        }

        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get("api/nsk/v1/resources/travelDocumentRequirementTypes/{$requirementTypeCode}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get travel document requirement type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get unit types
     * 
     * GET /api/nsk/v1/resources/unitTypes
     * Retrieves unit type localizations
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Unit types collection
     * @throws JamboJetApiException
     */
    public function getUnitTypes(?string $cultureCode = null): array
    {
        $params = $cultureCode ? ['cultureCode' => $cultureCode] : [];

        try {
            return $this->get('api/nsk/v1/resources/unitTypes', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get unit types: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate currency code
     * 
     * @param string $currencyCode Currency code (3 chars, e.g., USD, EUR, KES)
     * @throws JamboJetValidationException
     */
    private function validateCurrencyCode(string $currencyCode): void
    {
        if (empty($currencyCode)) {
            throw new JamboJetValidationException('Currency code is required');
        }

        if (strlen($currencyCode) !== 3) {
            throw new JamboJetValidationException('Currency code must be exactly 3 characters (ISO 4217)');
        }

        if (!ctype_alpha($currencyCode)) {
            throw new JamboJetValidationException('Currency code must contain only letters');
        }
    }

    /**
     * Validate carrier code
     * 
     * @param string $carrierCode Carrier code (2 chars, IATA airline code)
     * @throws JamboJetValidationException
     */
    private function validateCarrierCode(string $carrierCode): void
    {
        if (empty($carrierCode)) {
            throw new JamboJetValidationException('Carrier code is required');
        }

        if (strlen($carrierCode) !== 2) {
            throw new JamboJetValidationException('Carrier code must be exactly 2 characters (IATA airline code)');
        }

        if (!ctype_alpha($carrierCode)) {
            throw new JamboJetValidationException('Carrier code must contain only letters');
        }
    }

    /**
     * Validate document type code
     * 
     * @param string $documentTypeCode Document type code (max 4 chars)
     * @throws JamboJetValidationException
     */
    private function validateDocumentTypeCode(string $documentTypeCode): void
    {
        if (empty($documentTypeCode)) {
            throw new JamboJetValidationException('Document type code is required');
        }

        if (strlen($documentTypeCode) > 4) {
            throw new JamboJetValidationException('Document type code must be 4 characters or less');
        }
    }

    /**
     * Validate location code
     * 
     * @param string $locationCode Location code (3 chars)
     * @throws JamboJetValidationException
     */
    private function validateLocationCode(string $locationCode): void
    {
        if (empty($locationCode)) {
            throw new JamboJetValidationException('Location code is required');
        }

        if (strlen($locationCode) !== 3) {
            throw new JamboJetValidationException('Location code must be exactly 3 characters');
        }

        if (!ctype_alpha($locationCode)) {
            throw new JamboJetValidationException('Location code must contain only letters');
        }
    }

    /**
     * Validate city code
     * 
     * @param string $cityCode City code (3 chars)
     * @throws JamboJetValidationException
     */
    private function validateCityCode(string $cityCode): void
    {
        if (empty($cityCode)) {
            throw new JamboJetValidationException('City code is required');
        }

        if (strlen($cityCode) !== 3) {
            throw new JamboJetValidationException('City code must be exactly 3 characters');
        }

        if (!ctype_alpha($cityCode)) {
            throw new JamboJetValidationException('City code must contain only letters');
        }
    }

    /**
     * Validate SSR code
     * 
     * @param string $ssrCode SSR code (max 4 characters)
     * @throws JamboJetValidationException
     */
    private function validateSsrCode(string $ssrCode): void
    {
        if (empty($ssrCode)) {
            throw new JamboJetValidationException('SSR code is required');
        }

        if (strlen($ssrCode) > 4) {
            throw new JamboJetValidationException('SSR code must be 4 characters or less');
        }
    }

    /**
     * Validate SSR nest code
     * 
     * @param string $ssrNestCode SSR nest code (max 4 characters)
     * @throws JamboJetValidationException
     */
    private function validateSsrNestCode(string $ssrNestCode): void
    {
        if (empty($ssrNestCode)) {
            throw new JamboJetValidationException('SSR nest code is required');
        }

        if (strlen($ssrNestCode) > 4) {
            throw new JamboJetValidationException('SSR nest code must be 4 characters or less');
        }
    }

    /**
     * Validate fee code
     * 
     * @param string $feeCode Fee code (max 6 characters)
     * @throws JamboJetValidationException
     */
    private function validateFeeCode(string $feeCode): void
    {
        if (empty($feeCode)) {
            throw new JamboJetValidationException('Fee code is required');
        }

        if (strlen($feeCode) > 6) {
            throw new JamboJetValidationException('Fee code must be 6 characters or less');
        }
    }

    /**
     * Validate station code
     * 
     * @param string $stationCode Station code (3-letter IATA code)
     * @throws JamboJetValidationException
     */
    private function validateStationCode(string $stationCode): void
    {
        if (empty($stationCode)) {
            throw new JamboJetValidationException('Station code is required');
        }

        if (strlen($stationCode) !== 3) {
            throw new JamboJetValidationException('Station code must be exactly 3 characters (IATA code)');
        }

        if (!ctype_alpha($stationCode)) {
            throw new JamboJetValidationException('Station code must contain only letters');
        }
    }

    /**
     * Validate country code
     * 
     * @param string $countryCode Country code (2-letter ISO code)
     * @throws JamboJetValidationException
     */
    private function validateCountryCode(string $countryCode): void
    {
        if (empty($countryCode)) {
            throw new JamboJetValidationException('Country code is required');
        }

        if (strlen($countryCode) !== 2) {
            throw new JamboJetValidationException('Country code must be exactly 2 characters (ISO code)');
        }

        if (!ctype_alpha($countryCode)) {
            throw new JamboJetValidationException('Country code must contain only letters');
        }
    }

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