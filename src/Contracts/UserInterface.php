<?php

namespace SantosDave\JamboJet\Contracts;

interface UserInterface
{
    /**
     * Get current user information
     * GET /api/nsk/v#/user
     */
    public function getCurrentUser(): array;

    /**
     * Update current user
     * PUT /api/nsk/v#/user
     */
    public function updateCurrentUser(array $userData): array;

    /**
     * Patch current user
     * PATCH /api/nsk/v#/user
     */
    public function patchCurrentUser(array $patchData): array;

    /**
     * Create user account
     * POST /api/nsk/v#/user
     */
    public function createUser(array $userData): array;

    /**
     * Create multiple users (agent function)
     * POST /api/nsk/v#/users
     */
    public function createUsers(array $usersData): array;

    /**
     * Get users (agent function)
     * GET /api/nsk/v#/users
     */
    public function getUsers(array $criteria = []): array;

    /**
     * Get specific user (agent function)
     * GET /api/nsk/v#/users/{userKey}
     */
    public function getUserByKey(string $userKey): array;

    /**
     * Update user (agent function)
     * PUT /api/nsk/v#/users/{userKey}
     */
    public function updateUser(string $userKey, array $userData): array;

    /**
     * Change user password
     * POST /api/nsk/v#/user/password/change
     */
    public function changePassword(array $passwordData): array;

