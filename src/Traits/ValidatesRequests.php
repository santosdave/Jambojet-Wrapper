<?php

namespace SantosDave\JamboJet\Traits;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * ValidatesRequests Trait
 * 
 * Provides validation functionality for all service classes
 * Includes field validation, format validation, and structure validation
 * 
 * @package SantosDave\JamboJet\Traits
 */
trait ValidatesRequests
{
    /**
     * Validate required parameters
     * 
     * @param array $data Data to validate
     * @param array $required Array of required field names
     * @throws JamboJetValidationException
     */
    protected function validateRequired(array $data, array $required): void
    {
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $this->isEmpty($data[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new JamboJetValidationException(
                'Missing required parameters: ' . implode(', ', $missing),
                400,
                ['missing_fields' => $missing]
            );
        }
    }


    /**
     * Validate date range for search operations
     * 
     * @param string $startDate Start date (ISO 8601)
     * @param string $endDate End date (ISO 8601)
     * @throws JamboJetValidationException
     */
    private function validateDateRange(string $startDate, string $endDate): void
    {
        if (!strtotime($startDate)) {
            throw new JamboJetValidationException('Invalid start date format. Use ISO 8601 format (e.g., 2024-01-01T00:00:00Z)');
        }

        if (!strtotime($endDate)) {
            throw new JamboJetValidationException('Invalid end date format. Use ISO 8601 format (e.g., 2024-12-31T23:59:59Z)');
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            throw new JamboJetValidationException('End date must be after start date');
        }

        // Validate range is not too large (e.g., max 1 year)
        $diff = strtotime($endDate) - strtotime($startDate);
        $maxDays = 365;
        if ($diff > ($maxDays * 24 * 60 * 60)) {
            throw new JamboJetValidationException("Date range cannot exceed {$maxDays} days");
        }
    }

    /**
     * Validate person search options
     * 
     * @param array $options Search options
     * @throws JamboJetValidationException
     */
    private function validatePersonSearchOptions(array $options): void
    {
        if (isset($options['destination'])) {
            $this->validateStationCode($options['destination'], 'Destination');
        }

        if (isset($options['origin'])) {
            $this->validateStationCode($options['origin'], 'Origin');
        }

        if (isset($options['pageSize'])) {
            $this->validatePageSize($options['pageSize'], 10, 5000);
        }

        if (isset($options['startDate'], $options['endDate'])) {
            $this->validateDateRange($options['startDate'], $options['endDate']);
        }
    }

    /**
     * Validate organization code
     * 
     * @param string $code Organization code
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCode(string $code): void
    {
        if (empty($code)) {
            throw new JamboJetValidationException('Organization code is required');
        }

        if (strlen($code) > 10) {
            throw new JamboJetValidationException('Organization code cannot exceed 10 characters');
        }

        if (!preg_match('/^[A-Z0-9]+$/', $code)) {
            throw new JamboJetValidationException('Organization code must contain only uppercase letters and numbers');
        }
    }

    /**
     * Validate agency search data
     * 
     * @param array $data Search data
     * @throws JamboJetValidationException
     */
    private function validateAgencySearchData(array $data): void
    {
        if (isset($data['firstName'])) {
            $this->validateName($data['firstName'], 'First name');
        }

        if (isset($data['lastName'])) {
            $this->validateName($data['lastName'], 'Last name');
        }

        if (isset($data['phoneticSearch']) && !is_bool($data['phoneticSearch'])) {
            throw new JamboJetValidationException('Phonetic search must be a boolean value');
        }

        if (isset($data['filters']) && !is_array($data['filters'])) {
            throw new JamboJetValidationException('Filters must be an array');
        }
    }

    /**
     * Validate contact search data
     * 
     * @param array $data Contact search data
     * @throws JamboJetValidationException
     */
    private function validateContactSearchData(array $data): void
    {
        // At least one search criterion required
        $validFields = ['firstName', 'lastName', 'recordLocator', 'phoneNumber', 'emailAddress'];
        $hasValidField = false;

        foreach ($validFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $hasValidField = true;
                break;
            }
        }

        if (!$hasValidField) {
            throw new JamboJetValidationException('At least one contact search field is required: ' . implode(', ', $validFields));
        }

        if (isset($data['firstName'])) {
            $this->validateName($data['firstName'], 'First name');
        }

        if (isset($data['lastName'])) {
            $this->validateName($data['lastName'], 'Last name');
        }

        if (isset($data['recordLocator'])) {
            $this->validateRecordLocator($data['recordLocator']);
        }

        if (isset($data['phoneNumber'])) {
            $this->validatePhoneNumber($data['phoneNumber']);
        }

        if (isset($data['emailAddress'])) {
            $this->validateEmail($data['emailAddress']);
        }
    }

