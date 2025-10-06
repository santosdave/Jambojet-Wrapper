<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Navigation Action Request for JamboJet NSK API
 * 
 * Used with: 
 * - POST /api/nsk/v1/booking/navigation/getNextAction
 * - POST /api/nsk/v1/booking/navigation/getNavigationActions
 * - POST /api/nsk/v1/booking/navigation/validateAction
 * - POST /api/nsk/v1/booking/navigation/executeAction
 * 
 * @package SantosDave\JamboJet\Requests
 */
class NavigationActionRequest extends BaseRequest
{
    /**
     * Create a new navigation action request
     * 
     * @param string $requestType Required: Type of navigation request
     *   Values: 'getNextAction', 'getNavigationActions', 'validateAction', 'executeAction'
     * @param string|null $actionType Optional: Specific action type to validate/execute
     * @param array|null $contextData Optional: Context data for decision making
     * @param array|null $actionCriteria Optional: Criteria for filtering available actions
     * @param array|null $actionData Optional: Data for action execution
     * @param string|null $goalState Optional: Target goal state for navigation
     * @param string|null $actionCategory Optional: Category filter for actions
     * @param bool $includeWarnings Optional: Include validation warnings
     * @param bool $includeRecommendations Optional: Include action recommendations
     */
    public function __construct(
        public string $requestType,
        public ?string $actionType = null,
        public ?array $contextData = null,
        public ?array $actionCriteria = null,
        public ?array $actionData = null,
        public ?string $goalState = null,
        public ?string $actionCategory = null,
        public bool $includeWarnings = true,
        public bool $includeRecommendations = true
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        switch ($this->requestType) {
            case 'getNextAction':
                return $this->toNextActionArray();
            case 'getNavigationActions':
                return $this->toNavigationActionsArray();
            case 'validateAction':
                return $this->toValidateActionArray();
            case 'executeAction':
                return $this->toExecuteActionArray();
            default:
                return $this->filterNulls([
                    'contextData' => $this->contextData,
                    'actionCriteria' => $this->actionCriteria
                ]);
        }
    }

    /**
     * Convert to next action request array
     * 
     * @return array Next action request data
     */
    private function toNextActionArray(): array
    {
        return $this->filterNulls([
            'goalState' => $this->goalState,
            'contextData' => $this->contextData,
            'includeRecommendations' => $this->includeRecommendations
        ]);
    }

    /**
     * Convert to navigation actions request array
     * 
     * @return array Navigation actions request data
     */
    private function toNavigationActionsArray(): array
    {
        return $this->filterNulls([
            'actionCategory' => $this->actionCategory,
            'actionCriteria' => $this->actionCriteria,
            'includeWarnings' => $this->includeWarnings
        ]);
    }

    /**
     * Convert to validate action request array
     * 
     * @return array Validate action request data
     */
    private function toValidateActionArray(): array
    {
        return $this->filterNulls([
            'actionType' => $this->actionType,
            'actionData' => $this->actionData,
            'contextData' => $this->contextData,
            'includeWarnings' => $this->includeWarnings
        ]);
    }

