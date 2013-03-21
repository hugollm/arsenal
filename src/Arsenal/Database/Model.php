<?php
namespace Arsenal\Database;

abstract class Model
{
    private $db = null;
    private $table = '';
    private $id = null;
    private $lastProperties = array();
    
    final public function __construct(Database $db, $table, $id = null, $lastProperties = array())
    {
        $this->db = $db;
        $this->table = $table;
        $this->lastProperties = $lastProperties;
        $this->id = $id;
        $this->setPublicId($id);
        
        foreach($lastProperties as $key=>$val)
            $this->$key = $val;
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
        if($this->isSaved())
        {
            $props = $this->getChangedProperties();
            if(isset($props['id']))
                throw new \RuntimeException('You\'re not supposed to change the "id" property');
            
            if( ! $props) // nothing changed
                return;
            
            $sql = $this->db->sql('UPDATE :? SET', $this->table);
            foreach($props as $key=>$val)
                $sql->add(':? = ?', $key, $val)->add(',');
            $sql->back()->add('WHERE :id = ? LIMIT 1', $this->id)->exec();
        }
        else
        {
            $props = $this->getPublicProperties();
            if(isset($props['id']))
                throw new \RuntimeException('You\'re not supposed to set the "id" property manually');
            
            $sql = $this->db->sql('INSERT INTO :? (:?+) VALUES (?+)', $this->table, array_keys($props), $props)->exec();
            $id = $this->db->getLastInsertId();
            $this->id = $id;
            $this->setPublicId($id);
        }
        $this->lastProperties = $this->getPublicProperties();
    }
    
    public function drop()
    {
        if($this->isSaved())
        {
            $this->db->sql('DELETE FROM :? WHERE :id = ?', $this->table, $this->id)->exec();
            $this->id = null;
            $this->setPublicId(null);
        }
    }
    
    public function isSaved()
    {
        return (bool)$this->id;
    }
    
    public function isUpToDate()
    {
        if( ! $this->isSaved())
            return false;
        
        $properties = $this->getPublicProperties();
        foreach($properties as $key=>$val)
            if( ! isset($this->lastProperties[$key]) or $val !== $this->lastProperties[$key])
                return false;
        
        return true;
    }
    
    public function isTainted()
    {
        return ! $this->isUpToDate();
    }
    
    private function getPublicProperties()
    {
        $obj = $this;
        $callback = function() use($obj)
        {
            return get_object_vars($obj);
        };
        return $callback();
    }
    
    private function getChangedProperties()
    {
        $diff = array_diff($this->getPublicProperties(), $this->lastProperties);
        return $diff;
    }
    
    private function getPublicId()
    {
        $obj = $this;
        $callback = function() use($obj)
        {
            return $obj->id;
        };
        return $callback();
    }
    
    private function setPublicId($id)
    {
        $obj = $this;
        $callback = function() use($obj, $id)
        {
            $obj->id = $id;
        };
        $callback();
    }
}