    /**
     * Validate third party search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validateThirdPartySearchParams(array $params): void
    {
        // System code is required
        if (!isset($params['systemCode']) || empty($params['systemCode'])) {
            throw new JamboJetValidationException('System code is required for third party search');
        }

        if (strlen($params['systemCode']) > 3) {
            throw new JamboJetValidationException('System code cannot exceed 3 characters');
        }

        if (isset($params['agentId']) && (!is_int($params['agentId']) || $params['agentId'] < 0)) {
            throw new JamboJetValidationException('Agent ID must be a non-negative integer');
        }

        if (isset($params['organizationCode'])) {
            $this->validateOrganizationCode($params['organizationCode']);
        }

        if (isset($params['recordLocator'])) {
            $this->validateRecordLocator($params['recordLocator']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }

    /**
     * Validate reference number search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validateReferenceNumberSearchParams(array $params): void
    {
        if (isset($params['agentId']) && (!is_int($params['agentId']) || $params['agentId'] < 0)) {
            throw new JamboJetValidationException('Agent ID must be a non-negative integer');
        }

        if (isset($params['organizationCode'])) {
            $this->validateOrganizationCode($params['organizationCode']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }

    /**
     * Validate agent code search data
     * 
     * @param array $data Search data
     * @throws JamboJetValidationException
     */
    private function validateAgentCodeSearchData(array $data): void
    {
        if (isset($data['domainCode'])) {
            if (strlen($data['domainCode']) > 5) {
                throw new JamboJetValidationException('Domain code cannot exceed 5 characters');
            }
        }

        if (isset($data['firstName'])) {
            $this->validateName($data['firstName'], 'First name');
        }

        if (isset($data['lastName'])) {
            $this->validateName($data['lastName'], 'Last name');
        }

        if (isset($data['phoneticSearch']) && !is_bool($data['phoneticSearch'])) {
            throw new JamboJetValidationException('Phonetic search must be a boolean value');
        }

        if (isset($data['filters']) && !is_array($data['filters'])) {
            throw new JamboJetValidationException('Filters must be an array');
        }
    }

    /**
     * Validate external payment search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validateExternalPaymentSearchParams(array $params): void
    {
        // At least one of recordLocator or paymentKey required
        if (!isset($params['recordLocator']) && !isset($params['paymentKey'])) {
            throw new JamboJetValidationException('Either recordLocator or paymentKey is required for external payment search');
        }

        if (isset($params['recordLocator'])) {
            $this->validateRecordLocator($params['recordLocator']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }

    /**
     * Validate credit card search request
     * 
     * @param array $request Search request
     * @throws JamboJetValidationException
     */
    private function validateCreditCardSearchRequest(array $request): void
    {
        if (!isset($request['creditCardNumber']) || empty($request['creditCardNumber'])) {
            throw new JamboJetValidationException('Credit card number is required');
        }

        $cardNumber = preg_replace('/\s+/', '', $request['creditCardNumber']);

        if (!preg_match('/^\d{4,19}$/', $cardNumber)) {
            throw new JamboJetValidationException('Invalid credit card number format. Must be 4-19 digits');
        }

        if (isset($request['expiryDate'])) {
            if (!preg_match('/^\d{4}$/', $request['expiryDate'])) {
                throw new JamboJetValidationException('Expiry date must be in MMYY format (e.g., 1225 for Dec 2025)');
            }
        }

        if (isset($request['cardholderName'])) {
            if (strlen($request['cardholderName']) > 100) {
                throw new JamboJetValidationException('Cardholder name cannot exceed 100 characters');
            }
        }

        if (isset($request['filters']) && !is_array($request['filters'])) {
            throw new JamboJetValidationException('Filters must be an array');
        }
    }

