<?php
/**
 * CFBaseService
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */
namespace Bkstar123\CFBuddy\Services;

use GuzzleHttp\Client;

class CFServiceBase
{
    /**
     * @var GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Create instance
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('bkstar123_laravel_cfbuddy.cloudflare.base_url'),
            'headers' => [
                'X-Auth-Email' => config('bkstar123_laravel_cfbuddy.cloudflare.api_email'),
                'X-Auth-Key'   => config('bkstar123_laravel_cfbuddy.cloudflare.api_key'),
                'Authorization' => 'Bearer ' . config('bkstar123_laravel_cfbuddy.cloudflare.api_token'),
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
