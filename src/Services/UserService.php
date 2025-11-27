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
 * Handles all user management operations including CRUD, authentication, roles, 
 * profile management, and comprehensive person sub-resource management
 * 
 * Base endpoints: /api/nsk/v{version}/user, /api/nsk/v{version}/users
 * 
 * CORE USER OPERATIONS (11 endpoints):
 * - GET /api/nsk/v1/user - Get current user information
 * - PUT /api/nsk/v1/user - Update current user
 * - PATCH /api/nsk/v1/user - Patch current user
 * - POST /api/nsk/v1/user - Create user account (customer) v1
 * - POST /api/nsk/v2/user - Create user account (customer) v2
 * - GET /api/nsk/v1/users - Get users (agent function)
 * - POST /api/nsk/v1/users - Create multiple users (agent) v1
 * - POST /api/nsk/v2/users - Create multiple users (agent) v2
 * - GET /api/nsk/v1/users/{userKey} - Get specific user
 * - PUT /api/nsk/v1/users/{userKey} - Update specific user
 * - DELETE /api/nsk/v1/users/{userKey} - Delete specific user
 * 
 * USER ROLES MANAGEMENT (8 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/roles - Get all user roles
 * - POST /api/nsk/v1/users/{userKey}/roles - Create user role
 * - GET /api/nsk/v1/users/{userKey}/roles/{userRoleKey} - Get specific role
 * - PUT /api/nsk/v1/users/{userKey}/roles/{userRoleKey} - Update role
 * - PATCH /api/nsk/v1/users/{userKey}/roles/{userRoleKey} - Patch role
 * - DELETE /api/nsk/v1/users/{userKey}/roles/{userRoleKey} - Delete role
 * - GET /api/nsk/v1/users/byRole/{roleCode} - Get users by role
 * - GET /api/nsk/v1/users/byStation/{domainCode}/{stationCode} - Get users by station
 * 
 * IMPERSONATION & USER QUERIES (7 endpoints):
 * - GET /api/nsk/v1/user/impersonate - Get impersonation state
 * - POST /api/nsk/v1/user/impersonate - Start impersonation
 * - DELETE /api/nsk/v1/user/impersonate - Reset impersonation
 * - GET /api/nsk/v1/user/bookingsByContactCustomerNumber - Get bookings by contact
 * - GET /api/nsk/v1/users/byPerson/{personKey} - Get user by person key
 * - GET /api/nsk/v1/users/{userKey}/bookings - Get user bookings
 * - GET /api/nsk/v1/users/agents - Get all agents
 * 
 * PASSWORD MANAGEMENT (4 endpoints):
 * - POST /api/nsk/v1/user/password/change - Change current password v1
 * - POST /api/nsk/v2/user/password/change - Change current password v2
 * - POST /api/nsk/v1/users/{userKey}/password/reset - Reset user password v1
 * - POST /api/nsk/v2/users/{userKey}/password/reset - Reset user password v2
 * 
 * USER-PERSON CORE (3 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person - Get user's person
 * - PUT /api/nsk/v1/users/{userKey}/person - Update user's person
 * - PATCH /api/nsk/v1/users/{userKey}/person - Patch user's person
 * 
 * PERSON ADDRESSES (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/addresses - Get all addresses
 * - POST /api/nsk/v1/users/{userKey}/person/addresses - Create address
 * - GET /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey} - Get address
 * - PUT /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey} - Update address
 * - PATCH /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey} - Patch address
 * - DELETE /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey} - Delete address
 * 
 * PERSON AFFILIATIONS (5 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/affiliations - Get all affiliations
 * - POST /api/nsk/v1/users/{userKey}/person/affiliations - Create affiliation
 * - GET /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey} - Get affiliation
 * - PUT /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey} - Update affiliation
 * - DELETE /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey} - Delete affiliation
 * 
 * PERSON ALIASES (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/aliases - Get all aliases
 * - POST /api/nsk/v1/users/{userKey}/person/aliases - Create alias
 * - GET /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey} - Get alias
 * - PUT /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey} - Update alias
 * - PATCH /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey} - Patch alias
 * - DELETE /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey} - Delete alias
 * 
 * PERSON COMMENTS (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/comments - Get all comments
 * - POST /api/nsk/v1/users/{userKey}/person/comments - Create comment
 * - GET /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey} - Get comment
 * - PUT /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey} - Update comment
 * - PATCH /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey} - Patch comment
 * - DELETE /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey} - Delete comment
 * 
 * PERSON EMAILS (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/emails - Get all emails
 * - POST /api/nsk/v1/users/{userKey}/person/emails - Create email
 * - GET /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey} - Get email
 * - PUT /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey} - Update email
 * - PATCH /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey} - Patch email
 * - DELETE /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey} - Delete email
 * 
 * PERSON INFORMATION (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/information - Get all information
 * - POST /api/nsk/v1/users/{userKey}/person/information - Create information
 * - GET /api/nsk/v1/users/{userKey}/person/information/{personInformationKey} - Get information
 * - PUT /api/nsk/v1/users/{userKey}/person/information/{personInformationKey} - Update information
 * - PATCH /api/nsk/v1/users/{userKey}/person/information/{personInformationKey} - Patch information
 * - DELETE /api/nsk/v1/users/{userKey}/person/information/{personInformationKey} - Delete information
 * 
 * PERSON PHONE NUMBERS (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/phoneNumbers - Get all phone numbers
 * - POST /api/nsk/v1/users/{userKey}/person/phoneNumbers - Create phone number
 * - GET /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey} - Get phone number
 * - PUT /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey} - Update phone number
 * - PATCH /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey} - Patch phone number
 * - DELETE /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey} - Delete phone number
 * 
 * PERSON PREFERENCES (6 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/preferences - Get all preferences
 * - POST /api/nsk/v1/users/{userKey}/person/preferences - Create preference
 * - GET /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey} - Get preference
 * - PUT /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey} - Update preference
 * - PATCH /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey} - Patch preference
 * - DELETE /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey} - Delete preference
 * 
 * PERSON PROGRAMS (5 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/programs - Get all programs
 * - POST /api/nsk/v1/users/{userKey}/person/programs - Create program
 * - GET /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey} - Get program
 * - PUT /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey} - Update program
 * - DELETE /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey} - Delete program
 * 
 * PERSON STORED PAYMENTS (5 endpoints):
 * - GET /api/nsk/v1/users/{userKey}/person/storedPayments - Get all stored payments
 * - POST /api/nsk/v1/users/{userKey}/person/storedPayments - Create stored payment
 * - GET /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey} - Get stored payment
 * - PATCH /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey} - Patch stored payment
 * - DELETE /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey} - Delete stored payment
 * 
 * TOTAL: 86 endpoints fully implemented
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



    // =================================================================
    // USER BOOKINGS & ADDITIONAL OPERATIONS
    // =================================================================

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
     * Reset user password (agent function)
     * 
     * POST /api/nsk/v1/users/{userKey}/password/reset
     * GraphQL: usersForgotPassword
     * 
     * Requires agent permissions.
     * Invokes the forgot password reset process for a specific user.
     * This will trigger a password reset email or process based on system configuration.
     * 
     * @param string $userKey The unique user key
     * @param array $resetData Password reset request data (optional)
     * @return array Reset result
     * @throws JamboJetApiException
     */
    public function resetUserPassword(string $userKey, array $resetData = []): array
    {
        $this->validateUserKey($userKey);

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
     * Change specific user's password (agent function)
     * 
     * POST /api/nsk/v1/users/{userKey}/password/change
     * GraphQL: usersChangePassword
     * 
     * Requires agent permissions.
     * Changes a specific user's password (not the current logged-in user).
     * This follows domain level restrictions and could result in a failed change if there is:
     * - A minimum time requirement between password changes
     * - Invalid password length
     * - Invalid characters
     * 
     * @param string $userKey The unique user key
     * @param array $passwordData Password change request data (newPassword required)
     * @return array Change result
     * @throws JamboJetApiException
     */
    public function changeUserPassword(string $userKey, array $passwordData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserPasswordChangeRequest($passwordData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/password/change", $passwordData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to change user password: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get current user's bookings
     * 
     * GET /api/nsk/v1/user/bookings
     * 
     * Searches for upcoming and past bookings for the current logged-in user.
     * This endpoint returns bookings where the user is the primary contact.
     * 
     * QUERY PARAMETERS:
     * - StartDate: Booking start search date (ISO 8601)
     * - EndDate: Booking end search date (ISO 8601)
     * 
     * @param array $criteria Optional search criteria (startDate, endDate)
     * @return array List of bookings with trip information
     * @throws JamboJetApiException
     */
    public function getCurrentUserBookings(array $criteria = []): array
    {
        $this->validateBookingSearchCriteria($criteria);

        try {
            $queryString = !empty($criteria) ? '?' . http_build_query($criteria) : '';
            return $this->get("api/nsk/v1/user/bookings{$queryString}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get current user bookings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 2: ROLE MANAGEMENT (6 methods)
    // =================================================================

    /**
     * Get all roles for a specific user
     * 
     * GET /api/nsk/v1/users/{userKey}/roles
     * Retrieves all roles assigned to a specific user
     * 
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @return array List of user roles with role details
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
     * Create a new role for a specific user
     * 
     * POST /api/nsk/v1/users/{userKey}/roles
     * GraphQL: usersRoleAdd
     * 
     * Creates a new role assignment for a specific user.
     * Requires agent permissions.
     * 
     * ROLE DATA STRUCTURE:
     * - roleCode (string, required): Role code to assign
     * - organizationCode (string, optional): Organization code
     * - stationCode (string, optional): Station code
     * - effectiveAfter (datetime, required): When role becomes active
     * - effectiveBefore (datetime, optional): When role expires
     * - effectiveDays (array, optional): Days of week role is active [0-6, 0=Sunday]
     * 
     * @param string $userKey The unique user key
     * @param array $roleData Role creation data
     * @return array Created role information with userRoleKey
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
     * Get a specific role for a specific user
     * 
     * GET /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRole
     * 
     * Retrieves detailed information about a specific user role assignment.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array User role details including effective dates and permissions
     * @throws JamboJetApiException
     */
    public function getUserRole(string $userKey, string $userRoleKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/roles/{$userRoleKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update a specific role for a specific user
     * 
     * PUT /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleSet
     * 
     * Updates an existing role assignment (full replacement).
     * Requires agent permissions.
     * 
     * REQUIRED FIELDS:
     * - effectiveAfter (datetime): When role becomes active
     * 
     * OPTIONAL FIELDS:
     * - effectiveBefore (datetime): When role expires
     * - effectiveDays (array): Days of week role is active [0-6]
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $roleData Complete role data
     * @return array Update result
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
     * Patch a specific role for a specific user
     * 
     * PATCH /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleModify
     * 
     * Partially updates an existing role assignment.
     * Only specified fields are updated, others remain unchanged.
     * Requires agent permissions.
     * 
     * PATCHABLE FIELDS:
     * - effectiveAfter (datetime): When role becomes active
     * - effectiveBefore (datetime): When role expires
     * - effectiveDays (array): Days of week role is active
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $patchData Partial role data to update
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserRole(string $userKey, string $userRoleKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateUserRoleKey($userRoleKey);
        $this->validateUserRolePatchRequest($patchData);

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

    /**
     * Delete a specific role for a specific user
     * 
     * DELETE /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleDelete
     * 
     * Removes a role assignment from a specific user.
     * This permanently removes the role assignment.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array Deletion result
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

    // =================================================================
    // PHASE 3: IMPERSONATION & USER QUERIES (7 methods)
    // =================================================================

    /**
     * Get current impersonation state
     * 
     * GET /api/nsk/v1/user/impersonate
     * GraphQL: impersonate
     * 
     * Retrieves the logged-in user's current session roles state.
     * Returns information about whether the user is currently impersonating a role,
     * including original role and impersonated role details.
     * 
     * @return array Current impersonation state with role details
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
     * Start role impersonation
     * 
     * POST /api/nsk/v1/user/impersonate
     * GraphQL: impersonateSet
     * 
     * Impersonates a new role for the logged-in user.
     * Allows agents to temporarily assume another role for testing or support purposes.
     * 
     * REQUEST DATA:
     * - roleCode (string, required): Role code to impersonate
     * 
     * RESPONSE:
     * - HTTP 202: Accepted (impersonation started)
     * 
     * @param array $impersonationData Impersonation request (roleCode required)
     * @return array Impersonation result
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
     * Reset impersonation to original role
     * 
     * DELETE /api/nsk/v1/user/impersonate
     * GraphQL: impersonateDelete
     * 
     * Resets the logged-in user's role to their original state.
     * Ends any active impersonation session.
     * 
     * RESPONSES:
     * - HTTP 200: Success (no data returned)
     * - HTTP 200 with warning: Not currently impersonating (nsk:NoOperation)
     * 
     * @return array Reset result
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

    /**
     * Get bookings by contact customer number
     * 
     * GET /api/nsk/v1/user/bookingsByContactCustomerNumber
     * GraphQL: userBookingByContact
     * 
     * Searches for any bookings that have the current user as a contact
     * via their customer number. Returns bookings where the user is listed
     * as a contact or emergency contact.
     * 
     * @param array $criteria Optional search criteria (dates, filters)
     * @return array List of bookings where user is contact
     * @throws JamboJetApiException
     */
    public function getBookingsByContactCustomerNumber(array $criteria = []): array
    {
        try {
            $queryString = !empty($criteria) ? '?' . http_build_query($criteria) : '';
            return $this->get("api/nsk/v1/user/bookingsByContactCustomerNumber{$queryString}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bookings by contact customer number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user by person key
     * 
     * GET /api/nsk/v1/users/byPerson/{personKey}
     * GraphQL: usersByPerson
     * 
     * Retrieves a specific user by their associated person key.
     * Useful for finding user accounts linked to a person record.
     * Each person record can have one associated user account.
     * 
     * @param string $personKey The person key
     * @return array User information associated with the person
     * @throws JamboJetApiException
     */
    public function getUserByPersonKey(string $personKey): array
    {
        $this->validatePersonKey($personKey);

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

    /**
     * Get bookings for specific user
     * 
     * GET /api/nsk/v1/users/{userKey}/bookings
     * GraphQL: usersBookingsv2
     * 
     * Searches a specific user for upcoming and past bookings.
     * Requires agent permissions.
     * 
     * QUERY PARAMETERS:
     * - StartDate: Booking start search date (ISO 8601)
     * - EndDate: Booking end search date (ISO 8601)
     * 
     * @param string $userKey The unique user key
     * @param array $criteria Optional search criteria (StartDate, EndDate)
     * @return array List of user's bookings with trip information
     * @throws JamboJetApiException
     */
    public function getUserBookings(string $userKey, array $criteria = []): array
    {
        $this->validateUserKey($userKey);
        $this->validateBookingSearchCriteria($criteria);

        try {
            $queryString = !empty($criteria) ? '?' . http_build_query($criteria) : '';
            return $this->get("api/nsk/v1/users/{$userKey}/bookings{$queryString}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user bookings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Change password (v3 - enhanced validation)
     * 
     * POST /api/nsk/v3/user/password/change
     * GraphQL: userChangePasswordv3
     * 
     * Changes the logged-in user's password with enhanced validation.
     * Validates that the current password is correct before proceeding.
     * 
     * This follows domain level restrictions and could result in a failed change if:
     * - There is a minimum time requirement between password changes
     * - Invalid password length
     * - Invalid characters used
     * 
     * REQUEST DATA:
     * - currentPassword (string, required): Current password
     * - newPassword (string, required): New password
     * 
     * @param array $passwordData Password change request (currentPassword, newPassword)
     * @return array Change result
     * @throws JamboJetApiException
     */
    public function changePasswordV3(array $passwordData): array
    {
        $this->validatePasswordChangeRequest($passwordData);

        try {
            return $this->post('api/nsk/v3/user/password/change', $passwordData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to change password (v3): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 4: USER PREFERENCES (5 methods)
    // =================================================================

    /**
     * Get all user preferences
     * 
     * GET /api/nsk/v1/user/userPreferences
     * 
     * Retrieves all preference types for the current logged-in user.
     * Returns list of available preference types and their current state.
     * 
     * PREFERENCE TYPES:
     * - 0 = Default: Default user preferences
     * - 1 = Language: Language and culture preferences
     * - 2 = SkySpeed: Airline operational preferences
     * 
     * @return array List of all user preferences by type
     * @throws JamboJetApiException
     */
    public function getAllUserPreferences(): array
    {
        try {
            return $this->get('api/nsk/v1/user/userPreferences');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all user preferences: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific user preference by type
     * 
     * GET /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreference
     * 
     * Retrieves a specific preference type for the current user.
     * 
     * PREFERENCE TYPES:
     * - 0 or 'Default': Default user preferences
     * - 1 or 'Language': Language/culture preferences (cultureCode)
     * - 2 or 'SkySpeed': SkySpeed settings (currency, country, nationality, etc.)
     * 
     * SKYSPEED RESPONSE EXAMPLE:
     * {
     *   "currencyCode": "USD",
     *   "cultureCode": "en-US",
     *   "countryCode": "US",
     *   "nationality": "US",
     *   "collectionCurrencyCode": "EUR",
     *   "collectionCurrencySetting": 2,
     *   "flightResultFareType": 1
     * }
     * 
     * @param string|int $preferenceType Preference type (0=Default, 1=Language, 2=SkySpeed)
     * @return array Preference details for the specified type
     * @throws JamboJetApiException
     */
    public function getUserPreference($preferenceType): array
    {
        $this->validatePreferenceType($preferenceType);

        try {
            return $this->get("api/nsk/v1/user/userPreferences/{$preferenceType}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create user preference
     * 
     * POST /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreferenceAdd
     * 
     * Creates a new preference for the current user.
     * 
     * REQUEST DATA (varies by preference type):
     * - Default: General user preferences
     * - Language: { cultureCode: 'en-US' }
     * - SkySpeed: { currencyCode, cultureCode, countryCode, nationality, etc. }
     * 
     * SKYSPEED VALIDATION:
     * - All string codes validated for existence and active status
     * - CollectionCurrencySetting logic enforced
     * - Maximum string lengths: 3 characters for codes
     * 
     * @param string|int $preferenceType Preference type to create
     * @param array $preferenceData Preference data
     * @return array Created preference
     * @throws JamboJetApiException
     */
    public function createUserPreference($preferenceType, array $preferenceData): array
    {
        $this->validatePreferenceType($preferenceType);
        $this->validatePreferenceData($preferenceData);

        try {
            return $this->post("api/nsk/v1/user/userPreferences/{$preferenceType}", $preferenceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update user preference (full replacement)
     * 
     * PUT /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreferenceSet
     * 
     * Updates an existing preference (full replacement).
     * All fields must be provided.
     * 
     * SKYSPEED SETTINGS:
     * - If SkySpeed settings disabled, they will be automatically enabled on update
     * - All string properties validated for existence and active status
     * - CollectionCurrencySetting validation:
     *   * Can only be None if collectionCurrencyCode is null/empty
     *   * Cannot be None if collectionCurrencyCode has value
     * 
     * @param string|int $preferenceType Preference type to update
     * @param array $preferenceData Complete preference data
     * @return array Updated preference
     * @throws JamboJetApiException
     */
    public function updateUserPreference($preferenceType, array $preferenceData): array
    {
        $this->validatePreferenceType($preferenceType);
        $this->validatePreferenceData($preferenceData);

        try {
            return $this->put("api/nsk/v1/user/userPreferences/{$preferenceType}", $preferenceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch user preference (partial update)
     * 
     * PATCH /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreferenceModify
     * 
     * Partially updates an existing preference.
     * Only specified fields are updated, others remain unchanged.
     * 
     * @param string|int $preferenceType Preference type to patch
     * @param array $patchData Partial preference data to update
     * @return array Updated preference
     * @throws JamboJetApiException
     */
    public function patchUserPreference($preferenceType, array $patchData): array
    {
        $this->validatePreferenceType($preferenceType);

        if (empty($patchData)) {
            throw new JamboJetValidationException('Preference patch data cannot be empty');
        }

        try {
            return $this->patch("api/nsk/v1/user/userPreferences/{$preferenceType}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 5: SSO & API VARIANTS (9 methods) - FINAL PHASE
    // =================================================================

    /**
     * Get all SSO tokens for current user
     * 
     * GET /api/nsk/v1/user/singleSignOnToken
     * GraphQL: singleSignOnTokens
     * 
     * Retrieves all single sign-on tokens associated with the current user.
     * Returns list of SSO providers and their associated tokens.
     * 
     * SSO TOKEN STRUCTURE:
     * - providerKey: Unique provider identifier
     * - singleSignOn: The SSO token string
     * - expirationDate: Token expiration (optional)
     * 
     * @return array List of SSO tokens with provider information
     * @throws JamboJetApiException
     */
    public function getAllSsoTokens(): array
    {
        try {
            return $this->get('api/nsk/v1/user/singleSignOnToken');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all SSO tokens: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSO token by provider
     * 
     * GET /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnToken
     * 
     * Retrieves a specific SSO token for a given provider.
     * Returns 404 if token not found for provider.
     * 
     * @param string $providerKey SSO provider key
     * @return array SSO token details (providerKey, singleSignOn, expirationDate)
     * @throws JamboJetApiException
     */
    public function getSsoToken(string $providerKey): array
    {
        $this->validateProviderKey($providerKey);

        try {
            return $this->get("api/nsk/v1/user/singleSignOnToken/{$providerKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create/Link SSO token for provider
     * 
     * POST /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenAdd
     * 
     * Links a new SSO token with the logged-in user for a specific provider.
     * Creates association between user and SSO provider.
     * 
     * REQUEST DATA:
     * - singleSignOn (string, required, max 256): SSO token string
     * - expirationDate (datetime, optional): Token expiration date
     * 
     * @param string $providerKey SSO provider key
     * @param array $tokenData Token data (singleSignOn, expirationDate)
     * @return array Created token result
     * @throws JamboJetApiException
     */
    public function createSsoToken(string $providerKey, array $tokenData): array
    {
        $this->validateProviderKey($providerKey);
        $this->validateSsoTokenData($tokenData);

        try {
            return $this->post("api/nsk/v1/user/singleSignOnToken/{$providerKey}", $tokenData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update SSO token (full replacement)
     * 
     * PUT /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenSet
     * 
     * Updates an existing SSO token (full replacement).
     * All token fields must be provided.
     * 
     * @param string $providerKey SSO provider key
     * @param array $tokenData Complete token data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateSsoToken(string $providerKey, array $tokenData): array
    {
        $this->validateProviderKey($providerKey);
        $this->validateSsoTokenData($tokenData);

        try {
            return $this->put("api/nsk/v1/user/singleSignOnToken/{$providerKey}", $tokenData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch SSO token (partial update)
     * 
     * PATCH /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenModify
     * 
     * Patches an existing SSO token (partial update).
     * Only specified fields are updated, others remain unchanged.
     * 
     * @param string $providerKey SSO provider key
     * @param array $patchData Partial token data to update
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchSsoToken(string $providerKey, array $patchData): array
    {
        $this->validateProviderKey($providerKey);

        if (empty($patchData)) {
            throw new JamboJetValidationException('SSO token patch data cannot be empty');
        }

        try {
            return $this->patch("api/nsk/v1/user/singleSignOnToken/{$providerKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete SSO token
     * 
     * DELETE /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenDelete
     * 
     * Deletes an SSO token associated with a provider.
     * Removes the link between user and SSO provider.
     * 
     * @param string $providerKey SSO provider key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteSsoToken(string $providerKey): array
    {
        $this->validateProviderKey($providerKey);

        try {
            return $this->delete("api/nsk/v1/user/singleSignOnToken/{$providerKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create user account v2 (customer)
     * 
     * POST /api/nsk/v2/user
     * 
     * Creates a new customer user account using v2 endpoint.
     * Enhanced version with additional features/validation compared to v1.
     * 
     * @param array $userData User account creation data
     * @return array Created user information
     * @throws JamboJetApiException
     */
    public function createUserV2(array $userData): array
    {
        $this->validateUserCreateRequest($userData);

        try {
            return $this->post('api/nsk/v2/user', $userData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create multiple users v2 (agent function)
     * 
     * POST /api/nsk/v2/users
     * 
     * Creates multiple user accounts using v2 endpoint (requires agent permissions).
     * Enhanced version with additional features/validation compared to v1.
     * 
     * @param array $usersData Array of user data
     * @return array Created users information
     * @throws JamboJetApiException
     */
    public function createUsersV2(array $usersData): array
    {
        $this->validateUsersCreateRequest($usersData);

        try {
            return $this->post('api/nsk/v2/users', $usersData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create users (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user SkySpeed preferences v2 (agent function)
     * 
     * GET /api/nsk/v2/users/{userKey}/preferences/skySpeedSettings
     * GraphQL: usersPreferencesSkySpeedSettingsv2
     * 
     * Retrieves SkySpeed settings preferences for a specific user (agent function).
     * Returns 404 if SkySpeed preferences not found.
     * 
     * @param string $userKey The unique user key
     * @return array SkySpeed settings preferences
     * @throws JamboJetApiException
     */
    public function getUserSkySpeedPreferencesV2(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v2/users/{$userKey}/preferences/skySpeedSettings");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user SkySpeed preferences (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update user SkySpeed preferences v2 (agent function)
     * 
     * PUT /api/nsk/v2/users/{userKey}/preferences/skySpeedSettings
     * GraphQL: usersPreferencesSkySpeedSettingsSetv2
     * 
     * Updates SkySpeed settings preferences for a specific user (agent function).
     * If the SkySpeed settings of the user are disabled, they will be automatically
     * enabled and updated.
     * 
     * VALIDATION:
     * - All string properties validated for existence and active status
     * - Collection currency setting logic enforced
     * 
     * @param string $userKey The unique user key
     * @param array $preferencesData SkySpeed settings data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserSkySpeedPreferencesV2(string $userKey, array $preferencesData): array
    {
        $this->validateUserKey($userKey);
        $this->validateSkySpeedSettings($preferencesData);

        try {
            return $this->put("api/nsk/v2/users/{$userKey}/preferences/skySpeedSettings", $preferencesData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user SkySpeed preferences (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Get user's person record
     * 
     * GET /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPerson
     * 
     * Retrieves the complete person record associated with a specific user.
     * Returns comprehensive person information including addresses, emails,
     * phone numbers, programs, preferences, and all sub-resources.
     * 
     * @param string $userKey The unique user key
     * @return array Person record with all details
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
     * Update user's person record
     * 
     * PUT /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPersonSet
     * 
     * Updates the user's person record basic information (full replacement).
     * Requires complete person data structure.
     * 
     * @param string $userKey The unique user key
     * @param array $personData Complete person data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPerson(string $userKey, array $personData): array
    {
        $this->validateUserKey($userKey);
        $this->validatePersonData($personData);

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

    /**
     * Patch user's person record
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPersonModify
     * 
     * Partially updates the user's person record basic information.
     * Only provided fields will be updated.
     * 
     * @param string $userKey The unique user key
     * @param array $patchData Partial person data to update
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPerson(string $userKey, array $patchData): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all addresses for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/addresses
     * 
     * Retrieves all address records associated with the user's person.
     * 
     * @param string $userKey The unique user key
     * @return array List of addresses
     * @throws JamboJetApiException
     */
    public function getUserPersonAddresses(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/addresses");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person addresses: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create address for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/addresses
     * GraphQL: usersPersonAddressAdd
     * 
     * Creates a new address for the user's person.
     * 
     * @param string $userKey The unique user key
     * @param array $addressData Address data (lineOne, lineTwo, lineThree, city, provinceState, postalCode, countryCode, type)
     * @return array Created address
     * @throws JamboJetApiException
     */
    public function createUserPersonAddress(string $userKey, array $addressData): array
    {
        $this->validateUserKey($userKey);
        $this->validateAddressData($addressData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/addresses", $addressData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific address for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddress
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @return array Address details
     * @throws JamboJetApiException
     */
    public function getUserPersonAddress(string $userKey, string $personAddressKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAddressKey, 'Person address key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/addresses/{$personAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update address for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @param array $addressData Complete address data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonAddress(string $userKey, string $personAddressKey, array $addressData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAddressKey, 'Person address key');
        $this->validateAddressData($addressData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/addresses/{$personAddressKey}", $addressData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch address for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressModify
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @param array $patchData Partial address data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonAddress(string $userKey, string $personAddressKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAddressKey, 'Person address key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/addresses/{$personAddressKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete address for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonAddress(string $userKey, string $personAddressKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAddressKey, 'Person address key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/addresses/{$personAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person address: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all affiliations for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/affiliations
     * GraphQL: usersPersonAffiliations
     * 
     * @param string $userKey The unique user key
     * @return array List of affiliations
     * @throws JamboJetApiException
     */
    public function getUserPersonAffiliations(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/affiliations");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person affiliations: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create affiliation for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/affiliations
     * GraphQL: usersPersonAffiliationAdd
     * 
     * @param string $userKey The unique user key
     * @param array $affiliationData Affiliation data (organizationCode, personKey, etc.)
     * @return array Created affiliation
     * @throws JamboJetApiException
     */
    public function createUserPersonAffiliation(string $userKey, array $affiliationData): array
    {
        $this->validateUserKey($userKey);
        $this->validateRequired($affiliationData, ['organizationCode']);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/affiliations", $affiliationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific affiliation for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliation
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @return array Affiliation details
     * @throws JamboJetApiException
     */
    public function getUserPersonAffiliation(string $userKey, string $personAffiliationKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAffiliationKey, 'Person affiliation key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/affiliations/{$personAffiliationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update affiliation for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliationSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @param array $affiliationData Complete affiliation data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonAffiliation(string $userKey, string $personAffiliationKey, array $affiliationData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAffiliationKey, 'Person affiliation key');
        $this->validateRequired($affiliationData, ['organizationCode']);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/affiliations/{$personAffiliationKey}", $affiliationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete affiliation for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliationDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonAffiliation(string $userKey, string $personAffiliationKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAffiliationKey, 'Person affiliation key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/affiliations/{$personAffiliationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person affiliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all aliases for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/aliases
     * 
     * @param string $userKey The unique user key
     * @return array List of aliases
     * @throws JamboJetApiException
     */
    public function getUserPersonAliases(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/aliases");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person aliases: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create alias for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/aliases
     * GraphQL: usersPersonAliasAdd
     * 
     * @param string $userKey The unique user key
     * @param array $aliasData Alias data (name with firstName, lastName, title, suffix)
     * @return array Created alias
     * @throws JamboJetApiException
     */
    public function createUserPersonAlias(string $userKey, array $aliasData): array
    {
        $this->validateUserKey($userKey);
        $this->validateAliasData($aliasData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/aliases", $aliasData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific alias for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAlias
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @return array Alias details
     * @throws JamboJetApiException
     */
    public function getUserPersonAlias(string $userKey, string $personAliasKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAliasKey, 'Person alias key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/aliases/{$personAliasKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update alias for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @param array $aliasData Complete alias data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonAlias(string $userKey, string $personAliasKey, array $aliasData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAliasKey, 'Person alias key');
        $this->validateAliasData($aliasData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/aliases/{$personAliasKey}", $aliasData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch alias for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasModify
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @param array $patchData Partial alias data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonAlias(string $userKey, string $personAliasKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAliasKey, 'Person alias key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/aliases/{$personAliasKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete alias for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonAlias(string $userKey, string $personAliasKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personAliasKey, 'Person alias key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/aliases/{$personAliasKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person alias: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all comments for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/comments
     * GraphQL: usersPersonComments
     * 
     * @param string $userKey The unique user key
     * @return array List of comments
     * @throws JamboJetApiException
     */
    public function getUserPersonComments(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/comments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person comments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create comment for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/comments
     * GraphQL: usersPersonCommentAdd
     * 
     * @param string $userKey The unique user key
     * @param array $commentData Comment data (commentText required)
     * @return array Created comment
     * @throws JamboJetApiException
     */
    public function createUserPersonComment(string $userKey, array $commentData): array
    {
        $this->validateUserKey($userKey);
        $this->validateRequired($commentData, ['commentText']);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/comments", $commentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific comment for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonComment
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @return array Comment details
     * @throws JamboJetApiException
     */
    public function getUserPersonComment(string $userKey, string $personCommentKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personCommentKey, 'Person comment key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/comments/{$personCommentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update comment for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentSet
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @param array $commentData Complete comment data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonComment(string $userKey, string $personCommentKey, array $commentData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personCommentKey, 'Person comment key');
        $this->validateRequired($commentData, ['commentText']);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/comments/{$personCommentKey}", $commentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch comment for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentModify
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @param array $patchData Partial comment data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonComment(string $userKey, string $personCommentKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personCommentKey, 'Person comment key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/comments/{$personCommentKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete comment for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonComment(string $userKey, string $personCommentKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personCommentKey, 'Person comment key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/comments/{$personCommentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all emails for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/emails
     * 
     * @param string $userKey The unique user key
     * @return array List of emails
     * @throws JamboJetApiException
     */
    public function getUserPersonEmails(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/emails");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person emails: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create email for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/emails
     * GraphQL: usersPersonEmailAdd
     * 
     * @param string $userKey The unique user key
     * @param array $emailData Email data (address, type)
     * @return array Created email
     * @throws JamboJetApiException
     */
    public function createUserPersonEmail(string $userKey, array $emailData): array
    {
        $this->validateUserKey($userKey);
        $this->validateEmailData($emailData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/emails", $emailData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific email for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmail
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @return array Email details
     * @throws JamboJetApiException
     */
    public function getUserPersonEmail(string $userKey, string $personEmailAddressKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personEmailAddressKey, 'Person email address key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/emails/{$personEmailAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update email for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailSet
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @param array $emailData Complete email data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonEmail(string $userKey, string $personEmailAddressKey, array $emailData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personEmailAddressKey, 'Person email address key');
        $this->validateEmailData($emailData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/emails/{$personEmailAddressKey}", $emailData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch email for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailModify
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @param array $patchData Partial email data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonEmail(string $userKey, string $personEmailAddressKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personEmailAddressKey, 'Person email address key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/emails/{$personEmailAddressKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete email for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonEmail(string $userKey, string $personEmailAddressKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personEmailAddressKey, 'Person email address key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/emails/{$personEmailAddressKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 11: PERSON INFORMATION (6 methods)
    // =================================================================

    /**
     * Get all information for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/information
     * GraphQL: usersPersonInformations
     * 
     * @param string $userKey The unique user key
     * @return array List of information records
     * @throws JamboJetApiException
     */
    public function getUserPersonInformation(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/information");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create information for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/information
     * GraphQL: usersPersonInformationAdd
     * 
     * @param string $userKey The unique user key
     * @param array $informationData Information data (data, personInformationTypeCode)
     * @return array Created information
     * @throws JamboJetApiException
     */
    public function createUserPersonInformation(string $userKey, array $informationData): array
    {
        $this->validateUserKey($userKey);
        $this->validateRequired($informationData, ['data', 'personInformationTypeCode']);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/information", $informationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific information for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformation
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @return array Information details
     * @throws JamboJetApiException
     */
    public function getUserPersonInformationByKey(string $userKey, string $personInformationKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personInformationKey, 'Person information key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/information/{$personInformationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update information for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationSet
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @param array $informationData Complete information data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonInformation(string $userKey, string $personInformationKey, array $informationData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personInformationKey, 'Person information key');
        $this->validateRequired($informationData, ['data']);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/information/{$personInformationKey}", $informationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch information for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationModify
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @param array $patchData Partial information data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonInformation(string $userKey, string $personInformationKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personInformationKey, 'Person information key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/information/{$personInformationKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete information for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonInformation(string $userKey, string $personInformationKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personInformationKey, 'Person information key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/information/{$personInformationKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all phone numbers for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/phoneNumbers
     * 
     * @param string $userKey The unique user key
     * @return array List of phone numbers
     * @throws JamboJetApiException
     */
    public function getUserPersonPhoneNumbers(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/phoneNumbers");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person phone numbers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create phone number for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/phoneNumbers
     * GraphQL: usersPersonPhoneNumberAdd
     * 
     * @param string $userKey The unique user key
     * @param array $phoneData Phone number data (number, type)
     * @return array Created phone number
     * @throws JamboJetApiException
     */
    public function createUserPersonPhoneNumber(string $userKey, array $phoneData): array
    {
        $this->validateUserKey($userKey);
        $this->validatePhoneNumberData($phoneData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/phoneNumbers", $phoneData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific phone number for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumber
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @return array Phone number details
     * @throws JamboJetApiException
     */
    public function getUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPhoneNumberKey, 'Person phone number key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/phoneNumbers/{$personPhoneNumberKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update phone number for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberSet
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @param array $phoneData Complete phone number data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey, array $phoneData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPhoneNumberKey, 'Person phone number key');
        $this->validatePhoneNumberData($phoneData);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/phoneNumbers/{$personPhoneNumberKey}", $phoneData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch phone number for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberModify
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @param array $patchData Partial phone number data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPhoneNumberKey, 'Person phone number key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/phoneNumbers/{$personPhoneNumberKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete phone number for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPhoneNumberKey, 'Person phone number key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/phoneNumbers/{$personPhoneNumberKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 13: PERSON PREFERENCES (6 methods)
    // =================================================================

    /**
     * Get all preferences for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/preferences
     * 
     * @param string $userKey The unique user key
     * @return array List of preferences
     * @throws JamboJetApiException
     */
    public function getUserPersonPreferences(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/preferences");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person preferences: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create preference for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/preferences
     * GraphQL: usersPersonPreferenceAdd
     * 
     * @param string $userKey The unique user key
     * @param array $preferenceData Preference data (preferenceTypeCode, value)
     * @return array Created preference
     * @throws JamboJetApiException
     */
    public function createUserPersonPreference(string $userKey, array $preferenceData): array
    {
        $this->validateUserKey($userKey);
        $this->validateRequired($preferenceData, ['preferenceTypeCode', 'value']);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/preferences", $preferenceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific preference for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreference
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @return array Preference details
     * @throws JamboJetApiException
     */
    public function getUserPersonPreference(string $userKey, string $personPreferenceKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPreferenceKey, 'Person preference key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/preferences/{$personPreferenceKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update preference for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceSet
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @param array $preferenceData Complete preference data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonPreference(string $userKey, string $personPreferenceKey, array $preferenceData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPreferenceKey, 'Person preference key');
        $this->validateRequired($preferenceData, ['value']);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/preferences/{$personPreferenceKey}", $preferenceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch preference for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceModify
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @param array $patchData Partial preference data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonPreference(string $userKey, string $personPreferenceKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPreferenceKey, 'Person preference key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/preferences/{$personPreferenceKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete preference for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonPreference(string $userKey, string $personPreferenceKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personPreferenceKey, 'Person preference key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/preferences/{$personPreferenceKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person preference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 14: PERSON PROGRAMS (5 methods - NO PATCH)
    // =================================================================

    /**
     * Get all programs for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/programs
     * GraphQL: usersPersonPrograms
     * 
     * @param string $userKey The unique user key
     * @return array List of programs
     * @throws JamboJetApiException
     */
    public function getUserPersonPrograms(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/programs");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person programs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create program for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/programs
     * GraphQL: usersPersonProgramAdd
     * 
     * @param string $userKey The unique user key
     * @param array $programData Program data (programCode, programNumber)
     * @return array Created program
     * @throws JamboJetApiException
     */
    public function createUserPersonProgram(string $userKey, array $programData): array
    {
        $this->validateUserKey($userKey);
        $this->validateRequired($programData, ['programCode', 'programNumber']);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/programs", $programData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific program for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgram
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @return array Program details
     * @throws JamboJetApiException
     */
    public function getUserPersonProgram(string $userKey, string $personProgramKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personProgramKey, 'Person program key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/programs/{$personProgramKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update program for user's person
     * 
     * PUT /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgramSet
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @param array $programData Complete program data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateUserPersonProgram(string $userKey, string $personProgramKey, array $programData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personProgramKey, 'Person program key');
        $this->validateRequired($programData, ['programNumber']);

        try {
            return $this->put("api/nsk/v1/users/{$userKey}/person/programs/{$personProgramKey}", $programData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update user person program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete program for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgramDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonProgram(string $userKey, string $personProgramKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personProgramKey, 'Person program key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/programs/{$personProgramKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PHASE 15: PERSON STORED PAYMENTS (5 methods - NO PUT)
    // =================================================================

    /**
     * Get all stored payments for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/storedPayments
     * GraphQL: usersPersonStoredPayments
     * 
     * @param string $userKey The unique user key
     * @return array List of stored payments
     * @throws JamboJetApiException
     */
    public function getUserPersonStoredPayments(string $userKey): array
    {
        $this->validateUserKey($userKey);

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/storedPayments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person stored payments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create stored payment for user's person
     * 
     * POST /api/nsk/v1/users/{userKey}/person/storedPayments
     * GraphQL: usersPersonStoredPaymentAdd
     * 
     * @param string $userKey The unique user key
     * @param array $paymentData Stored payment data (paymentMethodType, accountNumber, etc.)
     * @return array Created stored payment
     * @throws JamboJetApiException
     */
    public function createUserPersonStoredPayment(string $userKey, array $paymentData): array
    {
        $this->validateUserKey($userKey);
        $this->validateStoredPaymentData($paymentData);

        try {
            return $this->post("api/nsk/v1/users/{$userKey}/person/storedPayments", $paymentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create user person stored payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific stored payment for user's person
     * 
     * GET /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPayment
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @return array Stored payment details
     * @throws JamboJetApiException
     */
    public function getUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personStoredPaymentKey, 'Person stored payment key');

        try {
            return $this->get("api/nsk/v1/users/{$userKey}/person/storedPayments/{$personStoredPaymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get user person stored payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch stored payment for user's person
     * 
     * PATCH /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPaymentModify
     * 
     * NOTE: To update account number, DELETE and POST a new stored payment
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @param array $patchData Partial stored payment data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function patchUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey, array $patchData): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personStoredPaymentKey, 'Person stored payment key');

        try {
            return $this->patch("api/nsk/v1/users/{$userKey}/person/storedPayments/{$personStoredPaymentKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch user person stored payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete stored payment for user's person
     * 
     * DELETE /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPaymentDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey): array
    {
        $this->validateUserKey($userKey);
        $this->validateKey($personStoredPaymentKey, 'Person stored payment key');

        try {
            return $this->delete("api/nsk/v1/users/{$userKey}/person/storedPayments/{$personStoredPaymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete user person stored payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate person data
     * 
     * @param array $data Person data
     * @throws JamboJetValidationException
     */
    private function validatePersonData(array $data): void
    {
        // Person basic information typically includes name fields
        if (isset($data['name'])) {
            $this->validateNameFields($data['name']);
        }

        // Validate dates if present
        if (isset($data['dateOfBirth'])) {
            $this->validateDate($data['dateOfBirth'], 'Date of birth');
        }

        // Validate gender if present
        if (isset($data['gender'])) {
            $validGenders = ['Male', 'Female', 'Unspecified', 'Unknown'];
            if (!in_array($data['gender'], $validGenders)) {
                throw new JamboJetValidationException(
                    'Invalid gender. Expected one of: ' . implode(', ', $validGenders),
                    400
                );
            }
        }
    }

    /**
     * Validate address data
     * 
     * @param array $data Address data
     * @throws JamboJetValidationException
     */
    private function validateAddressData(array $data): void
    {
        // Required fields for address
        $this->validateRequired($data, ['countryCode']);

        // Validate country code format (ISO 2-letter)
        if (isset($data['countryCode'])) {
            if (!preg_match('/^[A-Z]{2}$/', $data['countryCode'])) {
                throw new JamboJetValidationException(
                    'Country code must be a 2-letter ISO code (e.g., US, GB, KE)',
                    400
                );
            }
        }

        // Validate string lengths
        $lengths = [
            'lineOne' => ['max' => 128],
            'lineTwo' => ['max' => 128],
            'lineThree' => ['max' => 128],
            'city' => ['max' => 64],
            'provinceState' => ['max' => 64],
            'postalCode' => ['max' => 10],
        ];

        foreach ($lengths as $field => $rules) {
            if (isset($data[$field])) {
                $this->validateStringLengths([$field => $data[$field]], [$field => $rules]);
            }
        }
    }

    /**
     * Validate alias data
     * 
     * @param array $data Alias data
     * @throws JamboJetValidationException
     */
    private function validateAliasData(array $data): void
    {
        // Alias requires name structure
        $this->validateRequired($data, ['name']);

        if (isset($data['name'])) {
            $this->validateNameFields($data['name']);
        }
    }

    /**
     * Validate email data
     * 
     * @param array $data Email data
     * @throws JamboJetValidationException
     */
    private function validateEmailData(array $data): void
    {
        $this->validateRequired($data, ['address']);

        // Validate email format
        if (isset($data['address'])) {
            $this->validateFormats(['address' => $data['address']], ['address' => 'email']);
        }

        // Validate email type if provided
        if (isset($data['type'])) {
            // Type should be a single character
            if (strlen($data['type']) !== 1) {
                throw new JamboJetValidationException(
                    'Email type must be a single character',
                    400
                );
            }
        }
    }

    /**
     * Validate phone number data
     * 
     * @param array $data Phone number data
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumberData(array $data): void
    {
        $this->validateRequired($data, ['number']);

        // Validate phone number format (basic validation)
        if (isset($data['number'])) {
            // Remove spaces, dashes, parentheses for validation
            $cleanNumber = preg_replace('/[\s\-\(\)]/', '', $data['number']);

            if (!preg_match('/^\+?[0-9]{10,15}$/', $cleanNumber)) {
                throw new JamboJetValidationException(
                    'Invalid phone number format. Must be 10-15 digits, optionally starting with +',
                    400
                );
            }
        }

        // Validate phone type if provided
        if (isset($data['type'])) {
            $validTypes = ['Home', 'Work', 'Mobile', 'Fax', 'Other'];
            if (!in_array($data['type'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid phone type. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }
    }

    /**
     * Validate stored payment data
     * 
     * @param array $data Stored payment data
     * @throws JamboJetValidationException
     */
    private function validateStoredPaymentData(array $data): void
    {
        $this->validateRequired($data, ['paymentMethodType', 'accountNumber']);

        // Validate payment method type
        if (isset($data['paymentMethodType'])) {
            $validTypes = ['CreditCard', 'DebitCard', 'ExternalAccount', 'MC', 'FlexPay'];
            if (!in_array($data['paymentMethodType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid payment method type. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }

        // Validate card expiration if credit/debit card
        if (
            isset($data['paymentMethodType']) &&
            in_array($data['paymentMethodType'], ['CreditCard', 'DebitCard'])
        ) {

            if (isset($data['expiration'])) {
                // Expiration should be in MMYY or MM/YY format
                if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $data['expiration'])) {
                    throw new JamboJetValidationException(
                        'Invalid expiration date format. Use MMYY or MM/YY',
                        400
                    );
                }
            }
        }

        // Validate account number length (basic check)
        if (isset($data['accountNumber'])) {
            $length = strlen($data['accountNumber']);
            if ($length < 4 || $length > 19) {
                throw new JamboJetValidationException(
                    'Account number must be between 4 and 19 characters',
                    400
                );
            }
        }
    }

    /**
     * Validate name fields structure
     * 
     * @param array $name Name data structure
     * @throws JamboJetValidationException
     */
    private function validateNameFields(array $name): void
    {
        // Validate required name fields
        $this->validateRequired($name, ['first', 'last']);

        // Validate name field lengths
        $lengths = [
            'title' => ['max' => 20],
            'first' => ['max' => 50],
            'middle' => ['max' => 50],
            'last' => ['max' => 50],
            'suffix' => ['max' => 20],
        ];

        foreach ($lengths as $field => $rules) {
            if (isset($name[$field])) {
                $this->validateStringLengths([$field => $name[$field]], [$field => $rules]);
            }
        }

        // Validate name doesn't contain invalid characters
        foreach (['first', 'middle', 'last'] as $field) {
            if (isset($name[$field]) && preg_match('/[0-9]/', $name[$field])) {
                throw new JamboJetValidationException(
                    ucfirst($field) . ' name should not contain numbers',
                    400
                );
            }
        }
    }

    /**
     * Validate a generic key
     * 
     * @param string $key Key value
     * @param string $keyName Key name for error messages
     * @throws JamboJetValidationException
     */
    private function validateKey(string $key, string $keyName): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException(
                $keyName . ' cannot be empty',
                400
            );
        }

        // Keys are typically base64-encoded strings
        if (strlen($key) > 200) {
            throw new JamboJetValidationException(
                $keyName . ' is too long (max 200 characters)',
                400
            );
        }
    }

    /**
     * Validate date format
     * 
     * @param string $date Date string
     * @param string $fieldName Field name for error messages
     * @throws JamboJetValidationException
     */
    private function validateDate(string $date, string $fieldName): void
    {
        // Support ISO 8601 format: YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS
        $formats = ['Y-m-d', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i:s\Z'];

        $valid = false;
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            throw new JamboJetValidationException(
                $fieldName . ' must be in valid date format (YYYY-MM-DD or ISO 8601)',
                400
            );
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
        $this->validateRequired($data, ['roleCode']);

        if (isset($data['roleCode'])) {
            $this->validateStringLengths($data, ['roleCode' => ['max' => 10]]);
        }
    }

    /**
     * Validate user role key
     * 
     * @param string $userRoleKey User role key
     * @throws JamboJetValidationException
     */
    private function validateUserRoleKey(string $userRoleKey): void
    {
        $this->validateKey($userRoleKey, 'User role key');
    }

    /**
     * Validate user key
     * 
     * @param string $userKey
     * @throws JamboJetValidationException
     */
    private function validateUserKey(string $userKey): void
    {
        if (empty($userKey)) {
            throw new JamboJetValidationException('User key is required');
        }
    }

    /**
     * Validate multiple users creation request
     * 
     * @param array $usersData
     * @throws JamboJetValidationException
     */
    private function validateUsersCreateRequest(array $usersData): void
    {
        if (empty($usersData)) {
            throw new JamboJetValidationException('Users data is required');
        }

        if (!is_array($usersData)) {
            throw new JamboJetValidationException('Users data must be an array');
        }
    }

    /**
     * Validate user password change request (agent function)
     * 
     * @param array $passwordData
     * @throws JamboJetValidationException
     */
    private function validateUserPasswordChangeRequest(array $passwordData): void
    {
        if (empty($passwordData['newPassword'])) {
            throw new JamboJetValidationException('New password is required');
        }
    }

    /**
     * Validate booking search criteria
     * 
     * @param array $criteria
     * @throws JamboJetValidationException
     */
    private function validateBookingSearchCriteria(array $criteria): void
    {
        // Optional validation for date formats if provided
        if (isset($criteria['startDate']) && !empty($criteria['startDate'])) {
            if (!$this->isValidDateTime($criteria['startDate'])) {
                throw new JamboJetValidationException('Invalid start date format. Expected ISO 8601 format.');
            }
        }

        if (isset($criteria['endDate']) && !empty($criteria['endDate'])) {
            if (!$this->isValidDateTime($criteria['endDate'])) {
                throw new JamboJetValidationException('Invalid end date format. Expected ISO 8601 format.');
            }
        }
    }

    /**
     * Check if a string is a valid date-time format
     * 
     * @param string $dateTime
     * @return bool
     */
    private function isValidDateTime(string $dateTime): bool
    {
        try {
            new \DateTime($dateTime);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Validate user role creation request
     * 
     * @param array $roleData
     * @throws JamboJetValidationException
     */
    private function validateUserRoleCreateRequest(array $roleData): void
    {
        if (empty($roleData)) {
            throw new JamboJetValidationException('Role data is required');
        }

        // Role code validation
        if (empty($roleData['roleCode'])) {
            throw new JamboJetValidationException('Role code is required');
        }

        // Effective after validation
        if (empty($roleData['effectiveAfter'])) {
            throw new JamboJetValidationException('Effective after date is required');
        }

        if (!$this->isValidDateTime($roleData['effectiveAfter'])) {
            throw new JamboJetValidationException('Invalid effective after date format. Expected ISO 8601 format.');
        }

        // Effective before validation (if provided)
        if (isset($roleData['effectiveBefore']) && !empty($roleData['effectiveBefore'])) {
            if (!$this->isValidDateTime($roleData['effectiveBefore'])) {
                throw new JamboJetValidationException('Invalid effective before date format. Expected ISO 8601 format.');
            }
        }

        // Effective days validation (if provided)
        if (isset($roleData['effectiveDays']) && !empty($roleData['effectiveDays'])) {
            if (!is_array($roleData['effectiveDays'])) {
                throw new JamboJetValidationException('Effective days must be an array');
            }

            // Validate day values (0-6, where 0=Sunday)
            foreach ($roleData['effectiveDays'] as $day) {
                if (!is_int($day) || $day < 0 || $day > 6) {
                    throw new JamboJetValidationException('Effective days must contain integers between 0 and 6 (0=Sunday, 6=Saturday)');
                }
            }
        }
    }

    /**
     * Validate user role patch request (PATCH)
     * 
     * @param array $patchData
     * @throws JamboJetValidationException
     */
    private function validateUserRolePatchRequest(array $patchData): void
    {
        if (empty($patchData)) {
            throw new JamboJetValidationException('Role patch data is required');
        }

        // Validate date fields if provided
        if (isset($patchData['effectiveAfter']) && !empty($patchData['effectiveAfter'])) {
            if (!$this->isValidDateTime($patchData['effectiveAfter'])) {
                throw new JamboJetValidationException('Invalid effective after date format. Expected ISO 8601 format.');
            }
        }

        if (isset($patchData['effectiveBefore']) && !empty($patchData['effectiveBefore'])) {
            if (!$this->isValidDateTime($patchData['effectiveBefore'])) {
                throw new JamboJetValidationException('Invalid effective before date format. Expected ISO 8601 format.');
            }
        }

        // Effective days validation (if provided)
        if (isset($patchData['effectiveDays']) && !empty($patchData['effectiveDays'])) {
            if (!is_array($patchData['effectiveDays'])) {
                throw new JamboJetValidationException('Effective days must be an array');
            }

            foreach ($patchData['effectiveDays'] as $day) {
                if (!is_int($day) || $day < 0 || $day > 6) {
                    throw new JamboJetValidationException('Effective days must contain integers between 0 and 6 (0=Sunday, 6=Saturday)');
                }
            }
        }
    }

    /**
     * Validate preference type
     * 
     * @param string|int $preferenceType
     * @throws JamboJetValidationException
     */
    private function validatePreferenceType($preferenceType): void
    {
        if ($preferenceType === '' || $preferenceType === null) {
            throw new JamboJetValidationException('Preference type is required');
        }

        // Preference types can be: 0 (Default), 1 (Language), 2 (SkySpeed)
        // Or string representations: 'Default', 'Language', 'SkySpeed'
        $validTypes = [0, 1, 2, 'Default', 'Language', 'SkySpeed'];

        if (!in_array($preferenceType, $validTypes, true)) {
            throw new JamboJetValidationException(
                'Invalid preference type. Must be 0-2 or Default/Language/SkySpeed'
            );
        }
    }

    /**
     * Validate preference data
     * 
     * @param array $preferenceData
     * @throws JamboJetValidationException
     */
    private function validatePreferenceData(array $preferenceData): void
    {
        if (empty($preferenceData)) {
            throw new JamboJetValidationException('Preference data is required');
        }

        // Validate SkySpeed settings if applicable
        if (isset($preferenceData['currencyCode'])) {
            $this->validateSkySpeedSettings($preferenceData);
        }
    }

    /**
     * Validate SkySpeed settings
     * 
     * @param array $settings
     * @throws JamboJetValidationException
     */
    private function validateSkySpeedSettings(array $settings): void
    {
        // Validate string length for codes (max 3 characters)
        $codeFields = ['currencyCode', 'cultureCode', 'countryCode', 'nationality', 'collectionCurrencyCode'];

        foreach ($codeFields as $field) {
            if (isset($settings[$field]) && !empty($settings[$field])) {
                if (strlen($settings[$field]) > 3) {
                    throw new JamboJetValidationException(
                        "{$field} must be a maximum of 3 characters"
                    );
                }
            }
        }

        // Validate collection currency setting logic
        if (isset($settings['collectionCurrencySetting']) && isset($settings['collectionCurrencyCode'])) {
            $hasCollectionCurrency = !empty($settings['collectionCurrencyCode']);
            $settingIsNone = $settings['collectionCurrencySetting'] === 0;

            // Rule 1: collectionCurrencySetting can only be None (0) if collectionCurrencyCode is empty
            if ($hasCollectionCurrency && $settingIsNone) {
                throw new JamboJetValidationException(
                    'Collection currency setting cannot be None when collection currency code is provided'
                );
            }

            // Rule 2: collectionCurrencySetting cannot be None if collectionCurrencyCode has value
            if (!$hasCollectionCurrency && !$settingIsNone) {
                throw new JamboJetValidationException(
                    'Collection currency code is required when collection currency setting is not None'
                );
            }
        }
    }

    /**
     * Validate SSO provider key
     * 
     * @param string $providerKey
     * @throws JamboJetValidationException
     */
    private function validateProviderKey(string $providerKey): void
    {
        if (empty($providerKey)) {
            throw new JamboJetValidationException('SSO provider key is required');
        }
    }

    /**
     * Validate SSO token data
     * 
     * @param array $tokenData
     * @throws JamboJetValidationException
     */
    private function validateSsoTokenData(array $tokenData): void
    {
        if (empty($tokenData)) {
            throw new JamboJetValidationException('SSO token data is required');
        }

        // singleSignOn field is required
        if (empty($tokenData['singleSignOn'])) {
            throw new JamboJetValidationException('SSO token (singleSignOn) is required');
        }

        // Validate max length (256 characters)
        if (strlen($tokenData['singleSignOn']) > 256) {
            throw new JamboJetValidationException('SSO token cannot exceed 256 characters');
        }

        // Validate expiration date format if provided
        if (isset($tokenData['expirationDate']) && !empty($tokenData['expirationDate'])) {
            if (!$this->isValidDateTime($tokenData['expirationDate'])) {
                throw new JamboJetValidationException('Invalid expiration date format. Expected ISO 8601 format.');
            }
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
                throw new JamboJetValidationException(
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
                $this->validatePhoneNumber($phone, $index);
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
