<?php
namespace Arsenal\Database;

class Entity
{
    private $_meta = array();
    
    public function __construct(Database $db, $table, array $from = array())
    {
        $this->setMeta('database', $db);
        $this->setMeta('table', $table);
        
        if($from)
        {
            if(empty($from['id']))
                throw new \InvalidArgumentException('Constructing an Entity from an array requires it to have an "id" property');
            
            $this->setMeta('id', $from['id']);
            $this->setMeta('snapshot', $from);
            $this->fill($from);
        }
    }
    
    public function __get($key)
    {
        return null;
    }
    
    public function fill(array $properties, $filter = null)
    {
        if($filter)
        {
            $filter = str_replace(' ', '', $filter);
            $filter = explode(',', $filter);
            $filter = array_filter($filter);
        }
        foreach($properties as $key=>$val)
            if( ! $filter or in_array($key, $filter))
                $this->$key = $val;
    }
    
    public function save()
    {
        if( ! $this->isTainted())
            return;
        
        $db = $this->getMeta('database');
        $table = $this->getMeta('table');
        $id = $this->getmeta('id');
        
        if($this->isSaved())
        {
            $props = $this->getChangedProperties();
            if(isset($props['id']))
                throw new \RuntimeException('You\'re not supposed to change the "id" property');
            
            $sql = $db->sql('UPDATE :? SET', $table);
            foreach($props as $key=>$val)
                $sql->add(':? = ?', $key, $val)->add(',');
            $sql->back()->add('WHERE :id = ? LIMIT 1', $id)->exec();
        }
        else
        {
            $props = $this->getPublicProperties();
            if(isset($props['id']))
                throw new \RuntimeException('You\'re not supposed to set the "id" property manually');
            
            $sql = $db->sql('INSERT INTO :? (:?+) VALUES (?+)', $table, array_keys($props), $props)->exec();
            $id = $db->getLastInsertId();
            $this->id = $id;
            $this->setMeta('id', $id);
        }
        $snapshot = $this->getPublicProperties();
        $this->setMeta('snapshot', $snapshot);
    }
    
    public function drop()
    {
        if( ! $this->isSaved())
            throw new \LogicException('Cannot drop object that is not in database');
        
        $db = $this->getMeta('database');
        $table = $this->getMeta('table');
        $id = $this->getMeta('id');
        
        $db->sql('DELETE FROM :? WHERE :id = ? LIMIT 1', $table, $id)->exec();
        unset($this->id);
        $this->setMeta('id', null);
    }
    
    public function isSaved()
    {
        return (bool)$this->getMeta('id');
    }
    
    public function isTainted()
    {
        if( ! $this->isSaved())
            return true;
        
        $properties = $this->getPublicProperties();
        $snapshot = $this->getMeta('snapshot') ?: array();
        
        foreach($properties as $key=>$val)
            if( ! isset($snapshot[$key]) or $val !== $snapshot[$key])
                return true;
        
        return false;
    }
    
    private function getMeta($key)
    {
        return isset($this->_meta[$key]) ? $this->_meta[$key] : null;
    }
    
    private function setMeta($key, $val)
    {
        $this->_meta[$key] = $val;
    }
    
    private function getPublicProperties()
    {
        $obj = $this;
        $callback = function() use($obj)
        {
            $vars = get_object_vars($obj);
            return array_filter($vars, function($val){return ! is_null($val);});
        };
        return $callback();
    }
    
    private function getChangedProperties()
    {
        $diff = array_diff_assoc($this->getPublicProperties(), $this->getMeta('snapshot'));
        return $diff;
    }
}