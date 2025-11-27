<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\ETicketInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

class ETicketService implements ETicketInterface
{
    use HandlesApiRequests, ValidatesRequests;

    public function getETicket(
        string $eTicketNumber,
        ?string $hostedCarrierCode = null,
        ?string $alternateCarrierCode = null
    ): array {
        $this->validateETicketNumber($eTicketNumber);

        $params = array_filter([
            'HostedCarrierCode' => $hostedCarrierCode,
            'AlternateCarrierCode' => $alternateCarrierCode,
        ], fn($v) => $v !== null);

        try {
            return $this->get("api/nsk/v2/eTickets/{$eTicketNumber}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve e-ticket: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getETicketCoupons(
        string $eTicketNumber,
        ?string $hostedCarrierCode = null,
        ?string $alternateCarrierCode = null
    ): array {
        $this->validateETicketNumber($eTicketNumber);

        $params = array_filter([
            'HostedCarrierCode' => $hostedCarrierCode,
            'AlternateCarrierCode' => $alternateCarrierCode,
        ], fn($v) => $v !== null);

        try {
            return $this->get("api/nsk/v1/eTickets/{$eTicketNumber}/coupons", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve e-ticket coupons: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getETicketCoupon(
        string $eTicketNumber,
        int $couponNumber,
        ?string $hostedCarrierCode = null,
        ?string $alternateCarrierCode = null
    ): array {
        $this->validateETicketNumber($eTicketNumber);
        $this->validateCouponNumber($couponNumber);

        $params = array_filter([
            'HostedCarrierCode' => $hostedCarrierCode,
            'AlternateCarrierCode' => $alternateCarrierCode,
        ], fn($v) => $v !== null);

        try {
            return $this->get("api/nsk/v1/eTickets/{$eTicketNumber}/coupons/{$couponNumber}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve e-ticket coupon: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function updateETicketCoupon(
        string $eTicketNumber,
        int $couponNumber,
        array $couponUpdateData
    ): array {
        $this->validateETicketNumber($eTicketNumber);
        $this->validateCouponNumber($couponNumber);
        $this->validateCouponUpdateRequest($couponUpdateData);

        try {
            return $this->put("api/nsk/v2/eTickets/{$eTicketNumber}/coupons/{$couponNumber}", $couponUpdateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update e-ticket coupon: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function validateETicketNumber(string $eTicketNumber): void
    {
        if (empty($eTicketNumber)) {
            throw new JamboJetValidationException('E-ticket number is required', 400);
        }
        // E-ticket numbers are typically 13 digits
        if (!preg_match('/^\d{13}$/', $eTicketNumber)) {
            throw new JamboJetValidationException('E-ticket number must be 13 digits', 400);
        }
    }

    private function validateCouponNumber(int $couponNumber): void
    {
        if ($couponNumber < 1) {
            throw new JamboJetValidationException('Coupon number must be positive', 400);
        }
    }

    private function validateCouponUpdateRequest(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Coupon update data cannot be empty', 400);
        }
    }
}