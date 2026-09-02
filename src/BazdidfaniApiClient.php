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
        private readonly string $organizationCode,
        private readonly string $organizationHeader = 'X-Organization-Code',
        private readonly int $timeout = 15,
        private readonly int $retryTimes = 2,
        private readonly int $retrySleepMilliseconds = 200,
    ) {}

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function technicalInspections(array $query = []): array
    {
        return $this->get('/api/v1/webservice/technical-inspections', $query);
    }

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function selfStatements(array $query = []): array
    {
        return $this->get('/api/v1/webservice/self-statements', $query);
    }

    /**
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query): array
    {
        $response = $this->request()->get($path, array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null,
        ));

        return $response->throw()->json();
    }

    private function request(): PendingRequest
    {
        $this->ensureConfigured();

        return $this->http
            ->baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withToken($this->token)
            ->withHeaders([
                $this->organizationHeader => $this->organizationCode,
            ])
            ->timeout(max(1, $this->timeout))
            ->retry(
                max(1, $this->retryTimes),
                max(0, $this->retrySleepMilliseconds),
                throw: false,
            );
    }

    private function ensureConfigured(): void
    {
        if ($this->baseUrl === '') {
            throw new InvalidArgumentException('مقدار BAZDIDFANI_API_BASE_URL تنظیم نشده است.');
        }

        if ($this->token === '') {
            throw new InvalidArgumentException('مقدار BAZDIDFANI_API_TOKEN تنظیم نشده است.');
        }

        if ($this->organizationCode === '') {
            throw new InvalidArgumentException('مقدار BAZDIDFANI_ORGANIZATION_CODE تنظیم نشده است.');
        }
    }
}
