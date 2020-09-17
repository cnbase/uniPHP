<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Debug
{
    use InstanceTrait;

    /**
     * debug模式
     * @var bool
     */
    protected bool $isDebug = true;

    /**
     * debug模板
     * @var string
     */
    protected string $debugFile = __DIR__.'/../views/debug.html';

    /**
     * trace模式
     * @var bool
     */
    protected bool $isTrace = false;

    /**
     * trace目录
     * @var string
     */
    protected string $traceDir = '../runtime/trace';

    /**
     * 设置debug模式
     * @param bool $isDebug
     * @return $this
     */
    public function setDebug(bool $isDebug = true)
    {
        $this->isDebug = $isDebug?true:false;
        return $this;
    }

    /**
     * 设置debug模板
     * @param string|null $debugFile
     * @return $this
     */
    public function setDebugFile(string $debugFile = null)
    {
        if ($debugFile){
            try {
                if (!is_file($debugFile)){
                    throw new \ErrorException('Debug file not found.');
                }
                $this->debugFile = $debugFile;
            } catch (\Throwable $e){
                exit($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * 设置trace模式
     * @param bool $isTrace
     * @return $this
     */
    public function setTrace(bool $isTrace = true)
    {
        $this->isTrace = $isTrace?true:false;
        return $this;
    }

    /**
     * 设置trace目录
     * @param string|null $traceDir
     * @return $this
     */
    public function setTraceDir(string $traceDir = null)
    {
        if ($traceDir){
            try {
                if (!is_dir($traceDir)){
                    throw new \ErrorException('Trace dir not found.');
                }
                $this->traceDir = $traceDir;
            } catch (\Throwable $e){
                exit($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * 错误处理函数
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     */
    public function errorHandler(int $errNo, string $errStr, string $errFile, int $errLine)
    {
        $error = [
            'errNo'     =>  $errNo,
            'errStr'    =>  $errStr,
            'errFile'   =>  $errFile,
            'errLine'   =>  $errLine,
        ];
        $get_trace = debug_backtrace();
        $trace = [];
        foreach($get_trace as $item){
            $func = $item['function'];
            if(isset($item['type'])){
                $func = $item['type'].$func;
            }
            if (isset($item['class'])){
                $func = $item['class'].$func;
            }
            if ($func == 'uniPHP\core\Debug->errorHandler'){
                continue;
            }
            $trace[] = [
                'function'  =>  $func,
                'args'      =>  $item['args']??[],
                'file'      =>  $item['file']??'',
                'line'      =>  $item['line']??''
            ];
        }
        if ($this->traceDir){
            //拼接
            $content = "\n\nDate:".date('Y/m/d H:i:s')."\n".$error['errStr']."\n";
            $content .= $error['errFile']."(".$error['errLine'].")\n";
            if ($trace){
                foreach ($trace as $item){
                    $content .= "----------\n";
                    $content .= $item['file']?$item['file']."(".$item['line'].")\n":'';
                    $content .= $item['function']."\t".json_encode($item['args'])."\n";
                }
            }
            $content .= "==========\n\n";
            //写入
            file_put_contents($this->traceDir.'/trace_'.date('Ymd').'.log',$content,FILE_APPEND);
        }
        if ($this->isDebug){
            $this->render($error,$trace);
        }
    }

    /**
     * 异常处理函数
     * @param \Throwable $Exception
     */
    public function exceptionHandler(\Throwable $Exception)
    {
        $error = [
            'errNo'     =>  $Exception->getCode(),
            'errStr'    =>  $Exception->getMessage(),
            'errFile'   =>  $Exception->getFile(),
            'errLine'   =>  $Exception->getLine(),
        ];
        $get_trace = $Exception->getTrace();
        $trace = [];
        foreach($get_trace as $item){
            $func = $item['function'];
            if(isset($item['type'])){
                $func = $item['type'].$func;
            }
            if (isset($item['class'])){
                $func = $item['class'].$func;
            }
            if ($func == 'uniPHP\core\Debug->exceptionHandler'){
                continue;
            }
            $trace[] = [
                'function'  =>  $func,
                'args'      =>  $item['args']??[],
                'file'      =>  $item['file']??'',
                'line'      =>  $item['line']??''
            ];
        }
        if ($this->traceDir){
            //拼接
            $content = "\n\nDate:".date('Y/m/d H:i:s')."\n".$error['errStr']."\n";
            $content .= $error['errFile']."(".$error['errLine'].")\n";
            if ($trace){
                foreach ($trace as $item){
                    $content .= "----------\n";
                    $content .= $item['file']?$item['file']."(".$item['line'].")\n":'';
                    $content .= $item['function']."\t".json_encode($item['args'])."\n";
                }
            }
            $content .= "==========\n\n";
            //写入
            file_put_contents($this->traceDir.'/trace_'.date('Ymd').'.log',$content,FILE_APPEND);
        }
        if ($this->isDebug){
            $this->render($error,$trace);
        }
    }

    /**
     * 渲染输出错误信息
     * @param array $error
     * @param array $trace
     */
    public function render(array $error,array $trace)
    {
        ob_start();
        include $this->debugFile;
        ob_end_flush();
    }
}