<?php
namespace Arsenal\Database;

use Arsenal\TestFramework\TestFixture;
use Arsenal\Loggers\JsConsoleLogger;

abstract class DatabaseTest extends TestFixture
{
    protected static $db = null;
    
    public function __construct()
    {
        if(self::$db)
            return;
        
        self::$db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
        
        $schema = new Schema;
        
        $table = $schema->table('_users');
            $table->column('id', 'serial');
            $table->column('email', 'string', 255);
            $table->column('username', 'string', 30);
            $table->column('password', 'string', 64);
        $table->primary('id');
        
        self::$db->setLogger(new JsConsoleLogger);
        $docDb = new DoctrineWrapper(self::$db);
        $docDb->migrate($schema);
    }
}