<?php
namespace Chronos\Database;

use Chronos\TestFramework\Assert;

class SchemaTest
{
    private $schema;
    
    public function setUp()
    {
        $this->schema = new Schema;
    }
    
    public function build()
    {
        methods('\Doctrine\DBAL\Schema\Schema');
        methods('\Doctrine\DBAL\Schema\Table');
        
        $table = $this->schema->table('_tmp_users');
    }
}