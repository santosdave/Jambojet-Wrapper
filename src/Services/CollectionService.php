<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\CollectionInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;

class CollectionService implements CollectionInterface
{
    use HandlesApiRequests, ValidatesRequests;

    public function updateCollectionExpiration(string $accountCollectionKey, array $expirationData): array
    {
        $this->validateCollectionKey($accountCollectionKey);
        $this->validateExpirationData($expirationData);

        try {
            return $this->put("api/nsk/v1/collection/{$accountCollectionKey}/expirationDate", $expirationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update collection expiration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getCollectionTransactions(
        string $accountCollectionKey,
        string $startDate,
        ?string $endDate = null,
        ?bool $sortByNewest = null,
        ?int $pageSize = null,
        ?string $lastPageKey = null
    ): array {
        $this->validateCollectionKey($accountCollectionKey);
        $this->validateDateRange($startDate, $endDate);

        if ($pageSize !== null && ($pageSize < 10 || $pageSize > 5000)) {
            throw new JamboJetValidationException('Page size must be between 10 and 5000', 400);
        }

        $params = array_filter([
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'SortByNewest' => $sortByNewest,
            'PageSize' => $pageSize,
            'LastPageKey' => $lastPageKey,
        ], fn($v) => $v !== null);

        try {
            return $this->get("api/nsk/v2/collection/{$accountCollectionKey}/transactions", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get collection transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function validateCollectionKey(string $key): void
    {
        if (empty($key)) {
            throw new JamboJetValidationException('Account collection key is required', 400);
        }
    }

    private function validateExpirationData(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Expiration data cannot be empty', 400);
        }
    }

    private function validateDateRange(?string $startDate, ?string $endDate): void
    {
        if (empty($startDate)) {
            throw new JamboJetValidationException('Start date is required', 400);
        }

        if ($endDate !== null) {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            
            if ($end < $start) {
                throw new JamboJetValidationException('End date cannot be before start date', 400);
            }
        }
    }
}