<?php
/**
 * All settings for bkstar123/laravel-cloudflare-buddy package
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */

return [
    'cloudflare' => [
        'base_url' => env('CF_BASE_URI'),
        'api_email' => env('CF_API_EMAIL', ''),
        'api_key' => env('CF_API_KEY', '')
    ]
];
