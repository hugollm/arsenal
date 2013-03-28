<?php
namespace Arsenal\Database;

use Arsenal\Loggers\Logger;

class Database
{
    private $dsn = null;
    private $username = null;
    private $password = null;
    private $pdo = null;
    private $logger = null;
    
    public function __construct($dsn, $username = null, $password = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }
    
    public function getDriver()
    {
        return strstr($this->dsn, ':', true);
    }
    
    public function getQuoteIdentifierChar()
    {
        $driver = $this->getDriver();
        if($driver === 'mysql')
            return '`';
        else
            return '"';
    }
    
    public function getLastInsertId()
    {
        $pdo = $this->getPDO();
        return $pdo->lastInsertId();
    }
    
    public function getPDO()
    {
        $driver = $this->getDriver();
        $options = $options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
        if($driver == 'mysql')
            $options[1002] = "SET NAMES 'UTF8'";
        
        // only connect once (may throw exception)
        if( ! $this->pdo)
        {
            $start = microtime(true);
            $this->pdo = new \PDO($this->dsn, $this->username, $this->password, $options);
            $this->logConnection($driver, $start);
        }
        
        return $this->pdo;
    }
    
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function query($sql, array $params = array())
    {
        $sql = $this->normalizeIdentifierQuotes($sql);
        
        // dump($sql);
        // dump($params);
        
        $pdo = $this->getPDO();
        $start = microtime(true);
        
        $stm = $pdo->prepare($sql);
        $stm->execute(array_values($params));
        $results = $stm->fetchAll(\PDO::FETCH_OBJ);
        
        $this->logSql($sql, $start);
        
        return $results;
    }
    
    public function exec($sql, array $params = array())
    {
        $sql = $this->normalizeIdentifierQuotes($sql);
        
        // dump($sql);
        // dump($params);
        
        $pdo = $this->getPDO();
        $start = microtime(true);
        
        $stm = $pdo->prepare($sql);
        $stm->execute(array_values($params));
        $count = $stm->rowCount();
        
        $this->logSql($sql, $start);
        
        return $count;
    }
    
    public function transaction($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for database transaction');
        
        $pdo = $this->getPDO();
        $pdo->beginTransaction();
        
        static $depth = 0;
        try
        {
            $depth++;
            call_user_func($callback);
            $depth--;
            if($depth === 0)
                $pdo->commit();
        }
        catch(Exception $e)
        {
            $depth--;
            if($depth === 0)
                $pdo->rollback();
            throw $e;
        }
    }
    
    public function sql($sql = null)
    {
        $sqlB = new SqlBuilder($this);
        call_user_func_array(array($sqlB, 'add'), func_get_args());
        return $sqlB;
    }
    
    public function createEntity($table)
    {
        return new Entity($this, $table);
    }
    
    public function entityQuery($table)
    {
        return new EntityQuery($this, $table);
    }
    
    private function normalizeIdentifierQuotes($sql)
    {
        $q = $this->getQuoteIdentifierChar();
        return str_replace('`', $q, $sql);
    }
    
    private function logSql($sql, $start)
    {
        if($this->logger)
            $this->logger->debug('SQL('.round((microtime(true)-$start)*1000, 2).'ms): '.$sql);
    }
    
    private function logConnection($driver, $start)
    {
        if($this->logger)
            $this->logger->debug('Connect('.$driver.'): '.round((microtime(true)-$start)*1000, 2).'ms');
    }
}