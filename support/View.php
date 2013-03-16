<?php

class View
{
    private static $queue = array();
    private static $vars = array();
    
    public static function make()
    {
        $views = func_get_args();
        foreach($views as $view)
            self::$queue[] = $view;
        
        return 'app/views/'.array_shift(self::$queue);
    }
    
    public static function next()
    {
        return 'app/views/'.array_shift(self::$queue);
    }
    
    public static function get($key, $default = null)
    {
        return isset(self::$vars[$key]) ? self::$vars[$key] : $default;
    }
    
    public static function set($key, $val)
    {
        self::$vars[$key] = $val;
    }
    
    public static function keyPrint($key, $default = null)
    {
        $val = self::get($key, $default);
        self::safePrint($val);
    }
    
    public static function safePrint($text)
    {
        echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}