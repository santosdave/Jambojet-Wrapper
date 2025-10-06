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
}
