<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * GraphQL Query Request for JamboJet NSK API
 * 
 * Used with:
 * - POST /api/nsk/v1/graph
 * 
 * @package SantosDave\JamboJet\Requests
 */
class GraphQLQueryRequest extends BaseRequest
{
    /**
     * Create a new GraphQL query request
     * 
     * @param string $query Required: GraphQL query string (min 1 character)
     * @param mixed|null $variables Optional: GraphQL variables object/array
     * @param bool $cachedResults Optional: Allow caching and etag support (default: false)
     */
    public function __construct(
        public string $query,
        public mixed $variables = null,
        public bool $cachedResults = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        $data = [
            'query' => $this->query,
            'cachedResults' => $this->cachedResults
        ];

        if ($this->variables !== null) {
            $data['variables'] = $this->variables;
        }

        return $data;
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate required query
        if (empty($this->query)) {
            throw new JamboJetValidationException(
                'GraphQL query is required and cannot be empty',
                400
            );
        }

        // Validate query length
        if (strlen($this->query) < 1) {
            throw new JamboJetValidationException(
                'GraphQL query must have at least 1 character',
                400
            );
        }

        // Validate cachedResults is boolean
        if (!is_bool($this->cachedResults)) {
            throw new JamboJetValidationException(
                'cachedResults must be a boolean value',
                400
            );
        }

        // Validate variables if provided
        if ($this->variables !== null) {
            if (!is_array($this->variables) && !is_object($this->variables)) {
                throw new JamboJetValidationException(
                    'variables must be an array or object',
                    400
                );
            }
        }
    }

    /**
     * Create from array data
     * 
     * @param array $data Request data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            query: $data['query'] ?? '',
            variables: $data['variables'] ?? null,
            cachedResults: $data['cachedResults'] ?? false
        );
    }

    /**
     * Create simple query without variables
     * 
     * @param string $query GraphQL query string
     * @param bool $cached Use cached results
     * @return static
     */
    public static function simple(string $query, bool $cached = false): static
    {
        return new static(
            query: $query,
            variables: null,
            cachedResults: $cached
        );
    }

    /**
     * Create query with variables
     * 
     * @param string $query GraphQL query string
     * @param array $variables Query variables
     * @param bool $cached Use cached results
     * @return static
     */
    public static function withVariables(string $query, array $variables, bool $cached = false): static
    {
        return new static(
            query: $query,
            variables: $variables,
            cachedResults: $cached
        );
    }
}
