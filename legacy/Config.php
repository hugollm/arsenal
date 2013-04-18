<?php

class Config
{
    private static $configs = array();
    private static $envs = array();
    
    public static function load(array $configs)
    {
        foreach($configs as $env=>$array)
        {
            // if it doesn't have a condition or if the condition is truly
            if( ! isset($array['condition']) or $array['condition'])
            {
                self::$configs = array_merge(self::$configs, $array);
                self::$envs[$env] = true;
            }
        }
        
        unset(self::$configs['condition']);
    }
    
    public static function get($key = null)
    {
        if($key === null)
            return self::$configs;
        
        if( ! isset(self::$configs[$key]))
            throw new RunTimeException('Configuration not found: "'.$key.'"');
        
        return self::$configs[$key];
    }
    
    public static function set($key, $val)
    {
        self::$configs[$key] = $val;
    }
    
    public static function isLoaded($env)
    {
        return ! empty(self::$envs[$env]);
    }
}