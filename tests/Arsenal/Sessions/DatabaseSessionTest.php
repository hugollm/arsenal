<?php
namespace Arsenal\Sessions;
use Arsenal\Database\Database;
use Arsenal\Database\Schema;

class DatabaseSessionTest extends SessionTest
{
    private $table = 'sessions';
    
    protected function createSession(FakeCookieJar $cj = null)
    {
        if( ! $cj)
            $cj = $this->createCookieJar();
        
        return new DatabaseSession($cj, $this->getDatabase(), $this->table);
    }
    
    private function getDatabase()
    {
        $schema = new Schema;
        $table = $schema->table('sessions');
        $table->column('ssid', 'string', 40);
        $table->column('payload', 'text');
        $table->column('expiration', 'integer');
        $table->primary('ssid');
        
        $db = Database::createFromParams('sqlite', 'tests/tmp/database.sqlite');
        $db->migrate($schema);
        return $db;
    }
}