<?php

namespace SantosDave\JamboJet\Contracts;

interface NavigationInterface
{
    /**
     * Get next action for booking in state
     * POST /api/nsk/v1/booking/navigation/getNextAction
     */
    public function getNextAction(array $contextData = []): array;

    /**
     * Get all available navigation actions for booking
     * POST /api/nsk/v1/booking/navigation/getNavigationActions
     */
    public function getNavigationActions(array $actionCriteria = []): array;

    /**
     * Get booking workflow status
     * GET /api/nsk/v1/booking/navigation/status
     */
    public function getWorkflowStatus(): array;

    /**
     * Validate booking state for specific action
     * POST /api/nsk/v1/booking/navigation/validateAction
     */
    public function validateAction(string $actionType, array $validationData = []): array;

    /**
     * Get available navigation paths from current state
     * GET /api/nsk/v1/booking/navigation/paths
     */
    public function getAvailablePaths(): array;

    /**
     * Execute navigation action
     * POST /api/nsk/v1/booking/navigation/executeAction
     */
    public function executeAction(string $actionType, array $actionData): array;
}
