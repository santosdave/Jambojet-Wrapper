<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\CoreInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Requests\GraphQLQueryRequest;
use SantosDave\JamboJet\Requests\PromotionSearchRequest;
use SantosDave\JamboJet\Requests\Category50FareRulesRequest;

/**
 * Core Service for JamboJet NSK API
 * 
 * Handles core system operations including cache management, GraphQL queries,
 * pricing configuration, promotions, and advanced fare rules
 * Base endpoints: /api/v1/redis, /api/nsk/v1/graph, /api/apo/v1/pricing, /api/nsk/v1/promotions
 * 
 * Supported endpoints:
 * - GET /api/v1/redis - Get all Redis cache keys
 * - DELETE /api/v1/redis/{name} - Delete specific cache key
 * - DELETE /api/v1/redis - Delete all cache
 * - POST /api/nsk/v1/graph - Execute GraphQL query
 * - POST /api/nsk/v1/graph/{queryName} - Execute named GraphQL query
 * - POST /api/v2/graph/{queryName} - Execute named GraphQL query (v2)
 * - GET /api/apo/v1/pricing/configuration - Get pricing configuration
 * - GET /api/nsk/v1/promotions - Get promotions
 * - POST /api/nsk/v2/fareRules/category50/journeys/{journeyKey}/segments/{segmentKey} - Category 50 fare rules
 * 
 * @package SantosDave\JamboJet\Services
 */
