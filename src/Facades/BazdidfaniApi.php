<?php

namespace Bazdidfani\ApiClient\Facades;

use Bazdidfani\ApiClient\BazdidfaniApiClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> technicalInspections(string $organizationCode, array<string, int|string|null> $query = [])
 * @method static array<string, mixed> selfStatements(string $organizationCode, array<string, int|string|null> $query = [])
 *
 * @see BazdidfaniApiClient
 */
class BazdidfaniApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bazdidfani-api';
    }
}
