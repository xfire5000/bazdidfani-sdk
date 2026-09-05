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
     * ثبت یک بازدید فنی جدید برای شرکت متعلق به کد سازمان داده‌شده.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function submitTechnicalInspection(string $organizationCode, array $payload): array
    {
        return $this->post('/api/v1/webservice/technical-inspections', $organizationCode, $payload);
    }

    /**
     * فهرست مدیران فنی فعال شرکت.
     *
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function technicalManagers(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/technical-managers', $organizationCode, $query);
    }

    /**
     * فهرست ناوگان شرکت.
     *
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function fleet(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/fleet', $organizationCode, $query);
    }

    /**
     * فهرست شهرها (داده مرجع، مستقل از شرکت).
     *
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function cities(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/cities', $organizationCode, $query);
    }

    /**
     * فهرست استان‌ها (داده مرجع، مستقل از شرکت).
     *
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function states(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/states', $organizationCode, $query);
    }

    /**
     * فهرست انواع بارگیر، برای پر کردن loader_code هنگام ثبت بازدید (داده مرجع، مستقل از شرکت).
     *
     * @param  array<string, int|string|null>  $query
     * @return array<string, mixed>
     */
    public function loaderTypes(string $organizationCode, array $query = []): array
    {
        return $this->get('/api/v1/webservice/loader-types', $organizationCode, $query);
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, string $organizationCode, array $payload): array
    {
        $response = $this->request($organizationCode)->post($path, $payload);

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
