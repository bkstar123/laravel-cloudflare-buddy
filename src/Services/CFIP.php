<?php
/**
 * Get Cloudflare IP details
 *
 * @author: tuanha
 * @date: 05-Dec-2022
 */
namespace Bkstar123\CFBuddy\Services;

use Exception;
use GuzzleHttp\Client;
use Bkstar123\CFBuddy\Services\CFServiceBase;

class CFIP extends CFServiceBase
{
    /**
     * Get Cloudflare IPs
     *
     * @param string $network
     * @return mixed null|false|array
     */
    public function getCloudflareIP($network = null)
    {
        $url = "ips?networks=$network";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data["result"];
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
