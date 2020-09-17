<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Request
{
    use InstanceTrait;

    /**
     * @var string
     */
    protected string $method;

    /**
     * $_SERVER
     * @var array
     */
    protected array $server;

    public function __construct(){
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->server = $_SERVER;
    }

    /**
     * 获取请求时间
     * @param bool $float
     * @return mixed
     */
    public function request_time(bool $float = false)
    {
        return $float ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method == 'PUT';
    }

    /**
     * 是否为DELETE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->method == 'PATCH';
    }

    /**
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }
        return false;
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @return bool
     */
    public function isAjax()
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH');
        return ($value && 'xmlhttprequest' == strtolower($value)) ? true : false;
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile()
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }
        return false;
    }

    /**
     * 获取客户端IP地址
     * $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * $adv 是否进行高级模式获取（有可能被伪装）
     * @param int $type
     * @param bool $adv
     * @return mixed
     */
    public function ip(int $type = 0, bool $adv = true)
    {
        $type   = $type ? 1 : 0;
        static $ip = null;

        if (null !== $ip) {
            return $ip[$type];
        }

        $httpAgentIp = 'HTTP_X_REAL_IP';//IP代理标识

        if ($this->server($httpAgentIp)) {
            $ip = $this->server($httpAgentIp);
        } elseif ($adv) {
            if ($this->server('HTTP_X_FORWARDED_FOR')) {
                $arr = explode(',', $this->server('HTTP_X_FORWARDED_FOR'));
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif ($this->server('HTTP_CLIENT_IP')) {
                $ip = $this->server('HTTP_CLIENT_IP');
            } elseif ($this->server('REMOTE_ADDR')) {
                $ip = $this->server('REMOTE_ADDR');
            }
        } elseif ($this->server('REMOTE_ADDR')) {
            $ip = $this->server('REMOTE_ADDR');
        }

        // IP地址类型
        $ip_mode = (strpos($ip, ':') === false) ? 'ipv4' : 'ipv6';

        // IP地址合法验证
        if (filter_var($ip, FILTER_VALIDATE_IP) !== $ip) {
            $ip = ('ipv4' === $ip_mode) ? '0.0.0.0' : '::';
        }

        // 如果是ipv4地址，则直接使用ip2long返回int类型ip；如果是ipv6地址，暂时不支持，直接返回0
        $long_ip = ('ipv4' === $ip_mode) ? sprintf("%u", ip2long($ip)) : 0;

        $ip = [$ip, $long_ip];

        return $ip[$type];
    }

    /**
     * 获取GET参数
     * @param string|null $name
     * @param null $default
     * @param string $filter
     * @return array|mixed
     */
    public function get(string $name = null, $default = null, $filter = '')
    {
        return $this->input('GET', $name, $default, $filter);
    }

    /**
     * 获取POST参数
     * @param string|null $name
     * @param null $default
     * @param string $filter
     * @return array|mixed
     */
    public function post(string $name = null, $default = null, $filter = '')
    {
        return $this->input('POST', $name, $default, $filter);
    }

    /**
     * 获取REQUEST参数
     * @param string|null $name
     * @param null $default
     * @param string $filter
     * @return array|mixed
     */
    public function request(string $name = null, $default = null, $filter = '')
    {
        return $this->input('REQUEST', $name, $default, $filter);
    }

    /**
     * 获取SERVER参数
     * @param string|null $name
     * @param null $default
     * @return array|mixed|null
     */
    public function server(string $name = null, $default = null)
    {
        if ($name === null){
            return $this->server;
        } else {
            return $this->server[$name]??$default;
        }
    }

    /**
     * 返回过滤后的请求参数
     * @param string $request_method
     * @param string|null $name
     * @param null $default
     * @param string $filter
     * @return array|mixed
     */
    private function input(string $request_method = '',string $name = null,$default = null,$filter = '')
    {
        switch ($request_method){
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $data = $_POST;
                break;
            case 'REQUEST':
                $data = $_REQUEST;
                break;
            default:
                $data = [];
        }
        if ($name === null){
            return $data;
        }
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                $data = $default;
                break;
            }
        }
        if ($filter){
            if (is_array($data)){
                $data = array_map($filter,$data);
            } else {
                $data = call_user_func($filter,$data);
            }
        }
        return $data;
    }
}