<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Session
{
    use InstanceTrait;

    /**
     * 前缀
     * @var string
     */
    protected string $prefix = '_uniPHP_';

    /**
     * 获取前缀名
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 设置前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix = '')
    {
        $this->prefix = $prefix?:'_uniPHP_';
        return $this;
    }

    /**
     * 获取session值
     * @param string|null $name
     * @param string $prefix
     * @return mixed|null
     * @throws \ErrorException
     */
    public function get(string $name = null,string $prefix = '')
    {
        $this->start();
        if ($name === null){
            return null;
        }
        return isset($_SESSION[$prefix?:$this->prefix][$name])?$_SESSION[$prefix?:$this->prefix][$name]:null;
    }

    /**
     * 获取指定前缀所有session值
     * @param string $prefix
     * @return mixed|null
     * @throws \ErrorException
     */
    public function getAll(string $prefix = '')
    {
        $this->start();
        if (!$prefix){
            return $_SESSION[$this->prefix]??null;
        } else {
            return $_SESSION[$prefix]??null;
        }
    }

    /**
     * 设置session值
     * @param string $name
     * @param $value
     * @param string $prefix
     * @return bool
     * @throws \ErrorException
     */
    public function set(string $name,$value,string $prefix = '')
    {
        $this->start();
        if (!$name) return false;
        $_SESSION[$prefix??$this->prefix][$name] = $value;
        return true;
    }

    /**
     * 删除指定session
     * @param string $name
     * @param string $prefix
     * @return bool
     * @throws \ErrorException
     */
    public function delete(string $name,string $prefix = '')
    {
        $this->start();
        if ($name === null){
            return false;
        } else {
            unset($_SESSION[$prefix?:$this->prefix][$name]);
            return true;
        }
    }

    /**
     * 删除所有session
     * @param string $prefix
     * @return bool
     * @throws \ErrorException
     */
    public function deleteAll(string $prefix = '')
    {
        $this->start();
        if (!$prefix){
            unset($_SESSION[$this->prefix]);
        } else {
            unset($_SESSION[$prefix]);
        }
        return true;
    }

    /**
     * 开启session
     * @return bool
     * @throws \ErrorException
     */
    public function start()
    {
        if (PHP_SESSION_ACTIVE == session_status()){
            return true;
        }
        if(!session_start()){
            throw new \ErrorException('Session unable start.');
        }
        return true;
    }

    /**
     * 销毁session会话
     * @param false $destroy_cookie
     * @return bool
     */
    public function destroy($destroy_cookie = false)
    {
        if (!empty($_SESSION)) {
            // !!! don't use `unset($_SESSION)`,look php Doc 'session_unset()'
            $_SESSION = [];
        }

        if ($destroy_cookie){
            // 如果要清理的更彻底，那么同时删除会话 cookie
            // 注意：这样不但销毁了会话中的数据，还同时销毁了会话本身
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }

        session_unset();//清除内存$_SESSION的值，不删除会话文件及会话ID
        return session_destroy();//销毁会话数据，删除会话文件及会话ID，但内存$_SESSION变量依然保留
    }
}