# Laravel Bazdidfani API Client

کلاینت Laravel برای دریافت صفحه‌بندی‌شدهٔ بازدیدهای فنی و خوداظهاری‌ها از وب‌سرویس بازدید فنی.

## نصب از Packagist

بعد از انتشار پکیج در Packagist، مصرف‌کننده فقط این فرمان را اجرا می‌کند:

```bash
composer require bazdidfani/laravel-api-client:^1.0
```

Laravel سرویس‌پروایدر و Facade را با package discovery ثبت می‌کند.

## نصب محلی با Path Repository

اگر این پوشه داخل پروژهٔ مصرف‌کننده قرار دارد، به `composer.json` آن پروژه اضافه کنید:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/bazdidfani-api-client",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

سپس اجرا کنید:

```bash
composer require bazdidfani/laravel-api-client:@dev
```

## تنظیمات

در `.env` پروژهٔ مصرف‌کننده:

```dotenv
BAZDIDFANI_API_BASE_URL=https://api.example.com
BAZDIDFANI_API_TOKEN=bdf_replace-with-the-issued-site-token
BAZDIDFANI_ORGANIZATION_HEADER=X-Organization-Code
BAZDIDFANI_API_TIMEOUT=15
BAZDIDFANI_API_RETRY_TIMES=2
BAZDIDFANI_API_RETRY_SLEEP=200
```

برای هر سایت باید از سرور بازدید فنی یک توکن اختصاصی `bdf_...` دریافت کنید. این توکن به هیچ سازمان خاصی محدود نیست و برای هر کد سازمانی معتبر در سرور قابل استفاده است.

کد سازمان در `.env` نگهداری نمی‌شود و باید در هر فراخوانی متد به‌صورت اجباری ارسال شود. تنظیم `BAZDIDFANI_ORGANIZATION_HEADER` فقط نام هدری است که پکیج کد سازمان را داخل آن می‌فرستد؛ اختیاری است و در صورت حذف، مقدار `X-Organization-Code` استفاده می‌شود. برای سناریوی عادی نیازی به تعریف این متغیر ندارید و فقط زمانی آن را تنظیم کنید که سرور API نام هدر دیگری تعیین کرده باشد.

انتشار فایل config اختیاری است:

```bash
php artisan vendor:publish --tag=bazdidfani-api-config
```

## استفاده با Dependency Injection

```php
use Bazdidfani\ApiClient\BazdidfaniApiClient;

final class InspectionController
{
    public function __construct(
        private readonly BazdidfaniApiClient $bazdidfaniApi,
    ) {}

    public function index(): array
    {
        return $this->bazdidfaniApi->technicalInspections('12345678', [
            'page' => 1,
            'per_page' => 20,
            'status' => 1,
            'query' => 'راننده آزمایشی',
            'smart_number' => 1234567,
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
        ]);
    }
}
```

دریافت خوداظهاری‌ها:

```php
$result = $bazdidfaniApi->selfStatements('12345678', [
    'page' => 1,
    'per_page' => 20,
]);
```

یا با Facade:

```php
use Bazdidfani\ApiClient\Facades\BazdidfaniApi;

$result = BazdidfaniApi::technicalInspections('12345678', ['per_page' => 50]);
```

پارامتر `query` در فیلدهای عمومی بازدید جستجو می‌کند و `smart_number` فقط شماره هوشمند هفت‌رقمی دقیق را برمی‌گرداند. هر دو پارامتر برای متدهای `technicalInspections` و `selfStatements` قابل استفاده هستند.

خروجی هر متد آرایهٔ JSON پاسخ سرور، شامل `data`، `links`، `meta`، `success` و `message` است. پاسخ‌های ناموفق با `Illuminate\Http\Client\RequestException` گزارش می‌شوند.

## نمونه پاسخ‌ها

### پاسخ `technicalInspections`

