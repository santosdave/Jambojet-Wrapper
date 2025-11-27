<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Person Interface for JamboJet NSK API
 * 
 * Manages person records (customers and agents) with comprehensive CRUD operations
 * for persons and their related entities (addresses, emails, programs, etc.).
 * 
 * Base path: /api/nsk/v1/persons and /api/nsk/v2/persons
 * 
 * PERSON TYPES:
 * - 0 = None
 * - 1 = Customer (passengers, travelers)
 * - 2 = Agent (travel agents, airline staff)
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface PersonInterface
{
    // =================================================================
    // PHASE 1: CORE PERSON OPERATIONS (6 methods)
    // =================================================================

    /**
     * Create a new person record
     * POST /api/nsk/v1/persons
     * GraphQL: personAdd
     * 
     * Creates a new person record in the system.
     * Requires agent permissions.
     * 
     * PERSON DATA STRUCTURE:
     * - name (object, required): First, middle, last name, title, suffix
     * - type (int, optional): 0=None, 1=Customer, 2=Agent
     * - birthDate (string, optional): ISO 8601 format
     * - gender (string, optional): M/F/U (1 char)
     * - customerNumber (string, optional): Unique customer identifier
     * - culture (string, optional): Culture code (e.g., en-US)
     * - nationalityCode (string, optional): 3-letter country code
     * - nationalIdNumber (string, optional): Government ID number
     * - username (string, optional): Login username
     * 
     * USE CASES:
     * - New customer registration
     * - Agent account creation
     * - Customer profile import from external systems
     * - Loyalty program enrollment
     * 
     * @param array $personData Person creation data
     * @return array Created person with personKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPerson(array $personData): array;

    /**
     * Search person records with advanced filters
     * GET /api/nsk/v2/persons
     * GraphQL: personSearchv2
     * 
     * Powerful search endpoint supporting multiple filter criteria
     * and pagination for finding person records.
     * 
     * SEARCH FILTERS (all optional):
     * - username (string): Person's username
     * - firstName (string): First name search
     * - lastName (string): Last name search
     * - firstNameMatching (int): 0=StartsWith, 1=EndsWith, 2=Contains, 3=ExactMatch
     * - customerNumber (string): Customer number
     * - phoneNumber (string): Phone number
     * - emailAddress (string): Email address
     * - programNumber (string): Loyalty program number
     * - programCode (string): Loyalty program code
     * - type (int): Person type (0=None, 1=Customer, 2=Agent)
     * - nationalIdNumber (string): National ID number
     * - nationalIdNumberMatching (int): Match criteria (0-3)
     * - returnCount (int): Number of results to return
     * - activeOnly (bool): Include only active persons
     * - lastIndex (int): Last index for pagination
     * 
     * PAGINATION:
     * Results are paginated. Use lastIndex from previous response
     * to retrieve next page of results.
     * 
     * @param array $searchCriteria Search filters
     * @return array Search results with:
     *   - records (array): Array of person records
     *   - lastIndex (int): Index for next page
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchPersons(array $searchCriteria = []): array;

    /**
     * Get a specific person by key
     * GET /api/nsk/v1/persons/{personKey}
     * GraphQL: person
     * 
     * Retrieves complete person record including all basic information.
     * Does NOT include sub-resources (addresses, emails, etc.) - use
     * dedicated endpoints for those.
     * 
     * RETURNED DATA:
     * - personKey (string): Unique identifier
     * - name (object): Full name details
     * - type (int): Person type
     * - birthDate (string): Date of birth
     * - gender (string): Gender code
     * - customerNumber (string): Customer number
     * - culture (string): Culture/language preference
     * - nationalityCode (string): Nationality
     * - nationalIdNumber (string): Government ID
     * - status (int): Account status
     * - createdDate (string): Creation timestamp
     * - modifiedDate (string): Last modification timestamp
     * 
     * @param string $personKey Unique person key
     * @return array Complete person record
     * @throws JamboJetApiException
     */
    public function getPerson(string $personKey): array;

    /**
     * Update person record (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}
     * GraphQL: personSet
     * 
     * Performs a full update of person's basic information.
     * All fields in the request will replace existing values.
     * 
     * UPDATABLE FIELDS:
     * - name (object): Full name details
     * - birthDate (string): Date of birth
     * - gender (string): Gender code
     * - customerNumber (string): Customer number
     * - culture (string): Culture/language
     * - nationalityCode (string): Nationality
     * - nationalIdNumber (string): Government ID
     * - type (int): Person type (with restrictions)
     * 
     * BUSINESS RULES:
     * - Cannot change personKey
     * - Type changes may have restrictions
     * - Some fields may be immutable after creation
     * 
     * @param string $personKey Unique person key
     * @param array $personData Updated person data
     * @return array Update confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePerson(string $personKey, array $personData): array;

    /**
     * Patch person record (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}
     * GraphQL: personModify
     * 
     * Updates only specified fields, leaving others unchanged.
     * More efficient than PUT when updating few fields.
     * 
     * DELTA UPDATE:
     * Only include fields you want to change. Omitted fields
     * remain unchanged in the database.
     * 
     * EXAMPLE:
     * ```php
     * // Only update customer number and culture
     * $patch = [
     *     'customerNumber' => 'CUST12345',
     *     'culture' => 'en-US'
     * ];
     * ```
     * 
     * @param string $personKey Unique person key
     * @param array $patchData Fields to update (delta changes)
     * @return array Update confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function patchPerson(string $personKey, array $patchData): array;

    /**
     * Delete person record (set to terminated)
     * DELETE /api/nsk/v1/persons/{personKey}
     * GraphQL: personDelete
     * 
     * Soft deletes a person by setting record status to terminated.
     * Requires agent permissions.
     * 
     * DELETION BEHAVIOR:
     * - Person record is not physically deleted
     * - Status is set to terminated/inactive
     * - Historical data is preserved
     * - Person cannot be used for new bookings
     * - Existing bookings remain intact
     * 
     * RESTRICTIONS:
     * - Cannot delete person with active bookings (in some configurations)
     * - Cannot delete person with pending payments
     * - Requires appropriate permissions
     * 
     * USE CASES:
     * - Account closure requests
     * - Compliance with data retention policies
     * - Removing duplicate/test accounts
     * 
     * @param string $personKey Unique person key
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deletePerson(string $personKey): array;

    // =================================================================
    // PHASE 2: PERSON ACCOUNT OPERATIONS (7 methods)
    // =================================================================

    /**
     * Get person account and credits
     * GET /api/nsk/v1/persons/{personKey}/account
     * GraphQL: personsAccount
     * 
     * Retrieves the person's payment account including balance and collections.
     * If currency code is not provided, defaults to account's currency.
     * 
     * ACCOUNT INFORMATION:
     * - accountBalance (decimal): Current account balance
     * - currencyCode (string): Account currency
     * - status (int): Account status (0=Open, 1=Closed, 2=AgencyInactive, 3=Unknown)
     * - collections (array): List of account collections
     * - credits (array): Available credits
     * 
     * @param string $personKey Person key
     * @param string|null $currencyCode Optional currency code for balance conversion
     * @return array Account information
     * @throws JamboJetApiException
     */
    public function getPersonAccount(string $personKey, ?string $currencyCode = null): array;

    /**
     * Create person account
     * POST /api/nsk/v1/persons/{personKey}/account
     * GraphQL: personsAccountAdd
     * 
     * Creates a new payment account for the person.
     * 
     * ACCOUNT DATA:
     * - currencyCode (string, required): Account currency (3 chars)
     * - accountTypeCode (string, optional): Account type code
     * - organizationCode (string, optional): Organization code
     * 
     * USE CASES:
     * - Setting up customer wallet
     * - Corporate account creation
     * - Agency account initialization
     * 
     * @param string $personKey Person key
     * @param array $accountData Account creation data
     * @return array Created account
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccount(string $personKey, array $accountData): array;

    /**
     * Create account collection
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
     * COLLECTION DATA:
     * - transactionCode (string, required): See /api/nsk/v1/resources/accountTransactionCodes
     * - amount (decimal, required): Transaction amount
     * - currencyCode (string, required): Currency code
     * - expirationDate (string, optional): Collection expiration (ISO 8601)
     * - note (string, optional): Transaction note
     * 
     * @param string $personKey Person key
     * @param array $collectionData Collection/transaction data
     * @return array Created/updated collection
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccountCollection(string $personKey, array $collectionData): array;

    /**
     * Create account transaction
     * POST /api/nsk/v1/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: personsAccountTransactionsAdd
     * 
     * Adds a transaction to an existing account collection.
     * Collection must already exist - use createPersonAccountCollection to create new.
     * 
     * TRANSACTION DATA:
     * - amount (decimal, required): Transaction amount
     * - note (string, optional): Transaction note
     * - referenceNumber (string, optional): External reference
     * 
     * TRANSACTION TYPES:
     * - 0 = Default
     * - 1 = Payment
     * - 2 = Adjustment
     * - 3 = Supplementary
     * - 4 = Transfer
     * 
     * @param string $personKey Person key
     * @param string $accountCollectionKey Account collection key
     * @param array $transactionData Transaction data
     * @return array Created transaction
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonAccountTransaction(
        string $personKey,
        string $accountCollectionKey,
        array $transactionData
    ): array;

    /**
     * Get account collection transactions (v2)
     * GET /api/nsk/v2/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: personsAccountTransactionsv2
     * 
     * Retrieves paginated transactions for a specific account collection.
     * Version 2 with enhanced filtering and pagination.
     * 
     * QUERY PARAMETERS:
     * - StartDate (string, required): Start date (ISO 8601)
     * - EndDate (string, optional): End date (ISO 8601)
     * - SortByNewest (bool, optional): Sort by newest first
     * - PageSize (int, optional): Records per page (10-5000)
     * - LastPageKey (string, optional): Pagination cursor
     * 
     * @param string $personKey Person key
     * @param string $accountCollectionKey Account collection key
     * @param array $params Query parameters
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
    ): array;

    /**
     * Update person account status
     * PUT /api/nsk/v1/persons/{personKey}/account/status
     * GraphQL: personsAccountStatusSet
     * 
     * Updates the status of person's payment account.
     * 
     * ACCOUNT STATUSES:
     * - 0 = Open: Account is active
     * - 1 = Closed: Account is closed
     * - 2 = AgencyInactive: Agency account is inactive
     * - 3 = Unknown: Status unknown
     * 
     * USE CASES:
     * - Closing dormant accounts
     * - Suspending accounts for investigation
     * - Reactivating closed accounts
     * 
     * @param string $personKey Person key
     * @param int $status New account status (0-3)
     * @return array Status update confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonAccountStatus(string $personKey, int $status): array;

    /**
     * Get all person account transactions (v2)
     * GET /api/nsk/v2/persons/{personKey}/account/transactions
     * GraphQL: personsAccountAllTransactionsv2
     * 
     * Retrieves ALL transactions across all collections for the person.
     * Version 2 with pagination and filtering.
     * 
     * QUERY PARAMETERS:
     * - StartDate (string, required): Start date (ISO 8601)
     * - EndDate (string, optional): End date (ISO 8601)
     * - SortByNewest (bool, optional): Sort by newest first
     * - PageSize (int, optional): Records per page (10-5000)
     * - LastPageKey (string, optional): Pagination cursor
     * 
     * @param string $personKey Person key
     * @param array $params Query parameters
     * @return array Paginated transactions across all collections
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function getAllPersonAccountTransactions(string $personKey, array $params): array;


    // =================================================================
    // PHASE 3: SUB-RESOURCES PART 1 - ADDRESSES (6 methods)
    // =================================================================

    /**
     * Get all person addresses
     * GET /api/nsk/v1/persons/{personKey}/addresses
     */
    public function getPersonAddresses(string $personKey): array;

    /**
     * Create person address
     * POST /api/nsk/v1/persons/{personKey}/addresses
     * GraphQL: personAddressAdd
     */
    public function createPersonAddress(string $personKey, array $addressData): array;

    /**
     * Get person address
     * GET /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddress
     */
    public function getPersonAddress(string $personKey, string $personAddressKey): array;

    /**
     * Update person address (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressSet
     */
    public function updatePersonAddress(
        string $personKey,
        string $personAddressKey,
        array $addressData
    ): array;

    /**
     * Patch person address (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressModify
     */
    public function patchPersonAddress(
        string $personKey,
        string $personAddressKey,
        array $addressData
    ): array;

    /**
     * Delete person address
     * DELETE /api/nsk/v1/persons/{personKey}/addresses/{personAddressKey}
     * GraphQL: personAddressDelete
     */
    public function deletePersonAddress(string $personKey, string $personAddressKey): array;

    // =================================================================
    // PHASE 3: SUB-RESOURCES PART 1 - EMAILS (6 methods)
    // =================================================================

    /**
     * Get all person emails
     * GET /api/nsk/v1/persons/{personKey}/emails
     */
    public function getPersonEmails(string $personKey): array;

    /**
     * Create person email
     * POST /api/nsk/v1/persons/{personKey}/emails
     * GraphQL: personEmailAdd
     */
    public function createPersonEmail(string $personKey, array $emailData): array;

    /**
     * Get person email
     * GET /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmail
     */
    public function getPersonEmail(string $personKey, string $personEmailAddressKey): array;

    /**
     * Update person email (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailSet
     */
    public function updatePersonEmail(
        string $personKey,
        string $personEmailAddressKey,
        array $emailData
    ): array;

    /**
     * Patch person email (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailModify
     */
    public function patchPersonEmail(
        string $personKey,
        string $personEmailAddressKey,
        array $emailData
    ): array;

    /**
     * Delete person email
     * DELETE /api/nsk/v1/persons/{personKey}/emails/{personEmailAddressKey}
     * GraphQL: personEmailDelete
     */
    public function deletePersonEmail(string $personKey, string $personEmailAddressKey): array;

    // =================================================================
    // PHASE 3: SUB-RESOURCES PART 1 - PHONE NUMBERS (6 methods)
    // =================================================================

    /**
     * Get all person phone numbers
     * GET /api/nsk/v1/persons/{personKey}/phoneNumbers
     */
    public function getPersonPhoneNumbers(string $personKey): array;

    /**
     * Create person phone number
     * POST /api/nsk/v1/persons/{personKey}/phoneNumbers
     * GraphQL: personPhoneNumberAdd
     */
    public function createPersonPhoneNumber(string $personKey, array $phoneData): array;

    /**
     * Get person phone number
     * GET /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumber
     */
    public function getPersonPhoneNumber(string $personKey, string $personPhoneNumberKey): array;

    /**
     * Update person phone number (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberSet
     */
    public function updatePersonPhoneNumber(
        string $personKey,
        string $personPhoneNumberKey,
        array $phoneData
    ): array;

    /**
     * Patch person phone number (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberModify
     */
    public function patchPersonPhoneNumber(
        string $personKey,
        string $personPhoneNumberKey,
        array $phoneData
    ): array;

    /**
     * Delete person phone number
     * DELETE /api/nsk/v1/persons/{personKey}/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: personPhoneNumberDelete
     */
    public function deletePersonPhoneNumber(string $personKey, string $personPhoneNumberKey): array;

    /**
     * Get all person affiliations
     * GET /api/nsk/v1/persons/{personKey}/affiliations
     * GraphQL: personAffiliations
     */
    public function getPersonAffiliations(string $personKey): array;

    /**
     * Create person affiliation
     * POST /api/nsk/v1/persons/{personKey}/affiliations
     * GraphQL: personAffiliationAdd
     */
    public function createPersonAffiliation(string $personKey, array $affiliationData): array;

    /**
     * Get person affiliation
     * GET /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliation
     */
    public function getPersonAffiliation(string $personKey, string $personAffiliationKey): array;

    /**
     * Update person affiliation
     * PUT /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliationSet
     * Note: Affiliations do not support PATCH operations
     */
    public function updatePersonAffiliation(
        string $personKey,
        string $personAffiliationKey,
        array $affiliationData
    ): array;

    /**
     * Delete person affiliation
     * DELETE /api/nsk/v1/persons/{personKey}/affiliations/{personAffiliationKey}
     * GraphQL: personAffiliationDelete
     */
    public function deletePersonAffiliation(string $personKey, string $personAffiliationKey): array;

    // =================================================================
    // PHASE 4: SUB-RESOURCES PART 2 - ALIASES (6 methods)
    // =================================================================

    /**
     * Get all person aliases
     * GET /api/nsk/v1/persons/{personKey}/aliases
     */
    public function getPersonAliases(string $personKey): array;

    /**
     * Create person alias
     * POST /api/nsk/v1/persons/{personKey}/aliases
     * GraphQL: personAliasAdd
     */
    public function createPersonAlias(string $personKey, array $aliasData): array;

    /**
     * Get person alias
     * GET /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAlias
     */
    public function getPersonAlias(string $personKey, string $personAliasKey): array;

    /**
     * Update person alias (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasSet
     */
    public function updatePersonAlias(
        string $personKey,
        string $personAliasKey,
        array $aliasData
    ): array;

    /**
     * Patch person alias (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasModify
     */
    public function patchPersonAlias(
        string $personKey,
        string $personAliasKey,
        array $aliasData
    ): array;

    /**
     * Delete person alias
     * DELETE /api/nsk/v1/persons/{personKey}/aliases/{personAliasKey}
     * GraphQL: personAliasDelete
     */
    public function deletePersonAlias(string $personKey, string $personAliasKey): array;

    // =================================================================
    // PHASE 4: SUB-RESOURCES PART 2 - COMMENTS (6 methods)
    // =================================================================

    /**
     * Get all person comments
     * GET /api/nsk/v1/persons/{personKey}/comments
     * GraphQL: personComments
     */
    public function getPersonComments(string $personKey): array;

    /**
     * Create person comment
     * POST /api/nsk/v1/persons/{personKey}/comments
     * GraphQL: personCommentAdd
     */
    public function createPersonComment(string $personKey, array $commentData): array;

    /**
     * Get person comment
     * GET /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personComment
     */
    public function getPersonComment(string $personKey, string $personCommentKey): array;

    /**
     * Update person comment (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentSet
     */
    public function updatePersonComment(
        string $personKey,
        string $personCommentKey,
        array $commentData
    ): array;

    /**
     * Patch person comment (partial update)
     * PATCH /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentModify
     */
    public function patchPersonComment(
        string $personKey,
        string $personCommentKey,
        array $commentData
    ): array;

    /**
     * Delete person comment
     * DELETE /api/nsk/v1/persons/{personKey}/comments/{personCommentKey}
     * GraphQL: personCommentDelete
     */
    public function deletePersonComment(string $personKey, string $personCommentKey): array;

    /**
     * Get all person information
     * GET /api/nsk/v1/persons/{personKey}/information
     * GraphQL: personInformations
     * 
     * Retrieves all information entries associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of information entries
     * @throws JamboJetApiException
     */
    public function getPersonInformation(string $personKey): array;

    /**
     * Create person information
     * POST /api/nsk/v1/persons/{personKey}/information
     * GraphQL: personInformationAdd
     * 
     * Creates a new information entry for the person.
     * Requires personInformationTypeCode (1 char).
     * 
     * @param string $personKey Person key
     * @param array $informationData Information data:
     *   - personInformationTypeCode (string, required): Type code (1 char)
     *   - data (string, optional): Information data content
     * @return array Created information with personInformationKey
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function createPersonInformation(string $personKey, array $informationData): array;

    /**
     * Get person information item
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
    public function getPersonInformationItem(string $personKey, string $personInformationKey): array;

    /**
     * Update person information (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/information/{personInformationKey}
     * GraphQL: personInformationSet
     * 
     * Replaces entire information entry with new data.
     * 
     * @param string $personKey Person key
     * @param string $personInformationKey Person information key
     * @param array $informationData Complete information data
     * @return array Updated information
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonInformation(
        string $personKey,
        string $personInformationKey,
        array $informationData
    ): array;

    /**
     * Patch person information (partial update)
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
    ): array;

    /**
     * Delete person information
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
    public function deletePersonInformation(string $personKey, string $personInformationKey): array;

    // =================================================================
    // PHASE 5: SUB-RESOURCES PART 3 - PREFERENCES (6 methods)
    // =================================================================

    /**
     * Get all person preferences
     * GET /api/nsk/v1/persons/{personKey}/preferences
     * 
     * Retrieves all preferences associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of preferences
     * @throws JamboJetApiException
     */
    public function getPersonPreferences(string $personKey): array;

    /**
     * Create person preference
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
    public function createPersonPreference(string $personKey, array $preferenceData): array;

    /**
     * Get person preference
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
    public function getPersonPreference(string $personKey, string $personPreferenceKey): array;

    /**
     * Update person preference (full replacement)
     * PUT /api/nsk/v1/persons/{personKey}/preferences/{personPreferenceKey}
     * GraphQL: personPreferenceSet
     * 
     * Replaces entire preference with new data.
     * 
     * @param string $personKey Person key
     * @param string $personPreferenceKey Person preference key
     * @param array $preferenceData Complete preference data
     * @return array Updated preference
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updatePersonPreference(
        string $personKey,
        string $personPreferenceKey,
        array $preferenceData
    ): array;

    /**
     * Patch person preference (partial update)
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
    ): array;

    /**
     * Delete person preference
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
    public function deletePersonPreference(string $personKey, string $personPreferenceKey): array;

    // =================================================================
    // PHASE 5: SUB-RESOURCES PART 3 - PROGRAMS (2 methods)
    // =================================================================

    /**
     * Get all person programs
     * GET /api/nsk/v1/persons/{personKey}/programs
     * GraphQL: personPrograms
     * 
     * Retrieves all loyalty/customer programs associated with a person.
     * 
     * @param string $personKey Person key
     * @return array List of programs
     * @throws JamboJetApiException
     */
    public function getPersonPrograms(string $personKey): array;

    /**
     * Create person program
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
    public function createPersonProgram(string $personKey, array $programData): array;

    /**
     * Merge two person records
     * PUT /api/nsk/v1/persons/{personKey}/merge
     * GraphQL: personMerge
     * 
     * Merges two person records by combining their PNRs, account information,
     * and optionally customer programs. This is a destructive operation that
     * removes the source person and consolidates all data into the target person.
     * 
     * REQUIRES AGENT PERMISSIONS
     * 
     * MERGE BEHAVIOR:
     * - Target person (personKey): Person that will receive all merged data
     * - Source person (deletePersonKey): Person that will be deleted after merge
     * - PNRs from source are transferred to target
     * - Account balances and transactions are combined
     * - Customer programs can be selectively merged
     * - Historical data is preserved on target person
     * 
     * NAME HANDLING:
     * - If newName provided: Target person will have the new name
     * - If newName not provided: Target person keeps their existing name
     * 
     * CUSTOMER PROGRAMS:
     * - Specify customerProgramCodes to merge specific programs
     * - If not specified, programs are not automatically merged
     * 
     * USE CASES:
     * - Consolidating duplicate person records
     * - Merging customer profiles after identity verification
     * - Combining accounts for family members
     * - Data cleanup operations
     * 
     * WARNINGS:
     * - This operation cannot be undone
     * - Source person (deletePersonKey) will be permanently deleted
     * - All bookings will be reassigned to target person
     * - Ensure proper verification before merging
     * 
     * @param string $personKey Target person key (receives all merged data)
     * @param array $mergeData Merge request data:
     *   - deletePersonKey (string, required): Source person to be deleted (min 1 char)
     *   - newName (array, optional): New name for merged person
     *     - first (string, optional): First name (max 32 chars)
     *     - middle (string, optional): Middle name (max 32 chars)
     *     - last (string, optional): Last name (max 32 chars)
     *     - title (string, optional): Title (max 6 chars)
     *     - suffix (string, optional): Suffix (max 6 chars)
     *   - customerProgramCodes (array, optional): Program codes to merge
     * @return array Merge confirmation with updated person data
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function mergePersons(string $personKey, array $mergeData): array;
}
