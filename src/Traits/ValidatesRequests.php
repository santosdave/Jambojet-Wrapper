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
