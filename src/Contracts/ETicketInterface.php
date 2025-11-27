<?php


namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

interface ETicketInterface
{
    public function getETicket(string $eTicketNumber, ?string $hostedCarrierCode = null, ?string $alternateCarrierCode = null): array;
    public function getETicketCoupons(string $eTicketNumber, ?string $hostedCarrierCode = null, ?string $alternateCarrierCode = null): array;
    public function getETicketCoupon(string $eTicketNumber, int $couponNumber, ?string $hostedCarrierCode = null, ?string $alternateCarrierCode = null): array;
    public function updateETicketCoupon(string $eTicketNumber, int $couponNumber, array $couponUpdateData): array;
}
