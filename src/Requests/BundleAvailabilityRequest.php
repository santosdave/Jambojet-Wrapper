<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Bundle Availability Request for JamboJet NSK API
 * 
 * Used with: POST /api/nsk/v1/booking/bundle/availability
 * 
 * @package SantosDave\JamboJet\Requests
 */
class BundleAvailabilityRequest extends BaseRequest
{
    /**
     * Create a new bundle availability request
     * 
     * @param array|null $passengerKeys Optional: Specific passenger keys to check availability for
     * @param string|null $currencyCode Optional: Currency code for pricing
     * @param string|null $residentCountry Optional: Country of residence for passengers
     * @param string|null $sourceOrganization Optional: Organization for private fare evaluation
     * @param bool $filterBundles Optional: Filter to only show bundles with SSR availability
     */
    public function __construct(
        public ?array $passengerKeys = null,
        public ?string $currencyCode = null,
        public ?string $residentCountry = null,
        public ?string $sourceOrganization = null,
        public bool $filterBundles = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'passengerKeys' => $this->passengerKeys,
            'currencyCode' => $this->currencyCode,
            'residentCountry' => $this->residentCountry,
            'sourceOrganization' => $this->sourceOrganization,
            'filterBundles' => $this->filterBundles
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate passenger keys if provided
        if ($this->passengerKeys !== null) {
            if (!is_array($this->passengerKeys)) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            foreach ($this->passengerKeys as $index => $passengerKey) {
                if (!is_string($passengerKey) || empty(trim($passengerKey))) {
                    throw new JamboJetValidationException("passengerKeys[{$index}] must be a non-empty string");
                }
            }
        }

        // Validate currency code if provided
        if ($this->currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $this->currencyCode], ['currencyCode' => 'currency']);
        }

        // Validate resident country if provided
        if ($this->residentCountry !== null) {
            $this->validateFormats(['residentCountry' => $this->residentCountry], ['residentCountry' => 'country']);
        }

        // Validate source organization if provided
        if ($this->sourceOrganization !== null) {
            if (!is_string($this->sourceOrganization) || strlen($this->sourceOrganization) > 10) {
                throw new JamboJetValidationException('sourceOrganization must be a string with maximum 10 characters');
            }
        }

        // Validate filter bundles flag
        if (!is_bool($this->filterBundles)) {
            throw new JamboJetValidationException('filterBundles must be a boolean');
        }
    }
}
