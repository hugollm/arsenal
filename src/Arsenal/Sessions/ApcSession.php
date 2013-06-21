<?php
namespace Arsenal\Sessions;
use Arsenal\Http\CookieJar;

class ApcSession extends Session
{
    private $prefix;
    
    public function __construct(CookieJar $cookies, $prefix = 'session_')
    {
        if( ! function_exists('apc_fetch'))
            throw new \RuntimeException('ApcSession requires APC to be enabled');
        
        $this->prefix = $prefix;
        parent::__construct($cookies);
    }
    
    protected function read($id)
    {
        $fetch = apc_fetch($this->prefix.$id);
        if( ! $fetch)
            return array();
        return unserialize(base64_decode($fetch));
    }
    
    protected function write($id, array $payload, \DateTime $dt)
    {
        $ttl = $dt->getTimestamp() - time();
        apc_store($this->prefix.$id, base64_encode(serialize($payload)), $ttl);
    }
    
    protected function delete($id)
    {
        apc_delete($this->prefix.$id);
    }
    
    protected function revalidate($id, \DateTime $dt)
    {
        $ttl = $dt->getTimestamp() - time();
        $fetch = apc_fetch($this->prefix.$id);
        apc_store($this->prefix.$id, $fetch, $ttl);
    }
    
    protected function cleanup()
    {
        // no need
    }
}