<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class MySQL
{
    use InstanceTrait;

    /**
     * PDO 配置项
     * @var array
     */
    private static array $options = [];

    /**
     * PDO实例
     * @var array
     */
    private static array $pdo = [];

    /**
     * 获取PDO实例
     * @param string $db_alias
     * @param array $option
     * @return PDO
     * @throws \ErrorException
     */
    public static function pdo(string $db_alias = '',array $option = [])
    {
        if (!$db_alias){
            if ($pdo = end(static::$pdo)){
                return $pdo;
            }
            throw new \ErrorException('No pdo link.');
        } else {
            if (isset(static::$pdo[$db_alias]) && isset(static::$options[$db_alias])){
                if (!$option || $option == static::$options[$db_alias]){
                    return static::$pdo[$db_alias];
                }
                static::$options[$db_alias] = $option;
                static::$pdo[$db_alias] = new PDO($option);
                return static::$pdo[$db_alias];
            }
            if ($option){
                static::$options[$db_alias] = $option;
                static::$pdo[$db_alias] = new PDO($option);
                return static::$pdo[$db_alias];
            }
            throw new \ErrorException('PDO link fail.');
        }
    }
}