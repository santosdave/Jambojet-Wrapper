<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\EquipmentInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;


class EquipmentService implements EquipmentInterface
{
    use HandlesApiRequests, ValidatesRequests;

    public function swapEquipment(array $equipmentSwapRequest): array
    {
        $this->validateEquipmentSwapRequest($equipmentSwapRequest);

        try {
            return $this->post('api/dcs/v1/equipment/swap', $equipmentSwapRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Equipment swap failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function assignTailNumber(array $tailNumberRequest): array
    {
        $this->validateTailNumberRequest($tailNumberRequest);

        try {
            return $this->post('api/dcs/v1/equipment/tailNumber', $tailNumberRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Tail number assignment failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function validateEquipmentSwapRequest(array $data): void
    {
        $required = ['legKeys', 'newEquipmentCode'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new JamboJetValidationException("Missing required field: {$field}", 400);
            }
        }
    }

    private function validateTailNumberRequest(array $data): void
    {
        $required = ['legKey', 'tailNumber'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new JamboJetValidationException("Missing required field: {$field}", 400);
            }
        }
    }
}
