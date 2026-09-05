<?php

namespace Bazdidfani\ApiClient\Tests;

use Bazdidfani\ApiClient\BazdidfaniApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class BazdidfaniApiClientTest extends TestCase
{
    public function test_it_sends_authentication_and_organization_headers(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/technical-inspections*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        app(BazdidfaniApiClient::class)->technicalInspections('12345678', ['page' => 2]);

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-token')
            && $request->hasHeader('X-Organization-Code', '12345678')
            && $request['page'] === 2
        );
    }

    public function test_it_forwards_full_search_and_smart_number_parameters(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/self-statements*' => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        app(BazdidfaniApiClient::class)->selfStatements('87654321', [
            'query' => 'راننده آزمایشی',
            'smart_number' => 1234567,
        ]);

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-Organization-Code', '87654321')
            && $request['query'] === 'راننده آزمایشی'
            && $request['smart_number'] === 1234567
        );
    }

    public function test_it_rejects_an_empty_organization_code(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
        ]);
        Http::preventStrayRequests();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('کد سازمان برای ارسال درخواست الزامی است.');

        app(BazdidfaniApiClient::class)->technicalInspections('');
    }
}
