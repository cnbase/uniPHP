<?php
namespace uniPHP\core;

use uniPHP\traits\InstanceTrait;

class PDO
{
    use InstanceTrait;

    /**
     * @var \PDOStatement
     */
    protected \PDOStatement $statement;

    /**
     * @var \PDO
     */
    private \PDO $pdo;

    /**
     * @var array
     */
    private array $fetch_style = [
        'ARRAY' =>  \PDO::FETCH_ASSOC,
        'NUM'   =>  \PDO::FETCH_NUM,
        'OBJ'   =>  \PDO::FETCH_OBJ,
        'BOTH'  =>  \PDO::FETCH_BOTH
    ];

    /**
     * @var string
     */
    private string $msg = '';

    /**
     * PDO constructor.
     * @param array $options
     * @throws \ErrorException
     */
    public function __construct(array $options = [])
    {
        $host = $options['host']??'';
        $database = $options['database']??'';
        $user = $options['user']??'';
        $password = $options['password']??'';
        $port = $options['port']??'3306';
        $pdo_options = $options['options']??[];
        if (!$host || !$database || !$user || !$password){
            throw new \ErrorException("PDO options error.");
        }
        $dsn =  "mysql:dbname={$database};host={$host};port={$port}";
        try {
            $this->pdo = $pdo_options ? new \PDO($dsn,$user,$password,$pdo_options) : new \PDO($dsn,$user,$password);
        } catch (\PDOException $e) {
            //PDO Connection failed.
            throw new \ErrorException($e->getMessage());
        }
    }

    /**
     * 查询
     * @param string $sql
     * @param array $bind
     * @param string $fetch_style
     * @return array|false
     */
    public function query(string $sql = '',array $bind = [],string $fetch_style = 'array')
    {
        if ($this->_execute($sql,$bind)){
            return $this->statement->fetchAll($this->fetch_style[strtoupper($fetch_style)]??NULL);
        } else {
            return false;
        }
    }

    /**
     * 执行
     * 返回影响数
     * @param string $sql
     * @param array $bind
     * @return false|int
     */
    public function execute(string $sql = '',array $bind = [])
    {
        if($this->_execute($sql,$bind)){
            return $this->statement->rowCount();
        } else {
            return false;
        }
    }

    /**
     * 获取一条记录
     * @param string $sql
     * @param array $bind
     * @param string $fetch_style
     * @return false|mixed
     */
    public function one(string $sql = '',array $bind = [],string $fetch_style = 'array')
    {
        if ($this->_execute($sql,$bind)){
            return $this->statement->fetch($this->fetch_style[strtoupper($fetch_style)]??NULL);
        } else {
            return false;
        }
    }

    /**
     * 开启事务
     * @return bool
     */
    public function beginTransaction()
    {
        if (!$this->pdo->inTransaction()){
            return $this->pdo->beginTransaction();
        }
        return true;
    }

    /**
     * 事务回滚
     * @return bool
     */
    public function rollback()
    {
        if ($this->pdo->inTransaction()){
            return $this->pdo->rollBack();
        }
        return false;
    }

    /**
     * 事务提交
     * @return bool
     */
    public function commit()
    {
        if ($this->pdo->inTransaction()){
            return $this->pdo->commit();
        }
        return false;
    }

    /**
     * 获取最后一次插入ID
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 打印debug信息
     */
    public function debugDumpParams()
    {
        if ($this->statement){
            $this->statement->debugDumpParams();
        } else {
            echo 'statement empty.';
        }
    }

    /**
     * @param string $sql
     * @param array $bind
     * @return bool
     */
    private function _execute(string $sql = '',array $bind = [])
    {
        try {
            if (!$sql) {
                throw new \ErrorException('SQL is empty.');
            }
            // 预处理
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                throw new \ErrorException('PDO prepare sql fail.');
            }
            // 绑定参数
            foreach ($bind as $key => $value){
                if($statement->bindValue($key,$value) === false){
                    $error = $statement->errorInfo();
                    throw new \ErrorException("PDO bindValue fail. [SQLSTATE] {$error[0]};[errCode] {$error[1]};[errInfo] {$error[2]}");
                }
            }
            if($statement->execute() === false){
                $error = $statement->errorInfo();
                throw new \ErrorException("PDO execute fail. [SQLSTATE] {$error[0]};[errCode] {$error[1]};[errInfo] {$error[2]}");
            }
            $this->statement = $statement;
            return true;
        } catch (\Throwable $e){
            $this->msg = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取最后一次执行错误信息
     * @return string
     */
    public function error()
    {
        return $this->msg;
    }
}