    /**
     * Validate customer number
     * 
     * @param string $customerNumber Customer number
     * @throws JamboJetValidationException
     */
    private function validateCustomerNumber(string $customerNumber): void
    {
        if (empty($customerNumber)) {
            throw new JamboJetValidationException('Customer number is required');
        }

        if (strlen($customerNumber) < 1 || strlen($customerNumber) > 20) {
            throw new JamboJetValidationException('Customer number must be between 1 and 20 characters');
        }
    }

    /**
     * Validate customer number search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validateCustomerNumberSearchParams(array $params): void
    {
        if (isset($params['agentId']) && (!is_int($params['agentId']) || $params['agentId'] < 0)) {
            throw new JamboJetValidationException('Agent ID must be a non-negative integer');
        }

        if (isset($params['organizationCode'])) {
            $this->validateOrganizationCode($params['organizationCode']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }

    /**
     * Validate name field
     * 
     * @param string $name Name to validate
     * @param string $fieldName Field name for error messages
     * @throws JamboJetValidationException
     */
    private function validateName(string $name, string $fieldName): void
    {
        if (empty($name)) {
            throw new JamboJetValidationException("{$fieldName} cannot be empty");
        }

        if (strlen($name) > 32) {
            throw new JamboJetValidationException("{$fieldName} cannot exceed 32 characters");
        }

        // Basic name validation - letters, spaces, hyphens, apostrophes
        if (!preg_match("/^[a-zA-Z\s\-']+$/", $name)) {
            throw new JamboJetValidationException("{$fieldName} contains invalid characters. Only letters, spaces, hyphens, and apostrophes are allowed");
        }
    }

    /**
     * Validate last name search data
     * 
     * @param array $data Search data
     * @throws JamboJetValidationException
     */
    private function validateLastNameSearchData(array $data): void
    {
        if (isset($data['firstName'])) {
            $this->validateName($data['firstName'], 'First name');
        }

        if (isset($data['phoneticSearch']) && !is_bool($data['phoneticSearch'])) {
            throw new JamboJetValidationException('Phonetic search must be a boolean value');
        }

        if (isset($data['filters']) && !is_array($data['filters'])) {
            throw new JamboJetValidationException('Filters must be an array');
        }
    }

    /**
     * Validate phone search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validatePhoneSearchParams(array $params): void
    {
        if (isset($params['agentId']) && (!is_int($params['agentId']) || $params['agentId'] < 0)) {
            throw new JamboJetValidationException('Agent ID must be a non-negative integer');
        }

        if (isset($params['organizationCode'])) {
            $this->validateOrganizationCode($params['organizationCode']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }


    /**
     * Validate email search parameters
     * 
     * @param array $params Search parameters
     * @throws JamboJetValidationException
     */
    private function validateEmailSearchParams(array $params): void
    {
        if (isset($params['agentId']) && (!is_int($params['agentId']) || $params['agentId'] < 0)) {
            throw new JamboJetValidationException('Agent ID must be a non-negative integer');
        }

        if (isset($params['organizationCode'])) {
            $this->validateOrganizationCode($params['organizationCode']);
        }

        if (isset($params['pageSize'])) {
            $this->validatePageSize($params['pageSize'], 10, 5000);
        }
    }


    /**
     * Validate page size
     * 
     * @param int $pageSize Page size
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @throws JamboJetValidationException
     */
    private function validatePageSize(int $pageSize, int $min = 10, int $max = 5000): void
    {
        if ($pageSize < $min || $pageSize > $max) {
            throw new JamboJetValidationException("Page size must be between {$min} and {$max}");
        }
    }

    /**
     * Validate maximum length for a string field
     * 
     * @param string $value Value to validate
     * @param int $maxLength Maximum length
     * @param string $fieldName Field name for error messages
     * @throws JamboJetValidationException
     */
    private function validateMaxLength(string $value, int $maxLength, string $fieldName): void
    {
        if (strlen($value) > $maxLength) {
            throw new JamboJetValidationException("{$fieldName} cannot exceed {$maxLength} characters");
        }
    }


