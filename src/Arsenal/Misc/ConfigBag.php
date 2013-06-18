<?php
namespace Arsenal\Misc;

class ConfigBag
{
    private $configs = array();
    
    public function loadArray(array $configs)
    {
        $this->configs = array_merge($this->configs, $configs);
    }
    
    public function loadFile($path)
    {
        $this->loadArray(include $path);
    }
    
    public function get($key)
    {
        if( ! isset($this->configs[$key]))
            throw new \RuntimeException('Configuration not found: "'.$key.'"');
        return $this->configs[$key];
    }
    
    public function getAll()
    {
        return $this->configs;
    }
    
    public function set($key, $val)
    {
        $this->configs[$key] = $val;
    }
}