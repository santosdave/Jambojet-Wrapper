<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\UserInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * User Management Service for JamboJet NSK API
 * 
 * Handles all user management operations including CRUD, authentication, roles, and profile management
 * Base endpoints: /api/nsk/v{version}/user, /api/nsk/v{version}/users, /api/nsk/v{version}/persons
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/user - Get current user information
 * - PUT /api/nsk/v1/user - Update current user
 * - PATCH /api/nsk/v1/user - Patch current user
 * - POST /api/nsk/v1/user - Create user account (customer)
 * - POST /api/nsk/v2/user - Create user account (customer v2)
 * - GET /api/nsk/v1/users - Get users (agent function)
 * - POST /api/nsk/v1/users - Create multiple users (agent function)
 * - POST /api/nsk/v2/users - Create multiple users (agent function v2)
 * - GET /api/nsk/v1/users/{userKey} - Get specific user
 * - PUT /api/nsk/v1/users/{userKey} - Update specific user
 * - DELETE /api/nsk/v1/users/{userKey} - Delete specific user
 * - POST /api/nsk/v1/user/password/change - Change current user password
 * - POST /api/nsk/v1/users/{userKey}/password/reset - Reset user password
 * - GET /api/nsk/v1/user/impersonate - Get current impersonation state
 * - POST /api/nsk/v1/user/impersonate - Impersonate role
 * - DELETE /api/nsk/v1/user/impersonate - Reset impersonation
 * - Various role management endpoints
 * - Person management endpoints
 * 
 * @package SantosDave\JamboJet\Services
 */
