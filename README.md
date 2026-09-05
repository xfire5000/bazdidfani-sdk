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

## ثبت بازدید فنی

برای ثبت یک بازدید فنی جدید برای شرکت متعلق به `organizationCode`:

```php
$result = $bazdidfaniApi->submitTechnicalInspection('12345678', [
    'usage' => 'freighter', // یا 'passenger'
    'company_usage' => 1, // 1=باربری، 2=مسافربری، 3=هر دو — مطابق companies.company_usage
    'user_type' => 'company',
    'smart_number' => '1234567',
    'loader_code' => 100,
    'technical_manager_national_code' => '0098765432',
    // اختیاری:
    'branch_code' => 1, // پیش‌فرض ۱ (شرکت مادر)؛ برای ثبت روی یک شعبهٔ خاص ارسال شود
    'driver_national_code' => '0012345678',
    'driver_phone_number' => '09120000000',
    'Insurance_validity' => '2027-01-01',
    'validity_technical_examination' => '2027-01-01',
    'driver_health_card_validity' => '2027-01-01',
    'driver_certificate_validity' => '2027-01-01',
    'driver_birthdate' => '1370-01-01',
]);
```

توکن به هیچ سازمان یا شعبه‌ای محدود نیست؛ اگر شرکت مقصد چند شعبه (چند `branch_code` با یک `organization_code`) داشته باشد و `branch_code` ارسال نشود، بازدید برای شعبهٔ مادر (`branch_code=1`) ثبت می‌شود.

## مدیران فنی، ناوگان و داده‌های مرجع

```php
// مدیران فنی فعالِ شرکت
$managers = $bazdidfaniApi->technicalManagers('12345678', ['per_page' => 20]);

// ناوگان شرکت، با امکان جست‌وجو روی شمارهٔ هوشمند/پلاک
$vehicles = $bazdidfaniApi->fleet('12345678', ['query' => '1234567']);

// داده‌های مرجع — مستقل از شرکت، اما همچنان نیاز به هدر کد سازمان معتبر دارند
$cities = $bazdidfaniApi->cities('12345678', ['query' => 'تهران']);
$states = $bazdidfaniApi->states('12345678');
$loaderTypes = $bazdidfaniApi->loaderTypes('12345678');
```

`loaderTypes` برای پر کردن `loader_code` هنگام فراخوانی `submitTechnicalInspection` استفاده می‌شود.

همهٔ متدهای بالا با Facade هم در دسترس‌اند: `BazdidfaniApi::technicalManagers(...)`, `BazdidfaniApi::fleet(...)`, `BazdidfaniApi::submitTechnicalInspection(...)` و غیره.

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

### پاسخ `submitTechnicalInspection`

```json
{
    "success": true,
    "message": "بازدید فنی با موفقیت ثبت شد.",
    "data": {
        "bazdidfani": {
            "id": 512,
            "code": "TECH-512"
        }
    }
}
```

### پاسخ `technicalManagers`

```json
{
    "success": true,
    "message": "فهرست مدیران فنی شرکت با موفقیت دریافت شد.",
    "data": [
        {
            "id": 7,
            "national_code": "0098765432",
            "full_name": "مدیر فنی نمونه",
            "phone": "09120000000",
            "capacity": 10,
            "passenger_capacity": 0,
            "freighter_capacity": 10,
            "type": 1,
            "start_cooperate": "2026-01-01",
            "end_cooperate": "2027-01-01",
            "status": 1,
            "company": {
                "code": "12345678",
                "name": "شرکت نمونه"
            }
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": null },
    "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

### پاسخ `fleet`

```json
{
    "success": true,
    "message": "فهرست ناوگان شرکت با موفقیت دریافت شد.",
    "data": [
        {
            "id": 3,
            "status": "1",
            "vehicle": {
                "smart_number": "1234567",
                "plate": {
                    "first_number": "12",
                    "second_number": "345",
                    "third_character": "ب",
                    "fourth_number": "67"
                },
                "usage": "freighter",
                "VIN": "VIN-1234567",
                "date_made": "1400",
                "validity_technical_examination": "2027-01-01T00:00:00.000000Z",
                "loader": { "code": 100, "name": "بارگیر آزمایشی" }
            },
            "truck_info": {
                "capacity": 20000,
                "insurance_validity": "2027-01-01T00:00:00.000000Z",
                "insurance_number": null,
                "owner_phone_number": "09120000000",
                "chassis_number": null,
                "document_number": null,
                "document_date": null
            }
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": null },
    "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

### پاسخ `cities` / `states` / `loaderTypes`

```json
{
    "success": true,
    "message": "فهرست شهرها با موفقیت دریافت شد.",
    "data": [
        { "id": 1, "code": "1234", "name": "تهران", "state": { "code": "07", "name": "تهران" } }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": null },
    "meta": { "current_page": 1, "per_page": 15, "total": 1 }
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
