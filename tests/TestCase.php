<?php

namespace Bazdidfani\ApiClient\Tests;

use Bazdidfani\ApiClient\BazdidfaniApiClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [BazdidfaniApiClientServiceProvider::class];
    }
}
