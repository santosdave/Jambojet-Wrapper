<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Booking Create Request
 * 
 * Handles booking creation/commit requests for NSK API v3
 * Endpoint: POST /api/nsk/v3/booking
 * 
 * @package SantosDave\JamboJet\Requests
 */
class BookingCreateRequest extends BaseRequest
{
    /**
     * Create new booking creation request
     * 
     * @param array|null $passengers Optional: Passenger details to add/update
     * @param array|null $journeys Optional: Journey selections and fare details
     * @param array|null $contactDetails Optional: Contact information
     * @param array|null $specialRequests Optional: Special service requests (SSRs)
     * @param array|null $comments Optional: Booking comments
     * @param array|null $preferences Optional: Customer preferences
     * @param string|null $currencyCode Optional: Booking currency code
     * @param bool $validateOnly Optional: Validate only without committing (default: false)
     * @param bool $bypassWarnings Optional: Bypass validation warnings (default: false)
     */
    public function __construct(
        public ?array $passengers = null,
        public ?array $journeys = null,
        public ?array $contactDetails = null,
        public ?array $specialRequests = null,
        public ?array $comments = null,
        public ?array $preferences = null,
        public ?string $currencyCode = null,
        public bool $validateOnly = false,
        public bool $bypassWarnings = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'passengers' => $this->passengers,
            'journeys' => $this->journeys,
            'contactDetails' => $this->contactDetails,
            'specialRequests' => $this->specialRequests,
            'comments' => $this->comments,
            'preferences' => $this->preferences,
            'currencyCode' => $this->currencyCode,
            'validateOnly' => $this->validateOnly,
            'bypassWarnings' => $this->bypassWarnings,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // For booking creation, we need at least passengers or journeys to be meaningful
        if (empty($this->passengers) && empty($this->journeys)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Booking request must include passengers or journeys data',
                400
            );
        }

        // Validate passengers if provided
        if ($this->passengers) {
            $this->validatePassengers($this->passengers);
        }

        // Validate journeys if provided
        if ($this->journeys) {
            $this->validateJourneys($this->journeys);
        }

        // Validate contact details if provided
        if ($this->contactDetails) {
            $this->validateContactDetails($this->contactDetails);
        }

