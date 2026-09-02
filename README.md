# Laravel Bazdidfani API Client

کلاینت Laravel برای دریافت صفحه‌بندی‌شدهٔ بازدیدهای فنی و خوداظهاری‌ها از وب‌سرویس بازدید فنی.

## نصب از Packagist

بعد از انتشار پکیج در Packagist، مصرف‌کننده فقط این فرمان را اجرا می‌کند:

```bash
composer require bazdidfani/laravel-api-client:^1.0
```

Laravel سرویس‌پروایدر و Facade را با package discovery ثبت می‌کند.

## انتشار ریپو برای Composer

محتویات همین پوشه را در یک Git repository مستقل، برای مثال با نام `bazdidfani-laravel-api-client`، قرار دهید:

```bash
git init
git add .
git commit -m "Initial Laravel API client release"
git branch -M main
git remote add origin https://github.com/YOUR_ORGANIZATION/bazdidfani-laravel-api-client.git
git push -u origin main
git tag v1.0.0
git push origin v1.0.0
```

سپس آدرس ریپو را در [Packagist](https://packagist.org/packages/submit) ثبت کنید. نام موجود در `composer.json` برابر `bazdidfani/laravel-api-client` است؛ بنابراین پس از ثبت و ایجاد tag، فرمان `composer require` بدون تنظیم اضافی کار می‌کند.

## نصب مستقیم از Git بدون Packagist

برای ریپوی عمومی یا خصوصی می‌توان repository از نوع VCS تعریف کرد:

```bash
composer config repositories.bazdidfani-api-client vcs https://github.com/YOUR_ORGANIZATION/bazdidfani-laravel-api-client.git
composer require bazdidfani/laravel-api-client:dev-main
```

پس از ایجاد tag پایدار:

```bash
composer require bazdidfani/laravel-api-client:^1.0
```

برای GitHub خصوصی، دسترسی Composer را با توکن GitHub یا SSH همان محیط CI/سرور تنظیم کنید و توکن را داخل `composer.json` قرار ندهید.

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
BAZDIDFANI_API_TOKEN=replace-with-the-issued-static-token
BAZDIDFANI_ORGANIZATION_CODE=12345678
BAZDIDFANI_ORGANIZATION_HEADER=X-Organization-Code
BAZDIDFANI_API_TIMEOUT=15
BAZDIDFANI_API_RETRY_TIMES=2
BAZDIDFANI_API_RETRY_SLEEP=200
```

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
        return $this->bazdidfaniApi->technicalInspections([
            'page' => 1,
            'per_page' => 20,
            'status' => 1,
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
        ]);
    }
}
```

دریافت خوداظهاری‌ها:

```php
$result = $bazdidfaniApi->selfStatements([
    'page' => 1,
    'per_page' => 20,
]);
```

یا با Facade:

```php
use Bazdidfani\ApiClient\Facades\BazdidfaniApi;

$result = BazdidfaniApi::technicalInspections(['per_page' => 50]);
```

خروجی هر متد آرایهٔ JSON پاسخ سرور، شامل `data`، `links`، `meta`، `success` و `message` است. پاسخ‌های ناموفق با `Illuminate\Http\Client\RequestException` گزارش می‌شوند.

## اجرای تست پکیج

```bash
composer install
composer test
```
