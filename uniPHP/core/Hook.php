<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Hook
{
    use InstanceTrait;

    /**
     * 钩子列表
     * @var array
     */
    private array $hookList = [];

    /**
     * 添加钩子
     * @param string $name
     * @param callable $func
     * @return $this
     */
    public function add(string $name,callable $func)
    {
        $this->hookList[$name] = $func;
        return $this;
    }

    /**
     * 执行钩子函数
     * @param string $name
     * @param mixed ...$arguments
     * @throws \ErrorException
     */
    public function exec(string $name,...$arguments)
    {
        if (isset($this->hookList[$name]) && is_callable($this->hookList[$name])){
            call_user_func($this->hookList[$name],...$arguments);
        } else {
            throw new \ErrorException("Function {$name} can't callable.");
        }
    }
}