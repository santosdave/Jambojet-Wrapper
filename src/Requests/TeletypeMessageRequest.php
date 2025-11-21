<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;

/**
 * Teletype Message Request
 * 
 * Sends a generic teletype message
 * Endpoint: POST /api/nsk/v1/messages/teletype
 * 
 * @package SantosDave\JamboJet\Requests
 */
class TeletypeMessageRequest extends BaseRequest
{

    use ValidatesRequests;
    /**
     * Create new teletype message request
     * 
     * @param string $fromAddress Sender address (7-8 characters)
     * @param string $toAddress Recipient address (7-8 characters)
     * @param string $body Message body (min 1 character)
     */
    public function __construct(
        public string $fromAddress,
        public string $toAddress,
        public string $body
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return [
            'fromAddress' => $this->fromAddress,
            'toAddress' => $this->toAddress,
            'body' => $this->body,
        ];
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // Validate required fields
        $this->validateRequired($data, ['fromAddress', 'toAddress', 'body']);

        // Validate address lengths (7-8 characters)
        $fromLength = strlen($this->fromAddress);
        $toLength = strlen($this->toAddress);

        if ($fromLength < 7 || $fromLength > 8) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'From address must be 7-8 characters long',
                400
            );
        }

        if ($toLength < 7 || $toLength > 8) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'To address must be 7-8 characters long',
                400
            );
        }

        // Validate body has at least 1 character
        if (empty(trim($this->body))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Message body cannot be empty',
                400
            );
        }

        // Validate max lengths
        $this->validateStringLengths($data, [
            'fromAddress' => ['max' => 8],
            'toAddress' => ['max' => 8],
            'body' => ['max' => 10000]
        ]);
    }

    /**
     * Create from array
     * 
     * @param array $data Request data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            fromAddress: $data['fromAddress'],
            toAddress: $data['toAddress'],
            body: $data['body']
        );
    }

    /**
     * Create teletype message
     * 
     * @param string $from Sender address
     * @param string $to Recipient address
     * @param string $message Message body
     * @return static
     */
    public static function send(string $from, string $to, string $message): static
    {
        return new static(
            fromAddress: $from,
            toAddress: $to,
            body: $message
        );
    }
}
