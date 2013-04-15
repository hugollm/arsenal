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
        $pdo = $this->getPdo();
        return $pdo->lastInsertId();
    }
    
    public function getPdo()
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
        $stm = $this->executeStatement($sql, $params);
        return $stm->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function exec($sql, array $params = array())
    {
        $stm = $this->executeStatement($sql, $params);
        return $stm->rowCount();
    }
    
    public function queryOne($sql, array $params = array())
    {
        $results = $this->query($sql, $params);
        return current($results);
    }
    
    public function select($table, array $where = array(), array $fields = array())
    {
        $sql = 'SELECT ';
        if( ! $fields)
            $sql .= '* ';
        else
        {
            foreach($fields as $f)
                $sql .= "`$f`, ";
            $sql = substr($sql, 0, -2).' ';
        }
        $sql .= "FROM `$table` ";
        if($where)
        {
            $sql .= 'WHERE ';
            foreach($where as $key=>$val)
                $sql .= "`$key` = ? AND ";
            $sql = substr($sql, 0, -5);
        }
        $sql = rtrim($sql);
        return $this->query($sql, $where);
    }
    
    public function selectOne($table, array $where = array(), array $fields = array())
    {
        $sql = 'SELECT ';
        if( ! $fields)
            $sql .= '* ';
        else
        {
            foreach($fields as $f)
                $sql .= "`$f`, ";
            $sql = substr($sql, 0, -2).' ';
        }
        $sql .= "FROM `$table` ";
        if($where)
        {
            $sql .= 'WHERE ';
            foreach($where as $key=>$val)
                $sql .= "`$key` = ? AND ";
            $sql = substr($sql, 0, -5).' ';
        }
        $sql .= 'LIMIT 1';
        return $this->queryOne($sql, $where);
    }
    
    public function exists($table, array $where)
    {
        $sql = "SELECT 1 FROM `$table` ";
        if($where)
        {
            $sql .= 'WHERE ';
            foreach($where as $key=>$val)
                $sql .= "`$key` = ? AND ";
            $sql = substr($sql, 0, -5).' ';
        }
        $sql .= 'LIMIT 1';
        return (bool)$this->queryOne($sql, $where);
    }
    
    public function insert($table, array $params)
    {
        $keystring = '';
        $valstring = '';
        foreach($params as $key=>$val)
        {
            $keystring .= "`$key`, ";
            $valstring .= "?, ";
        }
        $keystring = substr($keystring, 0, -2);
        $valstring = substr($valstring, 0, -2);
        
        $sql = "INSERT INTO $table ($keystring) VALUES ($valstring)";
        return $this->exec($sql, $params);
    }
    
    public function update($table, array $params, array $where)
    {
        $sql = "UPDATE `$table` SET ";
        foreach($params as $key=>$val)
            $sql .= "`$key` = ?, ";
        $sql = substr($sql, 0, -2).' ';
        if($where)
        {
            $sql .= 'WHERE ';
            foreach($where as $key=>$val)
                $sql .= "`$key` = ? AND ";
            $sql = substr($sql, 0, -5);
        }
        $sql = trim($sql);
        $binds = array_merge(array_values($params), array_values($where));
        return $this->exec($sql, $binds);
    }
    
    public function upsert($table, array $params, array $where)
    {
        if($this->exists($table, $where))
            $this->update($table, $params, $where);
        else
            $this->insert($table, $params);
    }
    
    public function delete($table, array $where)
    {
        $sql = "DELETE FROM `$table` ";
        if($where)
        {
            $sql .= 'WHERE ';
            foreach($where as $key=>$val)
                $sql .= "`$key` = ? AND ";
            $sql = substr($sql, 0, -5);
        }
        $sql = trim($sql);
        return $this->exec($sql, $where);
    }
    
    public function begin()
    {
        $pdo = $this->getPdo();
        $pdo->beginTransaction();
        $this->logger->debug('BEGIN TRANSACTION');
    }
    
    public function commit()
    {
        $pdo = $this->getPdo();
        $pdo->commit();
        $this->logger->debug('COMMIT');
    }
    
    public function rollback()
    {
        $pdo = $this->getPdo();
        $pdo->rollback();
        $this->logger->debug('ROLLBACK');
    }
    
    public function transaction($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for database transaction');
        
        $this->begin();
        static $depth = 0;
        try
        {
            $depth++;
            call_user_func($callback);
            $depth--;
            if($depth === 0)
                $this->commit();
        }
        catch(Exception $e)
        {
            $depth--;
            if($depth === 0)
                $this->rollback();
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
    
    private function executeStatement($sql, array $params)
    {
        $sql = $this->normalizeIdentifierQuotes($sql);
        
        // dump($sql);
        // dump($params);
        
        $pdo = $this->getPdo();
        $start = microtime(true);
        
        $stm = $pdo->prepare($sql);
        $stm->execute(array_values($params));
        
        $this->logSql($sql, $start);
        
        return $stm;
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