    /**
     * Validate station code (3-letter IATA code)
     * 
     * @param string $stationCode Station code to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateStationCode(string $stationCode, string $fieldName = 'Station code'): void
    {
        if (strlen($stationCode) !== 3) {
            throw new JamboJetValidationException(
                "{$fieldName} must be exactly 3 characters"
            );
        }

        if (!preg_match('/^[A-Z]{3}$/', $stationCode)) {
            throw new JamboJetValidationException(
                "{$fieldName} must be 3 uppercase letters (IATA code)"
            );
        }
    }

    /**
     * Validate currency code (3-letter ISO 4217 code)
     * 
     * @param string $currencyCode Currency code to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateCurrencyCode(string $currencyCode, string $fieldName = 'Currency code'): void
    {
        if (strlen($currencyCode) !== 3) {
            throw new JamboJetValidationException(
                "{$fieldName} must be exactly 3 characters"
            );
        }

        if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new JamboJetValidationException(
                "{$fieldName} must be 3 uppercase letters (ISO 4217 code)"
            );
        }
    }

    /**
     * Validate date format (ISO 8601)
     * 
     * @param string $date Date string to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateDateFormat(string $date, string $fieldName = 'Date'): void
    {
        // Try ISO 8601 full format first
        $dateTime = \DateTime::createFromFormat(\DateTime::ISO8601, $date);

        // Fallback to simple date format
        if (!$dateTime) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        }

        if (!$dateTime) {
            throw new JamboJetValidationException(
                "{$fieldName} must be in ISO 8601 format (YYYY-MM-DD or YYYY-MM-DDTHH:MM:SSZ)"
            );
        }
    }

    /**
     * Validate email address format
     * 
     * @param string $email Email address to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateEmail(string $email, string $fieldName = 'Email address'): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new JamboJetValidationException(
                "{$fieldName} must be a valid email address"
            );
        }
    }

    /**
     * Validate phone number format
     * 
     * @param string $phone Phone number to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validatePhoneNumber(string $phone, string $fieldName = 'Phone number'): void
    {
        // Remove common separators for validation
        $cleanPhone = preg_replace('/[\s\-\(\)\.]+/', '', $phone);

        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 20) {
            throw new JamboJetValidationException(
                "{$fieldName} must be between 10 and 20 digits"
            );
        }

        if (!preg_match('/^[\+]?[0-9]+$/', $cleanPhone)) {
            throw new JamboJetValidationException(
                "{$fieldName} must contain only numbers and optional leading +"
            );
        }
    }

    /**
     * Validate passenger key format
     * 
     * @param string $passengerKey Passenger key to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validatePassengerKey(string $passengerKey, string $fieldName = 'Passenger key'): void
    {
        if (empty($passengerKey)) {
            throw new JamboJetValidationException("{$fieldName} is required");
        }

        // Passenger keys are base64-encoded strings
        if (!preg_match('/^[A-Za-z0-9\+\/\=\-\_]+$/', $passengerKey)) {
            throw new JamboJetValidationException(
                "{$fieldName} has invalid format"
            );
        }
    }

    /**
     * Validate journey key format
     * 
     * @param string $journeyKey Journey key to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateJourneyKey(string $journeyKey, string $fieldName = 'Journey key'): void
    {
        if (empty($journeyKey)) {
            throw new JamboJetValidationException("{$fieldName} is required");
        }

        // Journey keys are base64-encoded strings
        if (!preg_match('/^[A-Za-z0-9\+\/\=\-\_]+$/', $journeyKey)) {
            throw new JamboJetValidationException(
                "{$fieldName} has invalid format"
            );
        }
    }

    /**
     * Validate segment key format
     * 
     * @param string $segmentKey Segment key to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateSegmentKey(string $segmentKey, string $fieldName = 'Segment key'): void
    {
        if (empty($segmentKey)) {
            throw new JamboJetValidationException("{$fieldName} is required");
        }

        // Segment keys are base64-encoded strings
        if (!preg_match('/^[A-Za-z0-9\+\/\=\-\_]+$/', $segmentKey)) {
            throw new JamboJetValidationException(
                "{$fieldName} has invalid format"
            );
        }
    }

    /**
     * Validate pagination parameters
     * 
     * @param int $pageNumber Page number (must be >= 1)
     * @param int $pageSize Page size (must be between 1 and max)
     * @param int $maxPageSize Maximum allowed page size
     * @throws JamboJetValidationException
     */
    protected function validatePagination(int $pageNumber, int $pageSize, int $maxPageSize = 100): void
    {
        if ($pageNumber < 1) {
            throw new JamboJetValidationException('Page number must be 1 or greater');
        }

        if ($pageSize < 1 || $pageSize > $maxPageSize) {
            throw new JamboJetValidationException(
                "Page size must be between 1 and {$maxPageSize}"
            );
        }
    }

