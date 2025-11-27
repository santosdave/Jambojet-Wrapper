<?php


namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

interface EquipmentInterface
{
    public function swapEquipment(array $equipmentSwapRequest): array;
    public function assignTailNumber(array $tailNumberRequest): array;
}
