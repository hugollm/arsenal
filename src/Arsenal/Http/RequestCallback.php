<?php
namespace Arsenal\Http;

class RequestCallback
{
    private $callback;
    private $method = null;
    private $https = false;
    private $host = null;
    private $pattern = null;
    
    public function __construct($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('RequestCallback constructor needs a valid callback');
        
        $this->callback = $callback;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
    }
    
    public function setHttps($bool)
    {
        $this->https = $bool;
    }
    
    public function setHost($host)
    {
        $this->host = $host;
    }
    
    public function setPattern($pattern)
    {
        $this->pattern = new PathPattern($pattern);
    }
    
    public function match(Request $request, array &$matches = array())
    {
        if($this->method and ! $request->isMethod($this->method))
            return false;
        if($this->https and ! $request->isHttps())
            return false;
        if($this->host and ! $request->isHost($this->host))
            return false;
        if($this->pattern and ! $this->pattern->match($request->getPathInfo(), $matches))
            return false;
        return true;
    }
    
    public function tryAgainst(Request $request)
    {
        $matches = array();
        if($this->match($request, $matches))
        {
            call_user_func_array($this->callback, $matches);
            return true;
        }
        return false;
    }
}