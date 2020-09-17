<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class Uploader
{
    use InstanceTrait;

    /**
     * @var array
     */
    public array $files = [/*'input_name'=>[['name'=>'','type'=>'','tmp_name'=>'','error'=>'','size'=>'','is_uploaded'=>true|false,'extension'=>'jpg|png...']]*/];

    /**
     * 错误信息
     * @var string
     */
    private string $errMsg = '';

    /**
     * Uploader constructor.
     */
    public function __construct(){
        if ($_FILES){
            // 转换存储格式
            foreach ($_FILES as $input_name => $files){
                if (is_array($files)){
                    if (!isset($files['name']) || !isset($files['type']) || !isset($files['tmp_name']) || !isset($files['error']) || !isset($files['size'])){
                        // 非正常上传文件
                        continue;
                    }
                    if (is_array($files['name'])){
                        //多文件上传
                        foreach ($files as $field_name => $fieldArr){
                            foreach ($fieldArr as $file_index => $val){
                                if ($field_name == 'tmp_name'){
                                    $this->files[$input_name][$file_index]['is_uploaded'] = is_uploaded_file($val);
                                }
                                if ($field_name == 'name'){
                                    $extension =  explode('.',$val);
                                    $extension = strtolower(end($extension));
                                    $this->files[$input_name][$file_index]['extension'] = $extension;
                                }
                                $this->files[$input_name][$file_index][$field_name] = $val;
                            }
                        }
                    } else {
                        //单文件上传
                        $files['is_uploaded'] = is_uploaded_file($files['tmp_name']);
                        $extension = explode('.',$files['name']);
                        $files['extension'] = strtolower(end($extension));
                        $this->files[$input_name][] = $files;
                    }
                }
            }
        }
    }

    /**
     * 检查文件是否为合法上传
     * @param string $tmp_name
     * @return bool
     */
    public function isUploaded(string $tmp_name = '')
    {
        return is_uploaded_file($tmp_name);
    }

    /**
     * 获取单个文件大小
     * @param string $input_name
     * @param int $index
     * @return false|mixed
     */
    public function size(string $input_name = '',int $index = 0)
    {
        if (!isset($this->files[$input_name]) || !isset($this->files[$input_name][$index])){
            return $this->returnError('上传文件不存在');
        }
        return $this->files[$input_name][$index]['size'];
    }

    /**
     * 获取所有上传文件大小
     * @param string $input_name
     * @return array|false
     */
    public function sizeAll(string $input_name = '')
    {
        if (!isset($this->files[$input_name])){
            return $this->returnError('上传文件不存在');
        }
        $size = [];
        foreach ($this->files[$input_name] as $file){
            $size[] = $file['size'];
        }
        return $size;
    }

    /**
     * 获取单个文件md5值
     * @param string $input_name
     * @param int $index
     * @return bool|string
     */
    public function md5(string $input_name = '',int $index = 0)
    {
        if (!isset($this->files[$input_name]) || !isset($this->files[$input_name][$index])){
            return $this->returnError('上传文件不存在');
        }
        return md5_file($this->files[$input_name][$index]['tmp_name']);
    }

    /**
     * 获取所有上传文件的md5值
     * @param string $input_name
     * @return array|bool
     */
    public function md5All(string $input_name = '')
    {
        if (!isset($this->files[$input_name])){
            return $this->returnError('上传文件不存在');
        }
        $hash = [];
        foreach ($this->files[$input_name] as $file){
            $hash[] = md5_file($file['tmp_name']);
        }
        return $hash;
    }

    /**
     * 获取单个文件sha1值
     * @param string $input_name
     * @param int $index
     * @return bool|string
     */
    public function sha1(string $input_name = '',int $index = 0)
    {
        if (!isset($this->files[$input_name]) || !isset($this->files[$input_name][$index])){
            return $this->returnError('上传文件不存在');
        }
        return sha1_file($this->files[$input_name][$index]['tmp_name']);
    }

    /**
     * 获取所有上传文件的sha1值
     * @param string $input_name
     * @return array|bool
     */
    public function sha1All(string $input_name = '')
    {
        if (!isset($this->files[$input_name])){
            return $this->returnError('上传文件不存在');
        }
        $hash = [];
        foreach ($this->files[$input_name] as $file){
            $hash[] = sha1_file($file['tmp_name']);
        }
        return $hash;
    }

    /**
     * 获取单个文件的hash值
     * @param string $algo
     * @param string $input_name
     * @param int $index
     * @return false|string
     */
    public function hash(string $algo,string $input_name = '',int $index = 0)
    {
        if (!isset($this->files[$input_name]) || !isset($this->files[$input_name][$index])){
            return $this->returnError('上传文件不存在');
        }
        return hash_file($algo,$this->files[$input_name][$index]['tmp_name']);
    }

    /**
     * 获取所有上传文件的hash值
     * @param string $algo
     * @param string $input_name
     * @return array|bool
     */
    public function hashAll(string $algo,string $input_name = '')
    {
        if (!isset($this->files[$input_name])){
            return $this->returnError('上传文件不存在');
        }
        $hash = [];
        foreach ($this->files[$input_name] as $file){
            $hash[] = hash_file($algo,$file['tmp_name']);
        }
        return $hash;
    }

    /**
     * 移动单个文件
     * @param string $input_name 表单名
     * @param int $index 指定索引，默认第一个
     * @param string $dir 目录
     * @param bool $cover 是否覆盖
     * @param int $rename 是否重命名，0:md5值;1:采用原上传文件名;2:指定文件名
     * @param string|null $file_name 指定文件名,不含扩展名
     * @return false|mixed
     */
    public function move(string $input_name = '',int $index = 0,string $dir = '',bool $cover = true,int $rename = 1,string $file_name = null)
    {
        if (!isset($this->files[$input_name]) || !isset($this->files[$input_name][$index])){
            return $this->returnError('上传文件不存在');
        }
        $file = $this->files[$input_name][$index];
        if ($file['is_uploaded'] == false){
            return $this->returnError('非法上传文件');
        }
        if ($file['error'] !== UPLOAD_ERR_OK){
            return $this->returnError($this->codeToMessage($file['error']));
        }
        if ($file['size'] <= 0){
            return $this->returnError('文件内容为空');
        }
        $dir = rtrim($dir,'/');
        if ((!file_exists($dir) || !is_dir($dir)) && (!mkdir($dir,0775,true) || !chmod($dir,0775))){
            return $this->returnError('目录不存在或无权创建');
        }
        if ($rename === 0){
            // 自动生成文件名
            $md5_name = $this->md5($input_name,$index).'.'.$file['extension'];
            if (!$md5_name){
                return $this->returnError('自动生成文件名失败');
            }
            $file_name = $md5_name;
        } elseif ($rename === 1){
            $file_name = $file['name'];
        } elseif ($rename === 2){
            if (!$file_name){
                return $this->returnError('自定义文件名错误');
            }
            $file_name .= '.'.$file['extension'];
        } else {
            return $this->returnError('$rename参数错误');
        }
        $destination = $dir.DIRECTORY_SEPARATOR.$file_name;
        if (!$cover && file_exists($destination)){
            // 不允许覆盖
            return $this->returnError('目标目录存在同名文件');
        }
        if (move_uploaded_file($file['tmp_name'],$destination)){
            // 可能会有所修改
            $file['file_name'] = $file_name;
            return $file;
        } else {
            return $this->returnError('move文件失败');
        }
    }

    /**
     * 批量移动
     * @param string $dir 目录
     * @param bool $cover 是否覆盖
     * @param int $rename 是否重命名，0:md5值;1:采用原上传文件名;2:指定文件名
     * @param string|null $file_name 指定文件名,不含扩展名,自动拼接索引到文件名
     * @return array|bool
     */
    public function moveAll(string $dir = '',bool $cover = true,int $rename = 1,string $file_name = null)
    {
        $return = [];
        foreach ($this->files as $input_name){
            foreach ($input_name as $index => $file){
                if ($rename == 2){
                    $file_name .= $index;
                }
                $return[] = $this->move($input_name,$index,$dir,$cover,$rename,$file_name);
            }
        }
        return $return;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getErrMsg()
    {
        return $this->errMsg;
    }

    /**
     * 设置错误信息并返回false
     * @param string $errMsg
     * @return false
     */
    protected function returnError(string $errMsg = '')
    {
        $this->errMsg = $errMsg;
        return false;
    }

    /**
     * 映射上传错误代码
     * @param $code
     * @return string
     */
    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_OK:
                $message = 'success';
                break;
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }
}