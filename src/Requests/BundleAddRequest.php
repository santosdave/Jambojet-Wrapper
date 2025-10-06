<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Bundle Add Request for JamboJet NSK API
 * 
 * Used with: POST /api/nsk/v1/booking/bundle/add
 * 
 * @package SantosDave\JamboJet\Requests
 */
class BundleAddRequest extends BaseRequest
{
    /**
     * Create a new bundle add request
     * 
     * @param string $bundleCode Required: Bundle code to sell (max 4 characters)
     * @param array|null $passengerKeys Optional: List of passenger keys to sell the bundle for
     * @param array|null $upgradeRequest Optional: SSR upgrade request for customizing bundle SSRs
     * @param bool $forceWaveOnSell Optional: Force wave on sell (default: false)
     * @param string|null $currencyCode Optional: Currency code if different from booking currency
     */
    public function __construct(
        public string $bundleCode,
        public ?array $passengerKeys = null,
        public ?array $upgradeRequest = null,
        public bool $forceWaveOnSell = false,
        public ?string $currencyCode = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        $data = [
            'bundleCode' => $this->bundleCode
        ];

        if ($this->passengerKeys !== null) {
            $data['passengerKeys'] = $this->passengerKeys;
        }

        if ($this->upgradeRequest !== null) {
            $data['upgradeRequest'] = $this->upgradeRequest;
        }

        if ($this->forceWaveOnSell) {
            $data['forceWaveOnSell'] = $this->forceWaveOnSell;
        }

        if ($this->currencyCode !== null) {
            $data['currencyCode'] = $this->currencyCode;
        }

        return $data;
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate required bundle code
        $this->validateRequired(['bundleCode' => $this->bundleCode], ['bundleCode']);

        // Validate bundle code format
        if (!is_string($this->bundleCode) || empty(trim($this->bundleCode))) {
            throw new JamboJetValidationException('bundleCode must be a non-empty string');
        }

        if (strlen($this->bundleCode) > 4) {
            throw new JamboJetValidationException('bundleCode must be maximum 4 characters');
        }

        if (!preg_match('/^[A-Z0-9_-]+$/', $this->bundleCode)) {
            throw new JamboJetValidationException('bundleCode must contain only uppercase letters, numbers, underscores, and hyphens');
        }

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

        // Validate upgrade request if provided
        if ($this->upgradeRequest !== null) {
            if (!is_array($this->upgradeRequest)) {
                throw new JamboJetValidationException('upgradeRequest must be an array');
            }

            // Validate upgrade request structure
            if (isset($this->upgradeRequest['keys'])) {
                if (!is_array($this->upgradeRequest['keys'])) {
                    throw new JamboJetValidationException('upgradeRequest.keys must be an array');
                }

                foreach ($this->upgradeRequest['keys'] as $index => $ssrRequest) {
                    if (!is_array($ssrRequest)) {
                        throw new JamboJetValidationException("upgradeRequest.keys[{$index}] must be an array");
                    }

                    // Validate required ssrKey
                    if (!isset($ssrRequest['ssrKey']) || !is_string($ssrRequest['ssrKey']) || empty(trim($ssrRequest['ssrKey']))) {
                        throw new JamboJetValidationException("upgradeRequest.keys[{$index}].ssrKey is required and must be a non-empty string");
                    }

                    // Validate count if provided
                    if (isset($ssrRequest['count'])) {
                        if (!is_int($ssrRequest['count']) || $ssrRequest['count'] < 1 || $ssrRequest['count'] > 32767) {
                            throw new JamboJetValidationException("upgradeRequest.keys[{$index}].count must be an integer between 1 and 32767");
                        }
                    }

                    // Validate note if provided
                    if (isset($ssrRequest['note']) && !is_string($ssrRequest['note'])) {
                        throw new JamboJetValidationException("upgradeRequest.keys[{$index}].note must be a string");
                    }
                }
            }

            // Validate forceWaveOnSell if provided in upgrade request
            if (isset($this->upgradeRequest['forceWaveOnSell']) && !is_bool($this->upgradeRequest['forceWaveOnSell'])) {
                throw new JamboJetValidationException('upgradeRequest.forceWaveOnSell must be a boolean');
            }
        }

        // Validate forceWaveOnSell flag
        if (!is_bool($this->forceWaveOnSell)) {
            throw new JamboJetValidationException('forceWaveOnSell must be a boolean');
        }

        // Validate currency code if provided
        if ($this->currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $this->currencyCode], ['currencyCode' => 'currency']);
        }
    }
}
