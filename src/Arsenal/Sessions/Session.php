<?php
namespace Arsenal\Sessions;
use Arsenal\Http\CookieJar;
use Arsenal\Loggers\Logger;

abstract class Session
{
    private $cookieName = 'ssid';
    private $expiration = '24 hours';
    private $cleanupChance = 0.001;
    private $logger = null;
    
    private $cookies;
    private $isStarted = false;
    private $isNew = true;
    private $id = null;
    private $identityToken = null;
    private $startPayload = array();
    private $normalData = array();
    private $flashData = array();
    private $newFlashData = array();
    
    abstract protected function read($id);
    abstract protected function write($id, array $payload, \DateTime $dt);
    abstract protected function delete($id);
    abstract protected function revalidate($id, \DateTime $dt);
    abstract protected function cleanup();
    
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }
    
    public function __destruct()
    {
        $this->save();
    }
    
    public function setCookieName($name)
    {
        $this->cookieName = $name;
    }
    
    public function setExpiration($expiration)
    {
        new \DateTime($expiration); // validating
        $this->expiration = $expiration;
    }
    
    public function setCleanupChance($chance)
    {
        if($chance < 0 or $chance > 1)
            throw new \InvalidArgumentException('Cleanup chance must be a value between 0 and 1. Example: 1/100 = 0.01 = one chance in a hundred');
        $this->cleanupChance = $chance;
    }
    
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function getId()
    {
        $this->start();
        return $this->id;
    }
    
    public function get($key)
    {
        $this->start();
        return isset($this->normalData[$key]) ? $this->normalData[$key] : null;
    }
    
    public function getAll()
    {
        $this->start();
        return $this->normalData;
    }
    
    public function getFlash($key)
    {
        $this->start();
        return isset($this->flashData[$key]) ? $this->flashData[$key] : null;
    }
    
    public function getAllFlash()
    {
        $this->start();
        return $this->flashData;
    }
    
    public function set($key, $val)
    {
        $this->start();
        $this->normalData[$key] = $val;
        $this->register();
    }
    
    public function setFlash($key, $val)
    {
        $this->start();
        $this->flashData[$key] = $val;
        $this->newFlashData[$key] = $val;
        $this->register();
    }
    
    public function drop($key)
    {
        $this->start();
        unset($this->normalData[$key]);
    }
    
    public function dropAll()
    {
        $this->start();
        $this->normalData = array();
    }
    
    public function dropFlash($key)
    {
        $this->start();
        unset($this->flashData[$key]);
        unset($this->newFlashData[$key]);
    }
    
    public function dropAllFlash()
    {
        $this->start();
        $this->flashData = array();
        $this->newFlashData = array();
    }
    
    public function touch()
    {
        $this->start();
        if($this->id)
            $this->revalidate($this->id, new \DateTime($this->expiration));
    }
    
    public function save()
    {
        $this->start();
        $payload = $this->getPayload();
        if($payload !== $this->startPayload)
            $this->write($this->id, $payload, new \DateTime($this->expiration));
    }
    
    public function destroy()
    {
        $this->start();
        $this->delete($this->id);
        $this->unregister();
        $this->startPayload = array();
        $this->normalData = array();
        $this->flashData = array();
        $this->newFlashData = array();
    }
    
    private function start()
    {
        if($this->isStarted)
            return;
        $this->isStarted = true;
        
        // chance of cleaning up expired sessions
        if($this->chance($this->cleanupChance))
            $this->cleanup();
        
        
        // check if session already exists
        $this->id = $this->cookies->get($this->cookieName);
        if($this->id)
        {
            $this->isNew = false;
            $this->startPayload = $this->read($this->id);
            
            $this->parsePayload($this->startPayload);
            $this->revalidate($this->id, new \DateTime($this->expiration));
            
            if($this->identityToken and $this->identityToken !== $this->genSecurityToken())
            {
                if($this->logger)
                    $this->logger->warning('possible session hijack attempt from ip: '.$this->cookies->getRequest()->getIp());
                $this->destroy();
            }
            
            if( ! $this->startPayload)
                $this->destroy();
        }
    }
    
    private function register()
    {
        if($this->isNew and ($this->normalData or $this->newFlashData))
        {
            $this->isNew = false;
            $this->id = sha1(mt_rand());
            $this->identityToken = $this->genSecurityToken();
            $this->cookies->set($this->cookieName, $this->id);
        }
    }
    
    private function unregister()
    {
        $this->isNew = true;
        $this->id = null;
        $this->identityToken = null;
        $this->cookies->drop($this->cookieName);
    }
    
    private function getPayload()
    {
        $payload = $this->normalData;
        foreach($this->newFlashData as $key=>$val)
            $payload['[flash]:'.$key] = $val;
        if($this->identityToken)
            $payload['[identity_token]'] = $this->identityToken;
        return $payload;
    }
    
    private function parsePayload($payload)
    {
        foreach($payload as $key=>$val)
            if(strpos($key, '[flash]:') === 0)
                $this->flashData[substr($key, 8)] = $val;
            elseif($key === '[identity_token]')
                $this->identityToken = $val;
            else
                $this->normalData[$key] = $val;
    }
    
    private function genSecurityToken()
    {
        return sha1($this->cookies->getRequest()->getIp().$this->cookies->getRequest()->getUserAgent());
    }
    
    private function chance($chance)
    {
        $rand = mt_rand(1, 100000) / 100000;
        return $rand <= $chance;
    }
}