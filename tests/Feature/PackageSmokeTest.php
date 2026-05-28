<?php

namespace Molitor\Media\Tests\Feature;

use Molitor\Media\Providers\MediaServiceProvider;
use Tests\TestCase;

class PackageSmokeTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $this->assertTrue(class_exists(MediaServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(MediaServiceProvider::class));
    }
}