```json
{
    "data": [
        {
            "id": 100,
            "code": "TECH-100",
            "kind": "technical_inspection",
            "self_statement": false,
            "status": {
                "code": 4,
                "title": "در حال انجام بازدید فنی"
            },
            "type": 1,
            "description": null,
            "vehicle": {
                "smart_number": "1234567",
                "plate": {
                    "first_number": "12",
                    "second_number": "345",
                    "third_character": "ب",
                    "fourth_number": "67"
                },
                "loader_type": "بارگیر",
                "insurance_validity": "2027-01-01 00:00:00"
            },
            "driver": {
                "national_code": "0012345678",
                "full_name": "راننده آزمایشی",
                "father_name": "نام پدر",
                "health_card_validity": "2027-01-01 00:00:00",
                "smart_card_validity": "2027-01-01 00:00:00"
            },
            "technical_manager_national_code": "0098765432",
            "technical_inspection": {
                "id": 200,
                "status": "in_progress",
                "description": null,
                "latitude": "35.68920000",
                "longitude": "51.38900000",
                "external_id": null,
                "submitted_at": null,
                "started_at": "2026-09-02T10:30:00.000000Z"
            },
            "organization": {
                "code": "12345678",
                "name": "شرکت نمونه"
            },
            "created_at": "2026-09-02T10:00:00+00:00",
            "updated_at": "2026-09-02T10:30:00+00:00"
        }
    ],
    "links": {
        "first": "https://api.example.com/api/v1/webservice/technical-inspections?page=1",
        "last": "https://api.example.com/api/v1/webservice/technical-inspections?page=3",
        "prev": null,
        "next": "https://api.example.com/api/v1/webservice/technical-inspections?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "per_page": 20,
        "to": 20,
        "total": 42
    },
    "success": true,
    "message": "فهرست بازدیدهای فنی با موفقیت دریافت شد."
}
```

### پاسخ `selfStatements`

ساختار صفحه‌بندی و مشخصات خودرو و راننده مشابه پاسخ بالا است. تفاوت رکورد خوداظهاری به شکل زیر است:

```json
{
    "data": [
        {
            "id": 101,
            "code": "SELF-101",
            "kind": "self_statement",
            "self_statement": true,
            "status": {
                "code": 10,
                "title": "در حال انجام خوداظهاری"
            },
            "type": 1,
            "description": null,
            "vehicle": {
                "smart_number": "1234567",
                "plate": {
                    "first_number": "12",
                    "second_number": "345",
                    "third_character": "ب",
                    "fourth_number": "67"
                },
                "loader_type": "بارگیر",
                "insurance_validity": "2027-01-01 00:00:00"
            },
            "driver": {
                "national_code": "0012345678",
                "full_name": "راننده آزمایشی",
                "father_name": "نام پدر",
                "health_card_validity": "2027-01-01 00:00:00",
                "smart_card_validity": "2027-01-01 00:00:00"
            },
            "technical_manager_national_code": "0098765432",
            "technical_inspection": null,
            "organization": {
                "code": "12345678",
                "name": "شرکت نمونه"
            },
            "created_at": "2026-09-02T10:00:00+00:00",
            "updated_at": "2026-09-02T10:30:00+00:00"
        }
    ],
    "links": {
        "first": "https://api.example.com/api/v1/webservice/self-statements?page=1",
        "last": "https://api.example.com/api/v1/webservice/self-statements?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "per_page": 20,
        "to": 1,
        "total": 1
    },
    "success": true,
    "message": "فهرست خوداظهاری‌ها با موفقیت دریافت شد."
}
```

### پاسخ ناموفق

برای نمونه، اگر کد سازمان معتبر نباشد سرور پاسخ `403` برمی‌گرداند:

```json
{
    "success": false,
    "message": "دسترسی برای کد سازمانی ارسال‌شده مجاز نیست."
}
```

جزئیات پاسخ ناموفق از exception قابل دریافت است:

```php
use Illuminate\Http\Client\RequestException;

try {
    $result = $bazdidfaniApi->technicalInspections('12345678');
} catch (RequestException $exception) {
    $status = $exception->response->status();
    $error = $exception->response->json();
}
```

## اجرای تست پکیج

```bash
composer install
composer test
```
