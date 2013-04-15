<?php
namespace Arsenal\Storages;

abstract class MappedArrayStorage extends ArrayStorage
{
    private $loaded = false;
    private $snapshot = array();
    
    abstract protected function load(); // should return $items
    abstract protected function update(array $items);
    
    public function get($key, $default = null)
    {
        $this->_load();
        return parent::get($key, $default);
    }
    
    public function getAll()
    {
        $this->_load();
        return parent::getAll();
    }
    
    public function set($key, $val)
    {
        parent::set($key, $val);
        $this->_update();
    }
    
    public function setAll(array $items)
    {
        parent::setAll($items);
        $this->_update();
    }
    
    public function getAllKeys()
    {
        $this->_load();
        return parent::getAllKeys();
    }
    
    public function hasKey($key)
    {
        $this->_load();
        return parent::hasKey($key);
    }
    
    public function drop($key)
    {
        parent::drop($key);
        $this->_update();
    }
    
    public function clear()
    {
        parent::clear();
        $this->_update();
    }
    
    private function _load()
    {
        if( ! $this->loaded)
        {
            $items = $this->load();
            parent::setAll($items);
            $this->snapshot = $items;
            $this->loaded = true;
        }
    }
    
    private function _update()
    {
        $items = parent::getAll();
        if($this->snapshot != $items)
            $this->update($items);
        $this->snapshot = $items;
    }
}