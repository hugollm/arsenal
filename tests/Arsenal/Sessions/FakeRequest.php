<?php
namespace Arsenal\Sessions;
use Arsenal\Http\Request;

class FakeRequest extends Request
{
    private $ip = null;
    private $userAgent = null;
    
    public function getBasePath()
    {
        return '/';
    }
    
    public function getIp()
    {
        return $this->ip;
    }
    
    public function getUserAgent()
    {
        return $this->userAgent;
    }
    
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
    
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }
}