    /**
     * Convert to execute action request array
     * 
     * @return array Execute action request data
     */
    private function toExecuteActionArray(): array
    {
        return $this->filterNulls([
            'actionType' => $this->actionType,
            'actionData' => $this->actionData,
            'contextData' => $this->contextData
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate request type
        $validTypes = ['getNextAction', 'getNavigationActions', 'validateAction', 'executeAction'];
        if (!in_array($this->requestType, $validTypes)) {
            throw new JamboJetValidationException('requestType must be one of: ' . implode(', ', $validTypes));
        }

        // Validate based on request type
        switch ($this->requestType) {
            case 'validateAction':
            case 'executeAction':
                $this->validateActionRequest();
                break;
            case 'getNextAction':
                $this->validateNextActionRequest();
                break;
            case 'getNavigationActions':
                $this->validateNavigationActionsRequest();
                break;
        }

        // Validate boolean flags
        if (!is_bool($this->includeWarnings)) {
            throw new JamboJetValidationException('includeWarnings must be a boolean');
        }

        if (!is_bool($this->includeRecommendations)) {
            throw new JamboJetValidationException('includeRecommendations must be a boolean');
        }
    }

    /**
     * Validate action validation/execution request
     * 
     * @throws JamboJetValidationException
     */
    private function validateActionRequest(): void
    {
        // Action type is required for validation and execution
        if ($this->actionType === null || empty(trim($this->actionType))) {
            throw new JamboJetValidationException('actionType is required for ' . $this->requestType);
        }

        // Validate action type format
        $validActionTypes = [
            'AddPassenger',
            'RemovePassenger',
            'UpdatePassenger',
            'AddJourney',
            'RemoveJourney',
            'ChangeJourney',
            'AddAddOn',
            'RemoveAddOn',
            'UpdateAddOn',
            'ProceedToPayment',
            'ProcessPayment',
            'CommitBooking',
            'AddSeat',
            'RemoveSeat',
            'ChangeSeat',
            'AddBundle',
            'RemoveBundle',
            'UpdateBundle',
            'ValidateBooking',
            'CancelBooking'
        ];

        if (!in_array($this->actionType, $validActionTypes)) {
            throw new JamboJetValidationException('actionType must be one of: ' . implode(', ', $validActionTypes));
        }

        // Validate action data structure if provided
        if ($this->actionData !== null) {
            if (!is_array($this->actionData)) {
                throw new JamboJetValidationException('actionData must be an array');
            }

            // Validate based on action type
            $this->validateActionDataByType();
        }

        // Validate context data if provided
        if ($this->contextData !== null) {
            $this->validateContextData();
        }
    }

    /**
     * Validate next action request
     * 
     * @throws JamboJetValidationException
     */
    private function validateNextActionRequest(): void
    {
        // Validate goal state if provided
        if ($this->goalState !== null) {
            $validGoalStates = [
                'BookingComplete',
                'PaymentReady',
                'PassengersComplete',
                'JourneysSelected',
                'AddOnsSelected',
                'SeatsAssigned'
            ];

            if (!in_array($this->goalState, $validGoalStates)) {
                throw new JamboJetValidationException('goalState must be one of: ' . implode(', ', $validGoalStates));
            }
        }

        // Validate context data if provided
        if ($this->contextData !== null) {
            $this->validateContextData();
        }
    }

    /**
     * Validate navigation actions request
     * 
     * @throws JamboJetValidationException
     */
    private function validateNavigationActionsRequest(): void
    {
        // Validate action category if provided
        if ($this->actionCategory !== null) {
            $validCategories = [
                'Passengers',
                'Journeys',
                'AddOns',
                'Seats',
                'Bundles',
                'Payment',
                'Booking',
                'All'
            ];

            if (!in_array($this->actionCategory, $validCategories)) {
                throw new JamboJetValidationException('actionCategory must be one of: ' . implode(', ', $validCategories));
            }
        }

        // Validate action criteria if provided
        if ($this->actionCriteria !== null) {
            if (!is_array($this->actionCriteria)) {
                throw new JamboJetValidationException('actionCriteria must be an array');
            }

            // Validate specific criteria fields
            if (isset($this->actionCriteria['stage']) && !is_string($this->actionCriteria['stage'])) {
                throw new JamboJetValidationException('actionCriteria.stage must be a string');
            }

            if (isset($this->actionCriteria['requiredOnly']) && !is_bool($this->actionCriteria['requiredOnly'])) {
                throw new JamboJetValidationException('actionCriteria.requiredOnly must be a boolean');
            }
        }
    }

    /**
     * Validate context data structure
     * 
     * @throws JamboJetValidationException
     */
    private function validateContextData(): void
    {
        if (!is_array($this->contextData)) {
            throw new JamboJetValidationException('contextData must be an array');
        }

        // Validate current step if provided
        if (isset($this->contextData['currentStep']) && !is_string($this->contextData['currentStep'])) {
            throw new JamboJetValidationException('contextData.currentStep must be a string');
        }

        // Validate booking state if provided
        if (isset($this->contextData['bookingState'])) {
            if (!is_array($this->contextData['bookingState'])) {
                throw new JamboJetValidationException('contextData.bookingState must be an array');
            }
        }

        // Validate user preferences if provided
        if (isset($this->contextData['userPreferences'])) {
            if (!is_array($this->contextData['userPreferences'])) {
                throw new JamboJetValidationException('contextData.userPreferences must be an array');
            }
        }
    }

    /**
     * Validate action data based on action type
     * 
     * @throws JamboJetValidationException
     */
    private function validateActionDataByType(): void
    {
        switch ($this->actionType) {
            case 'AddPassenger':
            case 'UpdatePassenger':
                if (!isset($this->actionData['passengerInfo'])) {
                    throw new JamboJetValidationException('actionData.passengerInfo is required for passenger actions');
                }
                break;

            case 'AddJourney':
            case 'ChangeJourney':
                if (!isset($this->actionData['journeyKey']) && !isset($this->actionData['segments'])) {
                    throw new JamboJetValidationException('actionData.journeyKey or actionData.segments is required for journey actions');
                }
                break;

            case 'AddSeat':
            case 'ChangeSeat':
                if (!isset($this->actionData['seatAssignments'])) {
                    throw new JamboJetValidationException('actionData.seatAssignments is required for seat actions');
                }
                break;

            case 'ProcessPayment':
                if (!isset($this->actionData['paymentMethod'])) {
                    throw new JamboJetValidationException('actionData.paymentMethod is required for payment actions');
                }
                break;
        }
    }

    /**
     * Create request for getting next action
     * 
     * @param string|null $goalState Target goal state
     * @param array|null $contextData Optional context data
     * @return self
     */
    public static function forNextAction(?string $goalState = null, ?array $contextData = null): self
    {
        return new self(
            requestType: 'getNextAction',
            goalState: $goalState,
            contextData: $contextData
        );
    }

    /**
     * Create request for getting navigation actions
     * 
     * @param string|null $actionCategory Action category filter
     * @param array|null $actionCriteria Optional criteria
     * @return self
     */
    public static function forNavigationActions(?string $actionCategory = null, ?array $actionCriteria = null): self
    {
        return new self(
            requestType: 'getNavigationActions',
            actionCategory: $actionCategory,
            actionCriteria: $actionCriteria
        );
    }

    /**
     * Create request for validating action
     * 
     * @param string $actionType Action type to validate
     * @param array|null $actionData Optional action data
     * @return self
     */
    public static function forValidateAction(string $actionType, ?array $actionData = null): self
    {
        return new self(
            requestType: 'validateAction',
            actionType: $actionType,
            actionData: $actionData
        );
    }

    /**
     * Create request for executing action
     * 
     * @param string $actionType Action type to execute
     * @param array $actionData Action data
     * @return self
     */
    public static function forExecuteAction(string $actionType, array $actionData): self
    {
        return new self(
            requestType: 'executeAction',
            actionType: $actionType,
            actionData: $actionData
        );
    }
}