    /**
     * Validate amount (positive number with max 2 decimal places)
     * 
     * @param float $amount Amount to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateAmount(float $amount, string $fieldName = 'Amount'): void
    {
        if ($amount < 0) {
            throw new JamboJetValidationException(
                "{$fieldName} must be a positive number"
            );
        }

        // Check decimal places
        $decimals = strlen(substr(strrchr((string)$amount, "."), 1));
        if ($decimals > 2) {
            throw new JamboJetValidationException(
                "{$fieldName} must have maximum 2 decimal places"
            );
        }
    }

    /**
     * Validate string length
     * 
     * @param string $value Value to validate
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateLength(
        string $value,
        int $minLength,
        int $maxLength,
        string $fieldName
    ): void {
        $length = strlen($value);

        if ($length < $minLength || $length > $maxLength) {
            throw new JamboJetValidationException(
                "{$fieldName} must be between {$minLength} and {$maxLength} characters"
            );
        }
    }

    /**
     * Validate array has at least one element
     * 
     * @param array $array Array to validate
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    protected function validateNotEmpty(array $array, string $fieldName): void
    {
        if (empty($array)) {
            throw new JamboJetValidationException(
                "{$fieldName} must contain at least one item"
            );
        }
    }


    protected function validateAvailabilityWithSsrRequest(array $searchRequest) {}

    /**
     * Validate API version
     * 
     * @param int $version Version to validate
     * @param array $allowedVersions Allowed version numbers
     * @throws JamboJetValidationException
     */
    protected function validateApiVersion(int $version, array $allowedVersions): void
    {
        if (!in_array($version, $allowedVersions)) {
            throw new JamboJetValidationException(
                "Invalid API version: {$version}. Allowed versions: " . implode(', ', $allowedVersions),
                400
            );
        }
    }

    /**
     * Legacy method for backward compatibility
     * 
     * @param array $data Data to validate
     * @param array $required Array of required field names
     * @throws JamboJetValidationException
     */
    protected function validateRequiredFields(array $data, array $required): void
    {
        $this->validateRequired($data, $required);
    }

    /**
     * Validate parameter formats
     * 
     * @param array $data Data to validate
     * @param array $formats Array of field => format rules
     * @throws JamboJetValidationException
     */
    protected function validateFormats(array $data, array $formats): void
    {
        $errors = [];

        foreach ($formats as $field => $format) {
            if (!isset($data[$field]) || $this->isEmpty($data[$field])) {
                continue;
            }

            $value = $data[$field];

            switch ($format) {
                case 'date':
                    if (!$this->isValidDate($value)) {
                        $errors[$field] = "Invalid date format. Expected Y-m-d format.";
                    }
                    break;
                case 'datetime':
                    if (!$this->isValidDateTime($value)) {
                        $errors[$field] = "Invalid datetime format. Expected ISO 8601 format.";
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Invalid email format.";
                    }
                    break;
                case 'airport_code':
                    if (!preg_match('/^[A-Z]{3}$/', $value)) {
                        $errors[$field] = "Invalid airport code. Expected 3-letter IATA code.";
                    }
                    break;
                case 'country_code':
                    if (!preg_match('/^[A-Z]{2}$/', $value)) {
                        $errors[$field] = "Invalid country code. Expected 2-letter ISO code.";
                    }
                    break;
                case 'currency_code':
                    if (!preg_match('/^[A-Z]{3}$/', $value)) {
                        $errors[$field] = "Invalid currency code. Expected 3-letter ISO code.";
                    }
                    break;
                case 'passenger_type':
                    if (!preg_match('/^[A-Z]{3,4}$/', $value)) {
                        $errors[$field] = "Invalid passenger type. Expected 3-4 character code.";
                    }
                    break;
                case 'phone':
                    if (!preg_match('/^\+?[\d\s\-\(\)]+$/', $value)) {
                        $errors[$field] = "Invalid phone number format.";
                    }
                    break;
                case 'record_locator':
                    if (!preg_match('/^[A-Z0-9]{6}$/', $value)) {
                        $errors[$field] = "Invalid record locator. Expected 6-character alphanumeric.";
                    }
                    break;
                case 'positive_number':
                    if (!is_numeric($value) || $value <= 0) {
                        $errors[$field] = "Must be a positive number.";
                    }
                    break;
                case 'non_negative_number':
                    if (!is_numeric($value) || $value < 0) {
                        $errors[$field] = "Must be a non-negative number.";
                    }
                    break;
            }
        }

        if (!empty($errors)) {
            throw new JamboJetValidationException(
                'Format validation failed: ' . implode(', ', array_keys($errors)),
                400,
                ['format_errors' => $errors]
            );
        }
    }

