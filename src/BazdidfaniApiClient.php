<?php

namespace Bazdidfani\ApiClient;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use InvalidArgumentException;

class BazdidfaniApiClient
{
    public function __construct(
        private readonly Factory $http,
        private readonly string $baseUrl,
        private readonly string $token,
        private readonly string $organizationHeader = 'X-Organization-Code',
        private readonly int $timeout = 15,
        private readonly int $retryTimes = 2,
        private readonly int $retrySleepMilliseconds = 200,
    ) {}

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function technicalInspections(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/technical-inspections', $organizationCode, $query);
    }

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function selfStatements(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/self-statements', $organizationCode, $query);
    }

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, string $organizationCode, array $query): array
    {
        $response = $this->request($organizationCode)->get($path, array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null,
        ));

        return $response->throw()->json();
    }

    private function request(string $organizationCode): PendingRequest
    {
        $organizationCode = trim($organizationCode);

        $this->ensureConfigured($organizationCode);

        return $this->http
            ->baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withToken($this->token)
            ->withHeaders([
                $this->organizationHeader => $organizationCode,
            ])
            ->timeout(max(1, $this->timeout))
            ->retry(
                max(1, $this->retryTimes),
                max(0, $this->retrySleepMilliseconds),
                throw: false,
            );
    }

    private function ensureConfigured(string $organizationCode): void
    {
        if ($this->baseUrl === '') {
            throw new InvalidArgumentException('مقدار BAZDIDFANI_API_BASE_URL تنظیم نشده است.');
        }

        if ($this->token === '') {
            throw new InvalidArgumentException('مقدار BAZDIDFANI_API_TOKEN تنظیم نشده است.');
        }

        if ($organizationCode === '') {
            throw new InvalidArgumentException('کد سازمان برای ارسال درخواست الزامی است.');
        }
    }
}
