<?php
namespace Arsenal\Database;

use Doctrine\DBAL\Schema\Table as DoctrineTable;

class Table
{
    private $name = '';
    private $calls = array();
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function column($name, $type, $size = null, array $options = array())
    {
        if($type === 'serial')
        {
            $type = 'integer';
            $options['autoincrement'] = true;
            $options['unsigned'] = true;
        }
        elseif($type === 'reference')
        {
            $type = 'integer';
            $options['unsigned'] = true;
        }
        
        if($type === 'integer' and $size === 'small')
            $type = 'smallint';
        if($type === 'integer' and $size === 'big')
            $type = 'bigint';
        
        if(is_numeric($size))
            $options['length'] = $size;
        
        $this->call('addColumn', array($name, $type, $options));
    }
    
    public function primary($column) // $column, $column, $column...
    {
        $cols = func_get_args();
        $this->call('setPrimarykey', array($cols));
    }
    
    public function unique($column) // $column, $column, $column...
    {
        $cols = func_get_args();
        $this->call('addUniqueIndex', array($cols));
    }
    
    public function index($column) // $column, $column, $column...
    {
        $cols = func_get_args();
        $this->call('addIndex', array($cols));
    }
    
    public function foreign($myCol, $refTable, $refCol, $onDelete = null, $onUpdate = null)
    {
        $options = array();
        if($onDelete)
            $options['onDelete'] = strtoupper($onDelete);
        if($onUpdate)
            $options['onUpdate'] = strtoupper($onUpdate);
        
        $this->call('addForeignKeyConstraint', array($refTable, array($myCol), array($refCol), $options));
    }
    
    public function call($method, array $args)
    {
        $this->calls[] = array(
            'method' => $method,
            'args' => $args,
        );
    }
    
    public function runCalls(DoctrineTable $docTable)
    {
        foreach($this->calls as $call)
            call_user_func_array(array($docTable, $call['method']), $call['args']);
    }
}