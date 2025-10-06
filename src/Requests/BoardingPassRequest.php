<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Boarding Pass Request for JamboJet NSK API
 * 
 * Used with: 
 * - POST /api/nsk/v3/booking/boardingpasses/barcode
 * - POST /api/nsk/v3/booking/boardingpasses/validate
 * 
 * @package SantosDave\JamboJet\Requests
 */
class BoardingPassRequest extends BaseRequest
{
    /**
     * Create a new boarding pass request
     * 
     * @param string $requestType Required: Type of request ('barcode' or 'validate')
     * @param string|null $segmentKey Required for barcode: Segment key
     * @param string|null $passengerKey Required for barcode: Passenger key
     * @param array|null $segmentKeys Optional for validate: Array of segment keys
     * @param array|null $passengerKeys Optional for validate: Array of passenger keys
     * @param string|null $barcodeType Optional: Barcode type (PDF417, QR, CODE128, AZTEC)
     * @param string|null $format Optional: Output format (base64, url, binary)
     * @param int|null $width Optional: Barcode width (100-2000)
     * @param int|null $height Optional: Barcode height (100-2000)
     * @param int|null $dpi Optional: DPI setting (72-600)
     * @param bool $checkAvailability Optional: Check availability flag for validation
     * @param bool $checkTiming Optional: Check timing flag for validation
     * @param string|null $currentTime Optional: Current time for validation
     * @param array|null $validationRules Optional: Custom validation rules
     */
    public function __construct(
        public string $requestType,
        public ?string $segmentKey = null,
        public ?string $passengerKey = null,
        public ?array $segmentKeys = null,
        public ?array $passengerKeys = null,
        public ?string $barcodeType = null,
        public ?string $format = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $dpi = null,
        public bool $checkAvailability = false,
        public bool $checkTiming = false,
        public ?string $currentTime = null,
        public ?array $validationRules = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        if ($this->requestType === 'barcode') {
            return $this->toBarcodeArray();
        } elseif ($this->requestType === 'validate') {
            return $this->toValidateArray();
        }

        return [];
    }

    /**
     * Convert to barcode request array
     * 
     * @return array Barcode request data
     */
    private function toBarcodeArray(): array
    {
        return $this->filterNulls([
            'segmentKey' => $this->segmentKey,
            'passengerKey' => $this->passengerKey,
            'barcodeType' => $this->barcodeType,
            'format' => $this->format,
            'width' => $this->width,
            'height' => $this->height,
            'dpi' => $this->dpi
        ]);
    }

