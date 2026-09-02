<?php

namespace Bazdidfani\ApiClient\Tests;

use Bazdidfani\ApiClient\BazdidfaniApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class BazdidfaniApiClientTest extends TestCase
{
    public function test_it_sends_authentication_and_organization_headers(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.organization_code' => '12345678',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/technical-inspections*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        app(BazdidfaniApiClient::class)->technicalInspections(['page' => 2]);

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-token')
            && $request->hasHeader('X-Organization-Code', '12345678')
            && $request['page'] === 2
        );
    }
}
