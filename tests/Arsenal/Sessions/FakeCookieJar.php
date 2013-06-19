<?php
namespace Arsenal\Sessions;
use Arsenal\Http\CookieJar;

class FakeCookieJar extends CookieJar
{
    private $request;
    private $cookies = array();
    
    public function __construct(FakeRequest $request)
    {
        $this->request = $request;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function get($key)
    {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : null;
    }
    
    public function set($key, $val, $expiration = null)
    {
        $this->cookies[$key] = $val;
    }
    
    public function drop($key)
    {
        unset($this->cookies[$key]);
    }
}