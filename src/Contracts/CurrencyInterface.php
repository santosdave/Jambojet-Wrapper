<?php


namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

/**
 * Currency Interface for JamboJet NSK API
 * 
 * Handles currency conversion calculations with exchange rates.
 * Base endpoint: /api/nsk/v1/currency
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface CurrencyInterface
{
    /**
     * Calculate currency conversion
     * GET /api/nsk/v1/currency/converter
     * 
     * Converts amount from one currency to another using exchange rates.
     * If inverted is true, uses 1/(exchange rate) for calculation.
     * 
     * @param string $fromCurrencyCode Currency code amount is in (3 chars)
     * @param string $toCurrencyCode Currency code to convert to (3 chars)
     * @param float $amount Amount to convert
     * @param bool $inverted Use inverse exchange rate (default: false)
     * @return array Conversion result with converted amount and exchange rate
     * @throws JamboJetApiException
     */
    public function convertCurrency(
        string $fromCurrencyCode,
        string $toCurrencyCode,
        float $amount,
        bool $inverted = false
    ): array;
}