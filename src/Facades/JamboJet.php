<?php

namespace SantosDave\JamboJet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \SantosDave\JamboJet\Contracts\AuthenticationInterface auth()
 * @method static \SantosDave\JamboJet\Contracts\AvailabilityInterface availability()
 * @method static \SantosDave\JamboJet\Contracts\BookingInterface booking()
 * @method static \SantosDave\JamboJet\Contracts\PaymentInterface payment()
 * @method static \SantosDave\JamboJet\Contracts\UserInterface user()
 * @method static \SantosDave\JamboJet\Contracts\AccountInterface account()
 * @method static \SantosDave\JamboJet\Contracts\AddOnsInterface addOns()
 * @method static \SantosDave\JamboJet\Contracts\ResourcesInterface resources()
 * @method static array getConfig()
 */
class JamboJet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'jambojet';
    }
}
