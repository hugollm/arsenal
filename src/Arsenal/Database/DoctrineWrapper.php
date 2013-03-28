<?php
namespace Arsenal\Database;

use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use Doctrine\DBAL\DriverManager as DoctrineDriverManager;
use Doctrine\DBAL\Schema\Comparator as DoctrineComparator;

class DoctrineWrapper
{
    private $db = null;
    private $docConnection = null;
    private $docPlatform = null;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    public function migrate(Schema $schema)
    {
        $fromHash = $this->getSchemaHash();
        $toHash = $schema->getHash();
        
        if($fromHash === $toHash)
            return;
        
        $docSchema = $schema->createDoctrineSchema();
        $this->doctrineMigrate($docSchema);
        
        if($fromHash)
            $this->db->exec("UPDATE `_schema` SET `hash` = ?", array($toHash));
        else
            $this->db->exec("INSERT INTO `_schema` (`hash`) VALUES (?)", array($toHash));
    }
    
    public function getSchemaHash()
    {
        try
        {
            $results = $this->db->query("SELECT `hash` FROM `_schema`");
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
    
    public function dropTable($table)
    {
        $docSchema = $this->createDoctrineSchema();
        $dosSchema->dropTable($table);
        $this->doctrineMigrate($docSchema);
    }
    
    public function dropAllTables()
    {
        $docPlatform = $this->getDoctrinePlatform();
        $docSchema = $this->createDoctrineSchema();
        $sqls = $docSchema->toDropSql($docPlatform);
        foreach($sqls as $sql)
            $this->exec($sql);
    }
    
    public function reset()
    {
        $docSchema = $this->createDoctrineSchema();
        $this->dropAllTables();
        $this->doctrineMigrate($docSchema);
    }
    
    private function getDoctrineConnection()
    {
        if($this->docConnection)
            return $this->docConnection;
        
        $pdo = $this->db->getPDO();
        return $this->docConnection = DoctrineDriverManager::getConnection(array('pdo'=>$pdo));
    }
    
    private function getDoctrinePlatform()
    {
        if($this->docPlatform)
            return $this->docPlatform;
        
        return $this->docPlatform = $this->getDoctrineConnection()->getDatabasePlatform();
    }
    
    private function createDoctrineSchema()
    {
        return $this->docSchema = $this->getDoctrineConnection()->getSchemaManager()->createSchema();
    }
    
    private function doctrineMigrate(DoctrineSchema $toSchema)
    {
        $fromPlatform = $this->getDoctrinePlatform();
        $fromSchema = $this->createDoctrineSchema();
        
        $docComp = new DoctrineComparator;
        $docDiff = $docComp->compare($fromSchema, $toSchema);
        $sqls = $docDiff->toSaveSql($fromPlatform);
        
        foreach($sqls as $sql)
            $this->exec($sql);
    }
}