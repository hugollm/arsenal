<?php
namespace Chronos\Database;

use Chronos\TestFramework\Assert;

class DatabaseConnectionTest
{
    private $db;
    
    public function __construct()
    {
        $this->db = new Database('mysql:host=localhost;dbname=chronos', 'root', '');
    }
    
    public function queryUnexistantTable()
    {
        try
        {
            $this->db->query('SELECT * FROM unexistent_table');
        }
        catch(\PDOException $e)
        {
            Assert::pass();
        }
    }
}