<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\NavigationInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Navigation Service for JamboJet NSK API
 * 
 * Handles booking workflow navigation, determining next actions, validating states,
 * and guiding users through the booking process
 * Base endpoints: /api/nsk/v1/booking/navigation
 * 
 * Supported endpoints:
 * - POST /api/nsk/v1/booking/navigation/getNextAction - Get next recommended action
 * - POST /api/nsk/v1/booking/navigation/getNavigationActions - Get available actions
 * - GET /api/nsk/v1/booking/navigation/status - Get workflow status
 * - POST /api/nsk/v1/booking/navigation/validateAction - Validate action possibility
 * - GET /api/nsk/v1/booking/navigation/paths - Get available navigation paths
 * - POST /api/nsk/v1/booking/navigation/executeAction - Execute navigation action
 * 
 * @package SantosDave\JamboJet\Services
 */
class NavigationService implements NavigationInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - BOOKING NAVIGATION
    // =================================================================

    /**
     * Get next action for booking in state
     * 
     * POST /api/nsk/v1/booking/navigation/getNextAction
     * Determines the next recommended action based on current booking state
     * 
     * @param array $contextData Optional context data for decision making
     * @return array Next action recommendation
     * @throws JamboJetApiException
     */
    public function getNextAction(array $contextData = []): array
    {
        $this->validateContextData($contextData);

        try {
            return $this->post('api/nsk/v1/booking/navigation/getNextAction', $contextData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get next action: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all available navigation actions for booking
     * 
     * POST /api/nsk/v1/booking/navigation/getNavigationActions
     * Retrieves all possible actions that can be performed on the current booking
     * 
     * @param array $actionCriteria Action filtering criteria
     * @return array Available navigation actions
     * @throws JamboJetApiException
     */
    public function getNavigationActions(array $actionCriteria = []): array
    {
        $this->validateActionCriteria($actionCriteria);

        try {
            return $this->post('api/nsk/v1/booking/navigation/getNavigationActions', $actionCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get navigation actions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking workflow status
     * 
     * GET /api/nsk/v1/booking/navigation/status
     * Retrieves the current status and progress of the booking workflow
     * 
     * @return array Workflow status information
     * @throws JamboJetApiException
     */
    public function getWorkflowStatus(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/navigation/status');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get workflow status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate booking state for specific action
     * 
     * POST /api/nsk/v1/booking/navigation/validateAction
     * Checks if a specific action can be performed on the current booking
     * 
     * @param string $actionType Type of action to validate
     * @param array $validationData Additional validation data
     * @return array Validation result
     * @throws JamboJetApiException
     */
    public function validateAction(string $actionType, array $validationData = []): array
    {
        $this->validateActionType($actionType);
        $this->validateActionValidationData($validationData);

        $requestData = array_merge($validationData, ['actionType' => $actionType]);

        try {
            return $this->post('api/nsk/v1/booking/navigation/validateAction', $requestData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate action: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available navigation paths from current state
     * 
     * GET /api/nsk/v1/booking/navigation/paths
     * Retrieves all possible workflow paths from the current booking state
     * 
     * @return array Available navigation paths
     * @throws JamboJetApiException
     */
    public function getAvailablePaths(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/navigation/paths');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available paths: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute navigation action
     * 
     * POST /api/nsk/v1/booking/navigation/executeAction
     * Executes a specific navigation action on the current booking
     * 
     * @param string $actionType Type of action to execute
     * @param array $actionData Action execution data
     * @return array Execution result
     * @throws JamboJetApiException
     */
    public function executeAction(string $actionType, array $actionData): array
    {
        $this->validateActionType($actionType);
        $this->validateActionExecutionData($actionData);

        $requestData = array_merge($actionData, ['actionType' => $actionType]);

        try {
            return $this->post('api/nsk/v1/booking/navigation/executeAction', $requestData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to execute action: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // CONVENIENCE METHODS
    // =================================================================

    /**
     * Check if booking can proceed to payment
     * 
     * @return array Payment readiness status
     */
    public function canProceedToPayment(): array
    {
        return $this->validateAction('ProceedToPayment');
    }

    /**
     * Check if booking can be committed
     * 
     * @return array Commit readiness status
     */
    public function canCommitBooking(): array
    {
        return $this->validateAction('CommitBooking');
    }

    /**
     * Get available add-on actions
     * 
     * @return array Add-on actions
     */
    public function getAddOnActions(): array
    {
        return $this->getNavigationActions(['actionCategory' => 'AddOns']);
    }

    /**
     * Get available passenger actions
     * 
     * @return array Passenger actions
     */
    public function getPassengerActions(): array
    {
        return $this->getNavigationActions(['actionCategory' => 'Passengers']);
    }

    /**
     * Get recommended next steps for completing booking
     * 
     * @return array Recommended completion steps
     */
    public function getCompletionSteps(): array
    {
        return $this->getNextAction(['goalState' => 'BookingComplete']);
    }

    /**
     * Check booking completion percentage
     * 
     * @return array Completion status with percentage
     */
    public function getBookingProgress(): array
    {
        $status = $this->getWorkflowStatus();

        return [
            'completionPercentage' => $status['data']['completionPercentage'] ?? 0,
            'currentStage' => $status['data']['currentStage'] ?? 'Unknown',
            'nextStage' => $status['data']['nextStage'] ?? null,
            'remainingSteps' => $status['data']['remainingSteps'] ?? []
        ];
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND COMPLETE
    // =================================================================

    /**
     * Validate context data for next action request
     * 
     * @param array $contextData Context data
     * @throws JamboJetValidationException
     */
    private function validateContextData(array $contextData): void
    {
        // Context data is optional, but validate if provided

        if (isset($contextData['goalState'])) {
            $validGoalStates = [
                'BookingComplete',
                'PaymentComplete',
                'PassengersComplete',
                'AddOnsComplete',
                'SeatSelectionComplete',
                'BaggageComplete'
            ];
            if (!in_array($contextData['goalState'], $validGoalStates)) {
                throw new JamboJetValidationException(
                    'Invalid goal state. Expected one of: ' . implode(', ', $validGoalStates)
                );
            }
        }

        if (isset($contextData['priority'])) {
            $validPriorities = ['Low', 'Normal', 'High', 'Critical'];
            if (!in_array($contextData['priority'], $validPriorities)) {
                throw new JamboJetValidationException(
                    'Invalid priority. Expected one of: ' . implode(', ', $validPriorities)
                );
            }
        }

        if (isset($contextData['userType'])) {
            $validUserTypes = ['Customer', 'Agent', 'System'];
            if (!in_array($contextData['userType'], $validUserTypes)) {
                throw new JamboJetValidationException(
                    'Invalid user type. Expected one of: ' . implode(', ', $validUserTypes)
                );
            }
        }

        if (isset($contextData['timeConstraint'])) {
            if (!is_numeric($contextData['timeConstraint']) || $contextData['timeConstraint'] <= 0) {
                throw new JamboJetValidationException(
                    'Time constraint must be a positive number (in minutes)'
                );
            }
        }

        if (isset($contextData['preferences']) && !is_array($contextData['preferences'])) {
            throw new JamboJetValidationException('Preferences must be an array');
        }
    }

    /**
     * Validate action criteria
     * 
     * @param array $actionCriteria Action criteria
     * @throws JamboJetValidationException
     */
    private function validateActionCriteria(array $actionCriteria): void
    {
        if (isset($actionCriteria['actionCategory'])) {
            $validCategories = [
                'Booking',
                'Passengers',
                'Payment',
                'AddOns',
                'Seats',
                'Baggage',
                'Loyalty',
                'Notifications',
                'Cancellation'
            ];
            if (!in_array($actionCriteria['actionCategory'], $validCategories)) {
                throw new JamboJetValidationException(
                    'Invalid action category. Expected one of: ' . implode(', ', $validCategories)
                );
            }
        }

        if (isset($actionCriteria['scope'])) {
            $validScopes = ['Required', 'Optional', 'Recommended', 'All'];
            if (!in_array($actionCriteria['scope'], $validScopes)) {
                throw new JamboJetValidationException(
                    'Invalid scope. Expected one of: ' . implode(', ', $validScopes)
                );
            }
        }

        if (isset($actionCriteria['includeCompleted']) && !is_bool($actionCriteria['includeCompleted'])) {
            throw new JamboJetValidationException('includeCompleted must be a boolean value');
        }

        if (isset($actionCriteria['userRole'])) {
            $validRoles = ['Customer', 'Agent', 'Administrator'];
            if (!in_array($actionCriteria['userRole'], $validRoles)) {
                throw new JamboJetValidationException(
                    'Invalid user role. Expected one of: ' . implode(', ', $validRoles)
                );
            }
        }
    }

    /**
     * Validate action type
     * 
     * @param string $actionType Action type
     * @throws JamboJetValidationException
     */
    private function validateActionType(string $actionType): void
    {
        if (empty(trim($actionType))) {
            throw new JamboJetValidationException('Action type cannot be empty');
        }

        $validActionTypes = [
            'AddPassenger',
            'RemovePassenger',
            'UpdatePassenger',
            'AddSeat',
            'RemoveSeat',
            'UpdateSeat',
            'AddBaggage',
            'RemoveBaggage',
            'UpdateBaggage',
            'AddLoyaltyProgram',
            'RemoveLoyaltyProgram',
            'AddPayment',
            'ProcessPayment',
            'RefundPayment',
            'CommitBooking',
            'CancelBooking',
            'HoldBooking',
            'ProceedToPayment',
            'SendConfirmation',
            'GenerateTickets',
            'AddInsurance',
            'AddActivity',
            'AddHotel',
            'AddCar'
        ];

        if (!in_array($actionType, $validActionTypes)) {
            throw new JamboJetValidationException(
                'Invalid action type. Expected one of: ' . implode(', ', $validActionTypes)
            );
        }
    }

    /**
     * Validate action validation data
     * 
     * @param array $validationData Validation data
     * @throws JamboJetValidationException
     */
    private function validateActionValidationData(array $validationData): void
    {
        // Validation data is optional but should be validated if provided

        if (isset($validationData['passengerKey'])) {
            $this->validatePassengerKey($validationData['passengerKey']);
        }

        if (isset($validationData['segmentKey'])) {
            $this->validateSegmentKey($validationData['segmentKey']);
        }

        if (isset($validationData['amount'])) {
            if (!is_numeric($validationData['amount']) || $validationData['amount'] < 0) {
                throw new JamboJetValidationException(
                    'Amount must be a non-negative number'
                );
            }
        }

        if (isset($validationData['currency'])) {
            $this->validateFormats($validationData, ['currency' => 'currency_code']);
        }

        if (isset($validationData['validateOnly']) && !is_bool($validationData['validateOnly'])) {
            throw new JamboJetValidationException('validateOnly must be a boolean value');
        }
    }

    /**
     * Validate action execution data
     * 
     * @param array $actionData Action execution data
     * @throws JamboJetValidationException
     */
    private function validateActionExecutionData(array $actionData): void
    {
        // Execution data validation depends on action type
        // Common validations for all action types

        if (isset($actionData['passengerKey'])) {
            $this->validatePassengerKey($actionData['passengerKey']);
        }

        if (isset($actionData['segmentKey'])) {
            $this->validateSegmentKey($actionData['segmentKey']);
        }

        if (isset($actionData['amount'])) {
            if (!is_numeric($actionData['amount']) || $actionData['amount'] < 0) {
                throw new JamboJetValidationException(
                    'Amount must be a non-negative number'
                );
            }
        }

        if (isset($actionData['currency'])) {
            $this->validateFormats($actionData, ['currency' => 'currency_code']);
        }

        if (isset($actionData['confirmAction']) && !is_bool($actionData['confirmAction'])) {
            throw new JamboJetValidationException('confirmAction must be a boolean value');
        }

        if (isset($actionData['bypassWarnings']) && !is_bool($actionData['bypassWarnings'])) {
            throw new JamboJetValidationException('bypassWarnings must be a boolean value');
        }

        // Validate metadata if provided
        if (isset($actionData['metadata'])) {
            if (!is_array($actionData['metadata'])) {
                throw new JamboJetValidationException('Metadata must be an array');
            }
        }

        // Validate execution options
        if (isset($actionData['executionOptions'])) {
            $this->validateExecutionOptions($actionData['executionOptions']);
        }
    }

    /**
     * Validate execution options
     * 
     * @param array $options Execution options
     * @throws JamboJetValidationException
     */
    private function validateExecutionOptions(array $options): void
    {
        if (isset($options['async']) && !is_bool($options['async'])) {
            throw new JamboJetValidationException('async option must be a boolean value');
        }

        if (isset($options['timeout'])) {
            if (!is_numeric($options['timeout']) || $options['timeout'] <= 0) {
                throw new JamboJetValidationException(
                    'Timeout must be a positive number (in seconds)'
                );
            }
        }

        if (isset($options['retryCount'])) {
            if (!is_int($options['retryCount']) || $options['retryCount'] < 0) {
                throw new JamboJetValidationException(
                    'Retry count must be a non-negative integer'
                );
            }
        }

        if (isset($options['priority'])) {
            $validPriorities = ['Low', 'Normal', 'High'];
            if (!in_array($options['priority'], $validPriorities)) {
                throw new JamboJetValidationException(
                    'Invalid priority. Expected one of: ' . implode(', ', $validPriorities)
                );
            }
        }
    }

    /**
     * Validate passenger key
     * 
     * @param string $passengerKey Passenger key
     * @throws JamboJetValidationException
     */
    private function validatePassengerKey(string $passengerKey): void
    {
        if (empty(trim($passengerKey))) {
            throw new JamboJetValidationException('Passenger key cannot be empty');
        }

        if (strlen($passengerKey) < 5) {
            throw new JamboJetValidationException('Invalid passenger key format');
        }
    }

    /**
     * Validate segment key
     * 
     * @param string $segmentKey Segment key
     * @throws JamboJetValidationException
     */
    private function validateSegmentKey(string $segmentKey): void
    {
        if (empty(trim($segmentKey))) {
            throw new JamboJetValidationException('Segment key cannot be empty');
        }

        if (strlen($segmentKey) < 5) {
            throw new JamboJetValidationException('Invalid segment key format');
        }
    }
}
