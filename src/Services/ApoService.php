<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\ApoInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;

class ApoService implements ApoInterface
{
    use HandlesApiRequests, ValidatesRequests;

    public function getAncillaryPricingOptions(): array
    {
        try {
            return $this->get('api/nsk/v1/apo');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get APO data: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function addAncillaryPricingOptions(array $inputParameters): array
    {
        $this->validateInputParameters($inputParameters);

        try {
            return $this->post('api/nsk/v1/apo', $inputParameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add APO entries: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function updateAncillaryPricingOptions(array $inputParameters): array
    {
        $this->validateInputParameters($inputParameters);

        try {
            return $this->put('api/nsk/v1/apo', $inputParameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update APO data: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function deleteAllAncillaryPricingOptions(): array
    {
        try {
            return $this->delete('api/nsk/v1/apo');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete all APO data: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function updateAncillaryPricingOption(string $key, string $value): array
    {
        if (empty($key)) {
            throw new JamboJetValidationException('APO key is required', 400);
        }

        $params = ['inputParameterValue' => $value];

        try {
            return $this->put("api/nsk/v1/apo/{$key}", [], $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update APO entry: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function deleteAncillaryPricingOption(string $key): array
    {
        if (empty($key)) {
            throw new JamboJetValidationException('APO key is required', 400);
        }

        try {
            return $this->delete("api/nsk/v1/apo/{$key}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete APO entry: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function validateInputParameters(array $parameters): void
    {
        if (empty($parameters)) {
            throw new JamboJetValidationException('Input parameters cannot be empty', 400);
        }
    }
}