    /**
     * Validate nested array structure
     * 
     * @param array $data Data to validate
     * @param array $structure Expected structure ['field' => ['subfield1', 'subfield2']]
     * @throws JamboJetValidationException
     */
    protected function validateStructure(array $data, array $structure): void
    {
        $errors = [];

        foreach ($structure as $field => $requiredSubfields) {
            if (!isset($data[$field])) {
                continue;
            }

            if (is_array($data[$field])) {
                foreach ($data[$field] as $index => $item) {
                    if (!is_array($item)) {
                        $errors["{$field}[{$index}]"] = "Expected array structure";
                        continue;
                    }

                    foreach ($requiredSubfields as $subfield) {
                        if (!isset($item[$subfield]) || $this->isEmpty($item[$subfield])) {
                            $errors["{$field}[{$index}].{$subfield}"] = "Missing required subfield";
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new JamboJetValidationException(
                'Structure validation failed',
                400,
                ['structure_errors' => $errors]
            );
        }
    }

    /**
     * Check if value is empty (handles various data types)
     * 
     * @param mixed $value Value to check
     * @return bool True if empty
     */
    protected function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return empty($value);
        }

        return false;
    }

    /**
     * Validate date format (Y-m-d)
     * 
     * @param string $date Date string
     * @return bool True if valid
     */
    protected function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate datetime format (ISO 8601)
     * 
     * @param string $datetime Datetime string
     * @return bool True if valid
     */
    protected function isValidDateTime(string $datetime): bool
    {
        return strtotime($datetime) !== false;
    }

    /**
     * Validate array contains only allowed values
     * 
     * @param array $data Array to validate
     * @param array $allowedValues Allowed values
     * @param string $fieldName Field name for error reporting
     * @throws JamboJetValidationException
     */
    protected function validateAllowedValues(array $data, array $allowedValues, string $fieldName = 'value'): void
    {
        foreach ($data as $index => $value) {
            if (!in_array($value, $allowedValues)) {
                throw new JamboJetValidationException(
                    "Invalid {$fieldName} at index {$index}. Expected one of: " . implode(', ', $allowedValues),
                    400
                );
            }
        }
    }

    /**
     * Validate string length constraints
     * 
     * @param array $data Data to validate
     * @param array $constraints Length constraints ['field' => ['min' => 1, 'max' => 50]]
     * @throws JamboJetValidationException
     */
    protected function validateStringLengths(array $data, array $constraints): void
    {
        $errors = [];

        foreach ($constraints as $field => $limits) {
            if (!isset($data[$field])) {
                continue;
            }

            $value = $data[$field];
            if (!is_string($value)) {
                continue;
            }

            $length = strlen($value);

            if (isset($limits['min']) && $length < $limits['min']) {
                $errors[$field] = "Must be at least {$limits['min']} characters long";
            }

            if (isset($limits['max']) && $length > $limits['max']) {
                $errors[$field] = "Cannot exceed {$limits['max']} characters";
            }
        }

        if (!empty($errors)) {
            throw new JamboJetValidationException(
                'String length validation failed',
                400,
                ['length_errors' => $errors]
            );
        }
    }

    /**
     * Validate numeric range constraints
     * 
     * @param array $data Data to validate
     * @param array $constraints Range constraints ['field' => ['min' => 0, 'max' => 100]]
     * @throws JamboJetValidationException
     */
    protected function validateNumericRanges(array $data, array $constraints): void
    {
        $errors = [];

        foreach ($constraints as $field => $limits) {
            if (!isset($data[$field])) {
                continue;
            }

            $value = $data[$field];
            if (!is_numeric($value)) {
                continue;
            }

            if (isset($limits['min']) && $value < $limits['min']) {
                $errors[$field] = "Must be at least {$limits['min']}";
            }

            if (isset($limits['max']) && $value > $limits['max']) {
                $errors[$field] = "Cannot exceed {$limits['max']}";
            }
        }

        if (!empty($errors)) {
            throw new JamboJetValidationException(
                'Numeric range validation failed',
                400,
                ['range_errors' => $errors]
            );
        }
    }

    // =================================================================
    // CONVENIENCE VALIDATION METHODS FOR COMMON PATTERNS
    // =================================================================

    /**
     * Validate availability search request (used by AvailabilityService)
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    protected function validateAvailabilityRequest(array $data): void
    {
        $this->validateRequired($data, ['passengers', 'criteria']);

        // Validate passengers structure
        if (!isset($data['passengers']['types']) || empty($data['passengers']['types'])) {
            throw new JamboJetValidationException('Passenger types are required', 400);
        }

        // Validate criteria structure
        foreach ($data['criteria'] as $index => $criterion) {
            $this->validateRequired($criterion, ['stations', 'dates']);

            if (isset($criterion['stations'])) {
                $this->validateRequired($criterion['stations'], ['departureStation', 'arrivalStation']);
                $this->validateFormats($criterion['stations'], [
                    'departureStation' => 'airport_code',
                    'arrivalStation' => 'airport_code'
                ]);
            }

            if (isset($criterion['dates'])) {
                $this->validateRequired($criterion['dates'], ['beginDate']);
                $this->validateFormats($criterion['dates'], ['beginDate' => 'datetime']);
            }
        }
    }

    /**
     * Validate simple availability request
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    protected function validateSimpleRequest(array $data): void
    {
        $this->validateRequired($data, ['origin', 'destination', 'beginDate', 'passengers']);

        $this->validateFormats($data, [
            'origin' => 'airport_code',
            'destination' => 'airport_code',
            'beginDate' => 'datetime'
        ]);

        if ($data['origin'] === $data['destination']) {
            throw new JamboJetValidationException(
                'Origin and destination cannot be the same',
                400
            );
        }
    }

    /**
     * Validate booking creation request
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    protected function validateBookingRequest(array $data): void
    {
        // For booking creation, we need meaningful data
        if (empty($data['passengers']) && empty($data['journeys'])) {
            throw new JamboJetValidationException(
                'Booking request must include passengers or journeys data',
                400
            );
        }
    }

    /**
     * Validate payment processing request
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    protected function validatePaymentRequest(array $data): void
    {
        $this->validateRequired($data, ['paymentMethodType', 'amount', 'currencyCode']);

        $this->validateFormats($data, [
            'amount' => 'positive_number',
            'currencyCode' => 'currency_code'
        ]);

        $validPaymentMethods = [
            'CreditCard',
            'DebitCard',
            'Cash',
            'Voucher',
            'Loyalty',
            'BankTransfer',
            'PayPal',
            'MobileMoney',
            'Agency',
            'GiftCard'
        ];

        if (!in_array($data['paymentMethodType'], $validPaymentMethods)) {
            throw new JamboJetValidationException(
                'Invalid payment method type. Expected one of: ' . implode(', ', $validPaymentMethods),
                400
            );
        }
    }

    /**
     * Validate record locator format
     * 
     * @param string $recordLocator Record locator to validate
     * @throws JamboJetValidationException
     */
    protected function validateRecordLocator(string $recordLocator): void
    {
        $this->validateFormats(['recordLocator' => $recordLocator], ['recordLocator' => 'record_locator']);
    }

    /**
     * Validate user creation request
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    protected function validateUserRequest(array $data): void
    {
        $this->validateRequired($data, ['username', 'password', 'personalInfo']);

        $this->validateFormats($data, ['username' => 'email']);

        if (isset($data['personalInfo'])) {
            $this->validateRequired($data['personalInfo'], ['firstName', 'lastName', 'email']);
            $this->validateFormats($data['personalInfo'], ['email' => 'email']);
        }
    }
}
