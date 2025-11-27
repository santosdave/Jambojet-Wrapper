<?php

namespace SantosDave\JamboJet\Contracts;


interface ApoInterface
{
    public function getAncillaryPricingOptions(): array;
    public function addAncillaryPricingOptions(array $inputParameters): array;
    public function updateAncillaryPricingOptions(array $inputParameters): array;
    public function deleteAllAncillaryPricingOptions(): array;
    public function updateAncillaryPricingOption(string $key, string $value): array;
    public function deleteAncillaryPricingOption(string $key): array;
}