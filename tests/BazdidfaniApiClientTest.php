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

    public function test_it_submits_a_technical_inspection_as_a_post_request(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/technical-inspections' => Http::response([
                'success' => true,
                'data' => ['bazdidfani' => ['id' => 1, 'code' => 'BZ-1']],
            ]),
        ]);

        app(BazdidfaniApiClient::class)->submitTechnicalInspection('12345678', [
            'usage' => 'freighter',
            'company_usage' => 1,
            'user_type' => 'company',
            'smart_number' => '1234567',
            'loader_code' => 100,
            'technical_manager_national_code' => '0012345678',
        ]);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->hasHeader('X-Organization-Code', '12345678')
            && $request['smart_number'] === '1234567'
            && $request['technical_manager_national_code'] === '0012345678'
        );
    }

    public function test_it_lists_technical_managers_and_fleet(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/technical-managers*' => Http::response(['success' => true, 'data' => []]),
            'https://api.example.test/api/v1/webservice/fleet*' => Http::response(['success' => true, 'data' => []]),
        ]);

        app(BazdidfaniApiClient::class)->technicalManagers('12345678', ['per_page' => 10]);
        app(BazdidfaniApiClient::class)->fleet('12345678', ['query' => '1234567']);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/webservice/technical-managers')
            && $request->hasHeader('X-Organization-Code', '12345678')
            && $request['per_page'] === 10
        );
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/webservice/fleet')
            && $request['query'] === '1234567'
        );
    }

    public function test_it_lists_reference_data(): void
    {
        config()->set([
            'bazdidfani-api.base_url' => 'https://api.example.test',
            'bazdidfani-api.token' => 'test-token',
            'bazdidfani-api.retry_times' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.example.test/api/v1/webservice/cities*' => Http::response(['success' => true, 'data' => []]),
            'https://api.example.test/api/v1/webservice/states*' => Http::response(['success' => true, 'data' => []]),
            'https://api.example.test/api/v1/webservice/loader-types*' => Http::response(['success' => true, 'data' => []]),
        ]);

        app(BazdidfaniApiClient::class)->cities('12345678', ['query' => 'تهران']);
        app(BazdidfaniApiClient::class)->states('12345678');
        app(BazdidfaniApiClient::class)->loaderTypes('12345678');

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/webservice/cities')
            && $request->hasHeader('X-Organization-Code', '12345678')
            && $request['query'] === 'تهران'
        );
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/webservice/states'));
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/webservice/loader-types'));
    }
}
