<?php

namespace SantosDave\JamboJet\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetAuthenticationException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Services\TokenManager;

trait HandlesApiRequests
{
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->config = config('jambojet');

        $this->loadCachedTokenIfAvailable();
    }

    /**
     * Make HTTP GET request to JamboJet API
     */
    protected function get(string $endpoint, array $params = [], array $headers = []): array
    {
        return $this->makeRequest('GET', $endpoint, $params, $headers);
    }

    /**
     * Make HTTP POST request to JamboJet API
     */
    protected function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('POST', $endpoint, $data, $headers);
    }

    /**
     * Make HTTP PUT request to JamboJet API
     */
    protected function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('PUT', $endpoint, $data, $headers);
    }

    /**
     * Make HTTP PATCH request to JamboJet API
     */
    protected function patch(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('PATCH', $endpoint, $data, $headers);
    }

    /**
     * Make HTTP DELETE request to JamboJet API
     */
    protected function delete(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('DELETE', $endpoint, $data, $headers);
    }

    /**
     * Core method to handle API requests with retries, logging, and error handling
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        if (!str_contains($endpoint, 'token')) {
            $this->ensureValidToken();
        }

        $url = $this->buildUrl($endpoint);
        $headers = $this->buildHeaders($headers);

        // Check cache for GET requests
        if ($method === 'GET' && $this->config['cache']['enabled']) {
            $cacheKey = $this->getCacheKey($url, $data);
            if ($cached = Cache::get($cacheKey)) {
                return $cached;
            }
        }

        $response = null;
        $attempt = 0;
        $maxAttempts = $this->config['retry_attempts'];

        while ($attempt < $maxAttempts) {
            try {
                $response = $this->executeRequest($method, $url, $data, $headers);

                if ($response->successful()) {
                    $result = $this->handleSuccessResponse($response, $endpoint);

                    // Cache GET responses
                    if ($method === 'GET' && $this->config['cache']['enabled']) {
                        Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
                    }

                    return $result;
                }

                // Handle API errors
                $this->handleErrorResponse($response, $endpoint);
            } catch (JamboJetAuthenticationException $e) {
                // ADD: Automatic 401 recovery
                if ($e->getCode() === 401 && $attempt === 0 && !str_contains($endpoint, 'token')) {
                    Log::info('JamboJet: Token expired (401), attempting automatic refresh', [
                        'endpoint' => $endpoint,
                        'attempt' => $attempt + 1
                    ]);

                    try {
                        // Refresh token
                        $authService = app(AuthenticationInterface::class);
                        $tokenResponse = $authService->refreshToken();

                        // Update headers with new token
                        if (isset($tokenResponse['data']['token'])) {
                            $this->setAccessToken($tokenResponse['data']['token']);
                            $headers = $this->buildHeaders([]);

                            // Retry the request
                            $attempt++;
                            continue;
                        }
                    } catch (\Exception $refreshError) {
                        Log::error('JamboJet: Token refresh failed', [
                            'error' => $refreshError->getMessage()
                        ]);
                        throw $e; // Re-throw original exception
                    }
                }
                throw $e;
            } catch (\Exception $e) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    throw new JamboJetApiException(
                        "API request failed after {$maxAttempts} attempts: " . $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }

                // Exponential backoff
                sleep(pow(2, $attempt - 1));
            }
        }

        throw new JamboJetApiException("Maximum retry attempts exceeded for endpoint: {$endpoint}");
    }

    /**
     * Execute the actual HTTP request
     */
    protected function executeRequest(string $method, string $url, array $data, array $headers): Response
    {
        $httpClient = Http::withHeaders($headers)
            ->timeout($this->config['timeout']);

        // Log request if enabled
        Log::channel($this->config['logging']['channel'])->info('JamboJet API Request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'headers' => Arr::except($headers, ['Ocp-Apim-Subscription-Key', 'Authorization'])
        ]);

        switch (strtoupper($method)) {
            case 'GET':
                return $httpClient->get($url, $data);
            case 'POST':
                return $httpClient->post($url, $data);
            case 'PUT':
                return $httpClient->put($url, $data);
            case 'PATCH':
                return $httpClient->patch($url, $data);
            case 'DELETE':
                return $httpClient->delete($url, $data);
            default:
                throw new JamboJetApiException("Unsupported HTTP method: {$method}");
        }
    }

    // ADD: New method to ensure valid token before requests
    protected function ensureValidToken(): void
    {
        // Skip for auth endpoints
        if ($this->accessToken && method_exists($this, 'isTokenExpiringSoon')) {
            try {
                // Check if token expires in less than 2 minutes
                if ($this->isTokenExpiringSoon(120)) {
                    Log::info('JamboJet: Token expiring soon, refreshing proactively');

                    $authService = app(AuthenticationInterface::class);
                    $tokenResponse = $authService->refreshToken();

                    if (isset($tokenResponse['data']['token'])) {
                        $this->setAccessToken($tokenResponse['data']['token']);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('JamboJet: Proactive token refresh failed', [
                    'error' => $e->getMessage()
                ]);
                // Continue with existing token
            }
        }
    }

    // ADD: Load cached token if available
    protected function loadCachedTokenIfAvailable(): void
    {
        $tokenManager = app(TokenManager::class);

        if ($tokenManager->hasValidToken()) {
            $this->accessToken = $tokenManager->getToken();

            Log::debug('JamboJet: Auto-loaded cached token', [
                'expires_in' => $tokenManager->getRemainingSeconds()
            ]);
        }
    }

    /**
     * Build full URL for API endpoint
     */
    protected function buildUrl(string $endpoint): string
    {
        $baseUrl = rtrim($this->config['base_url'], '/');
        $endpoint = ltrim($endpoint, '/');

        return "{$baseUrl}/{$endpoint}";
    }

    /**
     * Build request headers including authentication
     */
    protected function buildHeaders(array $customHeaders = []): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'],
        ];

        // Add access token if available
        if ($this->accessToken) {
            $headers['Authorization'] = "Bearer {$this->accessToken}";
        }

        return array_merge($headers, $customHeaders);
    }

    /**
     * Handle successful API response
     */
    protected function handleSuccessResponse(Response $response, string $endpoint): array
    {
        $data = $response->json();

        // Log success if enabled
        if ($this->config['logging']['enabled']) {
            Log::channel($this->config['logging']['channel'])->info('JamboJet API Success', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $data
            ]);
        }

        return [
            'success' => true,
            'data' => $data,
            'message' => 'Request successful',
            'errors' => [],
            'meta' => [
                'endpoint' => $endpoint,
                'status_code' => $response->status(),
                'request_id' => $response->header('X-Request-ID', uniqid()),
                'timestamp' => now()->toISOString()
            ]
        ];
    }

    /**
     * Handle API error responses
     */
    protected function handleErrorResponse(Response $response, string $endpoint): void
    {
        $statusCode = $response->status();
        $errorData = $response->json();

        // Log error if enabled
        if ($this->config['logging']['enabled']) {
            Log::channel($this->config['logging']['channel'])->error('JamboJet API Error', [
                'endpoint' => $endpoint,
                'status' => $statusCode,
                'error' => $errorData
            ]);
        }

        // Handle specific error types
        switch ($statusCode) {
            case 401:
                throw new JamboJetAuthenticationException(
                    $errorData['message'] ?? 'Authentication failed',
                    $statusCode
                );
            case 400:
                throw new JamboJetValidationException(
                    $errorData['message'] ?? 'Validation failed',
                    $statusCode,
                    $errorData['errors'] ?? []
                );
            case 429:
                $retryAfter = $response->header('Retry-After') ?? 60;
                throw new JamboJetApiException(
                    "Rate limit exceeded. Retry after {$retryAfter} seconds.",
                    $statusCode,
                    null,
                    ['retry_after' => $retryAfter] // ADD: Include retry info
                );
            default:
                throw new JamboJetApiException(
                    $errorData['message'] ?? "API request failed with status {$statusCode}",
                    $statusCode
                );
        }
    }

    /**
     * Generate cache key for requests
     */
    protected function getCacheKey(string $url, array $data): string
    {
        $key = $this->config['cache']['prefix'] . '_' . md5($url . serialize($data));
        return $key;
    }

    /**
     * Set access token for authenticated requests
     */
    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Clear access token
     */
    public function clearAccessToken(): self
    {
        $this->accessToken = null;
        return $this;
    }
}
