<?php
class uniPHP
{
    /**
     * 框架版本号
     * @var string
     */
    public static string $version = '1.1.2';

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
    protected array $config;

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
     * uniPHP constructor.
     * @param array $option
     */
    public function __construct(array $option = []) {
        //初始化目录
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

    public function run()
    {
        //前置函数
        if (is_callable($this->beforeCreate)){
            call_user_func($this->beforeCreate);
        }
        //路由解析
        $this->loadRoute();
        if (is_null($this->config['entryFile'])){
            \uniPHP\core\Router::instance()->dispatch();
        } else {
            \uniPHP\core\Router::instance()->setEntryFile($this->config['entryFile'])->dispatch();
        }
        //后置函数
        if (is_callable($this->created)){
            call_user_func($this->created);
        }
    }

    /**
     * 加载路由配置
     */
    protected function loadRoute()
    {
        //app route
        $appRouteFile = $this->ROUTE_DIR.'/app.php';
        if (file_exists($appRouteFile)){
            require_once $appRouteFile;
        }
        $moduleRouteFile = $this->ROUTE_DIR.'/'.$this->MODULE_NAME.'.php';
        if (file_exists($moduleRouteFile)){
            require_once $moduleRouteFile;
        }
        unset($appRouteFile,$moduleRouteFile);
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