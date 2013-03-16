<?php

class Cookie
{
    public static function get($key = null)
    {
        if( ! $key)
            return $_COOKIE;
        else
            return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }
    
    public static function set($key, $val, $expire = null, $httpOnly = null, $secureOnly = null)
    {
        if($expire)
            $expire = new DateTime($expire);
        else
            $expire = new DateTime('+5 years');
        
        if($httpOnly === null)
            $httpOnly = Config::get('cookie.http_only');
        if($secureOnly === null)
            $secureOnly = Config::get('cookie.secure_only');
        
        setcookie($key, $val, $expire->getTimestamp(), Request::getUriBase(), null, $secureOnly, $httpOnly);
        $_COOKIE[$key] = $val;
    }
    
    static function drop($key, $httpOnly = null, $secureOnly = null)
    {
        if( ! isset($_COOKIE[$key]))
            return;
        
        if($httpOnly === null)
            $httpOnly = Config::get('cookie.http_only');
        if($secureOnly === null)
            $secureOnly = Config::get('cookie.secure_only');
        
        setcookie($key, false, strtotime('-5 years'), Request::getUriBase(), null, $secureOnly, $httpOnly);
        unset($_COOKIE[$key]);
    }
    
    static function dropAll()
    {
        foreach(self::get() as $key=>$val)
            self::drop($key);
    }
}