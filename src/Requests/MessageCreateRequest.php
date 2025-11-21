<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;

/**
 * Message Create Request
 * 
 * Creates a new message in the message queue
 * Endpoint: POST /api/nsk/v1/messages
 * 
 * @package SantosDave\JamboJet\Requests
 */
class MessageCreateRequest extends BaseRequest
{

    use ValidatesRequests;
    /**
     * Create new message request
     * 
     * @param string|null $typeCode Message type code
     * @param string|null $information Reason why message was added
     * @param string|null $body Message body content
     */
    public function __construct(
        public ?string $typeCode = null,
        public ?string $information = null,
        public ?string $body = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'typeCode' => $this->typeCode,
            'information' => $this->information,
            'body' => $this->body,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        // At least one field should be provided
        if (!$this->typeCode && !$this->information && !$this->body) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'At least one of typeCode, information, or body must be provided',
                400
            );
        }

        // Validate string lengths if provided
        $data = $this->toArray();
        $this->validateStringLengths($data, [
            'typeCode' => ['max' => 50],
            'information' => ['max' => 500],
            'body' => ['max' => 5000]
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
            typeCode: $data['typeCode'] ?? null,
            information: $data['information'] ?? null,
            body: $data['body'] ?? null
        );
    }

    /**
     * Create with type code
     * 
     * @param string $typeCode Message type code
     * @return static
     */
    public static function withType(string $typeCode): static
    {
        return new static(typeCode: $typeCode);
    }

    /**
     * Set information
     * 
     * @param string $information Information text
     * @return $this
     */
    public function withInformation(string $information): static
    {
        $this->information = $information;
        return $this;
    }

    /**
     * Set body
     * 
     * @param string $body Message body
     * @return $this
     */
    public function withBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }
}