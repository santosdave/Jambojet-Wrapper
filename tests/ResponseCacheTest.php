<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SantosDave\JamboJet\Contracts\BookingInterface;

/**
 * GET responses are cached (jambojet.cache). Many NSK endpoints answer for the booking held
 * in the caller's session, with nothing in the URL saying which booking that is, so a cached
 * answer must never be served to another session.
 */
class ResponseCacheTest extends TestCase
{
    public function test_one_session_never_gets_another_sessions_cached_booking(): void
    {
        Http::fake([
            '*/api/auth/v1/token/user' => self::tokenResponses('token-1'),
            '*/api/nsk/v1/booking' => fn (Request $request) => Http::response([
                'data' => ['recordLocator' => $request->header('Authorization')[0] === 'Bearer session-b' ? 'BBBBBB' : 'AAAAAA'],
            ]),
        ]);

        $first = app(BookingInterface::class)->getCurrentBooking();
        $other = app(BookingInterface::class)->setAccessToken('session-b')->preserveSession()->getCurrentBooking();

        $this->assertSame('AAAAAA', $first['data']['data']['recordLocator']);
        $this->assertSame('BBBBBB', $other['data']['data']['recordLocator']);
    }

    public function test_the_same_session_still_gets_its_cached_answer(): void
    {
        Http::fake([
            '*/api/auth/v1/token/user' => self::tokenResponses('token-1'),
            '*/api/nsk/v1/booking' => Http::response(['data' => ['recordLocator' => 'AAAAAA']]),
        ]);

        app(BookingInterface::class)->getCurrentBooking();
        app(BookingInterface::class)->getCurrentBooking();

        $this->assertCount(1, Http::recorded(fn (Request $request) => str_ends_with($request->url(), 'api/nsk/v1/booking')));
    }
}
