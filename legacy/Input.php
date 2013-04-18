<?php

class Input
{
    static function get($key = null)
    {
        if( ! $key)
            return $_GET;
        else
            return isset($_GET[$key]) ? $_GET[$key] : null;
    }
    
    static function post($key = null)
    {
        if( ! $key)
            return $_POST;
        else
            return isset($_POST[$key]) ? $_POST[$key] : null;
    }
    
    static function file($key = null)
    {
        if( ! $key)
            return $_FILES;
        else
            return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }
}