<?php

class Form
{
    private static $fields = null;
    private static $setFields = null;
    
    public static function fill(array $post)
    {
        self::start();
        $post = self::parseKeys($post);
        self::$fields = array_merge(self::$fields, $post);
        self::$setFields = array_merge(self::$setFields, $post);
        self::save();
    }
    
    public static function field($key, $default = null)
    {
        self::start();
        return isset(self::$fields[$key]) ? self::$fields[$key] : $default;
    }
    
    public static function set($key, $val)
    {
        self::start();
        self::$fields[$key] = $val;
        self::$setFields[$key] = $val;
        self::save();
    }
    
    private static function start()
    {
        if(self::$fields !== null) // only once
            return;
        self::$fields = Session::getFlash('form.fields') ?: array();
        self::$setFields = array();
    }
    
    private static function save()
    {
        Session::setFlash('form.fields', self::$setFields);
    }
    
    private static function parseKeys(array $array, $stack = array())
    {
        $newArray = array();
        foreach($array as $key=>$val)
        {
            if(is_array($val))
            {
                $tmpStack = $stack;
                $tmpStack[] = $key;
                $newArray = array_merge($newArray, self::parseKeys($val, $tmpStack));
            }
            else
            {
                if( ! $stack)
                    $tmpKey = $key;
                else
                {
                    $tmpStack = $stack;
                    $tmpKey = array_shift($tmpStack);
                    foreach($tmpStack as $k=>$v)
                        $tmpKey .= "[$v]";
                    $tmpKey .= "[$key]";
                }
                $newArray[$tmpKey] = $val;
            }
        }
        return $newArray;
    }
}