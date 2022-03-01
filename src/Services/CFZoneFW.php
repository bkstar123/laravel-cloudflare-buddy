<?php
/**
 * Interact with Cloudflare API to manage firewall rule configuration for a zone
 *
 * @author: tuanha
 * @date: 13-Dec-2020
 */
namespace Bkstar123\CFBuddy\Services;

use Exception;
use Bkstar123\CFBuddy\Services\CFServiceBase;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter;
use Bkstar123\CFBuddy\Components\CFFWAccessRule\CFFWAccessRule;

class CFZoneFW extends CFServiceBase
{
    /**
     * Create a new firewall rule for a zone
     *
     * @param string $zoneID
     * @param \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule $rule
     * @return bool
     */
    public function createFirewallRule(string $zoneID, CFFWRule $rule)
    {
        $options = [
            'body' => '[' . json_encode($rule->toArray()) . ']'
        ];
        $url = "zones/$zoneID/firewall/rules";
        try {
            $res = $this->client->request('POST', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Firewall rules for a zone by either rule description, or rule id, or rule ref
     *
     * @param string $zoneID
     * @param array $query
     * @return mixed (false | array of \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule objects)
     */
    public function getFWRuleForZone(string $zoneID, array $query)
    {
        foreach (array_keys($query) as $key) {
            if (!in_array($key, ['description', 'id', 'ref'])) {
                throw new Exception("The second argument of the method getFWRuleForZone() must be an associative array contains one or more of following keys: 'description', id', 'ref'");
            }
        }
        $queryString = http_build_query($query);
        $url = "zones/$zoneID/firewall/rules?" . $queryString;
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $rules = array_map(function ($rule) {
                        return new CFFWRule(
                            $rule['description'],
                            $rule['paused'],
                            new CFFWRuleFilter($rule['filter']['expression'], $rule['filter']['paused'], $rule['filter']['id']),
                            $rule['action'],
                            $rule['products'] ?? [],
                            $rule['id']
                        );
                    }, $data['result']);
                    return $rules;
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
     * Update a Firewall rule for a zone. It will not update the rule filter
     *
     * @param string $zoneID
     * @param \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule $rule
     * @return bool
     */
    public function updateFWRuleForZone(string $zoneID, CFFWRule $rule)
    {
        $options = [
            'body' => '[' . json_encode($rule->toArray()) . ']'
        ];
        $url = "zones/$zoneID/firewall/rules";
        try {
            $res = $this->client->request('PUT', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update a Firewall rule's filter for a zone
     *
     * @param string $zoneID
     * @param \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter $filter
     * @return bool
     */
    public function updateFWRuleFilterForZone(string $zoneID, CFFWRuleFilter $filter)
    {
        $options = [
            'body' => '[' . json_encode($filter->toArray()) . ']'
        ];
        $url = "zones/$zoneID/filters";
        try {
            $res = $this->client->request('PUT', $url, $options);
            $data = json_decode($res->getBody()->getContents(), true);
            return $data['success'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete a Firewall rule for a zone, it will not delete the rule's filter
     *
     * @param string $zoneID
     * @param \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule $rule
     * @return bool
     */
    public function deleteFWRuleForZone(string $zoneID, CFFWRule $rule)
    {
        $url = "zones/$zoneID/firewall/rules?id=" . $rule->id;
        try {
            $res = $this->client->request('DELETE', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete a Firewall rule's filter for a zone
     *
     * @param string $zoneID
     * @param \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter $filter
     * @return bool
     */
    public function deleteFWRuleFilterForZone(string $zoneID, CFFWRuleFilter $filter)
    {
        $url = "zones/$zoneID/filters?id=" . $filter->id;
        try {
            $res = $this->client->request('DELETE', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Firewall access rule for a zone
     *
     * @param string $zoneID
     * @param int $page
     * @param int $perPage
     * @return mixed (false | array of \Bkstar123\CFBuddy\Components\CFFWAccessRule\CFFWAccessRule objects)
     */
    public function getFWAccessRules(string $zoneID, $page, $perPage)
    {
        $url = "zones/$zoneID/firewall/access_rules/rules?page=$page&per_page=$perPage";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($data["success"]) {
                if (!empty($data['result'])) {
                    $rules = array_map(function ($rule) {
                        return new CFFWAccessRule(
                            $rule['configuration']['target'],
                            $rule['configuration']['value'],
                            $rule['mode'],
                            $rule['paused'],
                            $rule['notes']
                        );
                    }, $data['result']);
                    return $rules;
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
}
