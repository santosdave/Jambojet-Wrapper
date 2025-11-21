<?php

namespace SantosDave\JamboJet\Contracts;

interface ResourcesInterface
{
    /**
     * Get airports
     * GET /api/nsk/v#/resources/airports
     */
    public function getAirports(array $criteria = []): array;

    /**
     * Get countries
     * GET /api/nsk/v#/resources/countries
     */
    public function getCountries(array $criteria = []): array;


    /**
     * Get fare types
     * GET /api/nsk/v#/resources/fareTypes
     */
    public function getFareTypes(array $criteria = []): array;

    /**
     * Get passenger types
     * GET /api/nsk/v#/resources/passengerTypes
     */
    public function getPassengerTypes(array $criteria = []): array;

    /**
     * Get station types
     * GET /api/nsk/v#/resources/stationTypes
     */
    public function getStationTypes(array $criteria = []): array;

    /**
     * Get bundle configurations
     * GET /api/nsk/v#/resources/bundles/{bundleCode}
     */
    public function getBundleConfiguration(string $bundleCode, array $params = []): array;

    /**
     * Get bundle applications
     * GET /api/nsk/v#/resources/bundles/applications
     */
    public function getBundleApplications(array $criteria): array;

    /**
     * Get bundle rules
     * GET /api/nsk/v#/resources/bundles/rules
     */
    public function getBundleRules(array $criteria = []): array;

    /**
     * Get cultures (languages)
     * GET /api/nsk/v#/resources/cultures
     */
    public function getCultures(array $criteria = []): array;

    /**
     * Get customer creation settings
     * GET /api/nsk/v#/settings/user/customerCreation
     */
    public function getCustomerCreationSettings(array $params = []): array;
}