    /**
     * Delete user (soft delete - sets status to terminated)
     * DELETE /api/nsk/v1/users/{userKey}
     * GraphQL: usersDelete
     * 
     * Requires agent permissions.
     * This is a soft delete that sets the user record status to terminated.
     * 
     * @param string $userKey The unique user key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUser(string $userKey): array;

    /**
     * Reset user password (agent function)
     * POST /api/nsk/v1/users/{userKey}/password/reset
     * GraphQL: usersForgotPassword
     * 
     * Requires agent permissions.
     * Invokes the forgot password reset process for a specific user.
     * 
     * @param string $userKey The unique user key
     * @param array $resetData Password reset request data
     * @return array Reset result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function resetUserPassword(string $userKey, array $resetData = []): array;

    /**
     * Change specific user's password (agent function)
     * POST /api/nsk/v1/users/{userKey}/password/change
     * GraphQL: usersChangePassword
     * 
     * Requires agent permissions.
     * Changes a specific user's password (not the current logged-in user).
     * 
     * @param string $userKey The unique user key
     * @param array $passwordData Password change request data (newPassword)
     * @return array Change result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function changeUserPassword(string $userKey, array $passwordData): array;

    /**
     * Get current user's bookings
     * GET /api/nsk/v1/user/bookings
     * 
     * Searches for upcoming and past bookings for the current logged-in user.
     * 
     * @param array $criteria Optional search criteria (startDate, endDate, etc.)
     * @return array List of bookings
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getCurrentUserBookings(array $criteria = []): array;

    // =================================================================
    // PHASE 2: ROLE MANAGEMENT - NEW METHODS
    // =================================================================

    /**
     * Get all roles for a specific user
     * GET /api/nsk/v1/users/{userKey}/roles
     * 
     * Retrieves all roles assigned to a specific user.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @return array List of user roles
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserRoles(string $userKey): array;

    /**
     * Create a new role for a specific user
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
     * - effectiveDays (array, optional): Days of week role is active
     * 
     * @param string $userKey The unique user key
     * @param array $roleData Role creation data
     * @return array Created role information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserRole(string $userKey, array $roleData): array;

    /**
     * Get a specific role for a specific user
     * GET /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRole
     * 
     * Retrieves detailed information about a specific user role assignment.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array User role details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserRole(string $userKey, string $userRoleKey): array;

    /**
     * Update a specific role for a specific user
     * PUT /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleSet
     * 
     * Updates an existing role assignment (full replacement).
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $roleData Complete role data (effectiveAfter, effectiveBefore, effectiveDays)
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserRole(string $userKey, string $userRoleKey, array $roleData): array;

    /**
     * Patch a specific role for a specific user
     * PATCH /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleModify
     * 
     * Partially updates an existing role assignment.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @param array $patchData Partial role data to update
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserRole(string $userKey, string $userRoleKey, array $patchData): array;

    /**
     * Delete a specific role for a specific user
     * DELETE /api/nsk/v1/users/{userKey}/roles/{userRoleKey}
     * GraphQL: usersRoleDelete
     * 
     * Removes a role assignment from a specific user.
     * Requires agent permissions.
     * 
     * @param string $userKey The unique user key
     * @param string $userRoleKey The unique user role key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserRole(string $userKey, string $userRoleKey): array;

    // =================================================================
    // PHASE 3: IMPERSONATION & USER QUERIES - NEW METHODS
    // =================================================================

    /**
     * Get current impersonation state
     * GET /api/nsk/v1/user/impersonate
     * GraphQL: impersonate
     * 
     * Retrieves the logged-in user's current session roles state.
     * Returns information about whether the user is currently impersonating a role.
     * 
     * RETURNS:
     * - Current impersonation status
     * - Original role information
     * - Impersonated role information (if active)
     * 
     * @return array Current impersonation state with role details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getImpersonationState(): array;

    /**
     * Start role impersonation
     * POST /api/nsk/v1/user/impersonate
     * GraphQL: impersonateSet
     * 
     * Impersonates a new role for the logged-in user.
     * Allows agents to temporarily assume another role for testing or support.
     * 
     * REQUEST DATA:
     * - roleCode (string, required): Role code to impersonate
     * 
     * @param array $impersonationData Impersonation request (roleCode required)
     * @return array Impersonation result (202 Accepted)
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function startImpersonation(array $impersonationData): array;

    /**
     * Reset impersonation to original role
     * DELETE /api/nsk/v1/user/impersonate
     * GraphQL: impersonateDelete
     * 
     * Resets the logged-in user's role to their original state.
     * Ends any active impersonation session.
     * 
     * RESPONSES:
     * - 200: Success (no data returned)
     * - 200 with warning: Not currently impersonating
     * 
     * @return array Reset result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function resetImpersonation(): array;

    /**
     * Get bookings by contact customer number
     * GET /api/nsk/v1/user/bookingsByContactCustomerNumber
     * GraphQL: userBookingByContact
     * 
     * Searches for bookings where the current user is listed as a contact
     * via their customer number.
     * 
     * @param array $criteria Optional search criteria (dates, filters)
     * @return array List of bookings where user is contact
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getBookingsByContactCustomerNumber(array $criteria = []): array;

    /**
     * Get user by person key
     * GET /api/nsk/v1/users/byPerson/{personKey}
     * GraphQL: usersByPerson
     * 
     * Retrieves a specific user by their associated person key.
     * Useful for finding user accounts linked to a person record.
     * 
     * @param string $personKey The person key
     * @return array User information associated with the person
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserByPersonKey(string $personKey): array;

    /**
     * Get bookings for specific user
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
     * @return array List of user's bookings
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserBookings(string $userKey, array $criteria = []): array;

    /**
     * Change password (v3 - enhanced validation)
     * POST /api/nsk/v3/user/password/change
     * GraphQL: userChangePasswordv3
     * 
     * Changes the logged-in user's password with enhanced validation.
     * Validates current password before proceeding.
     * 
     * This follows domain level restrictions and could fail if:
     * - Minimum time requirement between password changes not met
     * - Invalid password length
     * - Invalid characters used
     * 
     * REQUEST DATA:
     * - currentPassword (string, required): Current password
     * - newPassword (string, required): New password
     * 
     * @param array $passwordData Password change request (currentPassword, newPassword)
     * @return array Change result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function changePasswordV3(array $passwordData): array;

    // =================================================================
    // PHASE 4: USER PREFERENCES - NEW METHODS
    // =================================================================

    /**
     * Get all user preferences
     * GET /api/nsk/v1/user/userPreferences
     * 
     * Retrieves all preference types for the current logged-in user.
     * Returns list of available preference types and their current state.
     * 
     * PREFERENCE TYPES:
     * - 0 = Default: Default user preferences
     * - 1 = Language: Language and culture preferences
     * - 2 = SkySpeed: Airline operational preferences (SkySpeed settings)
     * 
     * @return array List of all user preferences by type
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getAllUserPreferences(): array;

    /**
     * Get specific user preference by type
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
     * @param string|int $preferenceType Preference type (0=Default, 1=Language, 2=SkySpeed)
     * @return array Preference details for the specified type
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPreference($preferenceType): array;

    /**
     * Create user preference
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
     * @param string|int $preferenceType Preference type to create
     * @param array $preferenceData Preference data
     * @return array Created preference
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPreference($preferenceType, array $preferenceData): array;

    /**
     * Update user preference (full replacement)
     * PUT /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreferenceSet
     * 
     * Updates an existing preference (full replacement).
     * All fields must be provided.
     * 
     * SKYSPEED SETTINGS STRUCTURE:
     * - currencyCode (string, max 3): Currency code (USD, EUR, etc.)
     * - cultureCode (string, max 3): Culture code (en-US, etc.)
     * - countryCode (string, max 3): Country code (US, KE, etc.)
     * - nationality (string, max 3): Nationality code
     * - collectionCurrencyCode (string, optional): Collection currency
     * - collectionCurrencySetting (int): Collection currency setting
     * - flightResultFareType (int): Flight result fare type
     * 
     * @param string|int $preferenceType Preference type to update
     * @param array $preferenceData Complete preference data
     * @return array Updated preference
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPreference($preferenceType, array $preferenceData): array;

    /**
     * Patch user preference (partial update)
     * PATCH /api/nsk/v1/user/userPreferences/{preferenceType}
     * GraphQL: userPreferenceModify
     * 
     * Partially updates an existing preference.
     * Only specified fields are updated.
     * 
     * @param string|int $preferenceType Preference type to patch
     * @param array $patchData Partial preference data to update
     * @return array Updated preference
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPreference($preferenceType, array $patchData): array;

    /**
     * Get all SSO tokens for current user
     * GET /api/nsk/v1/user/singleSignOnToken
     * GraphQL: singleSignOnTokens
     * 
     * Retrieves all single sign-on tokens associated with the current user.
     * Returns list of SSO providers and their tokens.
     * 
     * @return array List of SSO tokens with provider information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getAllSsoTokens(): array;

    /**
     * Get specific SSO token by provider
     * GET /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnToken
     * 
     * Retrieves a specific SSO token for a provider.
     * 
     * @param string $providerKey SSO provider key
     * @return array SSO token details (providerKey, singleSignOn, expirationDate)
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getSsoToken(string $providerKey): array;

    /**
     * Create/Link SSO token for provider
     * POST /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenAdd
     * 
     * Links a new SSO token with the logged-in user for a specific provider.
     * 
     * REQUEST DATA:
     * - singleSignOn (string, required, max 256): SSO token
     * - expirationDate (datetime, optional): Token expiration
     * 
     * @param string $providerKey SSO provider key
     * @param array $tokenData Token data (singleSignOn, expirationDate)
     * @return array Created token result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createSsoToken(string $providerKey, array $tokenData): array;

    /**
     * Update SSO token (full replacement)
     * PUT /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenSet
     * 
     * Updates an existing SSO token (full replacement).
     * 
     * @param string $providerKey SSO provider key
     * @param array $tokenData Complete token data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateSsoToken(string $providerKey, array $tokenData): array;

    /**
     * Patch SSO token (partial update)
     * PATCH /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenModify
     * 
     * Patches an existing SSO token (partial update).
     * 
     * @param string $providerKey SSO provider key
     * @param array $patchData Partial token data to update
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchSsoToken(string $providerKey, array $patchData): array;

    /**
     * Delete SSO token
     * DELETE /api/nsk/v1/user/singleSignOnToken/{providerKey}
     * GraphQL: singleSignOnTokenDelete
     * 
     * Deletes an SSO token associated with a provider.
     * 
     * @param string $providerKey SSO provider key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteSsoToken(string $providerKey): array;

    /**
     * Create user account v2 (customer)
     * POST /api/nsk/v2/user
     * 
     * Creates a new customer user account using v2 endpoint.
     * Enhanced version with additional features/validation.
     * 
     * @param array $userData User account creation data
     * @return array Created user information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserV2(array $userData): array;

    /**
     * Create multiple users v2 (agent function)
     * POST /api/nsk/v2/users
     * 
     * Creates multiple user accounts using v2 endpoint (agent permissions required).
     * Enhanced version with additional features/validation.
     * 
     * @param array $usersData Array of user data
     * @return array Created users information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUsersV2(array $usersData): array;

    /**
     * Get user SkySpeed preferences v2 (agent function)
     * GET /api/nsk/v2/users/{userKey}/preferences/skySpeedSettings
     * GraphQL: usersPreferencesSkySpeedSettingsv2
     * 
     * Retrieves SkySpeed settings preferences for a specific user (agent function).
     * 
     * @param string $userKey The unique user key
     * @return array SkySpeed settings preferences
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserSkySpeedPreferencesV2(string $userKey): array;

    /**
     * Update user SkySpeed preferences v2 (agent function)
     * PUT /api/nsk/v2/users/{userKey}/preferences/skySpeedSettings
     * GraphQL: usersPreferencesSkySpeedSettingsSetv2
     * 
     * Updates SkySpeed settings preferences for a specific user (agent function).
     * If settings are disabled, they will be automatically enabled on update.
     * 
     * @param string $userKey The unique user key
     * @param array $preferencesData SkySpeed settings data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserSkySpeedPreferencesV2(string $userKey, array $preferencesData): array;

    /**
     * Get user's person record
     * GET /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPerson
     * 
     * Retrieves the complete person record associated with a specific user.
     * 
     * @param string $userKey The unique user key
     * @return array Person record with all details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPerson(string $userKey): array;

    /**
     * Update user's person record
     * PUT /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPersonSet
     * 
     * Updates the user's person record basic information (full replacement).
     * 
     * @param string $userKey The unique user key
     * @param array $personData Complete person data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPerson(string $userKey, array $personData): array;

    /**
     * Patch user's person record
     * PATCH /api/nsk/v1/users/{userKey}/person
     * GraphQL: usersPersonModify
     * 
     * Partially updates the user's person record basic information.
     * 
     * @param string $userKey The unique user key
     * @param array $patchData Partial person data to update
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPerson(string $userKey, array $patchData): array;

    /**
     * Get all addresses for user's person
     * GET /api/nsk/v1/users/{userKey}/person/addresses
     * 
     * @param string $userKey The unique user key
     * @return array List of addresses
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAddresses(string $userKey): array;

    /**
     * Create address for user's person
     * POST /api/nsk/v1/users/{userKey}/person/addresses
     * GraphQL: usersPersonAddressAdd
     * 
     * @param string $userKey The unique user key
     * @param array $addressData Address data
     * @return array Created address
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonAddress(string $userKey, array $addressData): array;

    /**
     * Get specific address for user's person
     * GET /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddress
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @return array Address details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAddress(string $userKey, string $personAddressKey): array;

    /**
     * Update address for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @param array $addressData Complete address data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonAddress(string $userKey, string $personAddressKey, array $addressData): array;

    /**
     * Patch address for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressModify
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @param array $patchData Partial address data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonAddress(string $userKey, string $personAddressKey, array $patchData): array;

    /**
     * Delete address for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/addresses/{personAddressKey}
     * GraphQL: usersPersonAddressDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAddressKey The unique person address key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonAddress(string $userKey, string $personAddressKey): array;

    /**
     * Get all affiliations for user's person
     * GET /api/nsk/v1/users/{userKey}/person/affiliations
     * GraphQL: usersPersonAffiliations
     * 
     * @param string $userKey The unique user key
     * @return array List of affiliations
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAffiliations(string $userKey): array;

    /**
     * Create affiliation for user's person
     * POST /api/nsk/v1/users/{userKey}/person/affiliations
     * GraphQL: usersPersonAffiliationAdd
     * 
     * @param string $userKey The unique user key
     * @param array $affiliationData Affiliation data
     * @return array Created affiliation
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonAffiliation(string $userKey, array $affiliationData): array;

    /**
     * Get specific affiliation for user's person
     * GET /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliation
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @return array Affiliation details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAffiliation(string $userKey, string $personAffiliationKey): array;

    /**
     * Update affiliation for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliationSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @param array $affiliationData Complete affiliation data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonAffiliation(string $userKey, string $personAffiliationKey, array $affiliationData): array;

    /**
     * Delete affiliation for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/affiliations/{personAffiliationKey}
     * GraphQL: usersPersonAffiliationDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAffiliationKey The unique person affiliation key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonAffiliation(string $userKey, string $personAffiliationKey): array;

    /**
     * Get all aliases for user's person
     * GET /api/nsk/v1/users/{userKey}/person/aliases
     * 
     * @param string $userKey The unique user key
     * @return array List of aliases
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAliases(string $userKey): array;

    /**
     * Create alias for user's person
     * POST /api/nsk/v1/users/{userKey}/person/aliases
     * GraphQL: usersPersonAliasAdd
     * 
     * @param string $userKey The unique user key
     * @param array $aliasData Alias data
     * @return array Created alias
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonAlias(string $userKey, array $aliasData): array;

    /**
     * Get specific alias for user's person
     * GET /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAlias
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @return array Alias details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonAlias(string $userKey, string $personAliasKey): array;

    /**
     * Update alias for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasSet
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @param array $aliasData Complete alias data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonAlias(string $userKey, string $personAliasKey, array $aliasData): array;

    /**
     * Patch alias for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasModify
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @param array $patchData Partial alias data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonAlias(string $userKey, string $personAliasKey, array $patchData): array;

    /**
     * Delete alias for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/aliases/{personAliasKey}
     * GraphQL: usersPersonAliasDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personAliasKey The unique person alias key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonAlias(string $userKey, string $personAliasKey): array;

    /**
     * Get all comments for user's person
     * GET /api/nsk/v1/users/{userKey}/person/comments
     * GraphQL: usersPersonComments
     * 
     * @param string $userKey The unique user key
     * @return array List of comments
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonComments(string $userKey): array;

    /**
     * Create comment for user's person
     * POST /api/nsk/v1/users/{userKey}/person/comments
     * GraphQL: usersPersonCommentAdd
     * 
     * @param string $userKey The unique user key
     * @param array $commentData Comment data
     * @return array Created comment
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonComment(string $userKey, array $commentData): array;

    /**
     * Get specific comment for user's person
     * GET /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonComment
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @return array Comment details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonComment(string $userKey, string $personCommentKey): array;

    /**
     * Update comment for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentSet
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @param array $commentData Complete comment data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonComment(string $userKey, string $personCommentKey, array $commentData): array;

    /**
     * Patch comment for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentModify
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @param array $patchData Partial comment data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonComment(string $userKey, string $personCommentKey, array $patchData): array;

    /**
     * Delete comment for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/comments/{personCommentKey}
     * GraphQL: usersPersonCommentDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personCommentKey The unique person comment key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonComment(string $userKey, string $personCommentKey): array;

    // =================================================================
    // PHASE 10: PERSON EMAILS
    // =================================================================

    /**
     * Get all emails for user's person
     * GET /api/nsk/v1/users/{userKey}/person/emails
     * 
     * @param string $userKey The unique user key
     * @return array List of emails
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonEmails(string $userKey): array;

    /**
     * Create email for user's person
     * POST /api/nsk/v1/users/{userKey}/person/emails
     * GraphQL: usersPersonEmailAdd
     * 
     * @param string $userKey The unique user key
     * @param array $emailData Email data
     * @return array Created email
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonEmail(string $userKey, array $emailData): array;

    /**
     * Get specific email for user's person
     * GET /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmail
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @return array Email details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonEmail(string $userKey, string $personEmailAddressKey): array;

    /**
     * Update email for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailSet
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @param array $emailData Complete email data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonEmail(string $userKey, string $personEmailAddressKey, array $emailData): array;

    /**
     * Patch email for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailModify
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @param array $patchData Partial email data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonEmail(string $userKey, string $personEmailAddressKey, array $patchData): array;

    /**
     * Delete email for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/emails/{personEmailAddressKey}
     * GraphQL: usersPersonEmailDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personEmailAddressKey The unique person email address key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonEmail(string $userKey, string $personEmailAddressKey): array;

    // =================================================================
    // PHASE 11: PERSON INFORMATION
    // =================================================================

    /**
     * Get all information for user's person
     * GET /api/nsk/v1/users/{userKey}/person/information
     * GraphQL: usersPersonInformations
     * 
     * @param string $userKey The unique user key
     * @return array List of information records
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonInformation(string $userKey): array;

    /**
     * Create information for user's person
     * POST /api/nsk/v1/users/{userKey}/person/information
     * GraphQL: usersPersonInformationAdd
     * 
     * @param string $userKey The unique user key
     * @param array $informationData Information data
     * @return array Created information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonInformation(string $userKey, array $informationData): array;

    /**
     * Get specific information for user's person
     * GET /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformation
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @return array Information details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonInformationByKey(string $userKey, string $personInformationKey): array;

    /**
     * Update information for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationSet
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @param array $informationData Complete information data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonInformation(string $userKey, string $personInformationKey, array $informationData): array;

    /**
     * Patch information for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationModify
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @param array $patchData Partial information data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonInformation(string $userKey, string $personInformationKey, array $patchData): array;

    /**
     * Delete information for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/information/{personInformationKey}
     * GraphQL: usersPersonInformationDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personInformationKey The unique person information key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonInformation(string $userKey, string $personInformationKey): array;

    /**
     * Get all phone numbers for user's person
     * GET /api/nsk/v1/users/{userKey}/person/phoneNumbers
     * 
     * @param string $userKey The unique user key
     * @return array List of phone numbers
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonPhoneNumbers(string $userKey): array;

    /**
     * Create phone number for user's person
     * POST /api/nsk/v1/users/{userKey}/person/phoneNumbers
     * GraphQL: usersPersonPhoneNumberAdd
     * 
     * @param string $userKey The unique user key
     * @param array $phoneData Phone number data
     * @return array Created phone number
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonPhoneNumber(string $userKey, array $phoneData): array;

    /**
     * Get specific phone number for user's person
     * GET /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumber
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @return array Phone number details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey): array;

    /**
     * Update phone number for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberSet
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @param array $phoneData Complete phone number data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey, array $phoneData): array;

    /**
     * Patch phone number for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberModify
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @param array $patchData Partial phone number data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey, array $patchData): array;

    /**
     * Delete phone number for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/phoneNumbers/{personPhoneNumberKey}
     * GraphQL: usersPersonPhoneNumberDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personPhoneNumberKey The unique person phone number key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonPhoneNumber(string $userKey, string $personPhoneNumberKey): array;

    // =================================================================
    // PHASE 13: PERSON PREFERENCES
    // =================================================================

    /**
     * Get all preferences for user's person
     * GET /api/nsk/v1/users/{userKey}/person/preferences
     * 
     * @param string $userKey The unique user key
     * @return array List of preferences
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonPreferences(string $userKey): array;

    /**
     * Create preference for user's person
     * POST /api/nsk/v1/users/{userKey}/person/preferences
     * GraphQL: usersPersonPreferenceAdd
     * 
     * @param string $userKey The unique user key
     * @param array $preferenceData Preference data
     * @return array Created preference
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonPreference(string $userKey, array $preferenceData): array;

    /**
     * Get specific preference for user's person
     * GET /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreference
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @return array Preference details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonPreference(string $userKey, string $personPreferenceKey): array;

    /**
     * Update preference for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceSet
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @param array $preferenceData Complete preference data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonPreference(string $userKey, string $personPreferenceKey, array $preferenceData): array;

    /**
     * Patch preference for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceModify
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @param array $patchData Partial preference data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonPreference(string $userKey, string $personPreferenceKey, array $patchData): array;

    /**
     * Delete preference for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/preferences/{personPreferenceKey}
     * GraphQL: usersPersonPreferenceDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personPreferenceKey The unique person preference key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonPreference(string $userKey, string $personPreferenceKey): array;

    // =================================================================
    // PHASE 14: PERSON PROGRAMS
    // =================================================================

    /**
     * Get all programs for user's person
     * GET /api/nsk/v1/users/{userKey}/person/programs
     * GraphQL: usersPersonPrograms
     * 
     * @param string $userKey The unique user key
     * @return array List of programs
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonPrograms(string $userKey): array;

    /**
     * Create program for user's person
     * POST /api/nsk/v1/users/{userKey}/person/programs
     * GraphQL: usersPersonProgramAdd
     * 
     * @param string $userKey The unique user key
     * @param array $programData Program data
     * @return array Created program
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonProgram(string $userKey, array $programData): array;

    /**
     * Get specific program for user's person
     * GET /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgram
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @return array Program details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonProgram(string $userKey, string $personProgramKey): array;

    /**
     * Update program for user's person
     * PUT /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgramSet
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @param array $programData Complete program data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function updateUserPersonProgram(string $userKey, string $personProgramKey, array $programData): array;

    /**
     * Delete program for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/programs/{personProgramKey}
     * GraphQL: usersPersonProgramDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personProgramKey The unique person program key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonProgram(string $userKey, string $personProgramKey): array;

    // =================================================================
    // PHASE 15: PERSON STORED PAYMENTS
    // =================================================================

    /**
     * Get all stored payments for user's person
     * GET /api/nsk/v1/users/{userKey}/person/storedPayments
     * GraphQL: usersPersonStoredPayments
     * 
     * @param string $userKey The unique user key
     * @return array List of stored payments
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonStoredPayments(string $userKey): array;

    /**
     * Create stored payment for user's person
     * POST /api/nsk/v1/users/{userKey}/person/storedPayments
     * GraphQL: usersPersonStoredPaymentAdd
     * 
     * @param string $userKey The unique user key
     * @param array $paymentData Stored payment data
     * @return array Created stored payment
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function createUserPersonStoredPayment(string $userKey, array $paymentData): array;

    /**
     * Get specific stored payment for user's person
     * GET /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPayment
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @return array Stored payment details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function getUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey): array;

    /**
     * Patch stored payment for user's person
     * PATCH /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPaymentModify
     * 
     * NOTE: To update account number, DELETE and POST a new stored payment
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @param array $patchData Partial stored payment data
     * @return array Update result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function patchUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey, array $patchData): array;

    /**
     * Delete stored payment for user's person
     * DELETE /api/nsk/v1/users/{userKey}/person/storedPayments/{personStoredPaymentKey}
     * GraphQL: usersPersonStoredPaymentDelete
     * 
     * @param string $userKey The unique user key
     * @param string $personStoredPaymentKey The unique person stored payment key
     * @return array Deletion result
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetApiException
     */
    public function deleteUserPersonStoredPayment(string $userKey, string $personStoredPaymentKey): array;
}
