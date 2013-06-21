<?php
namespace Arsenal\Database;

class EntityCollection implements \IteratorAggregate, \Countable
{
    private $objs = array();
    
    public function __construct(array $objs = array())
    {
        $this->objs = $objs;
    }
    
    public function __get($key)
    {
        $ar = array();
        foreach($this->objs as $obj)
            $ar[] = $obj->$key;
        return $ar;
    }
    
    public function __set($key, $val)
    {
        foreach($this->objs as $obj)
            $obj->$key = $val;
    }
    
    public function first()
    {
        return current($this->objs);
    }
    
    public function add(Entity $obj)
    {
        $this->objs[] = $obj;
    }
    
    public function isEmpty()
    {
        return empty($this->objs);
    }
    
    /*
        ex:
            $users->assign('posts', $posts, 'id', 'user_id');
    */
    public function assign($property, EntityCollection $collection, $thisCompareProperty, $assignCompareProperty)
    {
        foreach($this->objs as $a)
        {
            $objs = array();
            foreach($collection as $b)
                if($a->$thisCompareProperty === $b->$assignCompareProperty)
                    $objs[] = $b;
            $a->$property = new self($objs);
        }
    }
    
    /*
        ex:
            $posts->assignOne('author', $users, 'user_id', 'id');
    */
    public function assignOne($property, EntityCollection $collection, $thisCompareProperty, $assignCompareProperty)
    {
        foreach($this->objs as $a)
        {
            foreach($collection as $b)
                if($a->$thisCompareProperty === $b->$assignCompareProperty)
                    $a->$property = $b;
        }
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->objs);
    }
    
    public function count()
    {
        return count($this->objs);
    }
}