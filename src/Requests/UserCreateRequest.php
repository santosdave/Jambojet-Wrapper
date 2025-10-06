<?php

namespace SantosDave\JamboJet\Requests;

/**
 * User Create Request
 * 
 * Handles user creation requests for NSK API v1/v2
 * Endpoints: POST /api/nsk/v1/user, POST /api/nsk/v2/user
 * 
 * @package SantosDave\JamboJet\Requests
 */
class UserCreateRequest extends BaseRequest
{
    /**
     * Create new user creation request
     * 
     * @param string $username Required: Username for the account
     * @param string $password Required: Password for the account
     * @param array $personalInfo Required: Personal information (name, contact details)
     * @param array|null $address Optional: Address information
     * @param array|null $preferences Optional: User preferences
     * @param array|null $loyaltyPrograms Optional: Loyalty program memberships
     * @param string|null $cultureCode Optional: Culture/language code
     * @param bool $marketingConsent Optional: Marketing consent flag (default: false)
     * @param array|null $customFields Optional: Custom field values
     */
    public function __construct(
        public string $username,
        public string $password,
        public array $personalInfo,
        public ?array $address = null,
        public ?array $preferences = null,
        public ?array $loyaltyPrograms = null,
        public ?string $cultureCode = null,
        public bool $marketingConsent = false,
        public ?array $customFields = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'username' => $this->username,
            'password' => $this->password,
            'personalInfo' => $this->personalInfo,
            'address' => $this->address,
            'preferences' => $this->preferences,
            'loyaltyPrograms' => $this->loyaltyPrograms,
            'cultureCode' => $this->cultureCode,
            'marketingConsent' => $this->marketingConsent,
            'customFields' => $this->customFields,
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

        // Validate required fields
        $this->validateRequired($data, ['username', 'password', 'personalInfo']);

        // Validate username
        $this->validateUsername($this->username);

        // Validate password
        $this->validatePassword($this->password);

        // Validate personal info
        $this->validatePersonalInfo($this->personalInfo);

        // Validate address if provided
        if ($this->address) {
            $this->validateAddress($this->address);
        }

        // Validate culture code if provided
        if ($this->cultureCode) {
            $this->validateCultureCode($this->cultureCode);
        }

        // Validate loyalty programs if provided
        if ($this->loyaltyPrograms) {
            $this->validateLoyaltyPrograms($this->loyaltyPrograms);
        }

        // Validate preferences if provided
        if ($this->preferences) {
            $this->validatePreferences($this->preferences);
        }
    }

