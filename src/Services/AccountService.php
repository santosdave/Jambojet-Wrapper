<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\AccountInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Account Service for JamboJet NSK API
 * 
 * Handles account management operations including password management, account creation,
 * account collections, transactions, and status management
 * Base endpoints: /api/nsk/v{version}/account, /api/nsk/v{version}/persons/{personKey}/account,
 *                 /api/nsk/v{version}/bookings/{recordLocator}/account
 * 
 * Supported endpoints:
 * - POST /api/nsk/v1/account/password/change - Change account password (anonymous user)
 * - POST /api/nsk/v1/account/password/reset - Reset/forgot password
 * - GET /api/nsk/v1/persons/{personKey}/account - Get person account
 * - POST /api/nsk/v1/persons/{personKey}/account - Create person account
 * - PUT /api/nsk/v1/persons/{personKey}/account/status - Update person account status
 * - POST /api/nsk/v1/persons/{personKey}/account/collection - Create person account collection
 * - GET /api/nsk/v1/persons/{personKey}/account/collection/{collectionKey}/transactions - Get person transactions
 * - POST /api/nsk/v1/persons/{personKey}/account/collection/{collectionKey}/transactions - Create person transaction
 * - GET /api/nsk/v1/bookings/{recordLocator}/account - Get booking account
 * - POST /api/nsk/v1/bookings/{recordLocator}/account - Create booking account
 * - PUT /api/nsk/v1/bookings/{recordLocator}/account/status - Update booking account status
 * - POST /api/nsk/v1/bookings/{recordLocator}/account/collection - Create booking account collection
 * - GET /api/nsk/v1/bookings/{recordLocator}/account/transactions - Get all booking transactions
 * - GET /api/nsk/v1/bookings/{recordLocator}/account/collection/{collectionKey}/transactions - Get booking transactions
 * - POST /api/nsk/v1/bookings/{recordLocator}/account/collection/{collectionKey}/transactions - Create booking transaction
 * 
 * @package SantosDave\JamboJet\Services
 */
