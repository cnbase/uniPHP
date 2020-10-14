<?php
class uniPHP
{
    /**
     * 框架版本号
     * @var string
     */
    public static string $version = '1.2.9';

    /**
     * 入口文件
     * @var string
     */
    protected string $entryFile = 'index.php';

    /**
     * 框架根目录
     * @var string
     */
    protected string $ROOT_DIR = __DIR__;
    /**
     * 网站根目录
     * @var string
     */
    protected string $WEB_DIR = __DIR__.'/www';

    /**
     * 应用目录
     * @var string
     */
    protected string $APP_DIR = __DIR__.'/app';

    /**
     * 配置目录
     * @var string
     */
    protected string $CONF_DIR = __DIR__.'/config';

    /**
     * 路由目录
     * @var string
     */
    protected string $ROUTE_DIR = __DIR__.'/route';

    /**
     * 模块名称
     * @var string
     */
    protected string $MODULE_NAME = 'index';

    /**
     * 配置
     * @var array
     */
    protected array $config = [];

    /**
     * uniPHP run 前置回调函数
     * @var callable
     */
    protected $beforeCreate;

    /**
     * uniPHP run 后置回调函数
     * @var callable
     */
    protected $created;

    /**
     * @var self|null
     */
    protected static ?self $instance = null;

    /**
     * uniPHP constructor.
     * @param array $option
     */
    public function __construct(array $option = []) {
        //初始化目录
        isset($option['entryFile']) and $this->entryFile = $option['entryFile'];
        isset($option['ROOT_DIR']) and $this->ROOT_DIR = $option['ROOT_DIR'];
        isset($option['WEB_DIR']) and $this->WEB_DIR = $option['WEB_DIR'];
        isset($option['APP_DIR']) and $this->APP_DIR = $option['APP_DIR'];
        isset($option['CONF_DIR']) and $this->CONF_DIR = $option['CONF_DIR'];
        isset($option['ROUTE_DIR']) and $this->ROUTE_DIR = $option['ROUTE_DIR'];
        isset($option['MODULE_NAME']) and $this->MODULE_NAME = $option['MODULE_NAME'];
        //注册类自动加载
        spl_autoload_register([$this,'autoload']);
        //载入配置项
        $this->loadConfig();
        //配置时间
        date_default_timezone_set($this->config['timezone']);
        //错误异常处理函数
        try {
            $Debug = uniPHP::use('Debug')->setDebug($this->config['debug']??false)->setTrace($this->config['trace'])->setTraceDir($this->config['traceDir']);
            set_error_handler([&$Debug,'errorHandler']);
            set_exception_handler([&$Debug,'exceptionHandler']);
        } catch (\Throwable $e){
            exit($e->getMessage());
        }
    }

    /**
     * 单例模式
     * @param array $option
     * @return uniPHP|static
     */
    public static function instance(array $option = [])
    {
        if (!(self::$instance instanceof self)){
            self::$instance = new self($option);
        }
        return self::$instance;
    }

    /**
     * @param string $className
     * @param array ...$option
     * @return mixed
     * @throws ErrorException
     */
    public static function use(string $className,array ...$option)
    {
        if (class_exists($className)){
            return (new $className(...$option));
        }
        if (($className = '\uniPHP\core\\'.$className) && class_exists($className)){
            return $className::instance(...$option);
        }
        throw new \ErrorException('Not found class '.$className);
    }

    /**
     * 前置回调函数
     * @param callable $callback
     * @return $this
     */
    public function onBeforeCreate(callable $callback)
    {
        $this->beforeCreate = $callback;
        return $this;
    }

    /**
     * 后置回调函数
     * @param callable $callback
     * @return $this
     */
    public function onCreated(callable $callback)
    {
        $this->created = $callback;
        return $this;
    }

    /**
     * 框架开始执行
     * @throws ErrorException
     */
    public function run()
    {
        //前置函数
        if (is_callable($this->beforeCreate)){
            call_user_func($this->beforeCreate);
        }
        //路由解析
        $routes = $this->loadRoute();
        \uniPHP\core\Router::instance()->setEntryFile($this->entryFile)->addRoutes($routes)->dispatch();
        //后置函数
        if (is_callable($this->created)){
            call_user_func($this->created);
        }
    }

    /**
     * 加载路由配置
     * @return array
     */
    protected function loadRoute()
    {
        $route = [];
        //app route
        $appRouteFile = $this->ROUTE_DIR.'/app.php';
        if (file_exists($appRouteFile) && ($config = require_once $appRouteFile) && is_array($config)){
            $route = array_merge($route,$config);
        }
        $moduleRouteFile = $this->ROUTE_DIR.'/'.$this->MODULE_NAME.'.php';
        if (file_exists($moduleRouteFile) && ($config = require_once $moduleRouteFile) && is_array($config)){
            $route = array_merge($route,$config);
        }
        unset($appRouteFile,$moduleRouteFile,$config);
        return $route;
    }

    /**
     * 载入配置
     */
    protected function loadConfig()
    {
        //default
        $config = require_once __DIR__.'/config/config.php';
        if (is_array($config)){
            $this->config = $config;
        }
        //APP
        $appConfigFile = $this->CONF_DIR.'/app.php';
        if (file_exists($appConfigFile)){
            $config = require_once $appConfigFile;
            $this->config = array_merge($this->config,is_array($config)?$config:[]);
        }
        //module
        $moduleConfigFile = $this->CONF_DIR.'/'.$this->MODULE_NAME.'.php';
        if (file_exists($moduleConfigFile)){
            $config = require_once $moduleConfigFile;
            $this->config = array_merge($this->config,is_array($config)?$config:[]);
        }
        unset($config,$appConfigFile,$moduleConfigFile);
    }

    /**
     * 获取配置项
     * @param string $name
     * @return mixed|null
     */
    public function getConfig(string $name)
    {
        return $this->config[$name]??null;
    }

    /**
     * 设置配置项
     * @param string $name
     * @param $value
     */
    public function setConfig(string $name,$value)
    {
        $this->config[$name] = $value;
    }

    /**
     * 类自动加载
     * @param string $className
     */
    public function autoload(string $className)
    {
        $classPath = str_replace('\\','/',$className);
        if (strpos($classPath,'uniPHP/') === 0 && ($filePath = __DIR__.'/uniPHP/'.substr($classPath,7).'.php') && file_exists($filePath)){
            require_once $filePath;
            return;
        }
        if (($filePath = $this->APP_DIR.'/'.$this->MODULE_NAME.'/'.$classPath.'.php') && file_exists($filePath)){
            require_once $filePath;
            return;
        }
        if (($filePath = $this->APP_DIR.'/'.$classPath.'.php') && file_exists($filePath)){
            require_once $filePath;
            return;
        }
        if (($filePath = $this->ROOT_DIR.'/'.$classPath.'.php') && file_exists($filePath)){
            require_once $filePath;
            return;
        }
    }
}