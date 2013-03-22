<?php
namespace Arsenal\Database;

use Doctrine\DBAL\Schema\Schema as DoctrineSchema;

class Schema
{
    private $tables = array();
    
    public function __construct()
    {
        $table = $this->table('_schema');
            $table->column('hash', 'string', 40);
        // $table->primary('hash');
    }
    
    public function table($name)
    {
        $table = new Table($name);
        $this->tables[] = $table;
        return $table;
    }
    
    public function getHash()
    {
        return sha1(serialize($this));
    }
    
    public function createDoctrineSchema()
    {
        $docSchema = new DoctrineSchema;
        foreach($this->tables as $table)
        {
            $docTable = $docSchema->createTable($table->getName());
            $table->runCalls($docTable);
        }
        return $docSchema;
    }
}