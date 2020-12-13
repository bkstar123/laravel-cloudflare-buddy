<?php
/**
 * Interact with Cloudflare API to manage custom SSL configuration
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */
namespace Bkstar123\CFBuddy\Services;

use Exception;
use Bkstar123\CFBuddy\Services\CFServiceBase;

class CustomSSL extends CFServiceBase
{
    /**
     * @var \Bkstar123\CFBuddy\Services\ZoneMgmt
     */
    protected $zoneMgmt;

    /**
     * Create new instance
     */
    public function __construct()
    {
        parent::__construct();
        $this->zoneMgmt = resolve('zoneMgmt');
    }
    /**
     * Get the ID of the current SSL certificate for the given zone
     *
     * @param string $zoneID
     * @return mixed null|false|string
     */
    public function getCurrentCustomCertID($zoneID)
    {
        $url = "zones/$zoneID/custom_certificates?status=active";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (empty($data["result"])) {
                    return null; // No existing certs found
                } elseif (count($data["result"]) > 1) {
                    return false; // Do not expect to see more than one custom certificate there, stop and manually verify on Cloudflare
                }
                return $data["result"][0]["id"]; // The Id of the current certificate
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Upload new custom certificate for a zone
     *
     * @param string $zoneID
     * @param string $cert
     * @param string $key
     * @return boolean
     */
    public function uploadNewCustomCert($zoneID, $cert, $key)
    {
        $url = "zones/$zoneID/custom_certificates";
        $uploadData = [
            "certificate" => $cert,
            "private_key" => $key,
            "bundle_method" => "ubiquitous"
        ];
        $options = [
            'body' => json_encode($uploadData, JSON_UNESCAPED_SLASHES)
        ];
        try {
            $res = $this->client->request('POST', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data["success"];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove existing certificate for the given zone
     *
     * @param string $zoneID
     * @param string $ceertID
     * @return boolean
     */
    public function removeCurrentCert($zoneID, $certID)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request("DELETE", $url);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Fetch the certificate details for the given zone id & given certificate id
     *
     * @param string $zoneID
     * @param string $certID
     * @return mixed array|false
     */
    public function fetchCertData($zoneID, $certID)
    {
        $sslMode = $this->zoneMgmt->getZoneSSLMode($zoneID);
        $url = "zones/$zoneID/custom_certificates/$certID";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return [
                    'tls_mode' => $sslMode ?? null,
                    'issuer' => $data['result']['issuer'],
                    'uploaded_on' => $data['result']['uploaded_on'],
                    'modified_on' => $data['result']['modified_on'],
                    'expires_on' => $data['result']['expires_on'],
                    'hosts' => json_encode($data['result']['hosts'])
                ];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update an existing custom certificate for a zone
     *
     * @param string $zoneID
     * @param string $certID
     * @param string $cert
     * @param string $key
     * @return boolean
     */
    public function updateCustomCert($zoneID, $certID, $cert, $key)
    {
        $url = "zones/$zoneID/custom_certificates/$certID";
        $uploadData = [
            "certificate" => $cert,
            "private_key" => $key,
            "bundle_method" => "ubiquitous"
        ];
        $options = [
            'body' => json_encode($uploadData, JSON_UNESCAPED_SLASHES)
        ];
        try {
            $res = $this->client->request('PATCH', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data["success"];
        } catch (Exception $e) {
            return false;
        }
    }
}
