<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Template
{
    use InstanceTrait;

    /**
     * 模板变量
     * @var array
     */
    private array $v = [];

    /**
     * 模板文件
     * @var array
     */
    private array $tpl = [];

    /**
     * 基础模板别名
     * @var string|null
     */
    private ?string $baseTplAlias = null;

    /**
     * 设置基础模板别名
     * @param string $tplAlias
     * @return $this
     */
    public function setBaseTpl(string $tplAlias)
    {
        $this->baseTplAlias = $tplAlias;
        return $this;
    }

    /**
     * 用于模板中调取变量
     * echo $this->v('username');
     * @param string $name
     * @return mixed|null
     */
    public function v(string $name)
    {
        return isset($this->v[$name])?$this->v[$name]:null;
    }

    /**
     * 获取渲染后模板内容
     * @return false|string
     * @throws \ErrorException
     */
    public function fetch()
    {
        ob_start();
        $this->display();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 嵌入模板
     * @param string $tplAlias
     * @throws \ErrorException
     */
    public function loadTpl(string $tplAlias)
    {
        if (!$tplAlias){
            throw new \ErrorException('[loadTplError] Tpl alias cannot be null.');
        }
        if (!isset($this->tpl[$tplAlias])){
            throw new \ErrorException('[loadTplError] '.$tplAlias.' files not set.');
        }
        if ($this->tpl[$tplAlias]['type'] == 'file'){
            if (!file_exists($this->tpl[$tplAlias]['filePath'])){
                throw new \ErrorException('[loadTplError] '.$tplAlias.' files not exists.');
            }
            include $this->tpl[$tplAlias]['filePath'];
        }
        if ($this->tpl[$tplAlias]['type'] == 'data'){
            echo $this->tpl[$tplAlias]['content'];
        }
    }

    /**
     * 载入模板文件
     * @throws \ErrorException
     */
    public function display()
    {
        $tplAlias = $this->baseTplAlias?:array_key_first($this->tpl);
        $this->loadTpl($tplAlias);
    }

    /**
     * 模板变量赋值
     * @param string $name
     * @param $value
     */
    public function assign(string $name,$value)
    {
        $this->v[$name] = $value;
    }

    /**
     * 设置模板内容
     * @param string $tplAlias
     * @param string $content
     * @throws \ErrorException
     */
    public function setText(string $tplAlias,string $content)
    {
        if (!$tplAlias){
            throw new \ErrorException('[Template Error]$tplAlias cannot be empty.');
        }
        $this->tpl[$tplAlias] = ['content'=>$content,'type'=>'data'];
    }

    /**
     * 设置模板文件
     * @param string $tplAlias
     * @param string $tplPath
     * @throws \ErrorException
     */
    public function setTpl(string $tplAlias,string $tplPath)
    {
        if (!$tplAlias){
            throw new \ErrorException('[Template Error]$tplAlias cannot be empty.');
        }
        if (!file_exists($tplPath)){
            throw new \ErrorException('[Template Error]'.$tplAlias.' file not exists.');
        }
        $this->tpl[$tplAlias] = ['filePath'=>$tplPath,'type'=>'file'];
    }
}