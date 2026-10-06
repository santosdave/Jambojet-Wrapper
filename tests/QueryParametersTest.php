<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Options the NSK API reads from the query string reach it there (they used to be sent as
 * HTTP headers, which the API ignores), with booleans spelled true/false.
 */
class QueryParametersTest extends TestCase
{
    private function fakeNsk(): void
    {
        Http::fake([
            '*/api/auth/v1/token/user' => self::tokenResponses('token-1'),
            '*' => Http::response(['data' => ['ok' => true]]),
        ]);
    }

    private function sent(string $path): Request
    {
        $calls = Http::recorded(fn (Request $r) => str_contains($r->url(), $path))->values();
        $this->assertCount(1, $calls, "expected one call to {$path}");

        return $calls[0][0];
    }

    public function test_commit_sends_allow_concurrent_changes_in_the_query(): void
    {
        $this->fakeNsk();

        app('jambojet')->booking()->updateAndCommitBooking(['receivedBy' => 'Test'], true);

        $request = $this->sent('api/nsk/v3/booking');
        $this->assertSame('PUT', $request->method());
        $this->assertStringEndsWith('api/nsk/v3/booking?allowConcurrentChanges=true', $request->url());
        $this->assertFalse($request->hasHeader('allowConcurrentChanges'));
    }

    public function test_a_commit_without_the_flag_has_no_query(): void
    {
        $this->fakeNsk();

        app('jambojet')->booking()->updateAndCommitBooking(['receivedBy' => 'Test']);

        $this->assertStringEndsWith('api/nsk/v3/booking', $this->sent('api/nsk/v3/booking')->url());
    }

    public function test_a_delete_sends_its_options_in_the_query_with_false_spelled_out(): void
    {
        $this->fakeNsk();

        app('jambojet')->booking()->resetClassOfService('JM~SEGMENT1', false);

        $request = $this->sent('classOfService');
        $this->assertSame('DELETE', $request->method());
        $this->assertStringEndsWith('/classOfService?overSell=false', $request->url());
    }
}
