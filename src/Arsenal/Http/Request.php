<?php
namespace Arsenal\Http;

class Request
{
    private $server = array();
    private $headers = array();
    private $query = array();
    private $input = array();
    private $files = array();
    private $cookies = array();
    
    private $methodOverride = null;
    
    public function __construct(array $server = array(), array $query = array(), array $input = array(), array $files = array(), array $cookies = array())
    {
        $this->server = $server;
        $this->headers = $this->parseHeaders($server);
        $this->query = $query;
        $this->input = $input;
        $this->files = $files;
        $this->cookies = $cookies;
    }
    
    public static function createCurrent()
    {
        return new self($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE);
    }
    
    public function getServer()
    {
        return $this->server;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function getInput()
    {
        return $this->input;
    }
    
    public function getFiles()
    {
        return $this->files;
    }
    
    public function getCookies()
    {
        return $this->cookies;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }
    
    public function getProtocol()
    {
        return isset($this->server['SERVER_PROTOCOL']) ? $this->server['SERVER_PROTOCOL'] : 'HTTP/1.0';
    }
    
    public function getMethod()
    {
        $mo = $this->methodOverride;
        $override = ($mo and isset($this->input[$mo])) ? strtoupper($this->input[$mo]) : null;
        return $override ?: $this->server['REQUEST_METHOD'];
    }
    
    /*
        This method won't be changed, even if $this->setMethodOverride() is
        used.
    */
    public function getRealMethod()
    {
        return $this->server['REQUEST_METHOD'];
    }
    
    public function getScheme()
    {
        return ( ! empty($this->server['HTTPS']) and strtolower($this->server['HTTPS']) !== 'off' or $this->server['SERVER_PORT'] == 443) ? 'https' : 'http';
    }
    
    public function getHost()
    {
        return $this->getHeader('Host');
    }
    
    public function getBasePath()
    {
        $script = $this->server['SCRIPT_NAME'];
        $base = dirname($script);
        return $base;
    }
    
    public function getPathInfo()
    {
        $uri = $this->server['REQUEST_URI'];
        $script = $this->server['SCRIPT_NAME'];
        $base = dirname($script);
        
        if(strpos($uri, $script) === 0)
            return substr($uri, strlen($script));
        if(strpos($uri, $base) === 0)
            return substr($uri, strlen($base));
        
        throw new \RuntimeException('Request object was unable to guess base path');
    }
    
    public function getReferer()
    {
        return $this->getHeader('Referer');
    }
    
    public function getUserAgent()
    {
        return $this->getHeader('User-Agent');
    }
    
    public function getIp()
    {
        return isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : null;
    }
    
    public function getEtag()
    {
        $etag = $this->getHeader('If-None-Match');
        $etag = ltrim($etag, 'W');
        $etag = ltrim($etag, '/');
        $etag = trim($etag, '"');
        return $etag ?: null;
    }
    
    public function isEtagWeak()
    {
        $etag = $this->getHeader('If-None-Match');
        return strpos($etag, 'W') === 0;
    }
    
    public function isHttps()
    {
        return $this->getScheme() === 'https';
    }
    
    public function isMethod($method)
    {
        return ($this->getMethod() === strtoupper($method));
    }
    
    /*
        Allows to define a POST input that overrides the method for the
        request in $this->getMethod().
    */
    public function setMethodOverride($inputKey)
    {
        $this->methodOverride = $inputKey;
    }
    
    private function parseHeaders(array $server)
    {
        $headers = array();
        foreach($server as $key=>$val)
            if(strpos($key, 'HTTP_') === 0)
            {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = strtolower($key);
                $key = ucwords($key);
                $key = str_replace(' ', '-', $key);
                $headers[$key] = $val;
            }
        return $headers;
    }
}