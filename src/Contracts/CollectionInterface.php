<?php

namespace SantosDave\JamboJet\Contracts;

interface CollectionInterface
{
    public function updateCollectionExpiration(string $accountCollectionKey, array $expirationData): array;
    public function getCollectionTransactions(string $accountCollectionKey, string $startDate, ?string $endDate = null, ?bool $sortByNewest = null, ?int $pageSize = null, ?string $lastPageKey = null): array;
}
