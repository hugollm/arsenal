<?php
namespace Arsenal\Storages;

class ArrayStorage implements Storage
{
    private $items = array();
    
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }
    
    public function get($key, $default = null)
    {
        return isset($this->items[$key]) ? $this->items[$key] : $default;
    }
    
    public function set($key, $val)
    {
        $this->items[$key] = $val;
    }
    
    public function getAll()
    {
        return $this->items;
    }
    
    public function setAll(array $items)
    {
        $this->items = $items;
    }
    
    public function getAllKeys()
    {
        return array_keys($this->items);
    }
    
    public function hasKey($key)
    {
        return in_array($key, $this->getAllKeys());
    }
    
    public function drop($key)
    {
        unset($this->items[$key]);
    }
    
    public function clear()
    {
        $this->items = array();
    }
}