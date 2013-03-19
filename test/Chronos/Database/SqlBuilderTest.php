<?php
namespace Chronos\Database;

use Chronos\TestFramework\Assert;

class SqlBuilderTest
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
        
        Assert::isString($sql->getString())->isEqual("SELECT * FROM {$q}users{$q} WHERE {$q}id{$q} = ?");
        Assert::isArray($sql->getParams())->isEqual(array(7));
    }
}