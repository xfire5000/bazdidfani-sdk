<?php

return [
    'base_url' => env('BAZDIDFANI_API_BASE_URL'),
    'token' => env('BAZDIDFANI_API_TOKEN'),
    'organization_code' => env('BAZDIDFANI_ORGANIZATION_CODE'),
    'organization_header' => env('BAZDIDFANI_ORGANIZATION_HEADER', 'X-Organization-Code'),
    'timeout' => (int) env('BAZDIDFANI_API_TIMEOUT', 15),
    'retry_times' => (int) env('BAZDIDFANI_API_RETRY_TIMES', 2),
    'retry_sleep_milliseconds' => (int) env('BAZDIDFANI_API_RETRY_SLEEP', 200),
];
