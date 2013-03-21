<?php
namespace Arsenal\Database;

use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use Doctrine\DBAL\DriverManager as DoctrineDriverManager;
use Doctrine\DBAL\Schema\Comparator as DoctrineComparator;
use Doctrine\DBAL\DBALException;
use Arsenal\Loggers\Logger;

class Database
{
    private $dsn;
    private $username;
    private $password;
    private $pdo;
    
    private $logger = null;
    
    public function __construct($dsn, $username = null, $password = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->pdo = null;
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
    
    public function sql()
    {
        $sqlBuilder = new SqlBuilder($this);
        call_user_func_array(array($sqlBuilder, 'add'), func_get_args());
        return $sqlBuilder;
    }
    
    public function migrate(Schema $schema)
    {
        $fromHash = $this->getSchemaHash();
        $toHash = $schema->getHash();
        
        if($fromHash === $toHash)
            return;
        
        $pdo = $this->getPDO();
        $docConn = DoctrineDriverManager::getConnection(array('pdo'=>$pdo));
        $docPlatform = $docConn->getDatabasePlatform();
        $docSchemaManager = $docConn->getSchemaManager();
        
        $fromSchema = $docSchemaManager->createSchema();
        $toSchema = $schema->getDoctrineSchema();
        
        $docComp = new DoctrineComparator;
        $docDiff = $docComp->compare($fromSchema, $toSchema);
        $sqls = $docDiff->toSaveSql($docPlatform);
        
        foreach($sqls as $sql)
            $this->exec($sql);
        
        $q = $this->getQuoteIdentifierChar();
        if($fromHash)
            $this->exec("UPDATE {$q}_schema{$q} SET {$q}hash{$q} = ?", array($toHash));
        else
            $this->exec("INSERT INTO {$q}_schema{$q} ({$q}hash{$q}) VALUES (?)", array($toHash));
    }
    
    public function getSchemaHash()
    {
        $q = $this->getQuoteIdentifierChar();
        try
        {
            $results = $this->query("SELECT {$q}hash{$q} FROM {$q}_schema{$q}");
            $first = current($results);
            if($first)
                return $first->hash;
            else
                return false;
        }
        catch(\PDOException $e)
        {
            return false;
        }
    }
    
    public function dropAllTables()
    {
        $pdo = $this->getPDO();
        $docConn = DoctrineDriverManager::getConnection(array('pdo'=>$pdo));
        $docPlatform = $docConn->getDatabasePlatform();
        $docSchemaManager = $docConn->getSchemaManager();
        $docSchema = $docSchemaManager->createSchema();
        $sqls = $docSchema->toDropSql($docPlatform);
        foreach($sqls as $sql)
            $this->exec($sql);
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