class UserService implements UserInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - Current User Operations
    // =================================================================

    /**
     * Get current user information
     * 
     * GET /api/nsk/v1/user
     * Retrieves the current logged-in user's information
     * 
     * @return array Current user information
     * @throws JamboJetApiException
     */
    public function getCurrentUser(): array
    {
        try {
            return $this->get('api/nsk/v1/user');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get current user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update current user
     * 
     * PUT /api/nsk/v1/user
     * Updates the current logged-in user's information
     * 
     * @param array $userData User update data
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateCurrentUser(array $userData): array
    {
        $this->validateUserRequest($userData);

        try {
            return $this->put('api/nsk/v1/user', $userData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update current user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch current user information
     * 
     * PATCH /api/nsk/v1/user
     * Partially updates the current user's profile
     * 
     * @param array $userData Partial user data to update
     * @return array Updated user information
     * @throws JamboJetApiException
     */
    public function patchCurrentUser(array $userData): array
    {
        $this->validateUserPatchRequest($userData);

        try {
            return $this->patch('api/nsk/v1/user', $userData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch current user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create user account (Latest Version - v2)
     * 
     * POST /api/nsk/v2/user
     * Creates a new customer user account
     * 
     * @param array $userData User account creation data
     * @return array Created user information
     * @throws JamboJetApiException
     */
    public function createUser(array $userData): array
    {
        $this->validateUserCreateRequest($userData);

        try {
            return $this->post('api/nsk/v1/user', $userData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create multiple users (agent function)
     * 
     * POST /api/nsk/v1/users (Legacy)
     * POST /api/nsk/v2/users (Recommended)
     * Creates multiple users - agent function with permissions
     * 
     * @param array $usersData Array of user creation data
     * @param bool $useV2 Whether to use v2 endpoint
     * @return array Created users response
     * @throws JamboJetApiException
     */
    public function createUsers(array $usersData, bool $useV2 = true): array
    {
        if ($useV2) {
            $this->validateUsersCreateRequestV2($usersData);
            $endpoint = 'api/nsk/v2/users';
        } else {
            $this->validateUsersCreateRequest($usersData);
            $endpoint = 'api/nsk/v1/users';
        }

        try {
            return $this->post($endpoint, $usersData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create users: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get users list (Agent function)
     * 
     * GET /api/nsk/v1/users
     * Retrieves list of users (requires agent privileges)
     * 
     * @param array $criteria Search criteria for filtering users
     * @return array Users list
     * @throws JamboJetApiException
     */
    public function getUsers(array $criteria = []): array
    {
        $this->validateUsersSearchCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/users', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get users: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific user (agent function)
     * 
     * GET /api/nsk/v1/users/{userKey}
     * Retrieves a specific user by their unique key
     * 
     * @param string $userKey The unique user key
     * @return array User information
     * @throws JamboJetApiException
     */
    public function getUserByKey(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user by key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update specific user
     * 
     * PUT /api/nsk/v1/users/{userKey}
     * Updates a specific user's information (requires admin privileges)
     * 
     * @param string $userKey User identifier key
     * @param array $userData User data to update
     * @return array Updated user information
     * @throws JamboJetApiException
     */
    public function updateUser(string $userKey, array $userData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserUpdateRequest($userData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}", $userData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Change current user password
     * 
     * POST /api/nsk/v1/user/password/change
     * Changes the password for the current logged-in user
     * 
     * @param array $passwordData Password change data
     * @return array Password change response
     * @throws JamboJetApiException
     */
    public function changePassword(array $passwordData): array
    {
        $this->validatePasswordChangeRequest($passwordData);

        try {
            return $this->post('api/nsk/v1/user/password/change', $passwordData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to change password: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // EXTENDED USER MANAGEMENT OPERATIONS
    // =================================================================

    /**
     * Delete user (agent function)
     * 
     * DELETE /api/nsk/v1/users/{userKey}
     * Deletes/terminates a specific user account
     * 
     * @param string $userKey The unique user key
     * @return array Delete response
     * @throws JamboJetApiException
     */
    public function deleteUser(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create multiple users (Bulk operation)
     * 
     * POST /api/nsk/v2/users
     * Creates multiple user accounts in a single operation
     * 
     * @param array $usersData Array of user data for creation
     * @return array Bulk creation response
     * @throws JamboJetApiException
     */
    public function createMultipleUsers(array $usersData): array
    {
        $this->validateBulkUserCreateRequest($usersData);

        try {
            return $this->post('api/nsk/v2/users', ['users' => $usersData]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create multiple users: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch user (agent function)
     * 
     * PATCH /api/nsk/v1/users/{userKey}
     * Patches a specific user's information with delta changes
     * 
     * @param string $userKey The unique user key
     * @param array $patchData User patch data
     * @return array Patch response
     * @throws JamboJetApiException
     */
    public function patchUser(string $userKey, array $patchData): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Reset user password (Admin function)
     * 
     * POST /api/nsk/v1/users/{userKey}/password/reset
     * Resets password for a specific user
     * 
     * @param string $userKey User identifier key
     * @param array $resetData Password reset data
     * @return array Password reset response
     * @throws JamboJetApiException
     */
    public function resetUserPassword(string $userKey, array $resetData = []): array
    {
        $this->validateUserKey($userKey);
        $this->validatePasswordResetRequest($resetData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/password/reset", $resetData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to reset user password: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user by person key
     * 
     * GET /api/nsk/v1/users/byPerson/{personKey}
     * Retrieves a user by their associated person key
     * 
     * @param string $personKey The unique person key
     * @return array User information
     * @throws JamboJetApiException
     */
    public function getUserByPersonKey(string $personKey): array
    {
        if (empty($personKey)) {
            throw new JamboJetValidationException('Person key is required');
        }

        try {
            return $this->get("api/nsk/v1/users/byPerson/{$personKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user by person key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // USER ROLE MANAGEMENT
    // =================================================================

    /**
     * Get current user roles
     * 
     * GET /api/nsk/v1/user/roles
     * Gets all roles for the current logged-in user
     * 
     * @return array User roles
     * @throws JamboJetApiException
     */
    public function getCurrentUserRoles(): array
    {
        try {
            return $this->get('api/nsk/v1/user/roles');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get current user roles: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create role for current user
     * 
     * POST /api/nsk/v1/user/roles
     * Creates a new role for the current logged-in user
     * 
     * @param array $roleData Role creation data
     * @return array Role creation response
     * @throws JamboJetApiException
     */
    public function createCurrentUserRole(array $roleData): array
    {
        $this->validateUserRoleCreateRequest($roleData);

        try {
            return $this->post('api/nsk/v1/user/roles', $roleData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create current user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user roles (agent function)
     * 
     * GET /api/nsk/v1/users/{userKey}/roles
     * Gets all roles for a specific user
     * 
     * @param string $userKey The unique user key
     * @return array User roles
     * @throws JamboJetApiException
     */
    public function getUserRoles(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/roles");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user roles: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create user role (agent function)
     * 
     * POST /api/nsk/v1/users/{userKey}/roles
     * Creates a new role for a specific user
     * 
     * @param string $userKey The unique user key
     * @param array $roleData Role creation data
     * @return array Role creation response
     * @throws JamboJetApiException
     */
    public function createUserRole(string $userKey, array $roleData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleCreateRequest($roleData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/roles", $roleData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific user role
     * 
     * GET /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * Gets a specific role for a specific user
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array User role information
     * @throws JamboJetApiException
     */
    public function getSpecificUserRole(string $userKey, string $userRoleKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/roles/{$userRoleKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get specific user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update user role
     * 
     * PUT /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * Updates a specific role for a specific user
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $roleData Role update data
     * @return array Role update response
     * @throws JamboJetApiException
     */
    public function updateUserRole(string $userKey, string $userRoleKey, array $roleData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);
        $this->validateUserRoleEditRequest($roleData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/roles/{$userRoleKey}", $roleData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete user role
     * 
     * DELETE /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * Deletes a specific role for a specific user
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array Role deletion response
     * @throws JamboJetApiException
     */
    public function deleteUserRole(string $userKey, string $userRoleKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/roles/{$userRoleKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch user role
     * 
     * PATCH /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * Patches a specific role for a specific user
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $patchData Role patch data
     * @return array Role patch response
     * @throws JamboJetApiException
     */
    public function patchUserRole(string $userKey, string $userRoleKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/roles/{$userRoleKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // USER IMPERSONATION
    // =================================================================

    /**
     * Get current impersonation state
     * 
     * GET /api/nsk/v1/user/impersonate
     * Retrieves current user impersonation information
     * 
     * @return array Impersonation state
     * @throws JamboJetApiException
     */
    public function getImpersonationState(): array
    {
        try {
            return $this->get('api/nsk/v1/user/impersonate');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get impersonation state: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Start user impersonation
     * 
     * POST /api/nsk/v1/user/impersonate
     * Begin impersonating another user or role
     * 
     * @param array $impersonationData Impersonation details
     * @return array Impersonation response
     * @throws JamboJetApiException
     */
    public function startImpersonation(array $impersonationData): array
    {
        $this->validateImpersonationRequest($impersonationData);

        try {
            return $this->post('api/nsk/v1/user/impersonate', $impersonationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to start impersonation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Reset impersonation
     * 
     * DELETE /api/nsk/v1/user/impersonate
     * Resets the logged-in user's role to original state
     * 
     * @return array Reset response
     * @throws JamboJetApiException
     */
    public function resetImpersonation(): array
    {
        try {
            return $this->delete('api/nsk/v1/user/impersonate');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to reset impersonation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    // USER BOOKINGS & ADDITIONAL OPERATIONS
    // =================================================================

    /**
     * Get user bookings
     * 
     * GET /api/nsk/v1/user/bookings
     * Gets bookings for the current logged-in user
     * 
     * @param array $parameters Optional search parameters (StartDate, EndDate)
     * @return array User bookings
     * @throws JamboJetApiException
     */
    public function getUserBookings(array $parameters = []): array
    {
        try {
            return $this->get('api/nsk/v1/user/bookings', $parameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user bookings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user bookings by passenger
     * 
     * GET /api/nsk/v1/user/bookingsByPassenger
     * Gets bookings where the current user is a passenger
     * 
     * @param array $parameters Optional search parameters (StartDate, EndDate)
     * @return array User bookings by passenger
     * @throws JamboJetApiException
     */
    public function getUserBookingsByPassenger(array $parameters = []): array
    {
        try {
            return $this->get('api/nsk/v1/user/bookingsByPassenger', $parameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user bookings by passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user person information
     * 
     * GET /api/nsk/v1/users/{userKey}/person
     * Retrieves the specific user's person information
     * 
     * @param string $userKey The unique user key
     * @return array User person information
     * @throws JamboJetApiException
     */
    public function getUserPerson(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update user person information
     * 
     * PUT /api/nsk/v1/users/{userKey}/person
     * Updates the specific user's person record basic information
     * 
     * @param string $userKey The unique user key
     * @param array $personData Person update data
     * @return array Person update response
     * @throws JamboJetApiException
     */
    public function updateUserPerson(string $userKey, array $personData): array
    {
        $this->validateUserKey($userKey);
        $this->validatePersonEditRequest($personData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person", $personData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate user role key
     */
    private function validateUserRoleKey(string $userRoleKey): void
    {
        if (empty($userRoleKey)) {
            throw new JamboJetValidationException('User role key is required');
        }
    }

    /**
     * Validate user request (Final Fix)
     * 
     * @param array $data User request data
     * @throws JamboJetValidationException
     */
    private function validateUserRequest(array $data): void
    {
        // User request validation for general user operations
        // This method is used for various user operations, so validate common fields

        if (isset($data['username'])) {
            if (empty(trim($data['username']))) {
                throw new JamboJetValidationException('Username cannot be empty');
            }

            // Username validation (email or alphanumeric)
            if (
                !filter_var($data['username'], FILTER_VALIDATE_EMAIL) &&
                !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $data['username'])
            ) {
                throw new JamboJetValidationException(
                    'Username must be a valid email or alphanumeric string (3-50 characters)'
                );
            }
        }

        if (isset($data['password'])) {
            $this->validatePassword($data['password']);
        }

        if (isset($data['personKey'])) {
            $this->validatePersonKey($data['personKey']);
        }

        if (isset($data['organizationCode'])) {
            $this->validateOrganizationCode($data['organizationCode']);
        }

        if (isset($data['domainCode'])) {
            $this->validateDomainCode($data['domainCode']);
        }

        if (isset($data['locationGroupCode'])) {
            $this->validateLocationGroupCode($data['locationGroupCode']);
        }

        // Validate status if provided
        if (isset($data['status'])) {
            $validStatuses = ['Active', 'Pending', 'Suspended', 'Terminated'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid user status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        // Validate user type if provided
        if (isset($data['userType'])) {
            $validTypes = ['Customer', 'Agent', 'Administrator', 'System'];
            if (!in_array($data['userType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid user type. Expected one of: ' . implode(', ', $validTypes)
                );
            }
        }

        // Validate email notification preferences
        if (isset($data['emailNotifications']) && !is_bool($data['emailNotifications'])) {
            throw new JamboJetValidationException('Email notifications must be a boolean value');
        }

        // Validate language preference
        if (isset($data['languageCode'])) {
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $data['languageCode'])) {
                throw new JamboJetValidationException(
                    'Invalid language code format. Expected format: en or en-US'
                );
            }
        }

        // Validate timezone if provided
        if (isset($data['timezone'])) {
            try {
                new \DateTimeZone($data['timezone']);
            } catch (\Exception $e) {
                throw new JamboJetValidationException(
                    'Invalid timezone format'
                );
            }
        }
    }

    /**
     * Validate user customer create request
     */
    private function validateUserCustomerCreateRequest(array $data): void
    {
        $this->validateRequiredFields($data, ['personKey', 'username', 'password']);
    }

    /**
     * Validate user customer create request v2
     */
    private function validateUserCustomerCreateRequestV2(array $data): void
    {
        $this->validateRequiredFields($data, ['personKey', 'username', 'password']);
    }

    /**
     * Validate users create request
     */
    private function validateUsersCreateRequest(array $data): void
    {
        if (!is_array($data) || empty($data)) {
            throw new JamboJetValidationException('Users data array is required');
        }
    }

    /**
     * Validate users create request v2
     */
    private function validateUsersCreateRequestV2(array $data): void
    {
        if (!is_array($data) || empty($data)) {
            throw new JamboJetValidationException('Users data array is required');
        }
    }

    /**
     * Validate user edit request
     * 
     * @param array $data User edit data
     * @throws JamboJetValidationException
     */
    private function validateUserEditRequest(array $data): void
    {
        // User edit can have optional fields, but validate structure if provided
        if (isset($data['username'])) {
            if (empty(trim($data['username']))) {
                throw new JamboJetValidationException('Username cannot be empty');
            }

            // Username should be email format or alphanumeric
            if (
                !filter_var($data['username'], FILTER_VALIDATE_EMAIL) &&
                !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $data['username'])
            ) {
                throw new JamboJetValidationException(
                    'Username must be a valid email or alphanumeric string (3-50 characters)'
                );
            }
        }

        if (isset($data['password'])) {
            $this->validatePassword($data['password']);
        }

        if (isset($data['organizationCode'])) {
            $this->validateOrganizationCode($data['organizationCode']);
        }

        if (isset($data['locationGroupCode'])) {
            $this->validateLocationGroupCode($data['locationGroupCode']);
        }

        if (isset($data['domainCode'])) {
            $this->validateDomainCode($data['domainCode']);
        }

        // Validate status if provided
        if (isset($data['status'])) {
            $validStatuses = ['Active', 'Pending', 'Suspended', 'Terminated'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid user status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        // Validate roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $role) {
                if (isset($role['roleCode'])) {
                    $this->validateRoleCode($role['roleCode']);
                }
            }
        }
    }


    /**
     * Validate user role create request
     * 
     * @param array $data Role data
     * @throws JamboJetValidationException
     */
    private function validateUserRoleCreateRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['roleCode']);

        // Validate role code format
        $this->validateRoleCode($data['roleCode']);

        // Validate optional fields if provided
        if (isset($data['organizationCode'])) {
            $this->validateOrganizationCode($data['organizationCode']);
        }

        if (isset($data['locationGroupCode'])) {
            $this->validateLocationGroupCode($data['locationGroupCode']);
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $permission) {
                if (empty(trim($permission))) {
                    throw new JamboJetValidationException('Permission cannot be empty');
                }
            }
        }

        // Validate role description length
        if (isset($data['description'])) {
            $this->validateStringLengths($data, ['description' => ['max' => 500]]);
        }
    }

    /**
     * Validate user role edit request
     * 
     * @param array $data Role edit data
     * @throws JamboJetValidationException
     */
    private function validateUserRoleEditRequest(array $data): void
    {
        // Role edit typically involves status changes or permission updates
        if (isset($data['roleCode'])) {
            $this->validateRoleCode($data['roleCode']);
        }

        if (isset($data['status'])) {
            $validStatuses = ['Active', 'Inactive', 'Suspended'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid role status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $permission) {
                if (empty(trim($permission))) {
                    throw new JamboJetValidationException('Permission cannot be empty');
                }
            }
        }

        if (isset($data['organizationCode'])) {
            $this->validateOrganizationCode($data['organizationCode']);
        }

        if (isset($data['locationGroupCode'])) {
            $this->validateLocationGroupCode($data['locationGroupCode']);
        }
    }


    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate user creation request
     * Based on NSK user creation requirements
     * 
     * @param array $data User creation data
     * @throws JamboJetValidationException
     */
    private function validateUserCreateRequest(array $data): void
    {
        // Validate required fields for user creation
        $this->validateRequired($data, ['username', 'password', 'personalInfo']);

        // Validate username (typically email)
        $this->validateFormats($data, ['username' => 'email']);
        $this->validateStringLengths($data, ['username' => ['max' => 100]]);

        // Validate password strength
        $this->validatePassword($data['password']);

        // Validate personal information
        $this->validatePersonalInfo($data['personalInfo']);

        // Validate address if provided
        if (isset($data['address'])) {
            $this->validateUserAddress($data['address']);
        }

        // Validate preferences if provided
        if (isset($data['preferences'])) {
            $this->validateUserPreferences($data['preferences']);
        }

        // Validate loyalty programs if provided
        if (isset($data['loyaltyPrograms'])) {
            $this->validateLoyaltyPrograms($data['loyaltyPrograms']);
        }

        // Validate culture code if provided
        if (isset($data['cultureCode'])) {
            $this->validateCultureCode($data['cultureCode']);
        }

        // Validate marketing consent if provided
        if (isset($data['marketingConsent']) && !is_bool($data['marketingConsent'])) {
            throw new JamboJetValidationException(
                'marketingConsent must be a boolean value',
                400
            );
        }

        // Validate terms acceptance if provided
        if (isset($data['termsAccepted']) && !is_bool($data['termsAccepted'])) {
            throw new JamboJetValidationException(
                'termsAccepted must be a boolean value',
                400
            );
        }

        // Validate custom fields if provided
        if (isset($data['customFields'])) {
            $this->validateCustomFields($data['customFields']);
        }
    }

    /**
     * Validate user update request
     * 
     * @param array $data User update data
     * @throws JamboJetValidationException
     */
    private function validateUserUpdateRequest(array $data): void
    {
        // For updates, most fields are optional but must be valid if provided

        // Validate username if provided
        if (isset($data['username'])) {
            $this->validateFormats($data, ['username' => 'email']);
            $this->validateStringLengths($data, ['username' => ['max' => 100]]);
        }

        // Don't allow password updates through this method
        if (isset($data['password'])) {
            throw new JamboJetValidationException(
                'Password cannot be updated through this method. Use changePassword instead.',
                400
            );
        }

        // Validate personal info if provided
        if (isset($data['personalInfo'])) {
            $this->validatePersonalInfoUpdate($data['personalInfo']);
        }

        // Validate address if provided
        if (isset($data['address'])) {
            $this->validateUserAddress($data['address']);
        }

        // Validate preferences if provided
        if (isset($data['preferences'])) {
            $this->validateUserPreferences($data['preferences']);
        }

        // Validate loyalty programs if provided
        if (isset($data['loyaltyPrograms'])) {
            $this->validateLoyaltyPrograms($data['loyaltyPrograms']);
        }

        // Validate account status if provided
        if (isset($data['accountStatus'])) {
            $this->validateAccountStatus($data['accountStatus']);
        }

        // Validate user roles if provided
        if (isset($data['roles'])) {
            $this->validateUserRoles($data['roles']);
        }
    }

    /**
     * Validate user patch request (partial updates)
     * 
     * @param array $data Patch data
     * @throws JamboJetValidationException
     */
    private function validateUserPatchRequest(array $data): void
    {
        // Patch allows even more minimal updates
        // Just validate the format of provided fields

        if (empty($data)) {
            throw new JamboJetValidationException(
                'Patch request cannot be empty',
                400
            );
        }

        // Validate any provided fields using update validation
        $this->validateUserUpdateRequest($data);
    }

    /**
     * Validate password change request
     * 
     * @param array $data Password change data
     * @throws JamboJetValidationException
     */
    private function validatePasswordChangeRequest(array $data): void
    {
        $this->validateRequired($data, ['currentPassword', 'newPassword']);

        // Validate current password is not empty
        if (empty(trim($data['currentPassword']))) {
            throw new JamboJetValidationException(
                'Current password cannot be empty',
                400
            );
        }

        // Validate new password strength
        $this->validatePassword($data['newPassword']);

        // Ensure new password is different from current
        if ($data['currentPassword'] === $data['newPassword']) {
            throw new JamboJetValidationException(
                'New password must be different from current password',
                400
            );
        }

        // Validate confirmation password if provided
        if (isset($data['confirmPassword'])) {
            if ($data['newPassword'] !== $data['confirmPassword']) {
                throw new JamboJetValidationException(
                    'Password confirmation does not match new password',
                    400
                );
            }
        }
    }

    /**
     * Validate password reset request
     * 
     * @param array $data Password reset data
     * @throws JamboJetValidationException
     */
    private function validatePasswordResetRequest(array $data): void
    {
        // For admin password reset, new password might be provided
        if (isset($data['newPassword'])) {
            $this->validatePassword($data['newPassword']);
        }

        // Validate reset method if provided
        if (isset($data['resetMethod'])) {
            $validMethods = ['Email', 'SMS', 'AdminReset', 'SecurityQuestions'];
            if (!in_array($data['resetMethod'], $validMethods)) {
                throw new JamboJetValidationException(
                    'Invalid reset method. Expected one of: ' . implode(', ', $validMethods),
                    400
                );
            }
        }

        // Validate notification preferences if provided
        if (isset($data['sendNotification']) && !is_bool($data['sendNotification'])) {
            throw new JamboJetValidationException(
                'sendNotification must be a boolean value',
                400
            );
        }

        // Validate expiry time if provided
        if (isset($data['expiryMinutes'])) {
            $this->validateNumericRanges($data, ['expiryMinutes' => ['min' => 5, 'max' => 1440]]);
        }
    }

    /**
     * Validate bulk user creation request
     * 
     * @param array $usersData Array of user data
     * @throws JamboJetValidationException
     */
    private function validateBulkUserCreateRequest(array $usersData): void
    {
        if (empty($usersData)) {
            throw new JamboJetValidationException(
                'Users data cannot be empty for bulk creation',
                400
            );
        }

        // Validate maximum batch size
        if (count($usersData) > 100) {
            throw new JamboJetValidationException(
                'Maximum 100 users can be created in a single batch',
                400
            );
        }

        // Validate each user
        foreach ($usersData as $index => $userData) {
            try {
                $this->validateUserCreateRequest($userData);
            } catch (JamboJetValidationException $e) {
                throw new JamboJetValidationException(
                    "User validation failed at index {$index}: " . $e->getMessage(),
                    400
                );
            }
        }

        // Check for duplicate usernames/emails in the batch
        $usernames = array_column($usersData, 'username');
        if (count($usernames) !== count(array_unique($usernames))) {
            throw new JamboJetValidationException(
                'Duplicate usernames found in batch creation request',
                400
            );
        }
    }

    /**
     * Validate users search criteria
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validateUsersSearchCriteria(array $criteria): void
    {
        // Validate search filters
        if (isset($criteria['email'])) {
            $this->validateFormats($criteria, ['email' => 'email']);
        }

        if (isset($criteria['firstName'])) {
            $this->validateStringLengths($criteria, ['firstName' => ['max' => 50]]);
        }

        if (isset($criteria['lastName'])) {
            $this->validateStringLengths($criteria, ['lastName' => ['max' => 50]]);
        }

        if (isset($criteria['status'])) {
            $this->validateAccountStatus($criteria['status']);
        }

        if (isset($criteria['role'])) {
            $this->validateStringLengths($criteria, ['role' => ['max' => 50]]);
        }

        if (isset($criteria['createdAfter'])) {
            $this->validateFormats($criteria, ['createdAfter' => 'date']);
        }

        if (isset($criteria['createdBefore'])) {
            $this->validateFormats($criteria, ['createdBefore' => 'date']);
        }

        // Validate pagination parameters
        if (isset($criteria['startIndex'])) {
            $this->validateNumericRanges($criteria, ['startIndex' => ['min' => 0]]);
        }

        if (isset($criteria['itemCount'])) {
            $this->validateNumericRanges($criteria, ['itemCount' => ['min' => 1, 'max' => 100]]);
        }

        // Validate sort parameters
        if (isset($criteria['sortBy'])) {
            $validSortFields = ['firstName', 'lastName', 'email', 'createdDate', 'lastLoginDate'];
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

    /**
     * Validate impersonation request
     * 
     * @param array $data Impersonation data
     * @throws JamboJetValidationException
     */
    private function validateImpersonationRequest(array $data): void
    {
        // Must specify either target user or role
        if (!isset($data['targetUserKey']) && !isset($data['targetRole'])) {
            throw new JamboJetValidationException(
                'Either targetUserKey or targetRole must be specified',
                400
            );
        }

        // Validate target user key if provided
        if (isset($data['targetUserKey'])) {
            $this->validateUserKey($data['targetUserKey']);
        }

        // Validate target role if provided
        if (isset($data['targetRole'])) {
            $this->validateStringLengths($data, ['targetRole' => ['max' => 50]]);
        }

        // Validate impersonation reason if provided
        if (isset($data['reason'])) {
            $this->validateStringLengths($data, ['reason' => ['max' => 200]]);
        }

        // Validate session duration if provided
        if (isset($data['sessionDurationMinutes'])) {
            $this->validateNumericRanges($data, ['sessionDurationMinutes' => ['min' => 5, 'max' => 480]]);
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS FOR COMPLEX STRUCTURES
    // =================================================================

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @throws JamboJetValidationException
     */
    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new JamboJetValidationException(
                'Password must be at least 8 characters long'
            );
        }

        if (strlen($password) > 128) {
            throw new JamboJetValidationException(
                'Password cannot exceed 128 characters'
            );
        }

        // Check for at least one letter and one number
        if (!preg_match('/[A-Za-z]/', $password)) {
            throw new JamboJetValidationException(
                'Password must contain at least one letter'
            );
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new JamboJetValidationException(
                'Password must contain at least one number'
            );
        }
    }

    /**
     * Validate personal information structure
     * 
     * @param array $personalInfo Personal information
     * @throws JamboJetValidationException
     */
    private function validatePersonalInfo(array $personalInfo): void
    {
        // Required fields for user creation
        $this->validateRequired($personalInfo, ['firstName', 'lastName', 'email']);

        // Validate name fields
        $this->validateNameFields($personalInfo);

        // Validate email
        $this->validateFormats($personalInfo, ['email' => 'email']);

        // Validate phone if provided
        if (isset($personalInfo['phone'])) {
            $this->validateFormats($personalInfo, ['phone' => 'phone']);
        }

        // Validate date of birth if provided
        if (isset($personalInfo['dateOfBirth'])) {
            $this->validateDateOfBirth($personalInfo['dateOfBirth']);
        }

        // Validate gender if provided
        if (isset($personalInfo['gender'])) {
            $this->validateGender($personalInfo['gender']);
        }

        // Validate title if provided
        if (isset($personalInfo['title'])) {
            $this->validateTitle($personalInfo['title']);
        }

        // Validate nationality if provided
        if (isset($personalInfo['nationality'])) {
            $this->validateFormats($personalInfo, ['nationality' => 'country_code']);
        }

        // Validate emergency contact if provided
        if (isset($personalInfo['emergencyContact'])) {
            $this->validateEmergencyContact($personalInfo['emergencyContact']);
        }
    }

    /**
     * Validate personal info for updates (less strict)
     * 
     * @param array $personalInfo Personal information
     * @throws JamboJetValidationException
     */
    private function validatePersonalInfoUpdate(array $personalInfo): void
    {
        // For updates, validate only provided fields
        if (
            isset($personalInfo['firstName']) || isset($personalInfo['lastName']) ||
            isset($personalInfo['middleName'])
        ) {
            $this->validateNameFields($personalInfo);
        }

        if (isset($personalInfo['email'])) {
            $this->validateFormats($personalInfo, ['email' => 'email']);
        }

        if (isset($personalInfo['phone'])) {
            $this->validateFormats($personalInfo, ['phone' => 'phone']);
        }

        if (isset($personalInfo['dateOfBirth'])) {
            $this->validateDateOfBirth($personalInfo['dateOfBirth']);
        }

        if (isset($personalInfo['gender'])) {
            $this->validateGender($personalInfo['gender']);
        }

        if (isset($personalInfo['title'])) {
            $this->validateTitle($personalInfo['title']);
        }

        if (isset($personalInfo['nationality'])) {
            $this->validateFormats($personalInfo, ['nationality' => 'country_code']);
        }

        if (isset($personalInfo['emergencyContact'])) {
            $this->validateEmergencyContact($personalInfo['emergencyContact']);
        }
    }

    /**
     * Validate name fields
     * 
     * @param array $data Data containing name fields
     * @throws JamboJetValidationException
     */
    private function validateNameFields(array $data): void
    {
        $nameFields = ['title', 'firstName', 'middleName', 'lastName', 'suffix'];
        $nameLengths = [
            'title' => ['max' => 10],
            'firstName' => ['min' => 1, 'max' => 50],
            'middleName' => ['max' => 50],
            'lastName' => ['min' => 1, 'max' => 50],
            'suffix' => ['max' => 10]
        ];

        foreach ($nameFields as $field) {
            if (isset($data[$field])) {
                // Validate length
                if (isset($nameLengths[$field])) {
                    $this->validateStringLengths([$field => $data[$field]], [$field => $nameLengths[$field]]);
                }

                // Validate characters (letters, spaces, hyphens, apostrophes only)
                if (!preg_match("/^[a-zA-Z\s\-'\.]+$/", $data[$field])) {
                    throw new JamboJetValidationException(
                        "{$field} contains invalid characters. Only letters, spaces, hyphens, and apostrophes are allowed",
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate date of birth
     * 
     * @param string $dateOfBirth Date of birth
     * @throws JamboJetValidationException
     */
    private function validateDateOfBirth(string $dateOfBirth): void
    {
        $this->validateFormats(['dob' => $dateOfBirth], ['dob' => 'date']);

        $dob = new \DateTime($dateOfBirth);
        $now = new \DateTime();
        $age = $now->diff($dob)->y;

        // Age constraints
        if ($age < 13) {
            throw new JamboJetValidationException(
                'User must be at least 13 years old',
                400
            );
        }

        if ($age > 120) {
            throw new JamboJetValidationException(
                'Invalid date of birth. Age cannot exceed 120 years',
                400
            );
        }

        // Date cannot be in the future
        if ($dob > $now) {
            throw new JamboJetValidationException(
                'Date of birth cannot be in the future',
                400
            );
        }
    }

    /**
     * Validate gender
     * 
     * @param string $gender Gender value
     * @throws JamboJetValidationException
     */
    private function validateGender(string $gender): void
    {
        $validGenders = ['M', 'F', 'Male', 'Female', 'Other', 'Prefer not to say'];

        if (!in_array($gender, $validGenders)) {
            throw new JamboJetValidationException(
                'Invalid gender value. Expected one of: ' . implode(', ', $validGenders),
                400
            );
        }
    }

    /**
     * Validate organization code
     * 
     * @param string $organizationCode Organization code
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCode(string $organizationCode): void
    {
        if (empty(trim($organizationCode))) {
            throw new JamboJetValidationException('Organization code cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9]{2,10}$/', $organizationCode)) {
            throw new JamboJetValidationException(
                'Organization code must be 2-10 characters, alphanumeric uppercase'
            );
        }
    }

    /**
     * Validate title
     * 
     * @param string $title Title value
     * @throws JamboJetValidationException
     */
    private function validateTitle(string $title): void
    {
        $validTitles = ['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof', 'Rev', 'Sir', 'Madam'];

        if (!in_array($title, $validTitles)) {
            throw new JamboJetValidationException(
                'Invalid title. Expected one of: ' . implode(', ', $validTitles),
                400
            );
        }
    }


    /**
     * Validate location group code
     * 
     * @param string $locationGroupCode Location group code
     * @throws JamboJetValidationException
     */
    private function validateLocationGroupCode(string $locationGroupCode): void
    {
        if (empty(trim($locationGroupCode))) {
            throw new JamboJetValidationException('Location group code cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9]{2,10}$/', $locationGroupCode)) {
            throw new JamboJetValidationException(
                'Location group code must be 2-10 characters, alphanumeric uppercase'
            );
        }
    }

    /**
     * Validate domain code
     * 
     * @param string $domainCode Domain code
     * @throws JamboJetValidationException
     */
    private function validateDomainCode(string $domainCode): void
    {
        if (empty(trim($domainCode))) {
            throw new JamboJetValidationException('Domain code cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9]{2,10}$/', $domainCode)) {
            throw new JamboJetValidationException(
                'Domain code must be 2-10 characters, alphanumeric uppercase'
            );
        }
    }

    /**
     * Validate role code
     * 
     * @param string $roleCode Role code
     * @throws JamboJetValidationException
     */
    private function validateRoleCode(string $roleCode): void
    {
        if (empty(trim($roleCode))) {
            throw new JamboJetValidationException('Role code cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9_]{2,20}$/', $roleCode)) {
            throw new JamboJetValidationException(
                'Role code must be 2-20 characters, alphanumeric uppercase with underscores'
            );
        }
    }

    /**
     * Validate emergency contact
     * 
     * @param array $contact Emergency contact information
     * @throws JamboJetValidationException
     */
    private function validateEmergencyContact(array $contact): void
    {
        $this->validateRequired($contact, ['name', 'phone']);

        $this->validateStringLengths($contact, [
            'name' => ['min' => 2, 'max' => 100],
            'relationship' => ['max' => 50]
        ]);

        $this->validateFormats($contact, ['phone' => 'phone']);

        if (isset($contact['email'])) {
            $this->validateFormats($contact, ['email' => 'email']);
        }
    }

    /**
     * Validate user address
     * 
     * @param array $address Address information
     * @throws JamboJetValidationException
     */
    private function validateUserAddress(array $address): void
    {
        // Required fields for complete address
        $this->validateRequired($address, ['lineOne', 'city', 'countryCode']);

        $this->validateFormats($address, ['countryCode' => 'country_code']);

        $this->validateStringLengths($address, [
            'lineOne' => ['max' => 100],
            'lineTwo' => ['max' => 100],
            'lineThree' => ['max' => 100],
            'city' => ['max' => 50],
            'provinceState' => ['max' => 50],
            'postalCode' => ['max' => 20],
            'county' => ['max' => 50]
        ]);

        // Validate postal code format for specific countries
        if (isset($address['postalCode']) && isset($address['countryCode'])) {
            $this->validatePostalCodeFormat($address['postalCode'], $address['countryCode']);
        }
    }

    /**
     * Validate postal code format based on country
     * 
     * @param string $postalCode Postal code
     * @param string $countryCode Country code
     * @throws JamboJetValidationException
     */
    private function validatePostalCodeFormat(string $postalCode, string $countryCode): void
    {
        $patterns = [
            'US' => '/^\d{5}(-\d{4})?$/',
            'CA' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/',
            'GB' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            'KE' => '/^\d{5}$/',
            'DE' => '/^\d{5}$/',
            'FR' => '/^\d{5}$/',
        ];

        if (isset($patterns[$countryCode])) {
            if (!preg_match($patterns[$countryCode], $postalCode)) {
                throw new JamboJetValidationException(
                    "Invalid postal code format for country {$countryCode}",
                    400
                );
            }
        }
    }

    /**
     * Validate user preferences
     * 
     * @param array $preferences User preferences
     * @throws JamboJetValidationException
     */
    private function validateUserPreferences(array $preferences): void
    {
        // Validate language preference
        if (isset($preferences['language'])) {
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $preferences['language'])) {
                throw new JamboJetValidationException(
                    'Invalid language format. Expected format: en or en-US',
                    400
                );
            }
        }

        // Validate currency preference
        if (isset($preferences['currency'])) {
            $this->validateFormats($preferences, ['currency' => 'currency_code']);
        }

        // Validate time zone
        if (isset($preferences['timeZone'])) {
            $validTimeZones = timezone_identifiers_list();
            if (!in_array($preferences['timeZone'], $validTimeZones)) {
                throw new JamboJetValidationException(
                    'Invalid time zone',
                    400
                );
            }
        }

        // Validate communication preferences
        if (isset($preferences['communications'])) {
            $this->validateCommunicationPreferences($preferences['communications']);
        }

        // Validate accessibility preferences
        if (isset($preferences['accessibility'])) {
            $this->validateAccessibilityPreferences($preferences['accessibility']);
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
     * Validate communication preferences
     * 
     * @param array $communications Communication preferences
     * @throws JamboJetValidationException
     */
    private function validateCommunicationPreferences(array $communications): void
    {
        $booleanFields = [
            'emailMarketing',
            'smsMarketing',
            'phoneMarketing',
            'pushNotifications',
            'bookingUpdates',
            'flightAlerts',
            'promotionalOffers',
            'newsletter'
        ];

        foreach ($booleanFields as $field) {
            if (isset($communications[$field]) && !is_bool($communications[$field])) {
                throw new JamboJetValidationException(
                    "{$field} must be a boolean value",
                    400
                );
            }
        }

        // Validate preferred contact method
        if (isset($communications['preferredMethod'])) {
            $validMethods = ['Email', 'SMS', 'Phone', 'Push', 'None'];
            if (!in_array($communications['preferredMethod'], $validMethods)) {
                throw new JamboJetValidationException(
                    'Invalid preferred contact method. Expected one of: ' . implode(', ', $validMethods),
                    400
                );
            }
        }
    }

    /**
     * Validate accessibility preferences
     * 
     * @param array $accessibility Accessibility preferences
     * @throws JamboJetValidationException
     */
    private function validateAccessibilityPreferences(array $accessibility): void
    {
        $booleanFields = [
            'largeText',
            'highContrast',
            'screenReader',
            'keyboardNavigation',
            'reducedMotion',
            'alternativeFormats'
        ];

        foreach ($booleanFields as $field) {
            if (isset($accessibility[$field]) && !is_bool($accessibility[$field])) {
                throw new JamboJetValidationException(
                    "{$field} must be a boolean value",
                    400
                );
            }
        }
    }

    /**
     * Validate loyalty programs
     * 
     * @param array $loyaltyPrograms Loyalty programs
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyPrograms(array $loyaltyPrograms): void
    {
        foreach ($loyaltyPrograms as $index => $program) {
            $this->validateRequired($program, ['programCode', 'membershipNumber']);

            $this->validateStringLengths($program, [
                'programCode' => ['max' => 10],
                'membershipNumber' => ['max' => 50]
            ]);

            // Validate tier if provided
            if (isset($program['tier'])) {
                $this->validateStringLengths(['tier' => $program['tier']], ['tier' => ['max' => 20]]);
            }

            // Validate points balance if provided
            if (isset($program['pointsBalance'])) {
                $this->validateFormats(['points' => $program['pointsBalance']], ['points' => 'non_negative_number']);
            }
        }
    }

    /**
     * Validate culture code
     * 
     * @param string $cultureCode Culture code
     * @throws JamboJetValidationException
     */
    private function validateCultureCode(string $cultureCode): void
    {
        if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', $cultureCode)) {
            throw new JamboJetValidationException(
                'Invalid culture code format. Expected format: en-US',
                400
            );
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
        $validStatuses = ['Active', 'Inactive', 'Suspended', 'Locked', 'PendingVerification', 'Closed'];

        if (!in_array($status, $validStatuses)) {
            throw new JamboJetValidationException(
                'Invalid account status. Expected one of: ' . implode(', ', $validStatuses),
                400
            );
        }
    }

    /**
     * Validate user roles
     * 
     * @param array $roles User roles
     * @throws JamboJetValidationException
     */
    private function validateUserRoles(array $roles): void
    {
        if (empty($roles)) {
            throw new JamboJetValidationException(
                'User must have at least one role',
                400
            );
        }

        $validRoles = [
            'Customer',
            'Agent',
            'Admin',
            'SuperAdmin',
            'Manager',
            'Support',
            'Finance',
            'Operations',
            'Marketing'
        ];

        foreach ($roles as $index => $role) {
            if (!in_array($role, $validRoles)) {
                throw new JamboJetValidationException(
                    "Invalid role at index {$index}. Expected one of: " . implode(', ', $validRoles),
                    400
                );
            }
        }
    }

    /**
     * Validate custom fields
     * 
     * @param array $customFields Custom fields
     * @throws JamboJetValidationException
     */
    private function validateCustomFields(array $customFields): void
    {
        foreach ($customFields as $fieldName => $fieldValue) {
            // Validate field name format
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $fieldName)) {
                throw new JamboJetValidationException(
                    "Invalid custom field name: {$fieldName}. Must start with letter and contain only alphanumeric characters and underscores",
                    400
                );
            }

            // Validate field name length
            if (strlen($fieldName) > 50) {
                throw new JamboJetValidationException(
                    "Custom field name {$fieldName} exceeds maximum length of 50 characters",
                    400
                );
            }

            // Validate field value length if it's a string
            if (is_string($fieldValue) && strlen($fieldValue) > 500) {
                throw new JamboJetValidationException(
                    "Custom field value for {$fieldName} exceeds maximum length of 500 characters",
                    400
                );
            }
        }
    }

    /**
     * Validate user key
     * 
     * @param string $userKey User key
     * @throws JamboJetValidationException
     */
    private function validateUserKey(string $userKey): void
    {
        if (empty(trim($userKey))) {
            throw new JamboJetValidationException(
                'User key cannot be empty',
                400
            );
        }

        // User keys are typically alphanumeric with minimum length
        if (strlen($userKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid user key format',
                400
            );
        }
    }

    /**
     * Validate person key helper method
     * 
     * @param string $personKey Person key to validate
     * @throws JamboJetValidationException
     */
    private function validatePersonKey(string $personKey): void
    {
        if (empty(trim($personKey))) {
            throw new JamboJetValidationException('Person key cannot be empty');
        }

        // Person keys are typically alphanumeric with minimum length
        if (strlen($personKey) < 5) {
            throw new JamboJetValidationException('Invalid person key format');
        }

        if (!preg_match('/^[A-Za-z0-9\-]{5,50}$/', $personKey)) {
            throw new JamboJetValidationException(
                'Person key must be 5-50 characters, alphanumeric with hyphens'
            );
        }
    }


    /**
     * Validate user impersonate request
     * 
     * @param array $data Impersonation data
     * @throws JamboJetValidationException
     */
    private function validateUserImpersonateRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['roleCode']);

        // Validate role code
        $this->validateRoleCode($data['roleCode']);

        // Validate target user if specified
        if (isset($data['targetUserKey'])) {
            if (empty(trim($data['targetUserKey']))) {
                throw new JamboJetValidationException('Target user key cannot be empty');
            }
        }

        // Validate impersonation reason if provided
        if (isset($data['reason'])) {
            $this->validateStringLengths($data, ['reason' => ['min' => 10, 'max' => 200]]);
        }

        // Validate impersonation duration if provided
        if (isset($data['duration'])) {
            if (!is_int($data['duration']) || $data['duration'] < 1 || $data['duration'] > 480) {
                throw new JamboJetValidationException(
                    'Impersonation duration must be between 1 and 480 minutes'
                );
            }
        }
    }

    /**
     * Validate person create request
     * 
     * @param array $data Person data
     * @throws JamboJetValidationException
     */
    private function validatePersonCreateRequest(array $data): void
    {
        // Validate required personal information
        $this->validateRequired($data, ['name']);

        // Validate name structure
        if (isset($data['name'])) {
            $this->validatePersonName($data['name']);
        }

        // Validate contact information if provided
        if (isset($data['contactInfo'])) {
            $this->validatePersonContactInfo($data['contactInfo']);
        }

        // Validate addresses if provided
        if (isset($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $index => $address) {
                $this->validatePersonAddress($address, $index);
            }
        }

        // Validate travel documents if provided
        if (isset($data['travelDocuments']) && is_array($data['travelDocuments'])) {
            foreach ($data['travelDocuments'] as $index => $document) {
                $this->validateTravelDocument($document, $index);
            }
        }

        // Validate customer program enrollments
        if (isset($data['customerPrograms']) && is_array($data['customerPrograms'])) {
            foreach ($data['customerPrograms'] as $program) {
                $this->validateCustomerProgram($program);
            }
        }
    }

    /**
     * Validate person edit request
     * 
     * @param array $data Person edit data
     * @throws JamboJetValidationException
     */
    private function validatePersonEditRequest(array $data): void
    {
        // At least one field should be provided for editing
        $editableFields = ['name', 'contactInfo', 'addresses', 'travelDocuments', 'customerPrograms'];
        $hasEditableField = false;

        foreach ($editableFields as $field) {
            if (isset($data[$field])) {
                $hasEditableField = true;
                break;
            }
        }

        if (!$hasEditableField) {
            throw new JamboJetValidationException(
                'At least one editable field must be provided: ' . implode(', ', $editableFields)
            );
        }

        // Validate individual fields if provided
        if (isset($data['name'])) {
            $this->validatePersonName($data['name']);
        }

        if (isset($data['contactInfo'])) {
            $this->validatePersonContactInfo($data['contactInfo']);
        }

        if (isset($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $index => $address) {
                $this->validatePersonAddress($address, $index);
            }
        }

        if (isset($data['travelDocuments']) && is_array($data['travelDocuments'])) {
            foreach ($data['travelDocuments'] as $index => $document) {
                $this->validateTravelDocument($document, $index);
            }
        }

        // Validate version control if provided
        if (isset($data['version'])) {
            if (!is_int($data['version']) || $data['version'] < 1) {
                throw new JamboJetValidationException('Version must be a positive integer');
            }
        }
    }

    /**
     * Validate person search request
     * 
     * @param array $data Search criteria
     * @throws JamboJetValidationException
     */
    private function validatePersonSearchRequest(array $data): void
    {
        // At least one search criterion should be provided
        $searchFields = ['name', 'email', 'phone', 'documentNumber', 'customerNumber', 'dateOfBirth'];
        $hasSearchField = false;

        foreach ($searchFields as $field) {
            if (isset($data[$field]) && !empty(trim($data[$field]))) {
                $hasSearchField = true;
                break;
            }
        }

        if (!$hasSearchField) {
            throw new JamboJetValidationException(
                'At least one search criterion must be provided: ' . implode(', ', $searchFields)
            );
        }

        // Validate search field formats
        if (isset($data['email'])) {
            $this->validateFormats($data, ['email' => 'email']);
        }

        if (isset($data['phone'])) {
            $this->validateFormats($data, ['phone' => 'phone']);
        }

        if (isset($data['dateOfBirth'])) {
            $this->validateFormats($data, ['dateOfBirth' => 'date']);
        }

        // Validate search limits
        if (isset($data['maxResults'])) {
            $this->validateNumericRanges($data, ['maxResults' => ['min' => 1, 'max' => 100]]);
        }

        // Validate search filters
        if (isset($data['includeInactive']) && !is_bool($data['includeInactive'])) {
            throw new JamboJetValidationException('includeInactive must be a boolean value');
        }
    }

    // Helper validation methods

    /**
     * Validate person name structure
     * 
     * @param array $name Name data
     * @throws JamboJetValidationException
     */
    private function validatePersonName(array $name): void
    {
        $this->validateRequired($name, ['first', 'last']);

        $this->validateStringLengths($name, [
            'first' => ['min' => 1, 'max' => 50],
            'last' => ['min' => 1, 'max' => 50],
            'middle' => ['max' => 50],
            'title' => ['max' => 10],
            'suffix' => ['max' => 10]
        ]);

        // Validate name characters (letters, spaces, hyphens, apostrophes)
        $nameFields = ['first', 'last', 'middle'];
        foreach ($nameFields as $field) {
            if (isset($name[$field]) && !preg_match("/^[a-zA-Z\s\-']+$/", $name[$field])) {
                throw new JamboJetValidationException(
                    "{$field} name can only contain letters, spaces, hyphens, and apostrophes"
                );
            }
        }
    }

    /**
     * Validate person contact information
     * 
     * @param array $contactInfo Contact data
     * @throws JamboJetValidationException
     */
    private function validatePersonContactInfo(array $contactInfo): void
    {
        // Validate email if provided
        if (isset($contactInfo['email'])) {
            $this->validateFormats($contactInfo, ['email' => 'email']);
        }

        // Validate phone numbers if provided
        if (isset($contactInfo['phones']) && is_array($contactInfo['phones'])) {
            foreach ($contactInfo['phones'] as $index => $phone) {
                $this->validatePhoneContact($phone, $index);
            }
        }

        // Validate preferred contact method
        if (isset($contactInfo['preferredMethod'])) {
            $validMethods = ['EMAIL', 'PHONE', 'SMS', 'MAIL'];
            if (!in_array($contactInfo['preferredMethod'], $validMethods)) {
                throw new JamboJetValidationException(
                    'Invalid preferred contact method. Expected one of: ' . implode(', ', $validMethods)
                );
            }
        }
    }

    /**
     * Validate person address
     * 
     * @param array $address Address data
     * @param int $index Address index for error reporting
     * @throws JamboJetValidationException
     */
    private function validatePersonAddress(array $address, int $index): void
    {
        $this->validateRequired($address, ['lineOne', 'city', 'countryCode']);

        $this->validateStringLengths($address, [
            'lineOne' => ['min' => 1, 'max' => 100],
            'lineTwo' => ['max' => 100],
            'city' => ['min' => 1, 'max' => 50],
            'postalCode' => ['max' => 20],
            'provinceState' => ['max' => 50]
        ]);

        // Validate country code
        $this->validateFormats($address, ['countryCode' => 'country_code']);

        // Validate address type if provided
        if (isset($address['type'])) {
            $validTypes = ['HOME', 'WORK', 'BILLING', 'MAILING', 'OTHER'];
            if (!in_array($address['type'], $validTypes)) {
                throw new JamboJetValidationException(
                    "Invalid address type at index {$index}. Expected one of: " . implode(', ', $validTypes)
                );
            }
        }
    }

    /**
     * Validate travel document
     * 
     * @param array $document Travel document data
     * @param int $index Document index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateTravelDocument(array $document, int $index): void
    {
        $this->validateRequired($document, ['type', 'number', 'issuingCountry']);

        // Validate document type
        $validTypes = ['PASSPORT', 'NATIONAL_ID', 'DRIVING_LICENSE', 'MILITARY_ID', 'OTHER'];
        if (!in_array($document['type'], $validTypes)) {
            throw new JamboJetValidationException(
                "Invalid document type at index {$index}. Expected one of: " . implode(', ', $validTypes)
            );
        }

        // Validate country codes
        $this->validateFormats($document, [
            'issuingCountry' => 'country_code'
        ]);

        if (isset($document['nationality'])) {
            $this->validateFormats($document, ['nationality' => 'country_code']);
        }

        // Validate dates if provided
        if (isset($document['expirationDate'])) {
            $this->validateFormats($document, ['expirationDate' => 'date']);

            // Document should not be expired
            $expDate = new \DateTime($document['expirationDate']);
            $now = new \DateTime();
            if ($expDate < $now) {
                throw new JamboJetValidationException(
                    "Travel document at index {$index} is expired"
                );
            }
        }

        if (isset($document['issueDate'])) {
            $this->validateFormats($document, ['issueDate' => 'date']);
        }

        // Validate document number format
        $this->validateStringLengths($document, [
            'number' => ['min' => 1, 'max' => 50]
        ]);
    }
}
