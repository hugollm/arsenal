<?php
namespace Arsenal\Database;

/*
    An object that is mapped to a database table. The public properties 
    represents the column values. This have to be abstract so the object's
    properties won't collide with the private properties in this class.
*/
abstract class MappedObjectAbstract
{
    private $db = null;
    private $table = '';
    private $lastProperties = array();
    private $id = null; // the underline is to diferentiate it from the public id
    
    /*
        If a mapped object is 
    */
    final public function __construct(Database $db, $table, $id = null, $lastProperties = array())
    {
        $this->db = $db;
        $this->table = $table;
        
        $this->id = $id;
        $this->setPublic('id', $id);
        
        $this->lastProperties = $lastProperties;
        $this->fill($lastProperties);
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
        
        /*
            This closure ensures that no propertie set will be mistaken by 
            a private property of this class.
        */
        $obj = $this;
        $callback = function() use($obj, $properties, $filter)
        {
            foreach($properties as $key=>$val)
                if( ! $filter or in_array($key, $filter))
                    $obj->$key = $val;
        };
        $callback();
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
            $this->setPublic('id', $id);
        }
        $this->lastProperties = $this->getPublicProperties();
    }
    
    public function drop()
    {
        if($this->isSaved())
        {
            $this->db->sql('DELETE FROM :? WHERE :id = ?', $this->table, $this->id)->exec();
            $this->id = null; // private (for comparison)
            $this->setPublic('id', null);
        }
    }
    
    /*
        Does this object exists in the database?
    */
    public function isSaved()
    {
        return (bool)$this->id;
    }
    
    /*
        Does this object diverges from the database version?
        If not in database yet, it diverges.
    */
    public function isTainted()
    {
        if( ! $this->isSaved())
            return true;
        
        $properties = $this->getPublicProperties();
        foreach($properties as $key=>$val)
            if( ! isset($this->lastProperties[$key]) or $val !== $this->lastProperties[$key])
                return true;
        
        return false;
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
    
    private function setPublic($key, $val)
    {
        $obj = $this;
        $callback = function() use($obj, $key, $val)
        {
            $obj->$key = $val;
        };
        $callback();
    }
}