<?php
namespace Arsenal\Database;

class EntityQuery
{
    private $db;
    private $table;
    
    private $where = array();
    private $abort = false;
    
    public function __construct(Database $db, $table, $cast = null)
    {
        if($cast and ! is_callable($cast))
            throw new \InvalidArgumentException('Invalid cast callback for EntityQuery constructor');
        
        $this->db = $db;
        $this->table = $table;
        $this->cast = $cast;
    }
    
    public function where($key, $op, $val)
    {
        $op = strtoupper($op);
        $this->where[] = array('key' => $key, 'op' => $op, 'val' => $val);
        
        if($op === 'IN' and ! $val)
            $this->abort = true;
        
        return $this;
    }
    
    public function get($id)
    {
        $results = $this->db->sql('SELECT * FROM :? WHERE :id = ?', $this->table, $id)->query();
        $obj = current($results);
        return $this->cast($obj);
    }
    
    public function find()
    {
        if($this->abort)
            return new EntityCollection;
        
        $sql = $this->db->sql('SELECT * FROM :?', $this->table);
        $sql = $this->appendWhere($sql);
        $objs = $sql->query();
        $objs = $this->castMany($objs);
        return new EntityCollection($objs);
    }
    
    public function findOne()
    {
        $results = $this->find();
        return $results->first();
    }
    
    private function appendWhere(SqlBuilder $sql)
    {
        if( ! $this->where)
            return $sql;
        
        $sql->add('WHERE');
        foreach($this->where as $where)
        {
            if(is_array($where['val']))
                $ph = '(?+)';
            else
                $ph = '?';
            $sql->add(':? '.$where['op']." $ph", $where['key'], $where['val'])->add('AND');
        }
        return $sql->back();
    }
    
    private function cast($obj)
    {
        if($this->cast)
            return call_user_func($this->cast, $obj);
        else
            return new Entity($this->db, $this->table, (array)$obj);
    }
    
    private function castMany(array $objs)
    {
        foreach($objs as $k=>$obj)
            $objs[$k] = $this->cast($obj);
        return $objs;
    }
}