    /**
     * Convert to validation request array
     * 
     * @return array Validation request data
     */
    private function toValidateArray(): array
    {
        return $this->filterNulls([
            'segmentKeys' => $this->segmentKeys,
            'passengerKeys' => $this->passengerKeys,
            'checkAvailability' => $this->checkAvailability,
            'checkTiming' => $this->checkTiming,
            'currentTime' => $this->currentTime,
            'validationRules' => $this->validationRules
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate request type
        $validTypes = ['barcode', 'validate'];
        if (!in_array($this->requestType, $validTypes)) {
            throw new JamboJetValidationException('requestType must be one of: ' . implode(', ', $validTypes));
        }

        if ($this->requestType === 'barcode') {
            $this->validateBarcodeRequest();
        } elseif ($this->requestType === 'validate') {
            $this->validateEligibilityRequest();
        }
    }

    /**
     * Validate barcode generation request
     * 
     * @throws JamboJetValidationException
     */
    private function validateBarcodeRequest(): void
    {
        // Validate required fields for barcode generation
        $this->validateRequired([
            'segmentKey' => $this->segmentKey,
            'passengerKey' => $this->passengerKey
        ], ['segmentKey', 'passengerKey']);

        // Validate segment key format
        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $this->segmentKey)) {
            throw new JamboJetValidationException('segmentKey must be 8-64 characters containing only letters, numbers, underscores, and hyphens');
        }

        // Validate passenger key format
        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $this->passengerKey)) {
            throw new JamboJetValidationException('passengerKey must be 8-64 characters containing only letters, numbers, underscores, and hyphens');
        }

        // Validate barcode type if provided
        if ($this->barcodeType !== null) {
            $validTypes = ['PDF417', 'QR', 'CODE128', 'AZTEC'];
            if (!in_array(strtoupper($this->barcodeType), $validTypes)) {
                throw new JamboJetValidationException('barcodeType must be one of: ' . implode(', ', $validTypes));
            }
        }

        // Validate format if provided
        if ($this->format !== null) {
            $validFormats = ['base64', 'url', 'binary'];
            if (!in_array(strtolower($this->format), $validFormats)) {
                throw new JamboJetValidationException('format must be one of: ' . implode(', ', $validFormats));
            }
        }

        // Validate dimensions if provided
        if ($this->width !== null && (!is_int($this->width) || $this->width < 100 || $this->width > 2000)) {
            throw new JamboJetValidationException('width must be an integer between 100 and 2000');
        }

        if ($this->height !== null && (!is_int($this->height) || $this->height < 100 || $this->height > 2000)) {
            throw new JamboJetValidationException('height must be an integer between 100 and 2000');
        }

        // Validate DPI if provided
        if ($this->dpi !== null && (!is_int($this->dpi) || $this->dpi < 72 || $this->dpi > 600)) {
            throw new JamboJetValidationException('dpi must be an integer between 72 and 600');
        }
    }

    /**
     * Validate eligibility check request
     * 
     * @throws JamboJetValidationException
     */
    private function validateEligibilityRequest(): void
    {
        // At least one identifier should be provided
        if (empty($this->segmentKeys) && empty($this->passengerKeys)) {
            throw new JamboJetValidationException('Either segmentKeys or passengerKeys must be provided for validation');
        }

        // Validate segment keys if provided
        if ($this->segmentKeys !== null) {
            if (!is_array($this->segmentKeys)) {
                throw new JamboJetValidationException('segmentKeys must be an array');
            }

            foreach ($this->segmentKeys as $index => $segmentKey) {
                if (!is_string($segmentKey) || !preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $segmentKey)) {
                    throw new JamboJetValidationException("segmentKeys[{$index}] must be 8-64 characters containing only letters, numbers, underscores, and hyphens");
                }
            }
        }

        // Validate passenger keys if provided
        if ($this->passengerKeys !== null) {
            if (!is_array($this->passengerKeys)) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            foreach ($this->passengerKeys as $index => $passengerKey) {
                if (!is_string($passengerKey) || !preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $passengerKey)) {
                    throw new JamboJetValidationException("passengerKeys[{$index}] must be 8-64 characters containing only letters, numbers, underscores, and hyphens");
                }
            }
        }

        // Validate boolean flags
        if (!is_bool($this->checkAvailability)) {
            throw new JamboJetValidationException('checkAvailability must be a boolean');
        }

        if (!is_bool($this->checkTiming)) {
            throw new JamboJetValidationException('checkTiming must be a boolean');
        }

        // Validate current time if provided
        if ($this->currentTime !== null) {
            $this->validateFormats(['currentTime' => $this->currentTime], ['currentTime' => 'datetime']);
        }

        // Validate validation rules if provided
        if ($this->validationRules !== null && !is_array($this->validationRules)) {
            throw new JamboJetValidationException('validationRules must be an array');
        }
    }

    /**
     * Create a barcode generation request
     * 
     * @param string $segmentKey Segment key
     * @param string $passengerKey Passenger key
     * @param string|null $barcodeType Optional barcode type
     * @param string|null $format Optional output format
     * @return self
     */
    public static function forBarcode(string $segmentKey, string $passengerKey, ?string $barcodeType = null, ?string $format = null): self
    {
        return new self(
            requestType: 'barcode',
            segmentKey: $segmentKey,
            passengerKey: $passengerKey,
            barcodeType: $barcodeType,
            format: $format
        );
    }

    /**
     * Create an eligibility validation request
     * 
     * @param array|null $segmentKeys Optional segment keys
     * @param array|null $passengerKeys Optional passenger keys
     * @param bool $checkAvailability Check availability flag
     * @param bool $checkTiming Check timing flag
     * @return self
     */
    public static function forValidation(?array $segmentKeys = null, ?array $passengerKeys = null, bool $checkAvailability = false, bool $checkTiming = false): self
    {
        return new self(
            requestType: 'validate',
            segmentKeys: $segmentKeys,
            passengerKeys: $passengerKeys,
            checkAvailability: $checkAvailability,
            checkTiming: $checkTiming
        );
    }
}
