<?php
namespace Arsenal\Http;

class CookieJar
{
    private $request;
    private $response;
    private $cookies;
    
    private $path = null;
    private $domain = null;
    private $secureOnly = false;
    private $httpOnly = true;
    
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->cookies = $this->request->getCookies();
        
        $this->path = $request->getBasePath();
    }
    
    public function get($key)
    {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : null;
    }
    
    public function getAll()
    {
        return $this->cookies;
    }
    
    public function set($key, $val, $expiration = null)
    {
        $this->cookies[$key] = $val;
        $this->response->setCookie($key, $val, $expiration, $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
    }
    
    public function drop($key)
    {
        $this->response->dropCookie($key, $this->path, $this->domain, $this->secureOnly, $this->httpOnly);
    }
    
    public function dropAll()
    {
        foreach($this->cookies as $key=>$val)
            $this->drop($key);
        $this->cookies = array();
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
    
    public function setSecureOnly($bool)
    {
        $this->secureOnly = $bool;
    }
    
    public function setHttpOnly($bool)
    {
        $this->httpOnly = $bool;
    }
}