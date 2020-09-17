<?php
namespace uniPHP\traits;

trait InstanceTrait
{
    /**
     * @var $this |null
     */
    protected static ?self $instance = null;

    /**
     * @param mixed ...$option
     * @return static
     */
    public static function instance(...$option)
    {
        if (!static::$instance instanceof static){
            static::$instance = new static(...$option);
        }
        return static::$instance;
    }
}