<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Message Search Request
 * 
 * Search for messages based on criteria
 * Endpoint: GET /api/nsk/v2/messages
 * 
 * @package SantosDave\JamboJet\Requests
 */
class MessageSearchRequest extends BaseRequest
{
    // Hidden options enum values
    public const HIDDEN_NON_HIDDEN = 0;
    public const HIDDEN_HIDDEN = 1;
    public const HIDDEN_ALL = 2;

    /**
     * Create new message search request
     * 
     * @param string|null $messageKey Filter by message key
     * @param string|null $typeCode Filter by message type code
     * @param int|null $hiddenOptions Filter by hidden status (0=NonHidden, 1=Hidden, 2=All)
     * @param int|null $pageSize Number of results per page
     * @param int|null $pageNumber Page number for pagination
     * @param string|null $sortBy Field to sort by
     * @param string|null $sortOrder Sort order (asc/desc)
     */
    public function __construct(
        public ?string $messageKey = null,
        public ?string $typeCode = null,
        public ?int $hiddenOptions = null,
        public ?int $pageSize = null,
        public ?int $pageNumber = null,
        public ?string $sortBy = null,
        public ?string $sortOrder = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'messageKey' => $this->messageKey,
            'typeCode' => $this->typeCode,
            'hiddenOptions' => $this->hiddenOptions,
            'pageSize' => $this->pageSize,
            'pageNumber' => $this->pageNumber,
            'sortBy' => $this->sortBy,
            'sortOrder' => $this->sortOrder,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate hidden options if provided
        if ($this->hiddenOptions !== null) {
            if (!in_array($this->hiddenOptions, [self::HIDDEN_NON_HIDDEN, self::HIDDEN_HIDDEN, self::HIDDEN_ALL])) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Hidden options must be 0 (NonHidden), 1 (Hidden), or 2 (All)',
                    400
                );
            }
        }

        // Validate pagination
        if ($this->pageSize !== null && $this->pageSize < 1) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Page size must be at least 1',
                400
            );
        }

        if ($this->pageNumber !== null && $this->pageNumber < 1) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Page number must be at least 1',
                400
            );
        }

        // Validate sort order
        if ($this->sortOrder !== null) {
            $validOrders = ['asc', 'desc', 'ASC', 'DESC'];
            if (!in_array($this->sortOrder, $validOrders)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Sort order must be "asc" or "desc"',
                    400
                );
            }
        }
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
            messageKey: $data['messageKey'] ?? null,
            typeCode: $data['typeCode'] ?? null,
            hiddenOptions: $data['hiddenOptions'] ?? null,
            pageSize: $data['pageSize'] ?? null,
            pageNumber: $data['pageNumber'] ?? null,
            sortBy: $data['sortBy'] ?? null,
            sortOrder: $data['sortOrder'] ?? null
        );
    }

    /**
     * Search by message key
     * 
     * @param string $messageKey Message key
     * @return static
     */
    public static function byKey(string $messageKey): static
    {
        return new static(messageKey: $messageKey);
    }

    /**
     * Search by type code
     * 
     * @param string $typeCode Type code
     * @return static
     */
    public static function byType(string $typeCode): static
    {
        return new static(typeCode: $typeCode);
    }

    /**
     * Set pagination
     * 
     * @param int $pageSize Results per page
     * @param int $pageNumber Page number
     * @return $this
     */
    public function withPagination(int $pageSize, int $pageNumber = 1): static
    {
        $this->pageSize = $pageSize;
        $this->pageNumber = $pageNumber;
        return $this;
    }

    /**
     * Set sorting
     * 
     * @param string $sortBy Field to sort by
     * @param string $sortOrder Sort order (asc/desc)
     * @return $this
     */
    public function withSorting(string $sortBy, string $sortOrder = 'asc'): static
    {
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Show all messages (hidden and non-hidden)
     * 
     * @return $this
     */
    public function showAll(): static
    {
        $this->hiddenOptions = self::HIDDEN_ALL;
        return $this;
    }

    /**
     * Show only hidden messages
     * 
     * @return $this
     */
    public function showHiddenOnly(): static
    {
        $this->hiddenOptions = self::HIDDEN_HIDDEN;
        return $this;
    }

    /**
     * Show only non-hidden messages (default)
     * 
     * @return $this
     */
    public function showNonHiddenOnly(): static
    {
        $this->hiddenOptions = self::HIDDEN_NON_HIDDEN;
        return $this;
    }
}