    /**
     * Validate username format
     * 
     * @param string $username Username
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateUsername(string $username): void
    {
        // Typically email format for NSK
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Username must be a valid email address',
                400
            );
        }

        if (strlen($username) > 100) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Username cannot exceed 100 characters',
                400
            );
        }
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Password must be at least 8 characters long',
                400
            );
        }

        if (strlen($password) > 50) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Password cannot exceed 50 characters',
                400
            );
        }

        // Basic password strength requirements
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Password must contain at least one lowercase letter, one uppercase letter, and one digit',
                400
            );
        }
    }

    /**
     * Validate personal information
     * 
     * @param array $personalInfo Personal information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePersonalInfo(array $personalInfo): void
    {
        // Validate required personal info fields
        $this->validateRequired($personalInfo, ['firstName', 'lastName', 'email']);

        // Validate email format
        $this->validateFormats($personalInfo, ['email' => 'email']);

        // Validate name lengths
        foreach (['firstName', 'lastName', 'middleName'] as $nameField) {
            if (isset($personalInfo[$nameField])) {
                if (strlen($personalInfo[$nameField]) > 30) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "{$nameField} cannot exceed 30 characters",
                        400
                    );
                }

                if (strlen($personalInfo[$nameField]) < 1) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "{$nameField} cannot be empty",
                        400
                    );
                }
            }
        }

        // Validate date of birth if provided
        if (isset($personalInfo['dateOfBirth'])) {
            $this->validateFormats($personalInfo, ['dateOfBirth' => 'date']);

            // Check age (must be at least 13 years old)
            $dob = new \DateTime($personalInfo['dateOfBirth']);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;

            if ($age < 13) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'User must be at least 13 years old',
                    400
                );
            }
        }

        // Validate phone number if provided
        if (isset($personalInfo['phone'])) {
            if (!preg_match('/^\+?[\d\s\-\(\)]+$/', $personalInfo['phone'])) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Invalid phone number format',
                    400
                );
            }
        }

        // Validate gender if provided
        if (isset($personalInfo['gender'])) {
            $validGenders = ['M', 'F', 'Male', 'Female', 'Other'];
            if (!in_array($personalInfo['gender'], $validGenders)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Invalid gender value. Expected one of: ' . implode(', ', $validGenders),
                    400
                );
            }
        }
    }

    /**
     * Validate address information
     * 
     * @param array $address Address information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateAddress(array $address): void
    {
        // Validate country code if provided
        if (isset($address['countryCode'])) {
            $this->validateFormats($address, ['countryCode' => 'country_code']);
        }

        // Validate required address fields if any address is provided
        if (!empty($address)) {
            $this->validateRequired($address, ['lineOne', 'city', 'countryCode']);
        }

        // Validate postal code format for specific countries
        if (isset($address['postalCode']) && isset($address['countryCode'])) {
            $this->validatePostalCode($address['postalCode'], $address['countryCode']);
        }
    }

    /**
     * Validate postal code based on country
     * 
     * @param string $postalCode Postal code
     * @param string $countryCode Country code
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePostalCode(string $postalCode, string $countryCode): void
    {
        $patterns = [
            'US' => '/^\d{5}(-\d{4})?$/',
            'CA' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/',
            'GB' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            'KE' => '/^\d{5}$/', // Kenya uses 5-digit postal codes
        ];

        if (isset($patterns[$countryCode])) {
            if (!preg_match($patterns[$countryCode], $postalCode)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Invalid postal code format for country {$countryCode}",
                    400
                );
            }
        }
    }

    /**
     * Validate culture code
     * 
     * @param string $cultureCode Culture code
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateCultureCode(string $cultureCode): void
    {
        // Culture codes are typically in format like 'en-US', 'es-ES', etc.
        if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', $cultureCode)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid culture code format. Expected format: en-US',
                400
            );
        }
    }

    /**
     * Validate loyalty programs
     * 
     * @param array $loyaltyPrograms Loyalty programs
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateLoyaltyPrograms(array $loyaltyPrograms): void
    {
        foreach ($loyaltyPrograms as $index => $program) {
            $this->validateRequired($program, ['programCode', 'membershipNumber']);

            if (strlen($program['programCode']) > 10) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Loyalty program {$index} code cannot exceed 10 characters",
                    400
                );
            }

            if (strlen($program['membershipNumber']) > 50) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Loyalty program {$index} membership number cannot exceed 50 characters",
                    400
                );
            }
        }
    }

    /**
     * Validate user preferences
     * 
     * @param array $preferences User preferences
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePreferences(array $preferences): void
    {
        // Validate currency preference if provided
        if (isset($preferences['currency'])) {
            $this->validateFormats($preferences, ['currency' => 'currency_code']);
        }

        // Validate language preference if provided
        if (isset($preferences['language']) && strlen($preferences['language']) > 10) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Language preference cannot exceed 10 characters',
                400
            );
        }
    }

    /**
     * Create simple user registration request
     * 
     * @param string $email Email address (used as username)
     * @param string $password Password
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string|null $phone Optional phone number
     * @param string|null $dateOfBirth Optional date of birth (Y-m-d format)
     * @return self
     */
    public static function createSimple(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        ?string $phone = null,
        ?string $dateOfBirth = null
    ): self {
        $personalInfo = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
        ];

        if ($phone) {
            $personalInfo['phone'] = $phone;
        }

        if ($dateOfBirth) {
            $personalInfo['dateOfBirth'] = $dateOfBirth;
        }

        return new self(
            username: $email,
            password: $password,
            personalInfo: $personalInfo
        );
    }

    /**
     * Create user with address
     * 
     * @param string $email Email address
     * @param string $password Password
     * @param array $personalInfo Personal information
     * @param array $address Address information
     * @return self
     */
    public static function createWithAddress(
        string $email,
        string $password,
        array $personalInfo,
        array $address
    ): self {
        return new self(
            username: $email,
            password: $password,
            personalInfo: $personalInfo,
            address: $address
        );
    }
}
