<?php
/**
 * Manage Clouflare zone
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */
namespace Bkstar123\CFBuddy\Services;

use Exception;
use GuzzleHttp\Client;
use Bkstar123\CFBuddy\Services\CFServiceBase;

class ZoneMgmt extends CFServiceBase
{
    /**
     * Get the ID of the given zone name if it is found and being active
     *
     * @param string $zoneName
     * @return mixed null|false|string
     */
    public function getZoneID($zoneName)
    {
        $url = "zones?name=$zoneName&status=active";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (empty($data["result"])) {
                    return null; // zone not found
                } elseif (count($data["result"]) > 1) {
                    return false; // duplicated zoneID found for the given zone name
                }
                return $data["result"][0]["id"]; // The Id of the given zone name
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the SSL settings for a zone given by ID
     *
     * @param string $zoneID
     * @return mixed string|false|null
     */
    public function getZoneSSLMode($zoneID)
    {
        $url = "zones/$zoneID/settings/ssl";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data["result"]["value"] ?? null;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the paginated list of all Cloudflare zones under an account
     *
     * @param integer $page
     * @param integer $perPage
     * @return array|false
     */
    public function getPaginatedZones($page = 1, $perPage = 100, $status = '')
    {
        $zones = [];
        if (empty($status)) {
            $url = "zones?per_page=$perPage&page=$page";
        } else {
            $url = "zones?per_page=$perPage&page=$page&status=$status";
        }
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data['result'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the list of all sub domains configured under the given zone
     *
     * @param string  $zoneID
     * @param boolean $onlyDNSName
     * @param boolean $onlyProd
     * @param string  $content
     * @param boolean $proxied
     *
     * @return array
     */
    public function getZoneSubDomains(
        $zoneID,
        $hostname = null,
        $onlyDNSName = true,
        $onlyProd = true,
        $content = null,
        $proxied = true
    )
    {
        $zoneSubDomains = [];
        $page = 1;
        do {
            $data = $this->getDNSRecordsForAZone($zoneID, $hostname, $onlyDNSName, $onlyProd, $content, $proxied, $page, 100);
            if (empty($data)) {
                break;
            }
            $zoneSubDomains = array_merge($zoneSubDomains, $data);
            ++$page;
        } while (!empty($data));
        return $zoneSubDomains;
    }

    /**
     * Get the list of all DNS CNAME & A records for al hostnames under the given zone ID
     *
     * @param string $zoneID
     * @param bool $onlyDNSName
     * @param bool $onlyProd
     * @param bool $content
     * @param bool $proxied
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    public function getDNSRecordsForAZone(
        $zoneID,
        $hostname = null,
        $onlyDNSName = true,
        $onlyProd = true,
        $content = null,
        $proxied = true,
        $page = 1,
        $perPage = 100
    )
    {
        $entries = [];
        $url = "zones/$zoneID/dns_records?per_page=$perPage&page=$page";
        if (!is_null($content)) {
            $url .= "&content=$content";
        }
        if (!is_null($proxied)) {
            $proxied = (int) $proxied;
            $url .= "&proxied=$proxied";
        }
        if (!is_null($hostname)) {
            $url .= "&name=$hostname";
        }
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $dns_records = array_filter($data['result'], function ($record) use ($onlyProd) {
                        if ($onlyProd) {
                            return ($record['type'] == 'CNAME' && stristr($record['content'], 'episerver.net') && stristr($record['content'], 'prod.')) || $record['type'] == 'A';
                        } else {
                            return ($record['type'] == 'CNAME' && stristr($record['content'], 'episerver.net')) || $record['type'] == 'A';
                        }
                    });
                    $entries = array_map(function ($record) use ($onlyDNSName) {
                        if (!$onlyDNSName) {
                            return $record['name'] . "," . $record['type'] . "," . $record['content'];
                        } else {
                            return $record['name'];
                        }
                    }, $dns_records);
                    return $entries;
                } else {
                    return [];
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get universal SSL verification status for hostnames of the given zone
     *
     * @param $zoneID string
     *
     * @return false|null|array
     */
    public function getUniversalSSLVerificationStatus($zoneID)
    {
        $url = "zones/$zoneID/ssl/verification";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data['result'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get universal SSL setting status for a zone
     *
     * @param $zoneID string
     *
     * @return false|null|array
     */
    public function getUniversalSSLSettingStatus($zoneID)
    {
        $url = "zones/$zoneID/ssl/universal/settings";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return $data['result'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get CF-for-SaaS custom hostnames for the given SaaS zone
     *
     * @param $zoneID string
     * @param $getAll bool
     * @param $page int
     * @param $perPage int
     *
     * @return false|null|array
     */
    public function getCF4SaaSCustomHostnames($zoneID, $getAll = false, $page = 1, $perPage = 100)
    {
        if ($getAll) {
            $page = 1;
            $allHostnames = [];
            do {
                $hostnames = $this->getCF4SaaSCustomHostnames($zoneID, false, $page, 1000);
                if (empty($hostnames)) {
                    break;
                } else {
                    $allHostnames = array_merge($allHostnames, $hostnames);
                }
                ++$page;
            } while (!empty($hostnames));
            return $allHostnames;
        } else {
            $url = "zones/$zoneID/custom_hostnames?per_page=$perPage&page=$page";
            try {
                $res = $this->client->request('GET', $url);
                $data = json_decode($res->getBody()->getContents(), true);
                if ($data["success"]) {
                    $data = array_map(function ($item) {
                        return [
                            'hostname' => $item['hostname'],
                            'status' => $item['status'],
                            'custom_origin_server' => $item['custom_origin_server'],
                            'created_at' => $item['created_at']
                        ];
                    }, $data['result']);
                    return $data;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }     
    }
}
