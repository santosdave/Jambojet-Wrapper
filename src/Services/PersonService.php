<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\PersonInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Person Service for JamboJet NSK API
 * 
 * Manages person records (customers and agents) with CRUD operations.
 * Base endpoints: /api/nsk/v1/persons and /api/nsk/v2/persons
 * 
 * @package SantosDave\JamboJet\Services
 */
class PersonService implements PersonInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // PHASE 1: CORE PERSON OPERATIONS
    // =================================================================

    /**
     * Create a new person record
     * POST /api/nsk/v1/persons
     * GraphQL: personAdd
     */
    public function createPerson(array $personData): array
    {
        $this->validatePersonCreateData($personData);

        try {
            return $this->post('api/nsk/v1/persons', $personData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search person records with advanced filters
     * GET /api/nsk/v2/persons
     * GraphQL: personSearchv2
     */
    public function searchPersons(array $searchCriteria = []): array
    {
        $this->validatePersonSearchCriteria($searchCriteria);

        try {
            $queryString = !empty($searchCriteria) ? '?' . http_build_query($searchCriteria) : '';
            return $this->get("api/nsk/v2/persons{$queryString}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search persons: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get a specific person by key
     * GET /api/nsk/v1/persons/{personKey}
     * GraphQL: person
     */
    public function getPerson(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person record (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}
     * GraphQL: personSet
     */
    public function updatePerson(string $personKey, array $personData): array
    {
        $this->validatePersonKey($personKey);
        $this->validatePersonUpdateData($personData);

        try {
            return $this->put("api/nsk/v1/persons/{$personKey}", $personData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person record (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}
     * GraphQL: personModify
     */
    public function patchPerson(string $personKey, array $patchData): array
    {
        $this->validatePersonKey($personKey);
        $this->validatePersonPatchData($patchData);

        try {
            return $this->patch("api/nsk/v1/persons/{$personKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person record (set to terminated)
     * DELETE /api/nsk/v1/persons/{personKey}
     * GraphQL: personDelete
     */
    public function deletePerson(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person account and credits
     * 
     * GET /api/nsk/v1/persons/{personKey}/account
     * GraphQL: personsAccount
     * 
     * Retrieves the person's payment account including balance and collections.
     * If currency code is not provided, defaults to account's currency.
     * 
     * @param string $personKey Person key
     * @param string|null $currencyCode Optional currency code for balance conversion
     * @return array Account information with:
     *   - accountKey (string): Account identifier
     *   - totalAvailable (decimal): Current available balance
     *   - currencyCode (string): Account currency
     *   - status (int): Account status (0=Open, 1=Closed, 2=AgencyInactive, 3=Unknown)
     *   - collections (array): List of account collections
     * @throws JamboJetApiException
     */
    public function getPersonAccount(string $personKey, ?string $currencyCode = null): array
    {
        $this->validatePersonKey($personKey);

        if ($currencyCode !== null) {
            $this->validateCurrencyCode($currencyCode);
        }

        try {
            $endpoint = "api/nsk/v1/persons/{$personKey}/account";

            if ($currencyCode) {
                $endpoint .= "?currencyCode={$currencyCode}";
            }

            return $this->get($endpoint);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person account
     * 
     * POST /api/nsk/v1/persons/{personKey}/account
     * GraphQL: personsAccountAdd
     * 
     * Creates a new payment account for the person.
     * Only one account per person is allowed in most configurations.
     * 
     * @param string $personKey Person key
     * @param array $accountData Account creation data:
     *   - currencyCode (string, optional): Account currency (3 chars, defaults to system)
     *   - accountTypeCode (string, optional): Account type code
     *   - organizationCode (string, optional): Organization code
     * @return array Created account with accountKey and initial balance
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccount(string $personKey, array $accountData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateCreateAccountRequest($accountData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/account", $accountData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create account collection
     * 
     * POST /api/nsk/v1/persons/{personKey}/account/collection
     * GraphQL: personsAccountCollectionsAdd
     * 
     * Creates a new account collection or adds transaction to existing one.
     * Collections are unique based on transaction code and expiration date.
     * 
     * BEHAVIOR:
     * - If no matching collection exists: Creates new collection + transaction
     * - If matching collection found: Adds transaction to existing collection
     * 
     * @param string $personKey Person key
     * @param array $collectionData Collection/transaction data:
     *   - amount (decimal, required): Transaction amount
     *   - currencyCode (string, required): Currency code (3 chars)
     *   - transactionCode (string, optional): Transaction code (max 6 chars)
     *   - expiration (string, optional): Collection expiration (ISO 8601)
     *   - note (string, optional): Transaction note (max 128 chars)
     * @return array Created/updated collection with accountCollectionKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccountCollection(string $personKey, array $collectionData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAccountCollectionRequest($collectionData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/account/collection", $collectionData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person account collection: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create account transaction
     * 
     * POST /api/nsk/v1/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: personsAccountTransactionsAdd
     * 
     * Adds a transaction to an existing account collection.
     * Collection must already exist - use createPersonAccountCollection to create new.
     * 
     * @param string $personKey Person key
     * @param string $accountCollectionKey Account collection key
     * @param array $transactionData Transaction data:
     *   - amount (decimal, required): Transaction amount
     *   - currencyCode (string, required): Currency code (3 chars)
     *   - note (string, optional): Transaction note (max 128 chars)
     * @return array Created transaction
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccountTransaction(
        string $personKey,
        string $accountCollectionKey,
        array $transactionData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateAccountCollectionKey($accountCollectionKey);
        $this->validateTransactionRequest($transactionData);

        try {
            return $this->post(
                "api/nsk/v1/persons/{$personKey}/account/collection/{$accountCollectionKey}/transactions",
                $transactionData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person account transaction: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get account collection transactions (v2)
     * 
     * GET /api/nsk/v2/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: personsAccountTransactionsv2
     * 
     * Retrieves paginated transactions for a specific account collection.
     * Version 2 with enhanced filtering and pagination.
     * 
     * @param string $personKey Person key
     * @param string $accountCollectionKey Account collection key
     * @param array $params Query parameters:
     *   - StartDate (string, required): Start date (ISO 8601)
     *   - EndDate (string, optional): End date (ISO 8601)
     *   - SortByNewest (bool, optional): Sort by newest first (default: false)
     *   - PageSize (int, optional): Records per page (10-5000, default: 100)
     *   - LastPageKey (string, optional): Pagination cursor
     * @return array Paginated transactions with:
     *   - totalCount (int): Total transaction count
     *   - lastPageKey (string): Next page cursor
     *   - transactions (array): Transaction list
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function getPersonCollectionTransactions(
        string $personKey,
        string $accountCollectionKey,
        array $params
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateAccountCollectionKey($accountCollectionKey);
        $this->validateTransactionQueryParams($params);

        try {
            $queryString = http_build_query($params);
            return $this->get(
                "api/nsk/v2/persons/{$personKey}/account/collection/{$accountCollectionKey}/transactions?{$queryString}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person collection transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person account status
     * 
     * PUT /api/nsk/v1/persons/{personKey}/account/status
     * GraphQL: personsAccountStatusSet
     * 
     * Updates the status of person's payment account.
     * 
     * @param string $personKey Person key
     * @param int $status New account status
     *     0 = Open: Account is active and can be used
     *     1 = Closed: Account permanently closed
     *     2 = AgencyInactive: Account temporarily disabled
     *     3 = Unknown: Status unknown/undefined
     * @return array Status update confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonAccountStatus(string $personKey, int $status): array
    {
        $this->validatePersonKey($personKey);

        $statusData = ['status' => $status];
        $this->validateAccountStatusUpdate($statusData);

        try {
            return $this->put("api/nsk/v1/persons/{$personKey}/account/status", $statusData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person account status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all person account transactions (v2)
     * 
     * GET /api/nsk/v2/persons/{personKey}/account/transactions
     * GraphQL: personsAccountAllTransactionsv2
     * 
     * Retrieves ALL transactions across all account collections for a person.
     * Version 2 includes enhanced filtering and pagination.
     * 
     * @param string $personKey Person key
     * @param array $params Query parameters:
     *   - StartDate (string, required): Start date (ISO 8601)
     *   - EndDate (string, optional): End date (ISO 8601)
     *   - SortByNewest (bool, optional): Sort by newest first
     *   - PageSize (int, optional): Records per page (10-5000)
     *   - LastPageKey (string, optional): Pagination cursor
     *   - TransactionType (int, optional): Filter by transaction type
     * @return array Paginated transactions across all collections
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function getAllPersonAccountTransactions(string $personKey, array $params): array
    {
        $this->validatePersonKey($personKey);
        $this->validateTransactionQueryParams($params);

        try {
            $queryString = http_build_query($params);
            return $this->get("api/nsk/v2/persons/{$personKey}/account/transactions?{$queryString}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all person account transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all person addresses
     * 
     * GET /api/nsk/v1/persons/{personKey}/addresses
     * 
     * Retrieves all addresses associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of addresses
     * @throws JamboJetApiException
     */
    public function getPersonAddresses(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/addresses");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person addresses: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person address
     * 
     * POST /api/nsk/v1/persons/{personKey}/addresses
     * GraphQL: personAddressAdd
     * 
     * Creates a new address for the person.
     * 
     * @param string $personKey Person key
     * @param array $addressData Address data:
     *   - addressTypeCode (string, required): Address type code (max 1 char)
     *   - lineOne (string, optional): Address line 1 (max 128 chars)
     *   - lineTwo (string, optional): Address line 2 (max 128 chars)
     *   - lineThree (string, optional): Address line 3 (max 128 chars)
     *   - city (string, optional): City name (max 32 chars)
     *   - provinceState (string, optional): Province/state code (max 3 chars)
     *   - postalCode (string, optional): Postal/ZIP code (max 10 chars)
     *   - countryCode (string, optional): Country code (max 2 chars)
     *   - default (bool, optional): Set as default address
     * @return array Created address with personAddressKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAddress(string $personKey, array $addressData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAddressCreateRequest($addressData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/addresses", $addressData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person address
     * 
     * GET /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddress
     * 
     * Retrieves a specific address for a person.
     * 
     * @param string $personKey Person key
     * @param string $personAddressKey Person address key
     * @return array Address details
     * @throws JamboJetApiException
     */
    public function getPersonAddress(string $personKey, string $personAddressKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAddressKey, 'Address');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/addresses/{$personAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person address (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressSet
     * 
     * Replaces entire address with new data.
     * 
     * @param string $personKey Person key
     * @param string $personAddressKey Person address key
     * @param array $addressData Complete address data (all fields)
     * @return array Updated address
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonAddress(
        string $personKey,
        string $personAddressKey,
        array $addressData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAddressKey, 'Address');
        $this->validateAddressEditRequest($addressData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/addresses/{$personAddressKey}",
                $addressData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person address (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personAddressKey Person address key
     * @param array $addressData Partial address data (only fields to update)
     * @return array Updated address
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonAddress(
        string $personKey,
        string $personAddressKey,
        array $addressData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAddressKey, 'Address');
        $this->validateAddressEditRequest($addressData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/addresses/{$personAddressKey}",
                $addressData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person address
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressDelete
     * 
     * Removes an address from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personAddressKey Person address key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonAddress(string $personKey, string $personAddressKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAddressKey, 'Address');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/addresses/{$personAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // EMAILS (5 methods)
    // =================================================================

    /**
     * Get all person emails
     * 
     * GET /api/nsk/v1/persons/{personKey}/emails
     * 
     * Retrieves all email addresses associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of emails
     * @throws JamboJetApiException
     */
    public function getPersonEmails(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/emails");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person emails: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person email
     * 
     * POST /api/nsk/v1/persons/{personKey}/emails
     * GraphQL: personEmailAdd
     * 
     * Creates a new email address for the person.
     * 
     * @param string $personKey Person key
     * @param array $emailData Email data:
     *   - email (string, required): Email address (max 266 chars)
     *   - type (string, required): Email type code (1 char)
     *   - default (bool, optional): Set as default email
     * @return array Created email with personEmailKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonEmail(string $personKey, array $emailData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateEmailCreateRequest($emailData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/emails", $emailData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person email
     * 
     * GET /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmail
     * 
     * Retrieves a specific email address for a person.
     * 
     * @param string $personKey Person key
     * @param string $personEmailAddressKey Person email address key
     * @return array Email details
     * @throws JamboJetApiException
     */
    public function getPersonEmail(string $personKey, string $personEmailAddressKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personEmailAddressKey, 'Email');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/emails/{$personEmailAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person email (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailSet
     * 
     * Replaces entire email with new data.
     * 
     * @param string $personKey Person key
     * @param string $personEmailAddressKey Person email address key
     * @param array $emailData Complete email data:
     *   - email (string, required): Email address
     *   - default (bool, optional): Set as default
     * @return array Updated email
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonEmail(
        string $personKey,
        string $personEmailAddressKey,
        array $emailData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personEmailAddressKey, 'Email');
        $this->validateEmailEditRequest($emailData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/emails/{$personEmailAddressKey}",
                $emailData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person email (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personEmailAddressKey Person email address key
     * @param array $emailData Partial email data
     * @return array Updated email
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonEmail(
        string $personKey,
        string $personEmailAddressKey,
        array $emailData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personEmailAddressKey, 'Email');
        $this->validateEmailEditRequest($emailData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/emails/{$personEmailAddressKey}",
                $emailData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person email
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailDelete
     * 
     * Removes an email address from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personEmailAddressKey Person email address key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonEmail(string $personKey, string $personEmailAddressKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personEmailAddressKey, 'Email');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/emails/{$personEmailAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHONE NUMBERS (5 methods)
    // =================================================================

    /**
     * Get all person phone numbers
     * 
     * GET /api/nsk/v1/persons/{personKey}/phoneNumbers
     * 
     * Retrieves all phone numbers associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of phone numbers
     * @throws JamboJetApiException
     */
    public function getPersonPhoneNumbers(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/phoneNumbers");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person phone numbers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person phone number
     * 
     * POST /api/nsk/v1/persons/{personKey}/phoneNumbers
     * GraphQL: personPhoneNumberAdd
     * 
     * Creates a new phone number for the person.
     * 
     * @param string $personKey Person key
     * @param array $phoneData Phone number data:
     *   - type (int, required): Phone type (0=Other, 1=Home, 2=Work, 3=Mobile, 4=Fax)
     *   - number (string, required): Phone number (max 20 chars, digits only recommended)
     *   - default (bool, optional): Set as default phone number
     * @return array Created phone number with personPhoneNumberKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonPhoneNumber(string $personKey, array $phoneData): array
    {
        $this->validatePersonKey($personKey);
        $this->validatePhoneNumberRequest($phoneData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/phoneNumbers", $phoneData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person phone number
     * 
     * GET /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumber
     * 
     * Retrieves a specific phone number for a person.
     * 
     * @param string $personKey Person key
     * @param string $personPhoneNumberKey Person phone number key
     * @return array Phone number details
     * @throws JamboJetApiException
     */
    public function getPersonPhoneNumber(string $personKey, string $personPhoneNumberKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPhoneNumberKey, 'Phone number');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/phoneNumbers/{$personPhoneNumberKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person phone number (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberSet
     * 
     * Replaces entire phone number with new data.
     * 
     * @param string $personKey Person key
     * @param string $personPhoneNumberKey Person phone number key
     * @param array $phoneData Complete phone data (all fields)
     * @return array Updated phone number
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonPhoneNumber(
        string $personKey,
        string $personPhoneNumberKey,
        array $phoneData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPhoneNumberKey, 'Phone number');
        $this->validatePhoneNumberRequest($phoneData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/phoneNumbers/{$personPhoneNumberKey}",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person phone number (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personPhoneNumberKey Person phone number key
     * @param array $phoneData Partial phone data
     * @return array Updated phone number
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonPhoneNumber(
        string $personKey,
        string $personPhoneNumberKey,
        array $phoneData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPhoneNumberKey, 'Phone number');
        $this->validatePhoneNumberRequest($phoneData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/phoneNumbers/{$personPhoneNumberKey}",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person phone number
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberDelete
     * 
     * Removes a phone number from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personPhoneNumberKey Person phone number key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonPhoneNumber(string $personKey, string $personPhoneNumberKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPhoneNumberKey, 'Phone number');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/phoneNumbers/{$personPhoneNumberKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Get all person affiliations
     * 
     * GET /api/nsk/v1/persons/{personKey}/affiliations
     * GraphQL: personAffiliations
     * 
     * Retrieves all affiliations (related persons) associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of affiliations
     * @throws JamboJetApiException
     */
    public function getPersonAffiliations(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/affiliations");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person affiliations: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person affiliation
     * 
     * POST /api/nsk/v1/persons/{personKey}/affiliations
     * GraphQL: personAffiliationAdd
     * 
     * Creates a new affiliation (related person) for the person.
     * 
     * @param string $personKey Person key
     * @param array $affiliationData Affiliation data:
     *   - name (string, required): Name of the affiliated person (min 1 char)
     * @return array Created affiliation with personAffiliationKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAffiliation(string $personKey, array $affiliationData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAffiliationRequest($affiliationData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/affiliations", $affiliationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person affiliation
     * 
     * GET /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliation
     * 
     * Retrieves a specific affiliation for a person.
     * 
     * @param string $personKey Person key
     * @param string $personAffiliationKey Person affiliation key
     * @return array Affiliation details
     * @throws JamboJetApiException
     */
    public function getPersonAffiliation(string $personKey, string $personAffiliationKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAffiliationKey, 'Affiliation');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/affiliations/{$personAffiliationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person affiliation
     * 
     * PUT /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliationSet
     * 
     * Replaces entire affiliation with new data.
     * Note: Affiliations do not support PATCH operations.
     * 
     * @param string $personKey Person key
     * @param string $personAffiliationKey Person affiliation key
     * @param array $affiliationData Complete affiliation data:
     *   - name (string, required): Name of the affiliated person
     * @return array Updated affiliation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonAffiliation(
        string $personKey,
        string $personAffiliationKey,
        array $affiliationData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAffiliationKey, 'Affiliation');
        $this->validateAffiliationRequest($affiliationData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/affiliations/{$personAffiliationKey}",
                $affiliationData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person affiliation
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliationDelete
     * 
     * Removes an affiliation from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personAffiliationKey Person affiliation key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonAffiliation(string $personKey, string $personAffiliationKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAffiliationKey, 'Affiliation');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/affiliations/{$personAffiliationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // ALIASES (6 methods)
    // =================================================================

    /**
     * Get all person aliases
     * 
     * GET /api/nsk/v1/persons/{personKey}/aliases
     * 
     * Retrieves all alternate names associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of aliases
     * @throws JamboJetApiException
     */
    public function getPersonAliases(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/aliases");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person aliases: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person alias
     * 
     * POST /api/nsk/v1/persons/{personKey}/aliases
     * GraphQL: personAliasAdd
     * 
     * Creates a new alternate name for the person.
     * 
     * @param string $personKey Person key
     * @param array $aliasData Alias data:
     *   - type (int, optional): Alias type (0=Alias, 1=Variant)
     *   - first (string, optional): First name (max 32 chars)
     *   - middle (string, optional): Middle name (max 32 chars)
     *   - last (string, optional): Last name (max 32 chars)
     *   - title (string, optional): Title (max 6 chars)
     *   - suffix (string, optional): Suffix (max 6 chars)
     * @return array Created alias with personAliasKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAlias(string $personKey, array $aliasData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAliasRequest($aliasData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/aliases", $aliasData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person alias
     * 
     * GET /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAlias
     * 
     * Retrieves a specific alias for a person.
     * 
     * @param string $personKey Person key
     * @param string $personAliasKey Person alias key
     * @return array Alias details
     * @throws JamboJetApiException
     */
    public function getPersonAlias(string $personKey, string $personAliasKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAliasKey, 'Alias');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/aliases/{$personAliasKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person alias (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasSet
     * 
     * Replaces entire alias with new data.
     * 
     * @param string $personKey Person key
     * @param string $personAliasKey Person alias key
     * @param array $aliasData Complete alias data (all fields)
     * @return array Updated alias
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonAlias(
        string $personKey,
        string $personAliasKey,
        array $aliasData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAliasKey, 'Alias');
        $this->validateAliasRequest($aliasData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/aliases/{$personAliasKey}",
                $aliasData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person alias (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personAliasKey Person alias key
     * @param array $aliasData Partial alias data
     * @return array Updated alias
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonAlias(
        string $personKey,
        string $personAliasKey,
        array $aliasData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAliasKey, 'Alias');
        $this->validateAliasRequest($aliasData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/aliases/{$personAliasKey}",
                $aliasData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person alias
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasDelete
     * 
     * Removes an alias from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personAliasKey Person alias key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonAlias(string $personKey, string $personAliasKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personAliasKey, 'Alias');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/aliases/{$personAliasKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // COMMENTS (6 methods)
    // =================================================================

    /**
     * Get all person comments
     * 
     * GET /api/nsk/v1/persons/{personKey}/comments
     * GraphQL: personComments
     * 
     * Retrieves all comments associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of comments
     * @throws JamboJetApiException
     */
    public function getPersonComments(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/comments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person comments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person comment
     * 
     * POST /api/nsk/v1/persons/{personKey}/comments
     * GraphQL: personCommentAdd
     * 
     * Creates a new comment for the person.
     * 
     * @param string $personKey Person key
     * @param array $commentData Comment data:
     *   - text (string, optional): Comment text
     *   - type (int, optional): Comment type (0=Default, 1=Itinerary, 2=Manifest, 3=Alert, 4=Archive, 5=Voucher)
     * @return array Created comment with personCommentKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonComment(string $personKey, array $commentData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateCommentRequest($commentData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/comments", $commentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person comment
     * 
     * GET /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personComment
     * 
     * Retrieves a specific comment for a person.
     * 
     * @param string $personKey Person key
     * @param string $personCommentKey Person comment key
     * @return array Comment details
     * @throws JamboJetApiException
     */
    public function getPersonComment(string $personKey, string $personCommentKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personCommentKey, 'Comment');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/comments/{$personCommentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person comment (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentSet
     * 
     * Replaces entire comment with new data.
     * 
     * @param string $personKey Person key
     * @param string $personCommentKey Person comment key
     * @param array $commentData Complete comment data
     * @return array Updated comment
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonComment(
        string $personKey,
        string $personCommentKey,
        array $commentData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personCommentKey, 'Comment');
        $this->validateCommentRequest($commentData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/comments/{$personCommentKey}",
                $commentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person comment (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personCommentKey Person comment key
     * @param array $commentData Partial comment data
     * @return array Updated comment
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonComment(
        string $personKey,
        string $personCommentKey,
        array $commentData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personCommentKey, 'Comment');
        $this->validateCommentRequest($commentData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/comments/{$personCommentKey}",
                $commentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person comment
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentDelete
     * 
     * Removes a comment from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personCommentKey Person comment key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonComment(string $personKey, string $personCommentKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personCommentKey, 'Comment');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/comments/{$personCommentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all person information
     * 
     * GET /api/nsk/v1/persons/{personKey}/information
     * GraphQL: personInformations
     * 
     * Retrieves all information entries associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of information entries
     * @throws JamboJetApiException
     */
    public function getPersonInformation(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/information");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person information
     * 
     * POST /api/nsk/v1/persons/{personKey}/information
     * GraphQL: personInformationAdd
     * 
     * Creates a new information entry for the person.
     * 
     * @param string $personKey Person key
     * @param array $informationData Information data:
     *   - personInformationTypeCode (string, required): Type code (1 char)
     *   - data (string, optional): Information data content
     * @return array Created information with personInformationKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonInformation(string $personKey, array $informationData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateInformationCreateRequest($informationData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/information", $informationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person information item
     * 
     * GET /api/nsk/v1/persons/{personKey}/information/{personInformationKey}
     * GraphQL: personInformation
     * 
     * Retrieves a specific information entry for a person.
     * 
     * @param string $personKey Person key
     * @param string $personInformationKey Person information key
     * @return array Information details
     * @throws JamboJetApiException
     */
    public function getPersonInformationItem(string $personKey, string $personInformationKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personInformationKey, 'Information');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/information/{$personInformationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person information item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person information (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/information/{personInformationKey}
     * GraphQL: personInformationSet
     * 
     * Replaces entire information entry with new data.
     * 
     * @param string $personKey Person key
     * @param string $personInformationKey Person information key
     * @param array $informationData Complete information data:
     *   - data (string, optional): Information data content
     * @return array Updated information
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonInformation(
        string $personKey,
        string $personInformationKey,
        array $informationData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personInformationKey, 'Information');
        $this->validateInformationEditRequest($informationData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/information/{$personInformationKey}",
                $informationData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person information (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/information/{personInformationKey}
     * GraphQL: personInformationModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personInformationKey Person information key
     * @param array $informationData Partial information data
     * @return array Updated information
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonInformation(
        string $personKey,
        string $personInformationKey,
        array $informationData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personInformationKey, 'Information');
        $this->validateInformationEditRequest($informationData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/information/{$personInformationKey}",
                $informationData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person information
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/information/{personInformationKey}
     * GraphQL: personInformationDelete
     * 
     * Removes an information entry from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personInformationKey Person information key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonInformation(string $personKey, string $personInformationKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personInformationKey, 'Information');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/information/{$personInformationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PREFERENCES (6 methods)
    // =================================================================

    /**
     * Get all person preferences
     * 
     * GET /api/nsk/v1/persons/{personKey}/preferences
     * 
     * Retrieves all preferences associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of preferences
     * @throws JamboJetApiException
     */
    public function getPersonPreferences(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/preferences");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person preferences: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person preference
     * 
     * POST /api/nsk/v1/persons/{personKey}/preferences
     * GraphQL: personPreferenceAdd
     * 
     * Creates a new preference for the person.
     * 
     * @param string $personKey Person key
     * @param array $preferenceData Preference data:
     *   - code (string, optional): Preference code
     *   - value (string, optional): Preference value
     * @return array Created preference with personPreferenceKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonPreference(string $personKey, array $preferenceData): array
    {
        $this->validatePersonKey($personKey);
        $this->validatePreferenceRequest($preferenceData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/preferences", $preferenceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get person preference
     * 
     * GET /api/nsk/v1/persons/{personKey}/preferences/{personPreferenceKey}
     * GraphQL: personPreference
     * 
     * Retrieves a specific preference for a person.
     * 
     * @param string $personKey Person key
     * @param string $personPreferenceKey Person preference key
     * @return array Preference details
     * @throws JamboJetApiException
     */
    public function getPersonPreference(string $personKey, string $personPreferenceKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPreferenceKey, 'Preference');

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/preferences/{$personPreferenceKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update person preference (full replacement)
     * 
     * PUT /api/nsk/v1/persons/{personKey}/preferences/{personPreferenceKey}
     * GraphQL: personPreferenceSet
     * 
     * Replaces entire preference with new data.
     * 
     * @param string $personKey Person key
     * @param string $personPreferenceKey Person preference key
     * @param array $preferenceData Complete preference data:
     *   - value (string, optional): Preference value
     * @return array Updated preference
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonPreference(
        string $personKey,
        string $personPreferenceKey,
        array $preferenceData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPreferenceKey, 'Preference');
        $this->validatePreferenceEditRequest($preferenceData);

        try {
            return $this->put(
                "api/nsk/v1/persons/{$personKey}/preferences/{$personPreferenceKey}",
                $preferenceData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch person preference (partial update)
     * 
     * PATCH /api/nsk/v1/persons/{personKey}/preferences/{personPreferenceKey}
     * GraphQL: personPreferenceModify
     * 
     * Updates only specified fields.
     * 
     * @param string $personKey Person key
     * @param string $personPreferenceKey Person preference key
     * @param array $preferenceData Partial preference data
     * @return array Updated preference
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPersonPreference(
        string $personKey,
        string $personPreferenceKey,
        array $preferenceData
    ): array {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPreferenceKey, 'Preference');
        $this->validatePreferenceEditRequest($preferenceData, false);

        try {
            return $this->patch(
                "api/nsk/v1/persons/{$personKey}/preferences/{$personPreferenceKey}",
                $preferenceData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete person preference
     * 
     * DELETE /api/nsk/v1/persons/{personKey}/preferences/{personPreferenceKey}
     * GraphQL: personPreferenceDelete
     * 
     * Removes a preference from the person's record.
     * 
     * @param string $personKey Person key
     * @param string $personPreferenceKey Person preference key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePersonPreference(string $personKey, string $personPreferenceKey): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSubResourceKey($personPreferenceKey, 'Preference');

        try {
            return $this->delete("api/nsk/v1/persons/{$personKey}/preferences/{$personPreferenceKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PROGRAMS (2 methods)
    // =================================================================

    /**
     * Get all person programs
     * 
     * GET /api/nsk/v1/persons/{personKey}/programs
     * GraphQL: personPrograms
     * 
     * Retrieves all loyalty/customer programs associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of programs
     * @throws JamboJetApiException
     */
    public function getPersonPrograms(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/programs");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person programs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person program
     * 
     * POST /api/nsk/v1/persons/{personKey}/programs
     * GraphQL: personProgramAdd
     * 
     * Enrolls a person in a loyalty/customer program.
     * 
     * @param string $personKey Person key
     * @param array $programData Program data:
     *   - programNumber (string, required): Unique program number (max 32 chars)
     *   - effectiveDate (string, optional): When program becomes active (ISO 8601)
     *   - expirationDate (string, optional): When program expires (ISO 8601)
     *   - default (bool, optional): Set as default program
     *   - programLevelCode (string, optional): Program level/tier (max 3 chars)
     * @return array Created program enrollment with personCustomerProgramKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonProgram(string $personKey, array $programData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateProgramRequest($programData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/programs", $programData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Merge two person records
     * 
     * PUT /api/nsk/v1/persons/{personKey}/merge
     * GraphQL: personMerge
     * 
     * Merges two person records by combining their PNRs, account information,
     * and optionally customer programs. Requires agent permissions.
     * 
     * CRITICAL WARNING: This is a destructive operation that permanently
     * deletes the source person after transferring all data to the target.
     * 
     * @param string $personKey Target person key (receives all merged data)
     * @param array $mergeData Merge request data:
     *   - deletePersonKey (string, required): Source person to be deleted
     *   - newName (array, optional): New name for merged person
     *   - customerProgramCodes (array, optional): Program codes to merge
     * @return array Merge confirmation with updated person data
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function mergePersons(string $personKey, array $mergeData): array
    {
        $this->validatePersonKey($personKey);
        $this->validatePersonMergeRequest($mergeData);

        try {
            return $this->put("api/nsk/v1/persons/{$personKey}/merge", $mergeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to merge persons: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    

    // =================================================================
    // VALIDATION HELPERS
    // =================================================================

    /**
     * Validate information create request
     * 
     * @param array $data Information data
     * @throws JamboJetValidationException
     */
    private function validateInformationCreateRequest(array $data): void
    {
        // personInformationTypeCode is required for creation
        if (!isset($data['personInformationTypeCode'])) {
            throw new JamboJetValidationException('Person information type code is required');
        }

        // Must be exactly 1 character
        if (strlen($data['personInformationTypeCode']) !== 1) {
            throw new JamboJetValidationException('Person information type code must be exactly 1 character');
        }

        // Data is optional but validate if provided
        if (isset($data['data'])) {
            // No specific length limit in schema, reasonable validation
            if (strlen($data['data']) > 50000) {
                throw new JamboJetValidationException('Information data cannot exceed 50000 characters');
            }
        }
    }

    /**
     * Validate information edit request
     * 
     * @param array $data Information data
     * @param bool $strictValidation Whether to enforce strict validation
     * @throws JamboJetValidationException
     */
    private function validateInformationEditRequest(array $data, bool $strictValidation = true): void
    {
        // For edit requests, only data field can be updated
        if (isset($data['data']) && strlen($data['data']) > 50000) {
            throw new JamboJetValidationException('Information data cannot exceed 50000 characters');
        }
    }

    /**
     * Validate preference request
     * 
     * @param array $data Preference data
     * @throws JamboJetValidationException
     */
    private function validatePreferenceRequest(array $data): void
    {
        // Code and value are both optional
        // No specific validation rules in schema beyond being strings

        // Optional: Add reasonable length limits
        if (isset($data['code']) && strlen($data['code']) > 100) {
            throw new JamboJetValidationException('Preference code cannot exceed 100 characters');
        }

        if (isset($data['value']) && strlen($data['value']) > 500) {
            throw new JamboJetValidationException('Preference value cannot exceed 500 characters');
        }
    }

    /**
     * Validate preference edit request
     * 
     * @param array $data Preference data
     * @param bool $strictValidation Whether to enforce strict validation
     * @throws JamboJetValidationException
     */
    private function validatePreferenceEditRequest(array $data, bool $strictValidation = true): void
    {
        // For edit requests, only value can be updated
        if (isset($data['value']) && strlen($data['value']) > 500) {
            throw new JamboJetValidationException('Preference value cannot exceed 500 characters');
        }
    }

    /**
     * Validate program request
     * 
     * @param array $data Program data
     * @throws JamboJetValidationException
     */
    private function validateProgramRequest(array $data): void
    {
        // programNumber is required
        if (!isset($data['programNumber']) || empty(trim($data['programNumber']))) {
            throw new JamboJetValidationException('Program number is required');
        }

        // Length validation
        if (strlen($data['programNumber']) > 32) {
            throw new JamboJetValidationException('Program number cannot exceed 32 characters');
        }

        // programLevelCode validation if provided
        if (isset($data['programLevelCode']) && strlen($data['programLevelCode']) > 3) {
            throw new JamboJetValidationException('Program level code cannot exceed 3 characters');
        }

        // Date validations if provided
        if (isset($data['effectiveDate'])) {
            $this->validateIso8601Date($data['effectiveDate'], 'Effective date');
        }

        if (isset($data['expirationDate'])) {
            $this->validateIso8601Date($data['expirationDate'], 'Expiration date');

            // If both dates provided, expiration must be after effective
            if (isset($data['effectiveDate'])) {
                $effectiveTime = strtotime($data['effectiveDate']);
                $expirationTime = strtotime($data['expirationDate']);

                if ($expirationTime < $effectiveTime) {
                    throw new JamboJetValidationException('Expiration date must be after effective date');
                }
            }
        }
    }

    /**
     * Validate person merge request
     * 
     * @param array $data Merge request data
     * @throws JamboJetValidationException
     */
    private function validatePersonMergeRequest(array $data): void
    {
        // deletePersonKey is required
        if (!isset($data['deletePersonKey']) || empty(trim($data['deletePersonKey']))) {
            throw new JamboJetValidationException(
                'Delete person key is required for merge operation'
            );
        }

        // Must be at least 1 character
        if (strlen(trim($data['deletePersonKey'])) < 1) {
            throw new JamboJetValidationException(
                'Delete person key must be at least 1 character'
            );
        }

        // Validate deletePersonKey length (same as personKey validation)
        if (strlen($data['deletePersonKey']) > 100) {
            throw new JamboJetValidationException(
                'Delete person key cannot exceed 100 characters'
            );
        }

        // Validate newName if provided
        if (isset($data['newName'])) {
            $this->validatePersonNameStructure($data['newName']);
        }

        // Validate customerProgramCodes if provided
        if (isset($data['customerProgramCodes'])) {
            if (!is_array($data['customerProgramCodes'])) {
                throw new JamboJetValidationException(
                    'Customer program codes must be an array'
                );
            }

            // Validate each program code
            foreach ($data['customerProgramCodes'] as $code) {
                if (!is_string($code)) {
                    throw new JamboJetValidationException(
                        'Each customer program code must be a string'
                    );
                }

                if (empty(trim($code))) {
                    throw new JamboJetValidationException(
                        'Customer program codes cannot be empty'
                    );
                }

                if (strlen($code) > 50) {
                    throw new JamboJetValidationException(
                        'Customer program code cannot exceed 50 characters'
                    );
                }
            }
        }
    }

    /**
     * Validate person name structure
     * 
     * @param array $name Name data
     * @throws JamboJetValidationException
     */
    private function validatePersonNameStructure(array $name): void
    {
        // First name validation
        if (isset($name['first']) && strlen($name['first']) > 32) {
            throw new JamboJetValidationException('First name cannot exceed 32 characters');
        }

        // Middle name validation
        if (isset($name['middle']) && strlen($name['middle']) > 32) {
            throw new JamboJetValidationException('Middle name cannot exceed 32 characters');
        }

        // Last name validation
        if (isset($name['last']) && strlen($name['last']) > 32) {
            throw new JamboJetValidationException('Last name cannot exceed 32 characters');
        }

        // Title validation
        if (isset($name['title']) && strlen($name['title']) > 6) {
            throw new JamboJetValidationException('Title cannot exceed 6 characters');
        }

        // Suffix validation
        if (isset($name['suffix']) && strlen($name['suffix']) > 6) {
            throw new JamboJetValidationException('Suffix cannot exceed 6 characters');
        }
    }

    /**
     * Validate sub-resource key
     * 
     * @param string $key Sub-resource key
     * @param string $resourceType Resource type name for error message
     * @throws JamboJetValidationException
     */
    private function validateSubResourceKey(string $key, string $resourceType): void
    {
        if (empty($key)) {
            throw new JamboJetValidationException("{$resourceType} key is required");
        }

        if (strlen($key) > 100) {
            throw new JamboJetValidationException("{$resourceType} key cannot exceed 100 characters");
        }
    }

    /**
     * Validate address create request
     * 
     * @param array $data Address data
     * @throws JamboJetValidationException
     */
    private function validateAddressCreateRequest(array $data): void
    {
        // addressTypeCode is required for creation
        if (!isset($data['addressTypeCode'])) {
            throw new JamboJetValidationException('Address type code is required');
        }

        if (strlen($data['addressTypeCode']) > 1) {
            throw new JamboJetValidationException('Address type code must be exactly 1 character');
        }

        // Validate optional fields with common validation
        $this->validateAddressFields($data);
    }

    /**
     * Validate address edit request
     * 
     * @param array $data Address data
     * @param bool $requireAll Whether all fields are required (true for PUT, false for PATCH)
     * @throws JamboJetValidationException
     */
    private function validateAddressEditRequest(array $data, bool $requireAll = true): void
    {
        // For edit requests, no fields are strictly required (PATCH allows partial updates)
        // Just validate the ones that are provided
        $this->validateAddressFields($data);
    }

    /**
     * Validate address fields
     * 
     * @param array $data Address data
     * @throws JamboJetValidationException
     */
    private function validateAddressFields(array $data): void
    {
        // Line validations
        if (isset($data['lineOne']) && strlen($data['lineOne']) > 128) {
            throw new JamboJetValidationException('Address line 1 cannot exceed 128 characters');
        }

        if (isset($data['lineTwo']) && strlen($data['lineTwo']) > 128) {
            throw new JamboJetValidationException('Address line 2 cannot exceed 128 characters');
        }

        if (isset($data['lineThree']) && strlen($data['lineThree']) > 128) {
            throw new JamboJetValidationException('Address line 3 cannot exceed 128 characters');
        }

        // City validation
        if (isset($data['city']) && strlen($data['city']) > 32) {
            throw new JamboJetValidationException('City cannot exceed 32 characters');
        }

        // Province/State validation
        if (isset($data['provinceState']) && strlen($data['provinceState']) > 3) {
            throw new JamboJetValidationException('Province/State code cannot exceed 3 characters');
        }

        // Postal code validation
        if (isset($data['postalCode']) && strlen($data['postalCode']) > 10) {
            throw new JamboJetValidationException('Postal code cannot exceed 10 characters');
        }

        // Country code validation (2 chars)
        if (isset($data['countryCode'])) {
            if (strlen($data['countryCode']) > 2) {
                throw new JamboJetValidationException('Country code cannot exceed 2 characters');
            }

            if (!empty($data['countryCode']) && !ctype_alpha($data['countryCode'])) {
                throw new JamboJetValidationException('Country code must contain only letters');
            }
        }
    }

    /**
     * Validate email create request
     * 
     * @param array $data Email data
     * @throws JamboJetValidationException
     */
    private function validateEmailCreateRequest(array $data): void
    {
        // Email and type are required for creation
        if (!isset($data['email'])) {
            throw new JamboJetValidationException('Email address is required');
        }

        if (!isset($data['type'])) {
            throw new JamboJetValidationException('Email type is required');
        }

        // Type must be 1 character
        if (strlen($data['type']) !== 1) {
            throw new JamboJetValidationException('Email type must be exactly 1 character');
        }

        // Validate email address
        $this->validateEmail($data['email']);
    }

    /**
     * Validate email edit request
     * 
     * @param array $data Email data
     * @param bool $requireEmail Whether email is required (true for PUT, false for PATCH)
     * @throws JamboJetValidationException
     */
    private function validateEmailEditRequest(array $data, bool $requireEmail = true): void
    {
        if ($requireEmail && !isset($data['email'])) {
            throw new JamboJetValidationException('Email address is required');
        }

        // Validate email if provided
        if (isset($data['email'])) {
            $this->validateEmail($data['email']);
        }
    }

    /**
     * Validate phone number request
     * 
     * @param array $data Phone number data
     * @param bool $requireAll Whether all fields are required (true for create/PUT, false for PATCH)
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumberRequest(array $data, bool $requireAll = true): void
    {
        // Type and number are required for create/update
        if ($requireAll) {
            if (!isset($data['type'])) {
                throw new JamboJetValidationException('Phone number type is required');
            }

            if (!isset($data['number'])) {
                throw new JamboJetValidationException('Phone number is required');
            }
        }

        // Validate type if provided
        if (isset($data['type'])) {
            $this->validatePhoneNumberType($data['type']);
        }

        // Validate number if provided
        if (isset($data['number'])) {
            $this->validatePhoneNumber($data['number']);
        }
    }

    /**
     * Validate phone number type
     * 
     * @param int $type Phone number type
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumberType(int $type): void
    {
        $validTypes = [0, 1, 2, 3, 4]; // Other, Home, Work, Mobile, Fax

        if (!in_array($type, $validTypes, true)) {
            throw new JamboJetValidationException(
                'Invalid phone number type. Must be: 0 (Other), 1 (Home), 2 (Work), 3 (Mobile), or 4 (Fax)'
            );
        }
    }

    /**
     * Validate phone number format
     * 
     * @param string $number Phone number
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumber(string $number): void
    {
        if (empty(trim($number))) {
            throw new JamboJetValidationException('Phone number cannot be empty');
        }

        // Length validation
        if (strlen($number) > 20) {
            throw new JamboJetValidationException('Phone number cannot exceed 20 characters');
        }

        // Basic format validation - allow digits, spaces, parentheses, hyphens, plus signs
        if (!preg_match('/^[\d\s\(\)\-\+]+$/', $number)) {
            throw new JamboJetValidationException(
                'Phone number contains invalid characters. Only digits, spaces, parentheses, hyphens, and plus signs are allowed'
            );
        }

        // Check minimum digits (at least 5)
        $digitsOnly = preg_replace('/\D/', '', $number);
        if (strlen($digitsOnly) < 5) {
            throw new JamboJetValidationException('Phone number must contain at least 5 digits');
        }

        // Check maximum digits (15 per E.164 standard)
        if (strlen($digitsOnly) > 15) {
            throw new JamboJetValidationException('Phone number cannot exceed 15 digits');
        }
    }

    /**
     * Validate account collection key
     * 
     * @param string $key Account collection key
     * @throws JamboJetValidationException
     */
    private function validateAccountCollectionKey(string $key): void
    {
        if (empty($key)) {
            throw new JamboJetValidationException('Account collection key is required');
        }

        if (strlen($key) > 100) {
            throw new JamboJetValidationException('Account collection key cannot exceed 100 characters');
        }
    }

    /**
     * Validate currency code
     * 
     * @param string $code Currency code
     * @throws JamboJetValidationException
     */
    private function validateCurrencyCode(string $code): void
    {
        if (strlen($code) !== 3) {
            throw new JamboJetValidationException('Currency code must be exactly 3 characters');
        }

        if (!ctype_alpha($code)) {
            throw new JamboJetValidationException('Currency code must contain only letters');
        }
    }

    /**
     * Validate create account request
     * 
     * @param array $data Account creation data
     * @throws JamboJetValidationException
     */
    private function validateCreateAccountRequest(array $data): void
    {
        // currencyCode is optional but if provided must be valid
        if (isset($data['currencyCode'])) {
            $this->validateCurrencyCode($data['currencyCode']);
        }

        // accountTypeCode is optional
        if (isset($data['accountTypeCode']) && strlen($data['accountTypeCode']) > 10) {
            throw new JamboJetValidationException('Account type code cannot exceed 10 characters');
        }

        // organizationCode is optional
        if (isset($data['organizationCode']) && strlen($data['organizationCode']) > 10) {
            throw new JamboJetValidationException('Organization code cannot exceed 10 characters');
        }
    }

    /**
     * Validate account collection request
     * 
     * @param array $data Collection creation data
     * @throws JamboJetValidationException
     */
    private function validateAccountCollectionRequest(array $data): void
    {
        // Required fields
        if (!isset($data['amount'])) {
            throw new JamboJetValidationException('Amount is required for account collection');
        }

        if (!isset($data['currencyCode'])) {
            throw new JamboJetValidationException('Currency code is required for account collection');
        }

        // Validate amount
        if (!is_numeric($data['amount'])) {
            throw new JamboJetValidationException('Amount must be a valid number');
        }

        if ($data['amount'] <= 0) {
            throw new JamboJetValidationException('Amount must be greater than zero');
        }

        // Validate currency code
        $this->validateCurrencyCode($data['currencyCode']);

        // Optional: transactionCode
        if (isset($data['transactionCode'])) {
            if (strlen($data['transactionCode']) > 6) {
                throw new JamboJetValidationException('Transaction code cannot exceed 6 characters');
            }
        }

        // Optional: note
        if (isset($data['note']) && strlen($data['note']) > 128) {
            throw new JamboJetValidationException('Note cannot exceed 128 characters');
        }

        // Optional: expiration date
        if (isset($data['expiration'])) {
            $this->validateIso8601Date($data['expiration'], 'Expiration date');
        }
    }

    /**
     * Validate transaction request
     * 
     * @param array $data Transaction creation data
     * @throws JamboJetValidationException
     */
    private function validateTransactionRequest(array $data): void
    {
        // Required fields
        if (!isset($data['amount'])) {
            throw new JamboJetValidationException('Amount is required for transaction');
        }

        if (!isset($data['currencyCode'])) {
            throw new JamboJetValidationException('Currency code is required for transaction');
        }

        // Validate amount
        if (!is_numeric($data['amount'])) {
            throw new JamboJetValidationException('Amount must be a valid number');
        }

        // Note: Amount can be negative for debits/refunds

        // Validate currency code
        $this->validateCurrencyCode($data['currencyCode']);

        // Optional: note
        if (isset($data['note']) && strlen($data['note']) > 128) {
            throw new JamboJetValidationException('Note cannot exceed 128 characters');
        }
    }

    /**
     * Validate account status update
     * 
     * @param array $data Status update data
     * @throws JamboJetValidationException
     */
    private function validateAccountStatusUpdate(array $data): void
    {
        if (!isset($data['status'])) {
            throw new JamboJetValidationException('Status is required for account status update');
        }

        $validStatuses = [0, 1, 2, 3]; // Open, Closed, AgencyInactive, Unknown
        if (!in_array($data['status'], $validStatuses, true)) {
            throw new JamboJetValidationException(
                'Invalid account status. Must be 0 (Open), 1 (Closed), 2 (AgencyInactive), or 3 (Unknown)'
            );
        }

        // Optional: reason
        if (isset($data['reason']) && strlen($data['reason']) > 500) {
            throw new JamboJetValidationException('Reason cannot exceed 500 characters');
        }

        // Optional: effectiveDate
        if (isset($data['effectiveDate'])) {
            $this->validateIso8601Date($data['effectiveDate'], 'Effective date');
        }
    }

    /**
     * Validate transaction query parameters
     * 
     * @param array $params Query parameters
     * @throws JamboJetValidationException
     */
    private function validateTransactionQueryParams(array $params): void
    {
        // StartDate is required
        if (!isset($params['StartDate'])) {
            throw new JamboJetValidationException('StartDate is required for transaction queries');
        }

        $this->validateIso8601Date($params['StartDate'], 'Start date');

        // EndDate is optional but must be valid if provided
        if (isset($params['EndDate'])) {
            $this->validateIso8601Date($params['EndDate'], 'End date');

            // EndDate must be after StartDate
            $startTime = strtotime($params['StartDate']);
            $endTime = strtotime($params['EndDate']);

            if ($endTime < $startTime) {
                throw new JamboJetValidationException('End date must be after start date');
            }
        }

        // PageSize validation
        if (isset($params['PageSize'])) {
            $pageSize = $params['PageSize'];

            if (!is_numeric($pageSize)) {
                throw new JamboJetValidationException('PageSize must be a number');
            }

            if ($pageSize < 10 || $pageSize > 5000) {
                throw new JamboJetValidationException('PageSize must be between 10 and 5000');
            }
        }

        // SortByNewest is optional boolean
        if (isset($params['SortByNewest']) && !is_bool($params['SortByNewest'])) {
            throw new JamboJetValidationException('SortByNewest must be a boolean value');
        }

        // LastPageKey is optional string
        if (isset($params['LastPageKey']) && !is_string($params['LastPageKey'])) {
            throw new JamboJetValidationException('LastPageKey must be a string');
        }

        // TransactionType is optional integer (for getAllPersonAccountTransactions)
        if (isset($params['TransactionType'])) {
            $validTypes = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            if (!in_array($params['TransactionType'], $validTypes, true)) {
                throw new JamboJetValidationException('Invalid transaction type');
            }
        }
    }

    /**
     * Validate affiliation request
     * 
     * @param array $data Affiliation data
     * @throws JamboJetValidationException
     */
    private function validateAffiliationRequest(array $data): void
    {
        // Name is required
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new JamboJetValidationException('Affiliation name is required');
        }

        // Name must be at least 1 character
        if (strlen(trim($data['name'])) < 1) {
            throw new JamboJetValidationException('Affiliation name must be at least 1 character');
        }
    }

    /**
     * Validate alias request
     * 
     * @param array $data Alias data
     * @param bool $strictValidation Whether to enforce all validations (true for create/PUT)
     * @throws JamboJetValidationException
     */
    private function validateAliasRequest(array $data, bool $strictValidation = true): void
    {
        // Type validation if provided
        if (isset($data['type'])) {
            $validTypes = [0, 1]; // Alias, Variant
            if (!in_array($data['type'], $validTypes, true)) {
                throw new JamboJetValidationException(
                    'Invalid alias type. Must be: 0 (Alias) or 1 (Variant)'
                );
            }
        }

        // Name field validations
        if (isset($data['first']) && strlen($data['first']) > 32) {
            throw new JamboJetValidationException('First name cannot exceed 32 characters');
        }

        if (isset($data['middle']) && strlen($data['middle']) > 32) {
            throw new JamboJetValidationException('Middle name cannot exceed 32 characters');
        }

        if (isset($data['last']) && strlen($data['last']) > 32) {
            throw new JamboJetValidationException('Last name cannot exceed 32 characters');
        }

        if (isset($data['title']) && strlen($data['title']) > 6) {
            throw new JamboJetValidationException('Title cannot exceed 6 characters');
        }

        if (isset($data['suffix']) && strlen($data['suffix']) > 6) {
            throw new JamboJetValidationException('Suffix cannot exceed 6 characters');
        }

        // For strict validation, ensure at least one name field is provided
        if ($strictValidation) {
            $hasName = isset($data['first']) || isset($data['middle']) || isset($data['last']);
            if (!$hasName) {
                throw new JamboJetValidationException(
                    'At least one name field (first, middle, or last) must be provided'
                );
            }
        }
    }

    /**
     * Validate comment request
     * 
     * @param array $data Comment data
     * @param bool $strictValidation Whether to enforce all validations
     * @throws JamboJetValidationException
     */
    private function validateCommentRequest(array $data, bool $strictValidation = true): void
    {
        // Type validation if provided
        if (isset($data['type'])) {
            $validTypes = [0, 1, 2, 3, 4, 5]; // Default, Itinerary, Manifest, Alert, Archive, Voucher
            if (!in_array($data['type'], $validTypes, true)) {
                throw new JamboJetValidationException(
                    'Invalid comment type. Must be: 0 (Default), 1 (Itinerary), 2 (Manifest), 3 (Alert), 4 (Archive), or 5 (Voucher)'
                );
            }
        }

        // Text is optional but validate if provided
        // No specific length limit in schema, but reasonable validation
        if (isset($data['text']) && strlen($data['text']) > 10000) {
            throw new JamboJetValidationException('Comment text cannot exceed 10000 characters');
        }
    }

    /**
     * Validate ISO 8601 date format
     * 
     * @param string $date Date string
     * @param string $fieldName Field name for error message
     * @throws JamboJetValidationException
     */
    private function validateIso8601Date(string $date, string $fieldName): void
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            throw new JamboJetValidationException("{$fieldName} must be a valid ISO 8601 date");
        }
    }

    /**
     * Validate person key
     * 
     * @param string $personKey Person key
     * @throws JamboJetValidationException
     */
    private function validatePersonKey(string $personKey): void
    {
        if (empty($personKey)) {
            throw new JamboJetValidationException('Person key is required');
        }

        if (strlen($personKey) > 100) {
            throw new JamboJetValidationException('Person key cannot exceed 100 characters');
        }
    }

    /**
     * Validate person creation data
     * 
     * @param array $data Person data
     * @throws JamboJetValidationException
     */
    private function validatePersonCreateData(array $data): void
    {
        // Name is required for person creation
        if (!isset($data['name']) || empty($data['name'])) {
            throw new JamboJetValidationException('Person name is required for creation');
        }

        // Validate name structure
        $this->validatePersonName($data['name']);

        // Validate optional fields if present
        if (isset($data['type'])) {
            $this->validatePersonType($data['type']);
        }

        if (isset($data['birthDate'])) {
            $this->validateBirthDate($data['birthDate']);
        }

        if (isset($data['gender'])) {
            $this->validateGender($data['gender']);
        }

        if (isset($data['customerNumber'])) {
            $this->validateCustomerNumber($data['customerNumber']);
        }

        if (isset($data['nationalityCode'])) {
            $this->validateCountryCode($data['nationalityCode'], 'Nationality code');
        }

        if (isset($data['emailAddress'])) {
            $this->validateEmail($data['emailAddress']);
        }
    }

    /**
     * Validate person update data
     * 
     * @param array $data Person data
     * @throws JamboJetValidationException
     */
    private function validatePersonUpdateData(array $data): void
    {
        // Similar validation to create, but name might not be required for updates
        if (isset($data['name'])) {
            $this->validatePersonName($data['name']);
        }

        if (isset($data['type'])) {
            $this->validatePersonType($data['type']);
        }

        if (isset($data['birthDate'])) {
            $this->validateBirthDate($data['birthDate']);
        }

        if (isset($data['gender'])) {
            $this->validateGender($data['gender']);
        }

        if (isset($data['customerNumber'])) {
            $this->validateCustomerNumber($data['customerNumber']);
        }

        if (isset($data['nationalityCode'])) {
            $this->validateCountryCode($data['nationalityCode'], 'Nationality code');
        }
    }

    /**
     * Validate person patch data
     * 
     * @param array $data Patch data
     * @throws JamboJetValidationException
     */
    private function validatePersonPatchData(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Patch data cannot be empty. At least one field must be provided');
        }

        // Validate only fields that are present
        if (isset($data['name'])) {
            $this->validatePersonName($data['name']);
        }

        if (isset($data['type'])) {
            $this->validatePersonType($data['type']);
        }

        if (isset($data['birthDate'])) {
            $this->validateBirthDate($data['birthDate']);
        }

        if (isset($data['gender'])) {
            $this->validateGender($data['gender']);
        }

        if (isset($data['customerNumber'])) {
            $this->validateCustomerNumber($data['customerNumber']);
        }

        if (isset($data['nationalityCode'])) {
            $this->validateCountryCode($data['nationalityCode'], 'Nationality code');
        }
    }

    /**
     * Validate person search criteria
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validatePersonSearchCriteria(array $criteria): void
    {
        // Validate optional search parameters
        if (isset($criteria['FirstNameMatching'])) {
            $this->validateMatchCriteria($criteria['FirstNameMatching']);
        }

        if (isset($criteria['NationalIdNumberMatching'])) {
            $this->validateMatchCriteria($criteria['NationalIdNumberMatching']);
        }

        if (isset($criteria['Type'])) {
            $this->validatePersonType($criteria['Type']);
        }

        if (isset($criteria['ReturnCount'])) {
            if (!is_int($criteria['ReturnCount']) || $criteria['ReturnCount'] < 1 || $criteria['ReturnCount'] > 5000) {
                throw new JamboJetValidationException('Return count must be between 1 and 5000');
            }
        }

        if (isset($criteria['LastIndex'])) {
            if (!is_int($criteria['LastIndex']) || $criteria['LastIndex'] < 0) {
                throw new JamboJetValidationException('Last index must be a non-negative integer');
            }
        }

        if (isset($criteria['ActiveOnly']) && !is_bool($criteria['ActiveOnly'])) {
            throw new JamboJetValidationException('ActiveOnly must be a boolean value');
        }
    }

    /**
     * Validate person name structure
     * 
     * @param array $name Name data
     * @throws JamboJetValidationException
     */
    private function validatePersonName(array $name): void
    {
        if (!is_array($name)) {
            throw new JamboJetValidationException('Person name must be an object/array');
        }

        // Last name is typically required
        if (!isset($name['last']) || empty($name['last'])) {
            throw new JamboJetValidationException('Last name is required');
        }

        if (strlen($name['last']) > 32) {
            throw new JamboJetValidationException('Last name cannot exceed 32 characters');
        }

        // First name validation if present
        if (isset($name['first'])) {
            if (strlen($name['first']) > 32) {
                throw new JamboJetValidationException('First name cannot exceed 32 characters');
            }
        }

        // Middle name validation if present
        if (isset($name['middle'])) {
            if (strlen($name['middle']) > 32) {
                throw new JamboJetValidationException('Middle name cannot exceed 32 characters');
            }
        }

        // Title validation if present
        if (isset($name['title'])) {
            if (strlen($name['title']) > 10) {
                throw new JamboJetValidationException('Title cannot exceed 10 characters');
            }
        }

        // Suffix validation if present
        if (isset($name['suffix'])) {
            if (strlen($name['suffix']) > 10) {
                throw new JamboJetValidationException('Suffix cannot exceed 10 characters');
            }
        }
    }

    /**
     * Validate person type
     * 
     * @param int $type Person type
     * @throws JamboJetValidationException
     */
    private function validatePersonType(int $type): void
    {
        if (!in_array($type, [0, 1, 2], true)) {
            throw new JamboJetValidationException('Person type must be 0 (None), 1 (Customer), or 2 (Agent)');
        }
    }

    /**
     * Validate birth date
     * 
     * @param string $birthDate Birth date
     * @throws JamboJetValidationException
     */
    private function validateBirthDate(string $birthDate): void
    {
        if (!strtotime($birthDate)) {
            throw new JamboJetValidationException('Invalid birth date format. Use ISO 8601 format (e.g., 1990-01-15)');
        }

        // Birth date must be in the past
        if (strtotime($birthDate) > time()) {
            throw new JamboJetValidationException('Birth date cannot be in the future');
        }

        // Reasonable age check (e.g., not more than 150 years old)
        $minDate = strtotime('-150 years');
        if (strtotime($birthDate) < $minDate) {
            throw new JamboJetValidationException('Birth date cannot be more than 150 years ago');
        }
    }

    /**
     * Validate gender code
     * 
     * @param string $gender Gender code
     * @throws JamboJetValidationException
     */
    private function validateGender(string $gender): void
    {
        if (strlen($gender) !== 1) {
            throw new JamboJetValidationException('Gender must be a single character (M/F/U)');
        }

        if (!in_array(strtoupper($gender), ['M', 'F', 'U'], true)) {
            throw new JamboJetValidationException('Gender must be M (Male), F (Female), or U (Unspecified)');
        }
    }

    /**
     * Validate customer number
     * 
     * @param string $customerNumber Customer number
     * @throws JamboJetValidationException
     */
    private function validateCustomerNumber(string $customerNumber): void
    {
        if (empty($customerNumber)) {
            throw new JamboJetValidationException('Customer number cannot be empty');
        }

        if (strlen($customerNumber) < 1 || strlen($customerNumber) > 20) {
            throw new JamboJetValidationException('Customer number must be between 1 and 20 characters');
        }
    }

    /**
     * Validate country code (3 letters)
     * 
     * @param string $code Country code
     * @param string $fieldName Field name for error messages
     * @throws JamboJetValidationException
     */
    private function validateCountryCode(string $code, string $fieldName): void
    {
        if (strlen($code) !== 3) {
            throw new JamboJetValidationException("{$fieldName} must be exactly 3 characters");
        }

        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            throw new JamboJetValidationException("{$fieldName} must be 3 uppercase letters (e.g., USA, KEN, GBR)");
        }
    }

    /**
     * Validate email address
     * 
     * @param string $email Email address
     * @throws JamboJetValidationException
     */
    private function validateEmail(string $email): void
    {
        if (empty($email)) {
            throw new JamboJetValidationException('Email address cannot be empty');
        }

        if (strlen($email) > 266) {
            throw new JamboJetValidationException('Email address cannot exceed 266 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new JamboJetValidationException('Invalid email address format');
        }
    }

    /**
     * Validate match criteria
     * 
     * @param int $criteria Match criteria
     * @throws JamboJetValidationException
     */
    private function validateMatchCriteria(int $criteria): void
    {
        if (!in_array($criteria, [0, 1, 2, 3], true)) {
            throw new JamboJetValidationException('Match criteria must be 0 (StartsWith), 1 (EndsWith), 2 (Contains), or 3 (ExactMatch)');
        }
    }
}
