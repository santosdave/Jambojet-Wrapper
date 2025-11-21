<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\MessageInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Requests\MessageCreateRequest;
use SantosDave\JamboJet\Requests\MessageSearchRequest;
use SantosDave\JamboJet\Requests\TeletypeMessageRequest;

/**
 * Message Service for JamboJet NSK API
 * 
 * Handles message queue operations including creating, retrieving, deleting messages
 * and sending teletype messages
 * Base endpoints: /api/nsk/v1/messages, /api/nsk/v2/messages
 * 
 * Supported endpoints:
 * - POST /api/nsk/v1/messages - Create new message
 * - GET /api/nsk/v2/messages - Search messages
 * - GET /api/nsk/v1/messages/{messageKey} - Get specific message
 * - DELETE /api/nsk/v1/messages/{messageKey} - Delete specific message
 * - DELETE /api/nsk/v1/messages - Delete multiple messages
 * - POST /api/nsk/v1/messages/teletype - Send teletype message
 * 
 * @package SantosDave\JamboJet\Services
 */
class MessageService implements MessageInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - MESSAGE OPERATIONS
    // =================================================================

    /**
     * Create a new message
     * 
     * POST /api/nsk/v1/messages
     * Adds a new message item to the message queue
     * 
     * @param MessageCreateRequest|array $request Message request object or data array
     * @return array Created message response
     * @throws JamboJetApiException
     */
    public function createMessage(MessageCreateRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = MessageCreateRequest::fromArray($request);
        }

        $request->validate();

        try {
            return $this->post('api/nsk/v1/messages', $request->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create message: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get messages based on search criteria
     * 
     * GET /api/nsk/v2/messages
     * Retrieves a collection of teletype messages based on search criteria
     * 
     * @param MessageSearchRequest|array $criteria Search request object or criteria array
     * @return array Messages search response
     * @throws JamboJetApiException
     */
    public function getMessages(MessageSearchRequest|array $criteria = []): array
    {
        // Convert array to Request object if needed
        if (is_array($criteria)) {
            if (empty($criteria)) {
                $criteria = new MessageSearchRequest();
            } else {
                $criteria = MessageSearchRequest::fromArray($criteria);
            }
        }

        $criteria->validate();

        try {
            return $this->get('api/nsk/v2/messages', $criteria->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get messages: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific message by key
     * 
     * GET /api/nsk/v1/messages/{messageKey}
     * Retrieves a specific message item
     * 
     * @param string $messageKey Message identifier
     * @return array Message details
     * @throws JamboJetApiException
     */
    public function getMessage(string $messageKey): array
    {
        $this->validateMessageKey($messageKey);

        try {
            return $this->get("api/nsk/v1/messages/{$messageKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get message: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete specific message
     * 
     * DELETE /api/nsk/v1/messages/{messageKey}
     * Deletes a message item
     * 
     * @param string $messageKey Message identifier
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteMessage(string $messageKey): array
    {
        $this->validateMessageKey($messageKey);

        try {
            return $this->delete("api/nsk/v1/messages/{$messageKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete message: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete multiple messages
     * 
     * DELETE /api/nsk/v1/messages
     * Deletes a collection of message items
     * This endpoint does not perform additional verification that messages were deleted
     * 
     * @param array $messageKeys Array of message keys to delete
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteMessages(array $messageKeys): array
    {
        if (empty($messageKeys)) {
            throw new JamboJetValidationException(
                'Message keys array cannot be empty',
                400
            );
        }

        // Validate each message key
        foreach ($messageKeys as $key) {
            $this->validateMessageKey($key);
        }

        try {
            return $this->delete('api/nsk/v1/messages', $messageKeys);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete messages: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Send teletype message
     * 
     * POST /api/nsk/v1/messages/teletype
     * Sends a new generic teletype message to the specified address
     * 
     * @param TeletypeMessageRequest|array $request Teletype message request object or data array
     * @return array Send result
     * @throws JamboJetApiException
     */
    public function sendTeletypeMessage(TeletypeMessageRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = TeletypeMessageRequest::fromArray($request);
        }

        $request->validate();

        try {
            return $this->post('api/nsk/v1/messages/teletype', $request->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to send teletype message: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate message key format
     * 
     * @param string $messageKey Message key
     * @throws JamboJetValidationException
     */
    private function validateMessageKey(string $messageKey): void
    {
        if (empty(trim($messageKey))) {
            throw new JamboJetValidationException(
                'Message key cannot be empty',
                400
            );
        }

        // Validate max length
        if (strlen($messageKey) > 100) {
            throw new JamboJetValidationException(
                'Message key cannot exceed 100 characters',
                400
            );
        }
    }
}
