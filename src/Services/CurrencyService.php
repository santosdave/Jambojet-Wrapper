<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\CurrencyInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Currency Service for JamboJet NSK API
 * 
 * Handles currency conversion calculations with exchange rates.
 * 
 * Supported endpoint:
 * - GET /api/nsk/v1/currency/converter
 * 
 * @package SantosDave\JamboJet\Services
 */
class CurrencyService implements CurrencyInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Calculate currency conversion
     * GET /api/nsk/v1/currency/converter
     * 
     * Example Response:
     * {
     *   "data": {
     *     "convertedAmount": 2319.4,
     *     "fromCurrencyCode": "EUR",
     *     "exchangeRate": 1.16,
     *     "toCurrencyCode": "USD",
     *     "roundingFactor": 0.1,
     *     "amount": 1999.52,
     *     "marketingRoundingFactor": 0.1,
     *     "inverted": false
     *   }
     * }
     */
    public function convertCurrency(
        string $fromCurrencyCode,
        string $toCurrencyCode,
        float $amount,
        bool $inverted = false
    ): array {
        // Validate currency codes
        $this->validateCurrencyCode($fromCurrencyCode, 'From currency code');
        $this->validateCurrencyCode($toCurrencyCode, 'To currency code');

        // Validate amount
        if ($amount <= 0) {
            throw new JamboJetValidationException('Amount must be greater than zero', 400);
        }

        // Build query parameters
        $params = [
            'FromCurrencyCode' => strtoupper($fromCurrencyCode),
            'ToCurrencyCode' => strtoupper($toCurrencyCode),
            'Amount' => $amount,
            'Inverted' => $inverted,
        ];

        try {
            return $this->get('api/nsk/v1/currency/converter', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Currency conversion failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate currency code format
     * 
     * @param string $currencyCode Currency code to validate
     * @param string $fieldName Field name for error messages
     * @throws JamboJetValidationException
     */
    private function validateCurrencyCode(string $currencyCode, string $fieldName): void
    {
        if (empty($currencyCode)) {
            throw new JamboJetValidationException("{$fieldName} is required", 400);
        }

        // Currency codes must be 3 characters (ISO 4217)
        if (strlen($currencyCode) < 1 || strlen($currencyCode) > 3) {
            throw new JamboJetValidationException(
                "{$fieldName} must be 1-3 characters (ISO 4217 format)",
                400
            );
        }

        // Currency codes should be uppercase letters only
        if (!preg_match('/^[A-Z]{1,3}$/i', $currencyCode)) {
            throw new JamboJetValidationException(
                "{$fieldName} must contain only letters (A-Z)",
                400
            );
        }
    }
}
