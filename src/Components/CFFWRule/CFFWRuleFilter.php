<?php
/**
 * CFFWRuleFilter class
 *
 * @author: tuanha
 * @date: 25-Feb-2022
 */
namespace Bkstar123\CFBuddy\Components\CFFWRule;

class CFFWRuleFilter
{
    /**
     * @var string
     */
    public $id;
    
    /**
     * @var string
     */
    public $expression;

    /**
     * @var bool
     */
    public $paused;
    
    /**
     * Instantiate a \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter object
     * @param string  $expression
     * @param bool  $paused
     * @param string  $id
     *
     * @return void
     */
    public function __construct(string $expression, bool $paused = false, string $id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        $this->expression = $expression;
        $this->paused = $paused;
    }

    /**
     * Convert a \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter object to array
     *
     * @return array
     */
    public function toArray()
    {
        $res = [
            "expression" => $this->expression,
            'paused' => $this->paused
        ];
        if (!is_null($this->id)) {
            $res['id'] = $this->id;
        }
        return $res;
    }
}
