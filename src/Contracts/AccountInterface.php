<?php

namespace SantosDave\JamboJet\Contracts;

interface AccountInterface
{
    /**
     * Change account password
     * POST /api/nsk/v#/account/password/change
     */
    public function changePassword(array $credentials, string $newPassword): array;

    /**
     * Reset account password
     * POST /api/nsk/v#/account/password/reset
     */
    public function resetPassword(array $resetData): array;

    /**
     * Create account
     * POST /api/nsk/v#/accounts
     */
    public function createAccount(array $accountData): array;

    /**
     * Create account collection for booking
     * POST /api/nsk/v#/bookings/{recordLocator}/account/collection
     */
    public function createAccountCollection(string $recordLocator, array $collectionData): array;
}
