<?php

namespace SantosDave\JamboJet\Contracts;

interface MessageInterface
{
    /**
     * Create a new message
     * POST /api/nsk/v1/messages
     * 
     * @param array $messageData Message data (typeCode, information, body)
     * @return array Created message response
     */
    public function createMessage(array $messageData): array;

    /**
     * Get messages based on search criteria
     * GET /api/nsk/v2/messages
     * 
     * @param array $criteria Search criteria (filters, pagination, etc.)
     * @return array Messages search response
     */
    public function getMessages(array $criteria = []): array;

    /**
     * Get specific message by key
     * GET /api/nsk/v1/messages/{messageKey}
     * 
     * @param string $messageKey Message identifier
     * @return array Message details
     */
    public function getMessage(string $messageKey): array;

    /**
     * Delete specific message
     * DELETE /api/nsk/v1/messages/{messageKey}
     * 
     * @param string $messageKey Message identifier
     * @return array Deletion result
     */
    public function deleteMessage(string $messageKey): array;

    /**
     * Delete multiple messages
     * DELETE /api/nsk/v1/messages
     * 
     * @param array $messageKeys Array of message keys to delete
     * @return array Deletion result
     */
    public function deleteMessages(array $messageKeys): array;

    /**
     * Send teletype message
     * POST /api/nsk/v1/messages/teletype
     * 
     * @param array $messageData Teletype message data (fromAddress, toAddress, body)
     * @return array Send result
     */
    public function sendTeletypeMessage(array $messageData): array;
}
