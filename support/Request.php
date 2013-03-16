<?php

class Request
{
    private static $parsedUri = null;
    
    public static function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public static function getScheme()
    {
        return ( ! empty($_SERVER['HTTPS']) and strtolower($_SERVER['HTTPS']) !== 'off' or $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    }
    
    public static function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    public static function getProtocol()
    {
        return isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    }
    
    public static function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }
    
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
    
    public static function getIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }
    
    public static function getEtag()
    {
        return isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') : null;
    }
    
    public static function getUri()
    {
        return self::getUriBase().self::getUriArgs();
    }
    
    public static function getUriBase()
    {
        list($uriBase, $uriArgs) = self::parseUri($_SERVER);
        return $uriBase;
    }
    
    public static function getUriArgs()
    {
        list($uriBase, $uriArgs) = self::parseUri($_SERVER);
        return $uriArgs;
    }
    
    public static function getBaseUrl()
    {
        return self::getScheme().'://'.self::getHost().self::getUriBase().'/';
    }
    
    public static function isMethod($method)
    {
        return (self::getMethod() == strtoupper($method));
    }
    
    public static function isHttps()
    {
        return (self::getScheme() == 'https');
    }
    
    public static function getBaseTag()
    {
        return '<base href="'.self::getBaseUrl().'" />';
    }
    
    private static function parseUri($server)
    {
        // using cached
        if(self::$parsedUri)
            return self::$parsedUri;
        
        $path = empty($server['PATH_INFO']) ? null : $server['PATH_INFO'];
        $uri = empty($server['REQUEST_URI']) ? null : $server['REQUEST_URI'];
        if($uri)
            $uri = strstr($uri.'?', '?', true) ?: null; // before first '?'
        
        $uriBase = $path ? substr($uri, 0, strrpos($uri, $path)) : $uri;
        $uriArgs = $path;
        
        // extras '/' in the end of uriBase should be in the beginning of uriArgs
        while($uriBase[strlen($uriBase)-1] == '/')
        {
            $uriBase = substr($uriBase, 0, -1);
            $uriArgs = '/'.$uriArgs;
        }
        
        self::$parsedUri = array($uriBase, $uriArgs); // caching
        return array($uriBase, $uriArgs);
    }
}