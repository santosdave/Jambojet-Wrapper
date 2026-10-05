<?php

namespace SantosDave\JamboJet\Tests;

use Illuminate\Support\ServiceProvider;
use SantosDave\JamboJet\JamboJetServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_the_config_can_be_published(): void
    {
        $paths = ServiceProvider::pathsToPublish(JamboJetServiceProvider::class, 'jambojet-config');

        $this->assertCount(1, $paths);
        $this->assertFileExists(array_key_first($paths));
    }

    public function test_the_package_config_is_merged(): void
    {
        $this->assertSame(3600, config('jambojet.cache.ttl'));
    }
}
