<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Base Request Class
 * 
 * Provides common functionality for all request classes including
 * validation, serialization, and error handling
 * 
 * @package SantosDave\JamboJet\Requests
 */
abstract class BaseRequest
{
    /**
     * Convert request to array format
     * 
     * @return array Request data as associative array
     */
    abstract public function toArray(): array;

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    abstract public function validate(): void;

    /**
     * Get validated request data as array
     * 
     * @return array Validated request data
     * @throws JamboJetValidationException
     */
    public function getValidatedData(): array
    {
        $this->validate();
        return $this->toArray();
    }

    /**
     * Convert request to JSON
     * 
     * @return string JSON representation of request
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Check if request has any data
     * 
     * @return bool True if request contains data
     */
    public function hasData(): bool
    {
        return !empty($this->toArray());
    }

    /**
     * Validate required fields are present and not empty
     * 
     * @param array $data Data to validate
     * @param array $requiredFields List of required field names
     * @throws JamboJetValidationException
     */
    protected function validateRequired(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                throw new JamboJetValidationException("Field '{$field}' is required");
            }
        }
    }

    /**
     * Validate field formats
     * 
     * @param array $data Data to validate
     * @param array $formats Field name => format type mapping
     * @throws JamboJetValidationException
     */
    protected function validateFormats(array $data, array $formats): void
    {
        foreach ($formats as $field => $format) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $this->validateFieldFormat($field, $data[$field], $format);
            }
        }
    }

    /**
     * Validate individual field format
     * 
     * @param string $fieldName Field name for error messages
     * @param mixed $value Field value
     * @param string $format Expected format
     * @throws JamboJetValidationException
     */
    protected function validateFieldFormat(string $fieldName, $value, string $format): void
    {
        switch ($format) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid email address");
                }
                break;

            case 'date':
                if (!$this->isValidDate($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid date (YYYY-MM-DD)");
                }
                break;

            case 'datetime':
                if (!$this->isValidDateTime($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid datetime (ISO 8601)");
                }
                break;

            case 'airport_code':
                if (!$this->isValidAirportCode($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid 3-letter airport code");
                }
                break;

            case 'currency':
                if (!$this->isValidCurrencyCode($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid 3-letter currency code");
                }
                break;

            case 'country':
                if (!$this->isValidCountryCode($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid 2-letter country code");
                }
                break;

            case 'phone':
                if (!$this->isValidPhoneNumber($value)) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a valid phone number");
                }
                break;

            case 'positive_integer':
                if (!is_int($value) || $value <= 0) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a positive integer");
                }
                break;

            case 'non_negative_number':
                if (!is_numeric($value) || $value < 0) {
                    throw new JamboJetValidationException("Field '{$fieldName}' must be a non-negative number");
                }
                break;

            default:
                throw new JamboJetValidationException("Unknown validation format: {$format}");
        }
    }

    /**
     * Validate array fields structure
     * 
     * @param array $data Data to validate
     * @param array $arrayFields Field name => validation rules mapping
     * @throws JamboJetValidationException
     */
    protected function validateArrayFields(array $data, array $arrayFields): void
    {
        foreach ($arrayFields as $field => $rules) {
            if (isset($data[$field])) {
                if (!is_array($data[$field])) {
                    throw new JamboJetValidationException("Field '{$field}' must be an array");
                }

                // Check minimum items if specified
                if (isset($rules['min_items']) && count($data[$field]) < $rules['min_items']) {
                    throw new JamboJetValidationException("Field '{$field}' must have at least {$rules['min_items']} items");
                }

                // Check maximum items if specified
                if (isset($rules['max_items']) && count($data[$field]) > $rules['max_items']) {
                    throw new JamboJetValidationException("Field '{$field}' must have at most {$rules['max_items']} items");
                }

                // Validate each item if validation rule specified
                if (isset($rules['item_validation'])) {
                    foreach ($data[$field] as $index => $item) {
                        $this->validateArrayItem($field, $index, $item, $rules['item_validation']);
                    }
                }
            }
        }
    }

    /**
     * Validate individual array item
     * 
     * @param string $fieldName Parent field name
     * @param int $index Item index
     * @param mixed $item Item value
     * @param array $rules Validation rules
     * @throws JamboJetValidationException
     */
    protected function validateArrayItem(string $fieldName, int $index, $item, array $rules): void
    {
        $itemPath = "{$fieldName}[{$index}]";

        // Check required fields in item
        if (isset($rules['required']) && is_array($item)) {
            foreach ($rules['required'] as $requiredField) {
                if (!isset($item[$requiredField]) || $item[$requiredField] === null || $item[$requiredField] === '') {
                    throw new JamboJetValidationException("Field '{$itemPath}.{$requiredField}' is required");
                }
            }
        }

        // Check item formats
        if (isset($rules['formats']) && is_array($item)) {
            foreach ($rules['formats'] as $fieldKey => $format) {
                if (isset($item[$fieldKey])) {
                    $this->validateFieldFormat("{$itemPath}.{$fieldKey}", $item[$fieldKey], $format);
                }
            }
        }
    }

    /**
     * Filter null values from array
     * 
     * @param array $data Input array
     * @return array Filtered array without null values
     */
    protected function filterNulls(array $data): array
    {
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Filter empty values from array
     * 
     * @param array $data Input array
     * @return array Filtered array without empty values
     */
    protected function filterEmpty(array $data): array
    {
        return array_filter($data, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        });
    }

    // ==========================================
    // FORMAT VALIDATION HELPERS
    // ==========================================

    /**
     * Check if value is a valid date
     * 
     * @param string $date Date string
     * @return bool True if valid date
     */
    private function isValidDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) &&
            strtotime($date) !== false;
    }

    /**
     * Check if value is a valid datetime
     * 
     * @param string $datetime Datetime string
     * @return bool True if valid datetime
     */
    private function isValidDateTime(string $datetime): bool
    {
        return (bool) strtotime($datetime) &&
            (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $datetime) ||
                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $datetime));
    }

    /**
     * Check if value is a valid airport code
     * 
     * @param string $code Airport code
     * @return bool True if valid airport code
     */
    private function isValidAirportCode(string $code): bool
    {
        return (bool) preg_match('/^[A-Z]{3}$/', $code);
    }

    /**
     * Check if value is a valid currency code
     * 
     * @param string $code Currency code
     * @return bool True if valid currency code
     */
    private function isValidCurrencyCode(string $code): bool
    {
        return (bool) preg_match('/^[A-Z]{3}$/', $code);
    }

    /**
     * Check if value is a valid country code
     * 
     * @param string $code Country code
     * @return bool True if valid country code
     */
    private function isValidCountryCode(string $code): bool
    {
        return (bool) preg_match('/^[A-Z]{2}$/', $code);
    }

    /**
     * Check if value is a valid phone number
     * 
     * @param string $phone Phone number
     * @return bool True if valid phone number
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Allow various phone number formats
        return (bool) preg_match('/^[\+]?[0-9\s\-\(\)\.]{7,20}$/', $phone);
    }
}
