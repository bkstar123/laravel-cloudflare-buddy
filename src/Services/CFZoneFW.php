<?php
/**
 * Interact with Cloudflare API to manage firewall rule configuration for a zone
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */
namespace Bkstar123\CFBuddy\Services;

use Exception;

class CFZoneFW
{
    /**
     * Create a new firewall rule for a zone
     *
     * @param string $zoneID
     * @param string $action
     * @param array $filter
     * @param string $description
     * @return boolean
     */
    public function createFirewallRule($zoneID, $action, $filter, $description)
    {
        $payload = [
            "action" => $action,
            "filter" => $filter,
            'description' => $description
        ];
        $url = "zones/$zoneID/firewall/rules";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => config('bkstar123_laravel_cfbuddy.cloudflare.base_url') . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '['.json_encode($payload).']',
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-Auth-Key: " . config('bkstar123_laravel_cfbuddy.cloudflare.api_key'),
                "X-Auth-Email: " . config('bkstar123_laravel_cfbuddy.cloudflare.api_email')
            ]
        ]);
        try {
            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return false;
            } else {
                return json_decode($result)->success;
            }
        } catch (Exception $e) {
            curl_close($curl);
            return false;
        }
    }
}
