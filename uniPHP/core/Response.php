<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Response
{
    use InstanceTrait;

    /**
     * 跳转URL
     * @param string $url
     * @throws \ErrorException
     */
    public function redirect(string $url = '')
    {
        if (!headers_sent()) {
            header('Location: '.$url);
            exit;
        } else {
            throw new \ErrorException("Redirect fail.\nHeader information has been output");
        }
    }
}