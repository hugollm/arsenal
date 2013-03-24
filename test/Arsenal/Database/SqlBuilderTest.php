<?php
namespace Arsenal\Database;

use Arsenal\TestFramework\TestFixture;

class SqlBuilderTest extends TestFixture
{
    private $db;
    private $sql;
    
    public function __construct()
    {
        $this->db = new Database('sqlite:junk/database.sqlite');
    }
    
    public function setUp()
    {
        $this->sql = new SqlBuilder($this->db);
    }
    
    public function build()
    {
        $sql = $this->sql;
        $q = $this->db->getQuoteIdentifierChar();
        
        $sql->add('SELECT * FROM :users');
        $sql->add('lorem', 'ipsum')->back();
        $sql->add('WHERE :id = ?', 7);
        
        $this->assert($sql->getString())->is("SELECT * FROM {$q}users{$q} WHERE {$q}id{$q} = ?");
        $this->assert($sql->getParams())->is(array(7));
    }
}