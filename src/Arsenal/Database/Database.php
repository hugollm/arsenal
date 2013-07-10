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
    
    public static function createFromParams($driver, $host, $dbname = null, $username = null, $password = null, $port = null)
    {
        if($driver === 'sqlite')
            return new Database("$driver:$host");
        else
        {
            $dsn = "$driver:host=$host";
            if($dbname)
                $dsn .= ";dbname=$dbname";
            if($port)
                $dsn .= ";port=$port";
            return new Database($dsn, $username, $password);
        }
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
        // only connect once (may throw exception)
        if( ! $this->pdo)
        {
            $driver = $this->getDriver();
            $options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION);
            
            // 1002 is for PDO::MYSQL_ATTR_INIT_COMMAND, the number is
            // used instead because of a possible php bug
            if($driver == 'mysql')
                $options[1002] = "SET NAMES 'UTF8'";
            
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
        $sql = $this->sql('SELECT')->add('*');
        if($fields)
            $sql->back()->add(':fields')->ibinds('fields', $fields);
        $sql->add('FROM :table')->ibind('table', $table);
        if($where)
        {
            $sql->add('WHERE');
            foreach($where as $key=>$val)
                $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add('AND');
            $sql->back();
        }
        return $sql->query();
    }
    
    public function selectOne($table, array $where = array(), array $fields = array())
    {
        $sql = $this->sql('SELECT')->add('*');
        if($fields)
            $sql->back()->add(':fields')->ibinds('fields', $fields);
        $sql->add('FROM :table')->ibind('table', $table);
        if($where)
        {
            $sql->add('WHERE');
            foreach($where as $key=>$val)
                $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add('AND');
            $sql->back();
        }
        $sql->add('LIMIT 1');
        return $sql->queryOne();
    }
    
    public function exists($table, array $where = array())
    {
        $sql = $this->sql('SELECT 1 FROM :table');
        $sql->ibind('table', $table);
        if($where)
        {
            $sql->add('WHERE');
            foreach($where as $key=>$val)
                $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add('AND');
            $sql->back();
        }
        $sql->add('LIMIT 1');
        return (bool)$sql->queryOne();
    }
    
    public function insert($table, array $params)
    {
        $sql = $this->sql('INSERT INTO :table (:fields) VALUES (:params)');
        $sql->ibind('table', $table);
        $sql->ibinds('fields', array_keys($params));
        $sql->vbinds('params', $params);
        return $sql->exec();
    }
    
    public function update($table, array $params, array $where)
    {
        $sql = $this->sql('UPDATE :table SET');
        $sql->ibind('table', $table);
        foreach($params as $key=>$val)
            $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add(',');
        $sql->back();
        if($where)
        {
            $sql->add('WHERE');
            foreach($where as $key=>$val)
                $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add('AND');
            $sql->back();
        }
        return $sql->exec();
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
        $sql = $this->sql('DELETE FROM :table');
        $sql->ibind('table', $table);
        if($where)
        {
            $sql->add('WHERE');
            foreach($where as $key=>$val)
                $sql->add(':key = :val')->ibind('key', $key)->vbind('val', $val)->add('AND');
            $sql->back();
        }
        return $sql->exec();
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
        $sqlobj = new Sql($this);
        if($sql)
            $sqlobj->add($sql);
        return $sqlobj;
    }
    
    public function migrate(Schema $schema)
    {
        $doc = new DoctrineWrapper($this);
        $doc->migrate($schema);
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
            $this->logger->debug('SQL('.number_format((microtime(true)-$start)*1000, 2, '.', ',').'ms): '.$sql);
    }
    
    private function logConnection($driver, $start)
    {
        if($this->logger)
            $this->logger->debug('Connect('.$driver.'): '.number_format((microtime(true)-$start)*1000, 2, '.', ',').'ms');
    }
}