class AccountService implements AccountInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - Core Account Operations
    // =================================================================

    /**
     * Change account password
     * 
     * POST /api/nsk/v1/account/password/change
     * Changes the account password (for anonymous users with expired passwords)
     * Note: This endpoint is restricted to anonymous users only and remains anonymous after operation
     * For logged-in users, use the user service password change endpoint instead
     * 
     * @param array $passwordData Password change data (username, currentPassword, newPassword)
     * @return array Password change response
     * @throws JamboJetApiException
     */
    public function changePassword(array $credentials, string $newPassword): array
    {
        $requestData = [
            'credentials' => $credentials,
            'newPassword' => $newPassword
        ];

        $this->validateAccountChangePasswordRequest($requestData);

        try {
            return $this->post('api/nsk/v3/account/password/change', $requestData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Account password change (v3) failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Reset account password
     * 
     * POST /api/nsk/v1/account/password/reset
     * Invokes the forgot password reset for a specific account
     * 
     * @param array $resetData Password reset data (username, email, etc.)
     * @return array Password reset response
     * @throws JamboJetApiException
     */
    public function resetPassword(array $resetData): array
    {
        $this->validateAccountForgotPasswordRequest($resetData);

        try {
            return $this->post('api/nsk/v1/account/password/reset', $resetData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Password reset failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Create account
     * 
     * POST /api/nsk/v1/persons/{personKey}/account
     * Creates account for a specific person
     * 
     * @param array $accountData Account creation data
     * @return array Account creation response
     * @throws JamboJetApiException
     */
    public function createAccount(array $accountData): array
    {
        $this->validateCreateAccountRequest($accountData);

        if (!isset($accountData['personKey'])) {
            throw new JamboJetValidationException('personKey is required in account data');
        }

        $personKey = $accountData['personKey'];
        unset($accountData['personKey']); // Remove from body data

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/account", $accountData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create account collection for booking
     * 
     * POST /api/nsk/v1/bookings/{recordLocator}/account/collection
     * Creates account collection and transaction for a booking
     * 
     * @param string $recordLocator The booking record locator
     * @param array $collectionData Account collection data
     * @return array Account collection creation response
     * @throws JamboJetApiException
     */
    public function createAccountCollection(string $recordLocator, array $collectionData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAccountCollectionRequest($collectionData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/account/collection", $collectionData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create account collection: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PERSON ACCOUNT MANAGEMENT
    // =================================================================

    /**
     * Get person account
     * 
     * GET /api/nsk/v1/persons/{personKey}/account
     * Retrieves account information for a specific person
     * 
     * @param string $personKey Person identifier key
     * @return array Person account information
     * @throws JamboJetApiException
     */
    public function getPersonAccount(string $personKey): array
    {
        $this->validatePersonKey($personKey);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/account");
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
     * Creates a new account for a person
     * 
     * @param string $personKey Person identifier key
     * @param array $accountData Account creation data
     * @return array Account creation response
     * @throws JamboJetApiException
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
     * Update person account status
     * 
     * PUT /api/nsk/v1/persons/{personKey}/account/status
     * Updates the status of a person's account
     * 
     * @param string $personKey Person identifier key
     * @param array $statusData Account status data
     * @return array Status update response
     * @throws JamboJetApiException
     */
    public function updatePersonAccountStatus(string $personKey, array $statusData): array
    {
        $this->validatePersonKey($personKey);
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
     * Create person account collection
     * 
     * POST /api/nsk/v1/persons/{personKey}/account/collection
     * Creates account collection and transaction for a person
     * 
     * @param string $personKey The unique person key
     * @param array $collectionData Account collection data
     * @return array Account collection creation response
     * @throws JamboJetApiException
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
     * Get person account collection transactions
     * 
     * GET /api/nsk/v1/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * Retrieves transactions for a person's account collection
     * 
     * @param string $personKey The unique person key
     * @param string $accountCollectionKey The account collection key
     * @param array $params Transaction search parameters (StartTime, EndTime, etc.)
     * @return array Collection transactions
     * @throws JamboJetApiException
     */
    public function getPersonAccountTransactions(string $personKey, string $accountCollectionKey, array $params): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAccountCollectionKey($accountCollectionKey);
        $this->validateTransactionSearchParams($params);

        try {
            return $this->get("api/nsk/v1/persons/{$personKey}/account/collection/{$accountCollectionKey}/transactions", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get person account transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create person account transaction
     * 
     * POST /api/nsk/v1/persons/{personKey}/account/collection/{accountCollectionKey}/transactions
     * Creates a transaction for a person's account collection
     * 
     * @param string $personKey The unique person key
     * @param string $accountCollectionKey The account collection key
     * @param array $transactionData Transaction data
     * @return array Transaction creation response
     * @throws JamboJetApiException
     */
    public function createPersonAccountTransaction(string $personKey, string $accountCollectionKey, array $transactionData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateAccountCollectionKey($accountCollectionKey);
        $this->validateTransactionRequest($transactionData);

        try {
            return $this->post("api/nsk/v1/persons/{$personKey}/account/collection/{$accountCollectionKey}/transactions", $transactionData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person account transaction: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // BOOKING ACCOUNT MANAGEMENT
    // =================================================================

    /**
     * Get booking account
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/account
     * Retrieves account and collections for a booking
     * 
     * @param string $recordLocator The booking record locator
     * @return array Booking account information
     * @throws JamboJetApiException
     */
    public function getBookingAccount(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/account");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create booking account
     * 
     * POST /api/nsk/v1/bookings/{recordLocator}/account
     * Creates account for a booking
     * 
     * @param string $recordLocator The booking record locator
     * @param array $accountData Account creation data
     * @return array Account creation response
     * @throws JamboJetApiException
     */
    public function createBookingAccount(string $recordLocator, array $accountData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateCreateAccountRequest($accountData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/account", $accountData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create booking account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update booking account status
     * 
     * PUT /api/nsk/v1/bookings/{recordLocator}/account/status
     * Updates the status of a booking's account
     * 
     * @param string $recordLocator Booking record locator
     * @param array $statusData Account status data
     * @return array Status update response
     * @throws JamboJetApiException
     */
    public function updateBookingAccountStatus(string $recordLocator, array $statusData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAccountStatusUpdate($statusData);

        try {
            return $this->put("api/nsk/v1/bookings/{$recordLocator}/account/status", $statusData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update booking account status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create booking account collection
     * 
     * POST /api/nsk/v1/bookings/{recordLocator}/account/collection
     * Creates a new account collection for a booking
     * 
     * @param string $recordLocator Booking record locator
     * @param array $collectionData Collection creation data
     * @return array Collection creation response
     * @throws JamboJetApiException
     */
    public function createBookingAccountCollection(string $recordLocator, array $collectionData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAccountCollectionRequest($collectionData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/account/collection", $collectionData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create booking account collection: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all booking account transactions
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/account/transactions
     * Retrieves all transactions for all collections for a booking
     * 
     * @param string $recordLocator The booking record locator
     * @param array $params Transaction search parameters
     * @return array All booking transactions
     * @throws JamboJetApiException
     */
    public function getAllBookingAccountTransactions(string $recordLocator, array $params = []): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/account/transactions", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all booking account transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all booking account transactions
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/account/transactions
     * Retrieves all transactions for a booking's account
     * 
     * @param string $recordLocator Booking record locator
     * @param array $criteria Optional transaction filter criteria
     * @return array Transaction list
     * @throws JamboJetApiException
     */
    public function getBookingAccountTransactions(string $recordLocator, array $criteria = []): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateTransactionSearchCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/account/transactions", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking account transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create booking account transaction
     * 
     * POST /api/nsk/v1/bookings/{recordLocator}/account/collection/{collectionKey}/transactions
     * Creates a new transaction in a booking's account collection
     * 
     * @param string $recordLocator Booking record locator
     * @param string $collectionKey Collection identifier key
     * @param array $transactionData Transaction data
     * @return array Transaction creation response
     * @throws JamboJetApiException
     */
    public function createBookingAccountTransaction(string $recordLocator, string $collectionKey, array $transactionData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAccountCollectionKey($collectionKey);
        $this->validateTransactionRequest($transactionData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/account/collection/{$collectionKey}/transactions", $transactionData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create booking account transaction: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking account collection transactions
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/account/collection/{collectionKey}/transactions
     * Retrieves transactions for a specific collection in a booking's account
     * 
     * @param string $recordLocator Booking record locator
     * @param string $collectionKey Collection identifier key
     * @param array $criteria Optional transaction filter criteria
     * @return array Transaction list
     * @throws JamboJetApiException
     */
    public function getBookingAccountCollectionTransactions(string $recordLocator, string $collectionKey, array $criteria = []): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAccountCollectionKey($collectionKey);
        $this->validateTransactionSearchCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/account/collection/{$collectionKey}/transactions", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking account collection transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // HELPER METHODS FOR SPECIFIC ACCOUNT OPERATIONS
    // =================================================================

    /**
     * Get account transaction codes (reference data)
     * 
     * GET /api/nsk/v1/resources/accountTransactionCodes
     * Retrieves available account transaction codes for collections
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Available transaction codes
     * @throws JamboJetApiException
     */
    public function getAccountTransactionCodes(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v1/resources/accountTransactionCodes', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get account transaction codes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create account collection with auto-generated transaction
     * 
     * Helper method that combines collection creation with automatic transaction generation
     * 
     * @param string $recordLocator The booking record locator
     * @param array $collectionData Collection and transaction data
     * @return array Combined creation response
     * @throws JamboJetApiException
     */
    public function createAccountCollectionWithTransaction(string $recordLocator, array $collectionData): array
    {
        // This method leverages the API's automatic transaction creation
        // when creating a new account collection
        return $this->createAccountCollection($recordLocator, $collectionData);
    }

    /**
     * Get account balance summary
     * 
     * Helper method to calculate account balance from transactions
     * 
     * @param string $recordLocator The booking record locator
     * @return array Account balance summary
     * @throws JamboJetApiException
     */
    public function getAccountBalanceSummary(string $recordLocator): array
    {
        try {
            // Get account with collections
            $account = $this->getBookingAccount($recordLocator);

            // Get all transactions
            $transactions = $this->getAllBookingAccountTransactions($recordLocator);

            // Calculate balance (this would need to be implemented based on transaction structure)
            return [
                'success' => true,
                'data' => [
                    'account' => $account,
                    'transactions' => $transactions,
                    'balance_summary' => $this->calculateBalance($transactions)
                ],
                'message' => 'Account balance summary retrieved successfully'
            ];
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get account balance summary: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate record locator
     */
    private function validateRecordLocator(string $recordLocator): void
    {
        if (empty($recordLocator)) {
            throw new JamboJetValidationException('Record locator is required');
        }
    }

    /**
     * Validate transaction search parameters
     */
    private function validateTransactionSearchParams(array $params): void
    {
        if (!isset($params['StartTime'])) {
            throw new JamboJetValidationException('StartTime is required for transaction search');
        }

        if (isset($params['PageSize'])) {
            $pageSize = $params['PageSize'];
            if ($pageSize < 10 || $pageSize > 5000) {
                throw new JamboJetValidationException('PageSize must be between 10 and 5000');
            }
        }
    }

    // =================================================================
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Calculate account balance from transactions
     * 
     * @param array $transactions Transaction data
     * @return array Balance calculation
     */
    private function calculateBalance(array $transactions): array
    {
        // This would implement actual balance calculation logic
        // based on the transaction structure from the API

        return [
            'total_credits' => 0,
            'total_debits' => 0,
            'current_balance' => 0,
            'transaction_count' => count($transactions['data'] ?? [])
        ];
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate account password change v3 request
     * 
     * @param array $data Password change data with credentials
     * @throws JamboJetValidationException
     */
    private function validateAccountChangePasswordRequest(array $data): void
    {
        // Validate top level structure
        $this->validateRequired($data, ['credentials', 'newPassword']);

        $credentials = $data['credentials'];

        // Validate either username or alternateIdentifier is provided
        if (empty($credentials['username']) && empty($credentials['alternateIdentifier'])) {
            throw new JamboJetValidationException(
                'Either username or alternateIdentifier must be provided',
                400
            );
        }

        // Validate required credential fields
        $this->validateRequired($credentials, ['password', 'domain', 'location', 'loginRole']);

        // Validate username format if provided
        if (!empty($credentials['username'])) {
            $this->validateFormats(['username' => $credentials['username']], ['username' => 'email']);
        }

        // Validate password is not empty
        if (empty(trim($credentials['password']))) {
            throw new JamboJetValidationException(
                'Current password cannot be empty',
                400
            );
        }

        // Validate new password strength
        $this->validatePasswordStrength($data['newPassword']);

        // Ensure new password is different from current
        if ($credentials['password'] === $data['newPassword']) {
            throw new JamboJetValidationException(
                'New password must be different from current password',
                400
            );
        }

        // Validate domain, location, and loginRole lengths
        $this->validateStringLengths($credentials, [
            'domain' => ['max' => 10],
            'location' => ['max' => 10],
            'loginRole' => ['max' => 10]
        ]);
    }

    /**
     * Validate account forgot password request
     * 
     * @param array $data Password reset data
     * @throws JamboJetValidationException
     */
    private function validateAccountForgotPasswordRequest(array $data): void
    {
        $this->validateRequired($data, ['username']);

        // Validate username (typically email)
        $this->validateFormats($data, ['username' => 'email']);

        // Validate reset method if provided
        if (isset($data['resetMethod'])) {
            $validMethods = ['Email', 'SMS', 'SecurityQuestions'];
            if (!in_array($data['resetMethod'], $validMethods)) {
                throw new JamboJetValidationException(
                    'Invalid reset method. Expected one of: ' . implode(', ', $validMethods),
                    400
                );
            }
        }

        // Validate security question answers if provided
        if (isset($data['securityAnswers'])) {
            $this->validateSecurityAnswers($data['securityAnswers']);
        }

        // Validate phone number if SMS reset method
        if (isset($data['resetMethod']) && $data['resetMethod'] === 'SMS') {
            if (!isset($data['phoneNumber'])) {
                throw new JamboJetValidationException(
                    'Phone number is required for SMS reset method',
                    400
                );
            }
            $this->validateFormats($data, ['phoneNumber' => 'phone']);
        }

        // Validate domain if provided
        if (isset($data['domain'])) {
            $this->validateStringLengths($data, ['domain' => ['max' => 50]]);
        }

        // Validate locale for localized reset emails
        if (isset($data['locale'])) {
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $data['locale'])) {
                throw new JamboJetValidationException(
                    'Invalid locale format. Expected format: en or en-US',
                    400
                );
            }
        }
    }

    /**
     * Validate account creation request
     * 
     * @param array $data Account creation data
     * @throws JamboJetValidationException
     */
    private function validateCreateAccountRequest(array $data): void
    {
        // Validate account type if provided
        if (isset($data['accountType'])) {
            $this->validateAccountType($data['accountType']);
        }

        // Validate account settings if provided
        if (isset($data['settings'])) {
            $this->validateAccountSettings($data['settings']);
        }

        // Validate credit limit if provided
        if (isset($data['creditLimit'])) {
            $this->validateFormats($data, ['creditLimit' => 'non_negative_number']);

            // Reasonable credit limit validation
            $this->validateNumericRanges($data, ['creditLimit' => ['min' => 0, 'max' => 1000000]]);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate account notes if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 1000]]);
        }

        // Validate expiry date if provided
        if (isset($data['expiryDate'])) {
            $this->validateFormats($data, ['expiryDate' => 'date']);

            // Expiry date must be in the future
            $expiryDate = new \DateTime($data['expiryDate']);
            $now = new \DateTime();

            if ($expiryDate <= $now) {
                throw new JamboJetValidationException(
                    'Account expiry date must be in the future',
                    400
                );
            }
        }

        // Validate notification preferences if provided
        if (isset($data['notifications'])) {
            $this->validateAccountNotificationPreferences($data['notifications']);
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
        $this->validateRequired($data, ['status']);

        // Validate account status
        $this->validateAccountStatus($data['status']);

        // Validate reason if provided
        if (isset($data['reason'])) {
            $this->validateStringLengths($data, ['reason' => ['max' => 200]]);
        }

        // Validate reason code if provided
        if (isset($data['reasonCode'])) {
            $this->validateAccountStatusReasonCode($data['reasonCode']);
        }

        // Validate effective date if provided
        if (isset($data['effectiveDate'])) {
            $this->validateFormats($data, ['effectiveDate' => 'datetime']);
        }

        // Validate notes if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 500]]);
        }

        // Validate notification flag
        if (isset($data['sendNotification']) && !is_bool($data['sendNotification'])) {
            throw new JamboJetValidationException(
                'sendNotification must be a boolean value',
                400
            );
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
        $this->validateRequired($data, ['transactionCode']);

        // Validate transaction code
        $this->validateStringLengths($data, ['transactionCode' => ['max' => 20]]);

        // Validate collection type if provided
        if (isset($data['collectionType'])) {
            $this->validateCollectionType($data['collectionType']);
        }

        // Validate collection name if provided
        if (isset($data['collectionName'])) {
            $this->validateStringLengths($data, ['collectionName' => ['max' => 100]]);
        }

        // Validate description if provided
        if (isset($data['description'])) {
            $this->validateStringLengths($data, ['description' => ['max' => 500]]);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate activation date if provided
        if (isset($data['activationDate'])) {
            $this->validateFormats($data, ['activationDate' => 'datetime']);
        }

        // Validate expiry date if provided
        if (isset($data['expiryDate'])) {
            $this->validateFormats($data, ['expiryDate' => 'datetime']);

            // If both activation and expiry dates are provided, expiry must be after activation
            if (isset($data['activationDate'])) {
                $activationDate = new \DateTime($data['activationDate']);
                $expiryDate = new \DateTime($data['expiryDate']);

                if ($expiryDate <= $activationDate) {
                    throw new JamboJetValidationException(
                        'Collection expiry date must be after activation date',
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate transaction request
     * 
     * @param array $data Transaction data
     * @throws JamboJetValidationException
     */
    private function validateTransactionRequest(array $data): void
    {
        $this->validateRequired($data, ['amount', 'transactionType']);

        // Validate amount
        $this->validateFormats($data, ['amount' => 'positive_number']);

        // Validate transaction type
        $this->validateTransactionType($data['transactionType']);

        // Validate transaction reference if provided
        if (isset($data['reference'])) {
            $this->validateStringLengths($data, ['reference' => ['max' => 100]]);
        }

        // Validate description if provided
        if (isset($data['description'])) {
            $this->validateStringLengths($data, ['description' => ['max' => 255]]);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate transaction date if provided
        if (isset($data['transactionDate'])) {
            $this->validateFormats($data, ['transactionDate' => 'datetime']);
        }

        // Validate external reference if provided
        if (isset($data['externalReference'])) {
            $this->validateStringLengths($data, ['externalReference' => ['max' => 100]]);
        }

        // Validate payment method if provided
        if (isset($data['paymentMethod'])) {
            $this->validatePaymentMethod($data['paymentMethod']);
        }

        // Validate merchant information if provided
        if (isset($data['merchantInfo'])) {
            $this->validateMerchantInfo($data['merchantInfo']);
        }
    }

    /**
     * Validate transaction search criteria
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validateTransactionSearchCriteria(array $criteria): void
    {
        // Validate date range if provided
        if (isset($criteria['startDate'])) {
            $this->validateFormats($criteria, ['startDate' => 'date']);
        }

        if (isset($criteria['endDate'])) {
            $this->validateFormats($criteria, ['endDate' => 'date']);

            // If both dates provided, end date must be after start date
            if (isset($criteria['startDate'])) {
                $startDate = new \DateTime($criteria['startDate']);
                $endDate = new \DateTime($criteria['endDate']);

                if ($endDate < $startDate) {
                    throw new JamboJetValidationException(
                        'End date must be after start date',
                        400
                    );
                }
            }
        }

        // Validate amount range if provided
        if (isset($criteria['minAmount'])) {
            $this->validateFormats($criteria, ['minAmount' => 'non_negative_number']);
        }

        if (isset($criteria['maxAmount'])) {
            $this->validateFormats($criteria, ['maxAmount' => 'positive_number']);

            // If both amounts provided, max must be greater than min
            if (isset($criteria['minAmount']) && $criteria['maxAmount'] < $criteria['minAmount']) {
                throw new JamboJetValidationException(
                    'Maximum amount must be greater than minimum amount',
                    400
                );
            }
        }

        // Validate transaction type filter if provided
        if (isset($criteria['transactionType'])) {
            $this->validateTransactionType($criteria['transactionType']);
        }

        // Validate status filter if provided
        if (isset($criteria['status'])) {
            $this->validateTransactionStatus($criteria['status']);
        }

        // Validate pagination parameters
        if (isset($criteria['startIndex'])) {
            $this->validateNumericRanges($criteria, ['startIndex' => ['min' => 0]]);
        }

        if (isset($criteria['itemCount'])) {
            $this->validateNumericRanges($criteria, ['itemCount' => ['min' => 1, 'max' => 500]]);
        }

        // Validate sort parameters
        if (isset($criteria['sortBy'])) {
            $validSortFields = ['transactionDate', 'amount', 'transactionType', 'status', 'reference'];
            if (!in_array($criteria['sortBy'], $validSortFields)) {
                throw new JamboJetValidationException(
                    'Invalid sort field. Expected one of: ' . implode(', ', $validSortFields),
                    400
                );
            }
        }

        if (isset($criteria['sortOrder'])) {
            $validOrders = ['asc', 'desc'];
            if (!in_array(strtolower($criteria['sortOrder']), $validOrders)) {
                throw new JamboJetValidationException(
                    'Invalid sort order. Expected: asc or desc',
                    400
                );
            }
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS
    // =================================================================

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @throws JamboJetValidationException
     */
    private function validatePasswordStrength(string $password): void
    {
        // Basic length requirements
        if (strlen($password) < 8) {
            throw new JamboJetValidationException(
                'Password must be at least 8 characters long',
                400
            );
        }

        if (strlen($password) > 128) {
            throw new JamboJetValidationException(
                'Password cannot exceed 128 characters',
                400
            );
        }

        // Complexity requirements
        $hasLower = preg_match('/[a-z]/', $password);
        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasNumber = preg_match('/\d/', $password);
        $hasSpecial = preg_match('/[^a-zA-Z0-9]/', $password);

        $complexityCount = $hasLower + $hasUpper + $hasNumber + $hasSpecial;

        if ($complexityCount < 3) {
            throw new JamboJetValidationException(
                'Password must contain at least 3 of the following: lowercase letter, uppercase letter, number, special character',
                400
            );
        }

        // Check for common weak passwords
        $weakPasswords = [
            'password',
            '12345678',
            'qwerty123',
            'admin123',
            'welcome123',
            'password123',
            '123456789',
            'letmein123'
        ];

        if (in_array(strtolower($password), $weakPasswords)) {
            throw new JamboJetValidationException(
                'Password is too common and easily guessable',
                400
            );
        }
    }

    /**
     * Validate security answers
     * 
     * @param array $answers Security question answers
     * @throws JamboJetValidationException
     */
    private function validateSecurityAnswers(array $answers): void
    {
        if (empty($answers)) {
            throw new JamboJetValidationException(
                'At least one security answer is required',
                400
            );
        }

        foreach ($answers as $index => $answer) {
            if (!isset($answer['questionId']) || !isset($answer['answer'])) {
                throw new JamboJetValidationException(
                    "Security answer at index {$index} must include questionId and answer",
                    400
                );
            }

            // Validate question ID
            if (!is_int($answer['questionId']) || $answer['questionId'] < 1) {
                throw new JamboJetValidationException(
                    "Invalid question ID at security answer index {$index}",
                    400
                );
            }

            // Validate answer
            $this->validateStringLengths(['answer' => $answer['answer']], ['answer' => ['min' => 3, 'max' => 100]]);
        }
    }

    /**
     * Validate account type
     * 
     * @param string $accountType Account type
     * @throws JamboJetValidationException
     */
    private function validateAccountType(string $accountType): void
    {
        $validTypes = ['Personal', 'Business', 'Corporate', 'Agent', 'Credit', 'Prepaid', 'Loyalty'];

        if (!in_array($accountType, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid account type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate account settings
     * 
     * @param array $settings Account settings
     * @throws JamboJetValidationException
     */
    private function validateAccountSettings(array $settings): void
    {
        // Validate boolean settings
        $booleanSettings = [
            'autoPayEnabled',
            'notificationsEnabled',
            'overdraftProtection',
            'internationalTransactions',
            'onlineAccess',
            'mobileAccess'
        ];

        foreach ($booleanSettings as $setting) {
            if (isset($settings[$setting]) && !is_bool($settings[$setting])) {
                throw new JamboJetValidationException(
                    "{$setting} must be a boolean value",
                    400
                );
            }
        }

        // Validate transaction limits
        if (isset($settings['dailyLimit'])) {
            $this->validateFormats(['limit' => $settings['dailyLimit']], ['limit' => 'positive_number']);
        }

        if (isset($settings['monthlyLimit'])) {
            $this->validateFormats(['limit' => $settings['monthlyLimit']], ['limit' => 'positive_number']);
        }

        // Validate preferred language
        if (isset($settings['preferredLanguage'])) {
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $settings['preferredLanguage'])) {
                throw new JamboJetValidationException(
                    'Invalid preferred language format. Expected format: en or en-US',
                    400
                );
            }
        }

        // Validate time zone
        if (isset($settings['timeZone'])) {
            $validTimeZones = timezone_identifiers_list();
            if (!in_array($settings['timeZone'], $validTimeZones)) {
                throw new JamboJetValidationException(
                    'Invalid time zone',
                    400
                );
            }
        }
    }

    /**
     * Validate account status
     * 
     * @param string $status Account status
     * @throws JamboJetValidationException
     */
    private function validateAccountStatus(string $status): void
    {
        $validStatuses = ['Active', 'Inactive', 'Suspended', 'Closed', 'Pending', 'Frozen', 'Restricted'];

        if (!in_array($status, $validStatuses)) {
            throw new JamboJetValidationException(
                'Invalid account status. Expected one of: ' . implode(', ', $validStatuses),
                400
            );
        }
    }

    /**
     * Validate account status reason code
     * 
     * @param string $reasonCode Reason code
     * @throws JamboJetValidationException
     */
    private function validateAccountStatusReasonCode(string $reasonCode): void
    {
        $validReasonCodes = [
            'CustomerRequest',
            'Fraud',
            'NonPayment',
            'Violation',
            'Maintenance',
            'Regulatory',
            'Security',
            'Closure',
            'Migration',
            'Other'
        ];

        if (!in_array($reasonCode, $validReasonCodes)) {
            throw new JamboJetValidationException(
                'Invalid status reason code. Expected one of: ' . implode(', ', $validReasonCodes),
                400
            );
        }
    }

    /**
     * Validate collection type
     * 
     * @param string $collectionType Collection type
     * @throws JamboJetValidationException
     */
    private function validateCollectionType(string $collectionType): void
    {
        $validTypes = ['Credit', 'Debit', 'Loyalty', 'Voucher', 'Refund', 'Penalty', 'Fee', 'Deposit'];

        if (!in_array($collectionType, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid collection type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate transaction type
     * 
     * @param string $transactionType Transaction type
     * @throws JamboJetValidationException
     */
    private function validateTransactionType(string $transactionType): void
    {
        $validTypes = [
            'Credit',
            'Debit',
            'Transfer',
            'Payment',
            'Refund',
            'Fee',
            'Interest',
            'Penalty',
            'Adjustment',
            'Reversal',
            'Chargeback',
            'Deposit',
            'Withdrawal'
        ];

        if (!in_array($transactionType, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid transaction type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate transaction status
     * 
     * @param string $status Transaction status
     * @throws JamboJetValidationException
     */
    private function validateTransactionStatus(string $status): void
    {
        $validStatuses = ['Pending', 'Completed', 'Failed', 'Cancelled', 'Reversed', 'Disputed'];

        if (!in_array($status, $validStatuses)) {
            throw new JamboJetValidationException(
                'Invalid transaction status. Expected one of: ' . implode(', ', $validStatuses),
                400
            );
        }
    }

    /**
     * Validate payment method
     * 
     * @param string $paymentMethod Payment method
     * @throws JamboJetValidationException
     */
    private function validatePaymentMethod(string $paymentMethod): void
    {
        $validMethods = [
            'CreditCard',
            'DebitCard',
            'BankTransfer',
            'Cash',
            'Check',
            'MobileMoney',
            'PayPal',
            'Cryptocurrency',
            'Voucher',
            'Loyalty',
            'Credit'
        ];

        if (!in_array($paymentMethod, $validMethods)) {
            throw new JamboJetValidationException(
                'Invalid payment method. Expected one of: ' . implode(', ', $validMethods),
                400
            );
        }
    }

    /**
     * Validate merchant information
     * 
     * @param array $merchantInfo Merchant information
     * @throws JamboJetValidationException
     */
    private function validateMerchantInfo(array $merchantInfo): void
    {
        if (isset($merchantInfo['merchantId'])) {
            $this->validateStringLengths($merchantInfo, ['merchantId' => ['max' => 50]]);
        }

        if (isset($merchantInfo['merchantName'])) {
            $this->validateStringLengths($merchantInfo, ['merchantName' => ['max' => 100]]);
        }

        if (isset($merchantInfo['terminalId'])) {
            $this->validateStringLengths($merchantInfo, ['terminalId' => ['max' => 20]]);
        }
    }

    /**
     * Validate account notification preferences
     * 
     * @param array $notifications Notification preferences
     * @throws JamboJetValidationException
     */
    private function validateAccountNotificationPreferences(array $notifications): void
    {
        $booleanFields = [
            'emailNotifications',
            'smsNotifications',
            'pushNotifications',
            'transactionAlerts',
            'lowBalanceAlerts',
            'securityAlerts',
            'promotionalMessages',
            'accountUpdates'
        ];

        foreach ($booleanFields as $field) {
            if (isset($notifications[$field]) && !is_bool($notifications[$field])) {
                throw new JamboJetValidationException(
                    "{$field} must be a boolean value",
                    400
                );
            }
        }

        // Validate notification frequency
        if (isset($notifications['frequency'])) {
            $validFrequencies = ['Immediate', 'Daily', 'Weekly', 'Monthly', 'Never'];
            if (!in_array($notifications['frequency'], $validFrequencies)) {
                throw new JamboJetValidationException(
                    'Invalid notification frequency. Expected one of: ' . implode(', ', $validFrequencies),
                    400
                );
            }
        }

        // Validate preferred contact method
        if (isset($notifications['preferredMethod'])) {
            $validMethods = ['Email', 'SMS', 'Push', 'Phone', 'None'];
            if (!in_array($notifications['preferredMethod'], $validMethods)) {
                throw new JamboJetValidationException(
                    'Invalid preferred notification method. Expected one of: ' . implode(', ', $validMethods),
                    400
                );
            }
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
        if (empty(trim($personKey))) {
            throw new JamboJetValidationException(
                'Person key cannot be empty',
                400
            );
        }

        // Person keys are typically alphanumeric with minimum length
        if (strlen($personKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid person key format',
                400
            );
        }
    }

    /**
     * Validate account collection key
     * 
     * @param string $collectionKey Collection key
     * @throws JamboJetValidationException
     */
    private function validateAccountCollectionKey(string $collectionKey): void
    {
        if (empty(trim($collectionKey))) {
            throw new JamboJetValidationException(
                'Account collection key cannot be empty',
                400
            );
        }

        // Collection keys are typically alphanumeric with minimum length
        if (strlen($collectionKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid account collection key format',
                400
            );
        }
    }
}
