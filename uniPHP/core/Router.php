<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Router
{
    use InstanceTrait;

    /**
     * 入口文件
     * @var string
     */
    protected string $entryFile = 'index.php';

    /**
     * 请求方法
     * @var string
     */
    public string $method;

    /**
     * request_uri
     * @var false|mixed|string
     */
    public string $path;

    /**
     * 匹配成功的路由规则
     * @var string
     */
    public string $rule;

    /**
     * 路由规则回调函数
     * @var array
     */
    protected array $callbacks = [];

    /**
     * 自定义404
     * @var callable
     */
    protected $callback404;

    /**
     * 404错误页面模板
     * @var string
     */
    protected string $notFoundFile = __DIR__.'/../views/404.html';

    /**
     * Router constructor.
     */
    public function __construct(){
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->path = strpos($_SERVER['REQUEST_URI'],'?')===false?$_SERVER['REQUEST_URI']:substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?'));
        $this->path = '/'.ltrim($this->path,'/');
    }

    /**
     * 入口文件名
     * @param string $entryFile
     * @return $this
     */
    public function setEntryFile(string $entryFile = '')
    {
        $this->entryFile = $entryFile;
        return $this;
    }

    /**
     * 新增路由规则
     * $isRegular false:字符串匹配 true:正则匹配
     * @param string $method
     * @param string $patterns
     * @param callable $callback
     * @param bool $isRegular
     * @return $this
     * @throws \ErrorException
     */
    public function add(string $method,string $patterns,callable $callback,bool $isRegular = false)
    {
        if (!$method || !$patterns || !$callback){
            throw new \ErrorException('Add route fail '.($method?'`'.$method.'`':''));
        }
        $method = strtoupper($method);
        if (!$isRegular){
            $patterns = strtolower($patterns);
        }
        $this->callbacks[$method][$patterns] = ['isRegular'=>$isRegular,'callback'=>$callback];
        return $this;
    }

    /**
     * 自定义设置404
     * @param $callback
     * @return $this
     */
    public function add404(callable $callback)
    {
        $this->callback404 = $callback;
        return $this;
    }

    /**
     * 魔术方法
     * @param $method
     * @param $arguments
     * @throws \ErrorException
     */
    public function __call($method, $arguments)
    {
        $method = strtoupper($method);
        if (count($arguments) < 2){
            throw new \ErrorException('Add route fail '.($method?'`'.$method.'`':''));
        }
        $this->add($method,...$arguments);
    }

    // 路由监视
    public function dispatch()
    {
        $rawPath = $this->path;
        if (strpos($rawPath,'/'.$this->entryFile) === 0){
            $lowerPath = '/'.ltrim(strtolower(substr($rawPath,strlen('/'.$this->entryFile))),'/');
        } else {
            $lowerPath = strtolower($this->path);
        }
        // 优先匹配具体request_method
        if (array_key_exists($this->method,$this->callbacks)){
            foreach ($this->callbacks[$this->method] as $pattern => $callback){
                if (!$callback['isRegular'] && $pattern === $lowerPath){
                    $this->rule = $rawPath;
                    return call_user_func($callback['callback']);
                }
                if ($callback['isRegular'] && preg_match($pattern,$rawPath,$matches)){
                    $this->rule = $rawPath;
                    return call_user_func($callback['callback'],$matches);
                }
            }
        }
        // 无路由匹配，再次查询 any 泛解析
        if (array_key_exists('ANY',$this->callbacks)){
            foreach ($this->callbacks['ANY'] as $pattern => $callback){
                if ($pattern != '*'){
                    if (!$callback['isRegular'] && $pattern === $lowerPath){
                        $this->rule = $rawPath;
                        return call_user_func($callback['callback']);
                    }
                    if ($callback['isRegular'] && preg_match($pattern,$rawPath,$matches)){
                        $this->rule = $rawPath;
                        return call_user_func($callback['callback'],$matches);
                    }
                }
            }
            if (isset($this->callbacks['ANY']['*'])){
                $this->rule = $rawPath;
                return call_user_func($this->callbacks['ANY']['*']['callback']);
            }
        }
        // 路由无法匹配
        if (is_callable($this->callback404)){
            return call_user_func($this->callback404);
        } else {
            header(($_SERVER['SERVER_PROTOCOL']??'HTTP/1.1').' 404 Not Found');
            header("Status: 404 Not Found");
            if (file_exists($this->notFoundFile)) {
                ob_start();
                include $this->notFoundFile;
                ob_end_flush();
                exit();
            } else {
                exit('404 Not Found.');
            }
        }
    }
}