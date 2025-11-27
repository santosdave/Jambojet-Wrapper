<?php

namespace SantosDave\JamboJet\Contracts;

interface OneTimeTravelNotificationInterface
{
    public function createOneTimeTravelNotification(array $notificationData): array;
    public function getOneTimeTravelNotification(string $subscriptionNumber, ?string $destination = null): array;
    public function deleteOneTimeTravelNotification(string $subscriptionNumber, ?string $destination = null): array;
}