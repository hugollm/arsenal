<?php

class Error
{
    private static $errors = null;
    private static $setErrors = null;
    
    public static function fill(array $errors, $domain = null)
    {
        self::start();
        if($domain)
        {
            $newErrors = array();
            foreach($errors as $key=>$val)
                $newErrors[$domain.'.'.$key] = $val;
            $errors = $newErrors;
        }
        self::$errors = array_merge(self::$errors, $errors);
        self::$setErrors = array_merge(self::$setErrors, $errors);
        self::save();
    }
    
    public static function get($key = null)
    {
        self::start();
        if($key === null)
            return self::$errors;
        
        return isset(self::$errors[$key]) ? self::$errors[$key] : null;
    }
    
    public static function set($key, $message, $domain = null)
    {
        self::start();
        if($domain)
            $key = $domain.'.'.$key;
        self::$errors[$key] = $message;
        self::$setErrors[$key] = $message;
        self::save();
    }
    
    private static function start()
    {
        if(self::$errors !== null) // only once
            return;
        self::$errors = Session::getFlash('errors') ?: array();
        self::$setErrors = array();
    }
    
    private static function save()
    {
        Session::setFlash('errors', self::$setErrors);
    }
}