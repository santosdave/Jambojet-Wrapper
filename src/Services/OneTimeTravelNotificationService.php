<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\OneTimeTravelNotificationInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;

class OneTimeTravelNotificationService implements OneTimeTravelNotificationInterface
{
    use HandlesApiRequests, ValidatesRequests;

    public function createOneTimeTravelNotification(array $notificationData): array
    {
        $this->validateNotificationRequest($notificationData);

        try {
            return $this->post('api/nsk/v2/oneTimeTravelNotifications', $notificationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create notification: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getOneTimeTravelNotification(string $subscriptionNumber, ?string $destination = null): array
    {
        $this->validateSubscriptionNumber($subscriptionNumber);

        $params = $destination ? ['destination' => $destination] : [];

        try {
            return $this->get("api/nsk/v2/oneTimeTravelNotifications/{$subscriptionNumber}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get notification: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function deleteOneTimeTravelNotification(string $subscriptionNumber, ?string $destination = null): array
    {
        $this->validateSubscriptionNumber($subscriptionNumber);

        $params = $destination ? ['destination' => $destination] : [];

        try {
            return $this->delete("api/nsk/v2/oneTimeTravelNotifications/{$subscriptionNumber}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete notification: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function validateNotificationRequest(array $data): void
    {
        $required = ['notificationDestination', 'marketInformation'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new JamboJetValidationException("Missing required field: {$field}", 400);
            }
        }

        // Validate destination
        if (
            !isset($data['notificationDestination']['destination']) ||
            empty($data['notificationDestination']['destination'])
        ) {
            throw new JamboJetValidationException('Notification destination is required', 400);
        }

        // Validate delivery method
        if (!isset($data['notificationDestination']['deliveryMethodCode'])) {
            throw new JamboJetValidationException('Delivery method code is required', 400);
        }

        // Validate push notifications require device name
        if ($data['notificationDestination']['deliveryMethodCode'] === 2) {
            if (!isset($data['deviceName']) || empty($data['deviceName'])) {
                throw new JamboJetValidationException('Device name required for push notifications', 400);
            }
        }

        // Validate at least one event type
        if (empty($data['events']) && empty($data['timedEvents'])) {
            throw new JamboJetValidationException('At least one event or timed event is required', 400);
        }
    }

    private function validateSubscriptionNumber(string $subscriptionNumber): void
    {
        if (empty($subscriptionNumber)) {
            throw new JamboJetValidationException('Subscription number is required', 400);
        }
    }
}
