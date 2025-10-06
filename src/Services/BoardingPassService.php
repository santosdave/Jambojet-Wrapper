<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\BoardingPassInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Boarding Pass Service for JamboJet NSK API
 * 
 * Handles all boarding pass operations including retrieval, validation, and barcode generation
 * Base endpoints: /api/nsk/v3/booking/boardingpasses
 * 
 * Supported endpoints:
 * - GET /api/nsk/v3/booking/boardingpasses - Get all boarding passes for booking
 * - GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey} - Get boarding passes by segment
 * - GET /api/nsk/v3/booking/boardingpasses/passenger/{passengerKey} - Get boarding passes by passenger
 * - GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey}/passenger/{passengerKey} - Get specific boarding pass
 * - POST /api/nsk/v3/booking/boardingpasses/barcode - Generate boarding pass barcode
 * - POST /api/nsk/v3/booking/boardingpasses/validate - Validate boarding pass eligibility
 * 
 * @package SantosDave\JamboJet\Services
 */
class BoardingPassService implements BoardingPassInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Get boarding passes for booking in state
     * GET /api/nsk/v3/booking/boardingpasses
     */
    public function getBoardingPasses(array $criteria = []): array
    {
        $this->validateBoardingPassCriteria($criteria);

        $queryParams = $this->buildQueryParams($criteria);

        return $this->get('/api/nsk/v3/booking/boardingpasses', $queryParams);
    }

    /**
     * Get boarding passes by segment
     * GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey}
     */
    public function getBoardingPassesBySegment(string $segmentKey, array $options = []): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validateBoardingPassOptions($options);

        $queryParams = $this->buildQueryParams($options);

        return $this->get("/api/nsk/v3/booking/boardingpasses/segment/{$segmentKey}", $queryParams);
    }

    /**
     * Get boarding passes by passenger
     * GET /api/nsk/v3/booking/boardingpasses/passenger/{passengerKey}
     */
    public function getBoardingPassesByPassenger(string $passengerKey, array $options = []): array
    {
        $this->validatePassengerKey($passengerKey);
        $this->validateBoardingPassOptions($options);

        $queryParams = $this->buildQueryParams($options);

        return $this->get("/api/nsk/v3/booking/boardingpasses/passenger/{$passengerKey}", $queryParams);
    }

    /**
     * Get boarding pass for specific passenger on specific segment
     * GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey}/passenger/{passengerKey}
     */
    public function getBoardingPassForPassengerSegment(string $segmentKey, string $passengerKey, array $options = []): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateBoardingPassOptions($options);

        $queryParams = $this->buildQueryParams($options);

        return $this->get("/api/nsk/v3/booking/boardingpasses/segment/{$segmentKey}/passenger/{$passengerKey}", $queryParams);
    }

    /**
     * Generate boarding pass barcode
     * POST /api/nsk/v3/booking/boardingpasses/barcode
     */
    public function generateBarcode(array $barcodeData): array
    {
        $this->validateBarcodeRequest($barcodeData);

        return $this->post('/api/nsk/v3/booking/boardingpasses/barcode', $barcodeData);
    }

    /**
     * Validate boarding pass eligibility
     * POST /api/nsk/v3/booking/boardingpasses/validate
     */
    public function validateEligibility(array $validationCriteria): array
    {
        $this->validateEligibilityRequest($validationCriteria);

        return $this->post('/api/nsk/v3/booking/boardingpasses/validate', $validationCriteria);
    }

    // ==========================================
    // CONVENIENCE METHODS
    // ==========================================

    /**
     * Get all boarding passes for current booking state
     * 
     * @return array All boarding passes
     */
    public function getAllBoardingPasses(): array
    {
        return $this->getBoardingPasses();
    }

    /**
     * Check if boarding passes are available for booking
     * 
     * @return bool True if boarding passes can be generated
     */
    public function areBoardingPassesAvailable(): array
    {
        return $this->validateEligibility(['checkAvailability' => true]);
    }

    /**
     * Get boarding passes with specific format
     * 
     * @param string $format Format type (pdf, html, json)
     * @return array Formatted boarding passes
     */
    public function getBoardingPassesInFormat(string $format = 'json'): array
    {
        return $this->getBoardingPasses(['format' => $format]);
    }

    /**
     * Get boarding passes for specific flight
     * 
     * @param string $flightNumber Flight number
     * @param string $departureDate Departure date (YYYY-MM-DD)
     * @return array Boarding passes for the flight
     */
    public function getBoardingPassesForFlight(string $flightNumber, string $departureDate): array
    {
        return $this->getBoardingPasses([
            'flightNumber' => $flightNumber,
            'departureDate' => $departureDate
        ]);
    }

    // ==========================================
    // VALIDATION METHODS (No Empty Arrays!)
    // ==========================================

    /**
     * Validate boarding pass criteria
     */
    private function validateBoardingPassCriteria(array $criteria): void
    {
        // Optional criteria, but if provided should be valid
        if (!empty($criteria)) {
            // Validate format if provided
            if (isset($criteria['format'])) {
                $allowedFormats = ['json', 'pdf', 'html', 'xml'];
                if (!in_array(strtolower($criteria['format']), $allowedFormats)) {
                    throw new JamboJetValidationException('format must be one of: ' . implode(', ', $allowedFormats));
                }
            }

            // Validate flight number format if provided
            if (isset($criteria['flightNumber'])) {
                if (!is_string($criteria['flightNumber']) || empty(trim($criteria['flightNumber']))) {
                    throw new JamboJetValidationException('flightNumber must be a non-empty string');
                }

                // Flight number format validation (typically airline code + number)
                if (!preg_match('/^[A-Z]{2,3}[0-9]{1,4}[A-Z]?$/', $criteria['flightNumber'])) {
                    throw new JamboJetValidationException('flightNumber format is invalid (expected: XX123 or XXX1234)');
                }
            }

            // Validate departure date if provided
            if (isset($criteria['departureDate'])) {
                $this->validateFormats($criteria, ['departureDate' => 'date']);
            }

            // Validate include options if provided
            if (isset($criteria['includeBarcode']) && !is_bool($criteria['includeBarcode'])) {
                throw new JamboJetValidationException('includeBarcode must be a boolean');
            }

            if (isset($criteria['includeDetails']) && !is_bool($criteria['includeDetails'])) {
                throw new JamboJetValidationException('includeDetails must be a boolean');
            }
        }
    }

    /**
     * Validate segment key parameter
     */
    private function validateSegmentKey(string $segmentKey): void
    {
        if (empty(trim($segmentKey))) {
            throw new JamboJetValidationException('segmentKey cannot be empty');
        }

        // Segment key format validation (typically alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $segmentKey)) {
            throw new JamboJetValidationException('segmentKey contains invalid characters');
        }

        // Length validation (segment keys are typically 32-64 characters)
        if (strlen($segmentKey) < 8 || strlen($segmentKey) > 64) {
            throw new JamboJetValidationException('segmentKey length must be between 8 and 64 characters');
        }
    }

    /**
     * Validate passenger key parameter
     */
    private function validatePassengerKey(string $passengerKey): void
    {
        if (empty(trim($passengerKey))) {
            throw new JamboJetValidationException('passengerKey cannot be empty');
        }

        // Passenger key format validation (typically alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $passengerKey)) {
            throw new JamboJetValidationException('passengerKey contains invalid characters');
        }

        // Length validation (passenger keys are typically 32-64 characters)
        if (strlen($passengerKey) < 8 || strlen($passengerKey) > 64) {
            throw new JamboJetValidationException('passengerKey length must be between 8 and 64 characters');
        }
    }

    /**
     * Validate boarding pass options
     */
    private function validateBoardingPassOptions(array $options): void
    {
        // Use same validation as criteria since they're similar
        $this->validateBoardingPassCriteria($options);

        // Additional option-specific validations
        if (isset($options['language'])) {
            if (!is_string($options['language']) || !preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $options['language'])) {
                throw new JamboJetValidationException('language must be in format "en" or "en-US"');
            }
        }

        if (isset($options['timezone'])) {
            if (!is_string($options['timezone']) || empty(trim($options['timezone']))) {
                throw new JamboJetValidationException('timezone must be a non-empty string');
            }
        }
    }

    /**
     * Validate barcode generation request
     */
    private function validateBarcodeRequest(array $data): void
    {
        $this->validateRequired($data, ['segmentKey', 'passengerKey']);

        // Validate segment and passenger keys
        $this->validateSegmentKey($data['segmentKey']);
        $this->validatePassengerKey($data['passengerKey']);

        // Validate barcode type if provided
        if (isset($data['barcodeType'])) {
            $allowedTypes = ['PDF417', 'QR', 'CODE128', 'AZTEC'];
            if (!in_array(strtoupper($data['barcodeType']), $allowedTypes)) {
                throw new JamboJetValidationException('barcodeType must be one of: ' . implode(', ', $allowedTypes));
            }
        }

        // Validate barcode format if provided
        if (isset($data['format'])) {
            $allowedFormats = ['base64', 'url', 'binary'];
            if (!in_array(strtolower($data['format']), $allowedFormats)) {
                throw new JamboJetValidationException('format must be one of: ' . implode(', ', $allowedFormats));
            }
        }

        // Validate dimensions if provided
        if (isset($data['width'])) {
            if (!is_int($data['width']) || $data['width'] < 100 || $data['width'] > 2000) {
                throw new JamboJetValidationException('width must be an integer between 100 and 2000');
            }
        }

        if (isset($data['height'])) {
            if (!is_int($data['height']) || $data['height'] < 100 || $data['height'] > 2000) {
                throw new JamboJetValidationException('height must be an integer between 100 and 2000');
            }
        }

        // Validate DPI if provided
        if (isset($data['dpi'])) {
            if (!is_int($data['dpi']) || $data['dpi'] < 72 || $data['dpi'] > 600) {
                throw new JamboJetValidationException('dpi must be an integer between 72 and 600');
            }
        }
    }

    /**
     * Validate eligibility check request
     */
    private function validateEligibilityRequest(array $data): void
    {
        // At least one identifier should be provided
        if (empty($data)) {
            throw new JamboJetValidationException('Validation criteria cannot be empty');
        }

        // Validate segment keys if provided
        if (isset($data['segmentKeys'])) {
            if (!is_array($data['segmentKeys'])) {
                throw new JamboJetValidationException('segmentKeys must be an array');
            }

            foreach ($data['segmentKeys'] as $segmentKey) {
                $this->validateSegmentKey($segmentKey);
            }
        }

        // Validate passenger keys if provided
        if (isset($data['passengerKeys'])) {
            if (!is_array($data['passengerKeys'])) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            foreach ($data['passengerKeys'] as $passengerKey) {
                $this->validatePassengerKey($passengerKey);
            }
        }

        // Validate check availability flag if provided
        if (isset($data['checkAvailability']) && !is_bool($data['checkAvailability'])) {
            throw new JamboJetValidationException('checkAvailability must be a boolean');
        }

        // Validate check timing if provided
        if (isset($data['checkTiming']) && !is_bool($data['checkTiming'])) {
            throw new JamboJetValidationException('checkTiming must be a boolean');
        }

        // Validate current time if provided
        if (isset($data['currentTime'])) {
            $this->validateFormats($data, ['currentTime' => 'datetime']);
        }

        // Validate validation rules if provided
        if (isset($data['validationRules']) && !is_array($data['validationRules'])) {
            throw new JamboJetValidationException('validationRules must be an array');
        }
    }

    /**
     * Build query parameters for GET requests
     */
    private function buildQueryParams(array $params): array
    {
        $queryParams = [];

        // Convert array parameters to query string format
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                if (is_bool($value)) {
                    $queryParams[$key] = $value ? 'true' : 'false';
                } elseif (is_array($value)) {
                    $queryParams[$key] = implode(',', $value);
                } else {
                    $queryParams[$key] = (string)$value;
                }
            }
        }

        return $queryParams;
    }
}
