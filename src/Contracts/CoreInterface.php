<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Requests\GraphQLQueryRequest;
use SantosDave\JamboJet\Requests\PromotionSearchRequest;
use SantosDave\JamboJet\Requests\Category50FareRulesRequest;

interface CoreInterface
{
    /**
     * Redis Cache Management
     * GET /api/v1/redis
     * 
     * @return array List of cache keys
     */
    public function getRedisCacheKeys(): array;

    /**
     * Delete specific Redis cache key
     * DELETE /api/v1/redis/{name}
     * 
     * @param string $cacheKey Cache key name
     * @return array Deletion result
     */
    public function deleteRedisCacheKey(string $cacheKey): array;

    /**
     * Delete all Redis cache
     * DELETE /api/v1/redis
     * 
     * @return array Deletion result
     */
    public function deleteAllRedisCache(): array;

    /**
     * Execute GraphQL query
     * POST /api/nsk/v1/graph
     * 
     * @param GraphQLQueryRequest|array $graphQuery GraphQL query request or array
     * @return array Query result
     */
    public function executeGraphQuery(GraphQLQueryRequest|array $graphQuery): array;

    /**
     * Execute named GraphQL query
     * POST /api/nsk/v1/graph/{queryName}
     * POST /api/v2/graph/{queryName}
     * 
     * @param string $queryName Configured query name
     * @param array $variables Query variables
     * @param bool $cachedResults Use cached results
     * @param int $version API version (1 or 2, default: 1)
     * @return array Query result
     */
    public function executeNamedGraphQuery(string $queryName, array $variables = [], bool $cachedResults = false, int $version = 1): array;

    /**
     * Get pricing configuration
     * GET /api/apo/v1/pricing/configuration
     * 
     * @param string|null $source Pricing configuration source
     * @return array Pricing configuration
     */
    public function getPricingConfiguration(?string $source = null): array;

    /**
     * Get promotions
     * GET /api/nsk/v1/promotions
     * 
     * @param PromotionSearchRequest|array $criteria Promotion search request or array
     * @return array Promotions list
     */
    public function getPromotions(PromotionSearchRequest|array $criteria = []): array;

    /**
     * Get fare rules (Category 50)
     * POST /api/nsk/v2/fareRules/category50/journeys/{journeyKey}/segments/{segmentKey}
     * 
     * @param Category50FareRulesRequest|array $request Category 50 request object or array
     * @return array Category 50 fare rules
     */
    public function getCategory50FareRules(Category50FareRulesRequest|array $request): array;

    /**
     * Get specific promotion by code
     * GET /api/nsk/v1/promotions/{promotionCode}
     * 
     * @param string $promotionCode The promotion code
     * @return array Promotion details
     */
    public function getPromotion(string $promotionCode): array;

    /**
     * Validate promotion code
     * GET /api/nsk/v1/promotions/{promotionCode}/validate
     * 
     * @param string $promotionCode The promotion code
     * @param string|null $organizationCode Optional organization code
     * @return array Validation result
     */
    public function validatePromotion(string $promotionCode, ?string $organizationCode = null): array;

    /**
     * Get system health check
     * GET /api/v3/health
     * 
     * @return array Environment health status
     */
    public function getHealthCheck(): array;
}