        // Validate currency code if provided
        if ($this->currencyCode) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate special requests if provided
        if ($this->specialRequests) {
            $this->validateSpecialRequests($this->specialRequests);
        }
    }

    /**
     * Validate passengers data
     * 
     * @param array $passengers Passenger data
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassengers(array $passengers): void
    {
        foreach ($passengers as $index => $passenger) {
            // Basic passenger information validation
            if (isset($passenger['name'])) {
                $this->validatePassengerName($passenger['name'], $index);
            }

            // Validate passenger type if provided
            if (isset($passenger['type'])) {
                $this->validateFormats(['type' => $passenger['type']], ['type' => 'passenger_type']);
            }

            // Validate travel documents if provided
            if (isset($passenger['travelDocuments'])) {
                $this->validateTravelDocuments($passenger['travelDocuments'], $index);
            }

            // Validate contact info if provided
            if (isset($passenger['contactInfo'])) {
                $this->validatePassengerContact($passenger['contactInfo'], $index);
            }

            // Validate date of birth if provided
            if (isset($passenger['dateOfBirth'])) {
                $this->validateFormats(['dob' => $passenger['dateOfBirth']], ['dob' => 'date']);
            }
        }
    }

    /**
     * Validate passenger name structure
     * 
     * @param array $name Name data
     * @param int $passengerIndex Passenger index for error reporting
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassengerName(array $name, int $passengerIndex): void
    {
        // First and last names are typically required
        $requiredFields = ['first', 'last'];

        try {
            $this->validateRequired($name, $requiredFields);
        } catch (\SantosDave\JamboJet\Exceptions\JamboJetValidationException $e) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Passenger {$passengerIndex} name validation failed: " . $e->getMessage(),
                400
            );
        }

        // Validate name lengths (common airline restrictions)
        foreach (['first', 'middle', 'last'] as $nameField) {
            if (isset($name[$nameField]) && strlen($name[$nameField]) > 30) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Passenger {$passengerIndex} {$nameField} name exceeds 30 characters",
                    400
                );
            }
        }
    }

    /**
     * Validate travel documents
     * 
     * @param array $documents Travel documents
     * @param int $passengerIndex Passenger index for error reporting
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateTravelDocuments(array $documents, int $passengerIndex): void
    {
        foreach ($documents as $docIndex => $document) {
            if (isset($document['number']) && empty(trim($document['number']))) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Passenger {$passengerIndex} document {$docIndex} number cannot be empty",
                    400
                );
            }

            if (isset($document['expiryDate'])) {
                $this->validateFormats(['expiry' => $document['expiryDate']], ['expiry' => 'date']);

                // Check document is not expired
                $expiryDate = new \DateTime($document['expiryDate']);
                $now = new \DateTime();

                if ($expiryDate < $now) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Passenger {$passengerIndex} document {$docIndex} has expired",
                        400
                    );
                }
            }

            if (isset($document['issuingCountry'])) {
                $this->validateFormats(['country' => $document['issuingCountry']], ['country' => 'country_code']);
            }
        }
    }

    /**
     * Validate passenger contact information
     * 
     * @param array $contact Contact information
     * @param int $passengerIndex Passenger index for error reporting
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassengerContact(array $contact, int $passengerIndex): void
    {
        if (isset($contact['email'])) {
            $this->validateFormats(['email' => $contact['email']], ['email' => 'email']);
        }

        if (isset($contact['phone']) && empty(trim($contact['phone']))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Passenger {$passengerIndex} phone number cannot be empty",
                400
            );
        }
    }

    /**
     * Validate journeys data
     * 
     * @param array $journeys Journey selections
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateJourneys(array $journeys): void
    {
        foreach ($journeys as $index => $journey) {
            if (isset($journey['fareAvailabilityKey']) && empty(trim($journey['fareAvailabilityKey']))) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Journey {$index} fareAvailabilityKey cannot be empty",
                    400
                );
            }

            if (isset($journey['segments'])) {
                foreach ($journey['segments'] as $segIndex => $segment) {
                    if (isset($segment['inventoryKey']) && empty(trim($segment['inventoryKey']))) {
                        throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                            "Journey {$index} segment {$segIndex} inventoryKey cannot be empty",
                            400
                        );
                    }
                }
            }
        }
    }

    /**
     * Validate contact details
     * 
     * @param array $contactDetails Contact details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateContactDetails(array $contactDetails): void
    {
        if (isset($contactDetails['email'])) {
            $this->validateFormats($contactDetails, ['email' => 'email']);
        }

        if (isset($contactDetails['address']) && isset($contactDetails['address']['countryCode'])) {
            $this->validateFormats($contactDetails['address'], ['countryCode' => 'country_code']);
        }
    }

    /**
     * Validate special requests
     * 
     * @param array $specialRequests Special service requests
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateSpecialRequests(array $specialRequests): void
    {
        foreach ($specialRequests as $index => $ssr) {
            if (isset($ssr['code']) && empty(trim($ssr['code']))) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Special request {$index} code cannot be empty",
                    400
                );
            }
        }
    }

    /**
     * Create booking request with passengers and journeys
     * 
     * @param array $passengers Passenger details
     * @param array $journeys Journey selections
     * @param array|null $contactDetails Optional contact details
     * @param string|null $currencyCode Optional currency code
     * @return self
     */
    public static function createWithPassengersAndJourneys(
        array $passengers,
        array $journeys,
        ?array $contactDetails = null,
        ?string $currencyCode = null
    ): self {
        return new self(
            passengers: $passengers,
            journeys: $journeys,
            contactDetails: $contactDetails,
            currencyCode: $currencyCode
        );
    }

    /**
     * Create validation-only request
     * 
     * @param array $passengers Passenger details
     * @param array $journeys Journey selections
     * @return self
     */
    public static function createValidationOnly(array $passengers, array $journeys): self
    {
        return new self(
            passengers: $passengers,
            journeys: $journeys,
            validateOnly: true
        );
    }
}
