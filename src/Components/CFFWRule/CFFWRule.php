<?php
/**
 * CFFWRule class
 *
 * @author: tuanha
 * @date: 25-Feb-2022
 */
namespace Bkstar123\CFBuddy\Components\CFFWRule;

use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter;

class CFFWRule
{
    /**
     * @var string
     */
    public $id;
    
    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $paused;
    
    /**
     * @var \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter
     */
    public $filter;
    
    /**
     * @var string
     */
    public $action;

    /**
     * @var array
     */
    public $products;

    /**
     * Instantiate a \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule object
     * @param string  $description
     * @param bool  $paused
     * @param string  $filterID
     * @param string  $filterExpression
     * @param string  $action
     * @param array  $products
     * @param string  $id
     *
     * @return void
     */
    public function __construct(string $description, bool $paused, CFFWRuleFilter $filter, string $action, array $products = [], string $id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->description = $description;
        $this->paused = $paused;
        $this->filter = $filter;
        $this->action = $action;
        if (!empty($products)) {
            $this->products = $products;
        }
    }

    /**
     * Convert a \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule object to array
     *
     * @return array
     */
    public function toArray()
    {
        $res = [
            "action" => $this->action,
            "filter" => $this->filter->toArray(),
            'description' => $this->description,
            'paused' => $this->paused
        ];
        if (!is_null($this->id)) {
            $res['id'] = $this->id;
        }
        if (!empty($this->products)) {
            $res['products'] = $this->products;
        }
        return $res;
    }
}