class CoreService implements CoreInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // REDIS CACHE MANAGEMENT
    // =================================================================

    /**
     * Get all Redis cache keys
     * 
     * GET /api/v1/redis
     * Retrieves all cache items that exist in Redis
     * Requires admin/management permissions
     * 
     * @return array List of cache keys
     * @throws JamboJetApiException
     */
    public function getRedisCacheKeys(): array
    {
        try {
            return $this->get('api/v1/redis');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get Redis cache keys: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete specific Redis cache key
     * 
     * DELETE /api/v1/redis/{name}
     * Deletes a specific cache item from Redis based on cache item name
     * Note: Other API instances may still have locally stale cached data
     * 
     * @param string $cacheKey Cache key name
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteRedisCacheKey(string $cacheKey): array
    {
        $this->validateRequired(['cacheKey' => $cacheKey], ['cacheKey']);

        try {
            return $this->delete("api/v1/redis/{$cacheKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete Redis cache key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete all Redis cache
     * 
     * DELETE /api/v1/redis
     * Deletes all cache items (ignores session values)
     * Note: Other API instances may still have locally stale cached data
     * 
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteAllRedisCache(): array
    {
        try {
            return $this->delete('api/v1/redis');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete all Redis cache: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // GRAPHQL QUERIES
    // =================================================================

    /**
     * Execute GraphQL query
     * 
     * POST /api/nsk/v1/graph
     * Execute a GraphQL query with variables
     * 
     * @param GraphQLQueryRequest|array $graphQuery GraphQL query request or data array
     * @return array Query result
     * @throws JamboJetApiException
     */
    public function executeGraphQuery(GraphQLQueryRequest|array $graphQuery): array
    {
        // Convert array to Request object if needed
        if (is_array($graphQuery)) {
            $graphQuery = GraphQLQueryRequest::fromArray($graphQuery);
        }

        $graphQuery->validate();

        try {
            return $this->post('api/nsk/v1/graph', $graphQuery->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'GraphQL query execution failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Execute named GraphQL query
     * 
     * POST /api/nsk/v1/graph/{queryName}
     * POST /api/v2/graph/{queryName}
     * Execute a pre-configured named GraphQL query
     * 
     * @param string $queryName Configured query name
     * @param array $variables Query variables
     * @param bool $cachedResults Use cached results
     * @param int $version API version (1 or 2, default: 1)
     * @return array Query result
     * @throws JamboJetApiException
     */
    public function executeNamedGraphQuery(string $queryName, array $variables = [], bool $cachedResults = false, int $version = 1): array
    {
        $this->validateRequired(['queryName' => $queryName], ['queryName']);
        $this->validateApiVersion($version, [1, 2]);

        try {
            $endpoint = $version === 2
                ? "api/v2/graph/{$queryName}"
                : "api/nsk/v1/graph/{$queryName}";

            $queryParams = ['cachedResults' => $cachedResults];

            return $this->post($endpoint, $variables, [], $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Named GraphQL query execution failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PRICING CONFIGURATION
    // =================================================================

    /**
     * Get pricing configuration
     * 
     * GET /api/apo/v1/pricing/configuration
     * Gets pricing configuration data from ancillary pricing options service
     * 
     * @param string|null $source Pricing configuration source
     * @return array Pricing configuration
     * @throws JamboJetApiException
     */
    public function getPricingConfiguration(?string $source = null): array
    {
        try {
            $params = $source ? ['source' => $source] : [];
            return $this->get('api/apo/v1/pricing/configuration', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get pricing configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // PROMOTIONS
    // =================================================================

    /**
     * Get promotions
     * 
     * GET /api/nsk/v1/promotions
     * Gets promotions based on search criteria
     * 
     * @param PromotionSearchRequest|array $criteria Promotion search request or criteria array
     * @return array Promotions list
     * @throws JamboJetApiException
     */
    public function getPromotions(PromotionSearchRequest|array $criteria = []): array
    {
        // Convert array to Request object if needed
        if (is_array($criteria)) {
            $criteria = PromotionSearchRequest::fromArray($criteria);
        }

        $criteria->validate();

        try {
            return $this->get('api/nsk/v1/promotions', $criteria->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get promotions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // ADVANCED FARE RULES
    // =================================================================

    /**
     * Get Category 50 fare rules
     * 
     * POST /api/nsk/v2/fareRules/category50/journeys/{journeyKey}/segments/{segmentKey}
     * Gets Category 50 fare rule information for specific journey segment
     * 
     * @param Category50FareRulesRequest|array $request Category 50 request object or data array
     * @return array Category 50 fare rules
     * @throws JamboJetApiException
     */
    public function getCategory50FareRules(Category50FareRulesRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = Category50FareRulesRequest::fromArray($request);
        }

        $request->validate();

        try {
            $journeyKey = $request->getJourneyKey();
            $segmentKey = $request->getSegmentKey();

            return $this->post(
                "api/nsk/v2/fareRules/category50/journeys/{$journeyKey}/segments/{$segmentKey}",
                $request->toArray()
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get Category 50 fare rules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get system health check
     * 
     * GET /api/v3/health
     * Performs health check on the API environment
     * This endpoint does not require authentication
     * 
     * Response Codes:
     * - 200: System is healthy (Ok)
     * - 299: System has warnings (Warning) - Custom code
     * - 500: System has errors (Error)
     * 
     * @return array Environment health status
     * @throws JamboJetApiException
     */
    public function getHealthCheck(): array
    {
        try {
            return $this->get('api/v3/health');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Health check failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific promotion by code
     * 
     * GET /api/nsk/v1/promotions/{promotionCode}
     * Gets a promotion based on the promotion code
     * 
     * @param string $promotionCode The promotion code
     * @return array Promotion details
     * @throws JamboJetApiException
     */
    public function getPromotion(string $promotionCode): array
    {
        $this->validatePromotionCode($promotionCode);

        try {
            return $this->get("api/nsk/v1/promotions/{$promotionCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get promotion: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate promotion code
     * 
     * GET /api/nsk/v1/promotions/{promotionCode}/validate
     * Evaluates a promotion code and optional organization code to determine 
     * if the associated promotion is valid or not
     * This validates the promotion code based on the logged-in user if the
     * organization code is not provided
     * 
     * @param string $promotionCode The promotion code
     * @param string|null $organizationCode Optional organization code
     * @return array Validation result
     * @throws JamboJetApiException
     */
    public function validatePromotion(string $promotionCode, ?string $organizationCode = null): array
    {
        $this->validatePromotionCode($promotionCode);

        if ($organizationCode !== null) {
            $this->validateOrganizationCode($organizationCode);
        }

        try {
            $params = $organizationCode ? ['organizationCode' => $organizationCode] : [];
            return $this->get("api/nsk/v1/promotions/{$promotionCode}/validate", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate promotion: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate promotion code format
     * 
     * @param string $promotionCode Promotion code
     * @throws JamboJetValidationException
     */
    private function validatePromotionCode(string $promotionCode): void
    {
        if (empty(trim($promotionCode))) {
            throw new JamboJetValidationException(
                'Promotion code cannot be empty',
                400
            );
        }

        // Promotion codes are max 8 characters
        if (strlen($promotionCode) > 8) {
            throw new JamboJetValidationException(
                'Promotion code must be 8 characters or less',
                400
            );
        }
    }

    /**
     * Validate organization code format
     * 
     * @param string $organizationCode Organization code
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCode(string $organizationCode): void
    {
        if (empty(trim($organizationCode))) {
            throw new JamboJetValidationException(
                'Organization code cannot be empty',
                400
            );
        }

        // Organization codes are 2-10 characters
        $length = strlen($organizationCode);
        if ($length < 2 || $length > 10) {
            throw new JamboJetValidationException(
                'Organization code must be between 2 and 10 characters',
                400
            );
        }
    }
}