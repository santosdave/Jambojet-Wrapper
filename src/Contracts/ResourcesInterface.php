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


    // =================================================================
    //  SSR RESOURCES (15 methods) - NEW
    // =================================================================

    /**
     * Get all SSRs (Special Service Requests)
     * GET /api/nsk/v1/resources/Ssrs
     * 
     * @param array $criteria Optional filtering (ActiveOnly, CultureCode, ETag, StartIndex, ItemCount)
     * @return array SSR collection
     */
    public function getSsrs(array $criteria = []): array;

    /**
     * Get specific SSR by code
     * GET /api/nsk/v1/resources/Ssrs/{ssrCode}
     * 
     * @param string $ssrCode SSR code (4 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array SSR details
     */
    public function getSsr(string $ssrCode, ?string $cultureCode = null): array;

    /**
     * Get all SSR groups
     * GET /api/nsk/v1/resources/SsrGroups
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR groups collection
     */
    public function getSsrGroups(array $criteria = []): array;

    /**
     * Get specific SSR group
     * GET /api/nsk/v1/resources/SsrGroups/{ssrGroupCode}
     * 
     * @param string $ssrGroupCode SSR group code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR group details
     */
    public function getSsrGroup(string $ssrGroupCode, ?string $cultureCode = null): array;

    /**
     * Get all SSR nests
     * GET /api/nsk/v1/resources/SsrNests
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR nests collection
     */
    public function getSsrNests(array $criteria = []): array;

    /**
     * Get specific SSR nest
     * GET /api/nsk/v1/resources/SsrNests/{ssrNestCode}
     * 
     * @param string $ssrNestCode SSR nest code (4 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array SSR nest details
     */
    public function getSsrNest(string $ssrNestCode, ?string $cultureCode = null): array;

    /**
     * Get SSR restriction results
     * GET /api/nsk/v1/resources/SsrRestrictionResults
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR restriction results collection
     */
    public function getSsrRestrictionResults(array $criteria = []): array;

    /**
     * Get specific SSR restriction result
     * GET /api/nsk/v1/resources/SsrRestrictionResults/{ssrRestrictionResultCode}
     * 
     * @param string $ssrRestrictionResultCode Restriction result code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR restriction result details
     */
    public function getSsrRestrictionResult(string $ssrRestrictionResultCode, ?string $cultureCode = null): array;

    /**
     * Get SSR types
     * GET /api/nsk/v1/resources/SsrTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR types collection
     */
    public function getSsrTypes(array $criteria = []): array;

    /**
     * Get SSR availability types
     * GET /api/nsk/v1/resources/SsrAvailabilityTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR availability types collection
     */
    public function getSsrAvailabilityTypes(array $criteria = []): array;

    /**
     * Get SSR leg restrictions
     * GET /api/nsk/v1/resources/SsrLegRestrictions
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR leg restrictions collection
     */
    public function getSsrLegRestrictions(array $criteria = []): array;

    /**
     * Get SSRs (v2 endpoint)
     * GET /api/nsk/v2/resources/ssrs
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSRs collection (v2)
     */
    public function getSsrsV2(array $criteria = []): array;

    /**
     * Get specific SSR (v2 endpoint)
     * GET /api/nsk/v2/resources/ssrs/{ssrCode}
     * 
     * @param string $ssrCode SSR code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR details (v2)
     */
    public function getSsrV2(string $ssrCode, ?string $cultureCode = null): array;

    /**
     * Get SSR groups (v2 endpoint)
     * GET /api/nsk/v2/resources/ssrGroups
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR groups collection (v2)
     */
    public function getSsrGroupsV2(array $criteria = []): array;

    /**
     * Get specific SSR group (v2 endpoint)
     * GET /api/nsk/v2/resources/ssrGroups/{ssrGroupCode}
     * 
     * @param string $ssrGroupCode SSR group code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR group details (v2)
     */
    public function getSsrGroupV2(string $ssrGroupCode, ?string $cultureCode = null): array;

    // =================================================================
    //  FEE RESOURCES (13 methods) - NEW
    // =================================================================

    /**
     * Get specific fee by code
     * GET /api/nsk/v1/resources/fees/{feeCode}
     * 
     * @param string $feeCode Fee code (6 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array Fee details
     */
    public function getFee(string $feeCode, ?string $cultureCode = null): array;

    /**
     * Get fee details (not cached)
     * GET /api/nsk/v1/resources/fees/{feeCode}/details
     * 
     * @param string $feeCode Fee code
     * @return array Fee details with options
     */
    public function getFeeDetails(string $feeCode): array;

    /**
     * Get fee types
     * GET /api/nsk/v1/resources/feeTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fee types collection
     */
    public function getFeeTypes(array $criteria = []): array;

    /**
     * Get specific fee type
     * GET /api/nsk/v1/resources/feeTypes/{feeTypeCode}
     * 
     * @param string $feeTypeCode Fee type code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee type details
     */
    public function getFeeType(string $feeTypeCode, ?string $cultureCode = null): array;

    /**
     * Get fee categories
     * GET /api/nsk/v1/resources/feeCategories
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fee categories collection
     */
    public function getFeeCategories(array $criteria = []): array;

    /**
     * Get specific fee category
     * GET /api/nsk/v1/resources/feeCategories/{feeCategoryCode}
     * 
     * @param string $feeCategoryCode Fee category code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee category details
     */
    public function getFeeCategory(string $feeCategoryCode, ?string $cultureCode = null): array;

    /**
     * Get fees (v2 endpoint)
     * GET /api/nsk/v2/resources/fees
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fees collection (v2)
     */
    public function getFeesV2(array $criteria = []): array;

    /**
     * Get specific fee (v2 endpoint)
     * GET /api/nsk/v2/resources/fees/{feeCode}
     * 
     * @param string $feeCode Fee code
     * @param string|null $cultureCode Optional culture code
     * @return array Fee details (v2)
     */
    public function getFeeV2(string $feeCode, ?string $cultureCode = null): array;

    /**
     * Get fare basis codes
     * GET /api/nsk/v1/resources/fareBasisCodes
     * 
     * @param string|null $eTag Optional ETag for caching
     * @return array Fare basis codes list
     */
    public function getFareBasisCodes(?string $eTag = null): array;

    /**
     * Get fare discounts
     * GET /api/nsk/v1/resources/fareDiscounts
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fare discounts collection
     */
    public function getFareDiscounts(array $criteria = []): array;

    /**
     * Get specific fare discount
     * GET /api/nsk/v1/resources/fareDiscounts/{fareDiscountCode}
     * 
     * @param string $fareDiscountCode Fare discount code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare discount details
     */
    public function getFareDiscount(string $fareDiscountCode, ?string $cultureCode = null): array;

    /**
     * Get fare surcharges
     * GET /api/nsk/v1/resources/fareSurcharges
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Fare surcharges collection
     */
    public function getFareSurcharges(array $criteria = []): array;

    /**
     * Get specific fare surcharge
     * GET /api/nsk/v1/resources/fareSurcharges/{fareSurchargeCode}
     * 
     * @param string $fareSurchargeCode Fare surcharge code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare surcharge details
     */
    public function getFareSurcharge(string $fareSurchargeCode, ?string $cultureCode = null): array;


    // =================================================================
    //  STATION RESOURCES (10 methods) - NEW
    // =================================================================

    /**
     * Get station categories
     * GET /api/nsk/v1/resources/stationCategories
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Station categories collection
     */
    public function getStationCategories(array $criteria = []): array;

    /**
     * Get specific station category
     * GET /api/nsk/v1/resources/stationCategories/{stationCategoryCode}
     * 
     * @param string $stationCategoryCode Station category code
     * @param string|null $cultureCode Optional culture code
     * @return array Station category details
     */
    public function getStationCategory(string $stationCategoryCode, ?string $cultureCode = null): array;

    /**
     * Get specific station type
     * GET /api/nsk/v1/resources/stationTypes/{stationTypeCode}
     * 
     * @param string $stationTypeCode Station type code
     * @param string|null $cultureCode Optional culture code
     * @return array Station type details
     */
    public function getStationType(string $stationTypeCode, ?string $cultureCode = null): array;

    /**
     * Get stations summary
     * GET /api/nsk/v1/resources/stations/summary
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Stations summary collection
     */
    public function getStationsSummary(array $criteria = []): array;

    /**
     * Get stations (v2 endpoint)
     * GET /api/nsk/v2/resources/stations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Stations collection (v2)
     */
    public function getStationsV2(array $criteria = []): array;

    /**
     * Get specific station (v2 endpoint)
     * GET /api/nsk/v2/resources/stations/{stationCode}
     * 
     * @param string $stationCode Station code (3-letter IATA)
     * @param string|null $cultureCode Optional culture code
     * @return array Station details (v2)
     */
    public function getStationV2(string $stationCode, ?string $cultureCode = null): array;

    /**
     * Get station details (v2 endpoint)
     * GET /api/nsk/v2/resources/stations/{stationCode}/details
     * 
     * @param string $stationCode Station code
     * @return array Station detailed information (v2)
     */
    public function getStationDetailsV2(string $stationCode): array;

    /**
     * Get station city
     * GET /api/nsk/v1/resources/stations/{stationCode}/city
     * 
     * @param string $stationCode Station code
     * @return array City information for station
     */
    public function getStationCity(string $stationCode): array;

    /**
     * Get station timezone
     * GET /api/nsk/v1/resources/stations/{stationCode}/timezone
     * 
     * @param string $stationCode Station code
     * @return array Timezone information for station
     */
    public function getStationTimezone(string $stationCode): array;

    /**
     * Get specific fare type by code
     * GET /api/nsk/v1/resources/FareTypes/{fareTypeCode}
     * 
     * @param string $fareTypeCode Fare type code
     * @param string|null $cultureCode Optional culture code
     * @return array Fare type details
     */
    public function getFareType(string $fareTypeCode, ?string $cultureCode = null): array;

    // =================================================================
    //  DOCUMENT TYPE RESOURCES (6 methods) - NEW
    // =================================================================

    /**
     * Get document types
     * GET /api/nsk/v1/resources/DocumentTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document types collection (passports, visas, IDs, etc.)
     */
    public function getDocumentTypes(array $criteria = []): array;

    /**
     * Get specific document type
     * GET /api/nsk/v1/resources/DocumentTypes/{documentTypeCode}
     * 
     * @param string $documentTypeCode Document type code (4 chars max)
     * @param string|null $cultureCode Optional culture code
     * @return array Document type details
     */
    public function getDocumentType(string $documentTypeCode, ?string $cultureCode = null): array;

    /**
     * Get document type groups
     * GET /api/nsk/v1/resources/DocumentTypeGroups
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document type groups collection
     */
    public function getDocumentTypeGroups(array $criteria = []): array;

    /**
     * Get document type requirements
     * GET /api/nsk/v1/resources/DocumentTypeRequirements
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document requirements collection
     */
    public function getDocumentTypeRequirements(array $criteria = []): array;

    /**
     * Get document types (v2 endpoint)
     * GET /api/nsk/v2/resources/documentTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Document types collection (v2)
     */
    public function getDocumentTypesV2(array $criteria = []): array;

    /**
     * Get specific document type (v2 endpoint)
     * GET /api/nsk/v2/resources/documentTypes/{documentTypeCode}
     * 
     * @param string $documentTypeCode Document type code
     * @param string|null $cultureCode Optional culture code
     * @return array Document type details (v2)
     */
    public function getDocumentTypeV2(string $documentTypeCode, ?string $cultureCode = null): array;

    // =================================================================
    //  MARKET & ROUTE RESOURCES (12 methods) - NEW
    // =================================================================

    /**
     * Get markets
     * GET /api/nsk/v2/resources/markets
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Markets collection
     */
    public function getMarkets(array $criteria = []): array;

    /**
     * Get markets by location
     * GET /api/nsk/v2/resources/markets/{locationCode}
     * 
     * @param string $locationCode Location code (3 chars)
     * @param string|null $cultureCode Optional culture code
     * @return array Markets for location
     */
    public function getMarketsByLocation(string $locationCode, ?string $cultureCode = null): array;

    /**
     * Get specific market
     * GET /api/nsk/v2/resources/markets/{locationCode}/{travelLocationCode}
     * 
     * @param string $locationCode Origin location code
     * @param string $travelLocationCode Destination location code
     * @param string|null $cultureCode Optional culture code
     * @return array Market details
     */
    public function getMarket(string $locationCode, string $travelLocationCode, ?string $cultureCode = null): array;

    /**
     * Get market routes
     * GET /api/nsk/v1/resources/marketRoutes
     * 
     * @param array $params Query parameters (DepartureStation, ArrivalStation)
     * @return array Market routes collection
     */
    public function getMarketRoutes(array $params = []): array;

    /**
     * Get cities
     * GET /api/nsk/v1/resources/Cities
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Cities collection
     */
    public function getCities(array $criteria = []): array;

    /**
     * Get specific city
     * GET /api/nsk/v1/resources/Cities/{cityCode}
     * 
     * @param string $cityCode City code (3 chars)
     * @param string|null $cultureCode Optional culture code
     * @return array City details
     */
    public function getCity(string $cityCode, ?string $cultureCode = null): array;

    /**
     * Get locations
     * GET /api/nsk/v1/resources/Locations
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Locations collection
     */
    public function getLocations(array $criteria = []): array;

    /**
     * Get specific location
     * GET /api/nsk/v1/resources/Locations/{locationCode}
     * 
     * @param string $locationCode Location code
     * @param string|null $cultureCode Optional culture code
     * @return array Location details
     */
    public function getLocation(string $locationCode, ?string $cultureCode = null): array;

    /**
     * Get MACs (Metropolitan Area Codes)
     * GET /api/nsk/v2/resources/macs
     * 
     * @param array $criteria Optional filtering criteria
     * @return array MACs collection
     */
    public function getMacs(array $criteria = []): array;

    /**
     * Get specific MAC
     * GET /api/nsk/v2/resources/macs/{macCode}
     * 
     * @param string $macCode MAC code
     * @param string|null $cultureCode Optional culture code
     * @return array MAC details
     */
    public function getMac(string $macCode, ?string $cultureCode = null): array;

    /**
     * Get subzones for a zone
     * GET /api/nsk/v1/resources/zones/{zoneCode}/subZones
     * 
     * @param string $zoneCode Zone code
     * @return array Subzones collection
     */
    public function getSubZones(string $zoneCode): array;

    /**
     * Get specific subzone
     * GET /api/nsk/v1/resources/zones/{zoneCode}/subZones/{subZoneCode}
     * 
     * @param string $zoneCode Zone code
     * @param string $subZoneCode Subzone code
     * @return array Subzone details
     */
    public function getSubZone(string $zoneCode, string $subZoneCode): array;

    // =================================================================
    //  TAX RESOURCES (8 methods) - NEW
    // =================================================================

    /**
     * Get tax codes
     * GET /api/nsk/v1/resources/TaxCodes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax codes collection
     */
    public function getTaxCodes(array $criteria = []): array;

    /**
     * Get specific tax code
     * GET /api/nsk/v1/resources/TaxCodes/{taxCode}
     * 
     * @param string $taxCode Tax code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax code details
     */
    public function getTaxCode(string $taxCode, ?string $cultureCode = null): array;

    /**
     * Get tax types
     * GET /api/nsk/v1/resources/TaxTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax types collection
     */
    public function getTaxTypes(array $criteria = []): array;

    /**
     * Get specific tax type
     * GET /api/nsk/v1/resources/TaxTypes/{taxTypeCode}
     * 
     * @param string $taxTypeCode Tax type code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax type details
     */
    public function getTaxType(string $taxTypeCode, ?string $cultureCode = null): array;

    /**
     * Get taxes (v2 endpoint)
     * GET /api/nsk/v2/resources/taxes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Taxes collection (v2)
     */
    public function getTaxesV2(array $criteria = []): array;

    /**
     * Get specific tax (v2 endpoint)
     * GET /api/nsk/v2/resources/taxes/{taxCode}
     * 
     * @param string $taxCode Tax code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax details (v2)
     */
    public function getTaxV2(string $taxCode, ?string $cultureCode = null): array;

    /**
     * Get tax categories
     * GET /api/nsk/v1/resources/TaxCategories
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Tax categories collection
     */
    public function getTaxCategories(array $criteria = []): array;

    /**
     * Get specific tax category
     * GET /api/nsk/v1/resources/TaxCategories/{taxCategoryCode}
     * 
     * @param string $taxCategoryCode Tax category code
     * @param string|null $cultureCode Optional culture code
     * @return array Tax category details
     */
    public function getTaxCategory(string $taxCategoryCode, ?string $cultureCode = null): array;

    // =================================================================
    //  CARRIER & EXTERNAL SYSTEM RESOURCES (5 methods) - NEW
    // =================================================================

    /**
     * Get carriers
     * GET /api/nsk/v1/resources/carriers
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Carriers collection
     */
    public function getCarriers(array $criteria = []): array;

    /**
     * Get specific carrier
     * GET /api/nsk/v1/resources/carriers/{carrierCode}
     * 
     * @param string $carrierCode Carrier code (2 chars, IATA airline code)
     * @param string|null $cultureCode Optional culture code
     * @return array Carrier details
     */
    public function getCarrier(string $carrierCode, ?string $cultureCode = null): array;

    /**
     * Get external systems
     * GET /api/nsk/v1/resources/ExternalSystems
     * 
     * @param array $criteria Optional filtering criteria
     * @return array External systems collection
     */
    public function getExternalSystems(array $criteria = []): array;

    /**
     * Get specific external system
     * GET /api/nsk/v1/resources/ExternalSystems/{externalSystemCode}
     * 
     * @param string $externalSystemCode External system code
     * @param string|null $cultureCode Optional culture code
     * @return array External system details
     */
    public function getExternalSystem(string $externalSystemCode, ?string $cultureCode = null): array;

    /**
     * Get SSR types (v2 endpoint)
     * GET /api/nsk/v2/resources/ssrTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array SSR types collection (v2)
     */
    public function getSsrTypesV2(array $criteria = []): array;

    // =================================================================
    //  PERSON & COMMUNICATION RESOURCES (8 methods) - NEW
    // =================================================================

    /**
     * Get person information types
     * GET /api/nsk/v1/resources/PersonInformationTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Person information types collection
     */
    public function getPersonInformationTypes(array $criteria = []): array;

    /**
     * Get notification delivery methods
     * GET /api/nsk/v1/resources/NotificationDeliveryMethods
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Notification delivery methods collection
     */
    public function getNotificationDeliveryMethods(array $criteria = []): array;

    /**
     * Get gender localizations
     * GET /api/nsk/v1/resources/genders
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Gender localizations
     */
    public function getGenders(?string $cultureCode = null): array;

    /**
     * Get message types
     * GET /api/nsk/v1/resources/MessageTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Message types collection
     */
    public function getMessageTypes(array $criteria = []): array;

    /**
     * Get specific message type
     * GET /api/nsk/v1/resources/MessageTypes/{messageTypeCode}
     * 
     * @param string $messageTypeCode Message type code
     * @param string|null $cultureCode Optional culture code
     * @return array Message type details
     */
    public function getMessageType(string $messageTypeCode, ?string $cultureCode = null): array;

    /**
     * Get payment methods
     * GET /api/nsk/v2/resources/paymentMethods
     * 
     * @param array $params Query parameters (PaymentMethodType, CurrencyCode, etc.)
     * @return array Payment methods collection
     */
    public function getPaymentMethods(array $params = []): array;

    /**
     * Get specific payment method
     * GET /api/nsk/v2/resources/paymentMethods/{paymentMethodCode}
     * 
     * @param string $paymentMethodCode Payment method code
     * @param array $params Query parameters (PaymentMethodType, CurrencyCode, CultureCode)
     * @return array Payment method details
     */
    public function getPaymentMethod(string $paymentMethodCode, array $params = []): array;

    /**
     * Get SSR types specific code (Note: This is duplicate from earlier, may need to consolidate)
     * GET /api/nsk/v1/resources/SsrTypes/{ssrTypeCode}
     * 
     * @param string $ssrTypeCode SSR type code
     * @param string|null $cultureCode Optional culture code
     * @return array SSR type details
     */
    public function getSsrType(string $ssrTypeCode, ?string $cultureCode = null): array;

    // =================================================================
    //  STATUS RESOURCES (8 methods) - NEW
    // =================================================================

    /**
     * Get segment statuses
     * GET /api/nsk/v1/resources/SegmentStatuses
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Segment statuses collection
     */
    public function getSegmentStatuses(array $criteria = []): array;

    /**
     * Get specific segment status
     * GET /api/nsk/v1/resources/SegmentStatuses/{segmentStatus}
     * 
     * @param int $segmentStatus Segment status code
     * @param string|null $cultureCode Optional culture code
     * @return array Segment status details
     */
    public function getSegmentStatus(int $segmentStatus, ?string $cultureCode = null): array;

    /**
     * Get passenger statuses
     * GET /api/nsk/v1/resources/PassengerStatuses
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Passenger statuses collection
     */
    public function getPassengerStatuses(array $criteria = []): array;

    /**
     * Get specific passenger status
     * GET /api/nsk/v1/resources/PassengerStatuses/{passengerStatus}
     * 
     * @param int $passengerStatus Passenger status code
     * @param string|null $cultureCode Optional culture code
     * @return array Passenger status details
     */
    public function getPassengerStatus(int $passengerStatus, ?string $cultureCode = null): array;

    /**
     * Get standby priorities
     * GET /api/nsk/v1/resources/StandByPriorities
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Standby priorities collection
     */
    public function getStandbyPriorities(array $criteria = []): array;

    /**
     * Get specific standby priority
     * GET /api/nsk/v1/resources/StandByPriorities/{priorityCode}
     * 
     * @param string $priorityCode Standby priority code
     * @param string|null $cultureCode Optional culture code
     * @return array Standby priority details
     */
    public function getStandbyPriority(string $priorityCode, ?string $cultureCode = null): array;

    /**
     * Get booking statuses
     * GET /api/nsk/v1/resources/bookingStatuses
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Booking statuses collection
     */
    public function getBookingStatuses(?string $cultureCode = null): array;

    /**
     * Get journey statuses
     * GET /api/nsk/v1/resources/journeyStatuses
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Journey statuses collection
     */
    public function getJourneyStatuses(?string $cultureCode = null): array;

    // =================================================================
    //  ROLE & PERMISSION RESOURCES (5 methods) - NEW
    // =================================================================

    /**
     * Get roles
     * GET /api/nsk/v1/resources/roles
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Roles collection
     */
    public function getRoles(?string $cultureCode = null): array;

    /**
     * Get specific role (v2 endpoint)
     * GET /api/nsk/v2/resources/roles/{roleCode}
     * 
     * @param string $roleCode Role code
     * @return array Role details
     */
    public function getRole(string $roleCode): array;

    /**
     * Delete role (BETA - may be removed in future)
     * DELETE /api/nsk/v1/resources/roles/{roleCode}
     * 
     * @param string $roleCode Role code
     * @return array Deletion confirmation
     */
    public function deleteRole(string $roleCode): array;

    /**
     * Get organization types
     * GET /api/nsk/v1/resources/organizationTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Organization types collection
     */
    public function getOrganizationTypes(array $criteria = []): array;

    /**
     * Get specific organization type
     * GET /api/nsk/v1/resources/organizationTypes/{organizationTypeCode}
     * 
     * @param string $organizationTypeCode Organization type code
     * @param string|null $cultureCode Optional culture code
     * @return array Organization type details
     */
    public function getOrganizationType(string $organizationTypeCode, ?string $cultureCode = null): array;

    // =================================================================
    //  CONTENT RESOURCES (10 methods) - NEW
    // =================================================================

    /**
     * Get all content items
     * GET /api/nsk/v1/resources/contents
     * 
     * @param array $params Query parameters (ContentType, ActiveOnly, StartDate, EndDate, etc.)
     * @return array Content items collection
     */
    public function getContents(array $params = []): array;

    /**
     * Create content item
     * POST /api/nsk/v1/resources/contents
     * 
     * @param array $contentData Content creation data
     * @return array Created content item
     */
    public function createContent(array $contentData): array;

    /**
     * Get specific content item
     * GET /api/nsk/v1/resources/contents/{contentId}
     * 
     * @param int $contentId Content ID
     * @param bool $convertRtfToHtml Convert RTF to HTML
     * @param string|null $eTag ETag for caching
     * @return array Content item details
     */
    public function getContent(int $contentId, bool $convertRtfToHtml = false, ?string $eTag = null): array;

    /**
     * Delete content item
     * DELETE /api/nsk/v1/resources/contents/{contentId}
     * Currently only supports deleting news content items
     * 
     * @param int $contentId Content ID
     * @return array Deletion confirmation
     */
    public function deleteContent(int $contentId): array;

    /**
     * Get resource settings categories
     * GET /api/nsk/v1/resources/settings/categories
     * 
     * @param string|null $eTag ETag for caching
     * @return array Settings categories collection
     */
    public function getSettingsCategories(?string $eTag = null): array;

    /**
     * Get resource settings data
     * POST /api/nsk/v1/resources/settings/data
     * 
     * @param array $settingsRequest Settings request data
     * @param string|null $eTag ETag for caching
     * @return array Settings data
     */
    public function getSettingsData(array $settingsRequest, ?string $eTag = null): array;

    /**
     * Get specific settings category
     * GET /api/nsk/v1/resources/settings/categories/{categoryPath}
     * 
     * @param string $categoryPath Category path
     * @param string|null $eTag ETag for caching
     * @return array Category details
     */
    public function getSettingsCategory(string $categoryPath, ?string $eTag = null): array;

    /**
     * Get contact types
     * GET /api/nsk/v1/resources/contactTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Contact types collection
     */
    public function getContactTypes(array $criteria = []): array;

    /**
     * Get specific contact type
     * GET /api/nsk/v1/resources/contactTypes/{contactTypeCode}
     * 
     * @param string $contactTypeCode Contact type code
     * @param string|null $cultureCode Optional culture code
     * @return array Contact type details
     */
    public function getContactType(string $contactTypeCode, ?string $cultureCode = null): array;

    /**
     * Get titles (salutations)
     * GET /api/nsk/v1/resources/titles
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Titles collection (Mr, Mrs, Ms, Dr, etc.)
     */
    public function getTitles(array $criteria = []): array;

    // =================================================================
    //  MISCELLANEOUS RESOURCES (19 methods) - NEW
    // =================================================================

    /**
     * Get change reasons
     * GET /api/nsk/v1/resources/changeReasons
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Change reasons collection
     */
    public function getChangeReasons(array $criteria = []): array;

    /**
     * Get specific change reason
     * GET /api/nsk/v1/resources/changeReasons/{changeReasonCode}
     * 
     * @param string $changeReasonCode Change reason code
     * @param string|null $cultureCode Optional culture code
     * @return array Change reason details
     */
    public function getChangeReason(string $changeReasonCode, ?string $cultureCode = null): array;

    /**
     * Get comments types
     * GET /api/nsk/v1/resources/commentTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Comment types collection
     */
    public function getCommentTypes(array $criteria = []): array;

    /**
     * Get specific comment type
     * GET /api/nsk/v1/resources/commentTypes/{commentTypeCode}
     * 
     * @param string $commentTypeCode Comment type code
     * @param string|null $cultureCode Optional culture code
     * @return array Comment type details
     */
    public function getCommentType(string $commentTypeCode, ?string $cultureCode = null): array;

    /**
     * Get currency codes
     * GET /api/nsk/v1/resources/currencyCodes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Currency codes collection
     */
    public function getCurrencyCodes(array $criteria = []): array;

    /**
     * Get specific currency code
     * GET /api/nsk/v1/resources/currencyCodes/{currencyCode}
     * 
     * @param string $currencyCode Currency code (3 chars, e.g., USD, EUR, KES)
     * @param string|null $cultureCode Optional culture code
     * @return array Currency code details
     */
    public function getCurrencyCode(string $currencyCode, ?string $cultureCode = null): array;

    /**
     * Get equipment types
     * GET /api/nsk/v1/resources/equipmentTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Equipment types collection
     */
    public function getEquipmentTypes(array $criteria = []): array;

    /**
     * Get specific equipment type
     * GET /api/nsk/v1/resources/equipmentTypes/{equipmentTypeCode}
     * 
     * @param string $equipmentTypeCode Equipment type code
     * @param string|null $cultureCode Optional culture code
     * @return array Equipment type details
     */
    public function getEquipmentType(string $equipmentTypeCode, ?string $cultureCode = null): array;

    /**
     * Get identification types
     * GET /api/nsk/v1/resources/identificationTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Identification types collection
     */
    public function getIdentificationTypes(array $criteria = []): array;

    /**
     * Get specific identification type
     * GET /api/nsk/v1/resources/identificationTypes/{identificationTypeCode}
     * 
     * @param string $identificationTypeCode Identification type code
     * @param string|null $cultureCode Optional culture code
     * @return array Identification type details
     */
    public function getIdentificationType(string $identificationTypeCode, ?string $cultureCode = null): array;

    /**
     * Get phone number types
     * GET /api/nsk/v1/resources/phoneNumberTypes
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Phone number types collection
     */
    public function getPhoneNumberTypes(?string $cultureCode = null): array;

    /**
     * Get supplier types
     * GET /api/nsk/v1/resources/supplierTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Supplier types collection
     */
    public function getSupplierTypes(array $criteria = []): array;

    /**
     * Get specific supplier type
     * GET /api/nsk/v1/resources/supplierTypes/{supplierTypeCode}
     * 
     * @param string $supplierTypeCode Supplier type code
     * @param string|null $cultureCode Optional culture code
     * @return array Supplier type details
     */
    public function getSupplierType(string $supplierTypeCode, ?string $cultureCode = null): array;

    /**
     * Get time periods
     * GET /api/nsk/v1/resources/timePeriods
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Time periods collection
     */
    public function getTimePeriods(array $criteria = []): array;

    /**
     * Get specific time period
     * GET /api/nsk/v1/resources/timePeriods/{timePeriodCode}
     * 
     * @param string $timePeriodCode Time period code
     * @param string|null $cultureCode Optional culture code
     * @return array Time period details
     */
    public function getTimePeriod(string $timePeriodCode, ?string $cultureCode = null): array;

    /**
     * Get travel document requirement types
     * GET /api/nsk/v1/resources/travelDocumentRequirementTypes
     * 
     * @param array $criteria Optional filtering criteria
     * @return array Travel document requirement types collection
     */
    public function getTravelDocumentRequirementTypes(array $criteria = []): array;

    /**
     * Get specific travel document requirement type
     * GET /api/nsk/v1/resources/travelDocumentRequirementTypes/{requirementTypeCode}
     * 
     * @param string $requirementTypeCode Requirement type code
     * @param string|null $cultureCode Optional culture code
     * @return array Requirement type details
     */
    public function getTravelDocumentRequirementType(string $requirementTypeCode, ?string $cultureCode = null): array;

    /**
     * Get unit types
     * GET /api/nsk/v1/resources/unitTypes
     * 
     * @param string|null $cultureCode Optional culture code
     * @return array Unit types collection
     */
    public function getUnitTypes(?string $cultureCode = null): array;
}