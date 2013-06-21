<?php
namespace Arsenal\Storages;

abstract class TrailKeeperStorage implements Storage
{
    private $items = array();
    private $hasAll = false;
    private $hasAllKeys = false;
    
    abstract protected function _get($key, $default = null);
    abstract protected function _set($key, $val);
    abstract protected function _getAll();
    abstract protected function _setAll(array $items);
    abstract protected function _getAllKeys();
    abstract protected function _hasKey($key);
    abstract protected function _drop($key);
    abstract protected function _clear();
    
    public function get($key, $default = null)
    {
        if(isset($this->items[$key]))
            return $this->items[$key];
        return $this->items[$key] = $this->_get($key, $default);
    }
    
    public function set($key, $val)
    {
        if(isset($this->items[$key]) and $this->items[$key] === $val)
            return;
        $this->_set($key, $val);
        $this->items[$key] = $val;
    }
    
    public function getAll()
    {
        if($this->hasAll)
            return $this->items;
        $this->items = $this->_getAll();
        $this->hasAll = true;
        $this->hasAllKeys = true;
        return $this->items;
    }
    
    public function setAll(array $items)
    {
        if($this->items === $items)
            return;
        $this->_setAll($items);
        $this->items = $items;
        $this->hasAll = true;
        $this->hasAllKeys = true;
    }
    
    public function getAllKeys()
    {
        if($this->hasAllKeys)
            return array_keys($this->items);
        $keys = $this->_getAllKeys();
        foreach($keys as $key)
            if( ! isset($this->items[$key]))
                $this->items[$key] = null;
        $this->hasAllKeys = true;
        return $keys;
    }
    
    public function hasKey($key)
    {
        if(array_key_exists($key, $this->items))
            return true;
        $has = $this->_hasKey($key);
        if($has)
            $this->items[$key] = null;
        return $has;
    }
    
    public function drop($key)
    {
        $this->_drop($key);
        unset($this->items[$key]);
    }
    
    public function clear()
    {
        $this->_clear();
        $this->items = array();
        $this->hasAll = true;
        $this->hasAllKeys = true;
    }
}