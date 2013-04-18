<?php

class Session
{
    private static $sid = null;
    private static $token = null;
    private static $expire = null;
    private static $payload = null;
    private static $flashPayload = null;
    private static $setFlash = null;
    private static $bean = null;
    
    public static function get($key = null)
    {
        self::start();
        if($key === null)
            return self::$payload ?: array();
        return isset(self::$payload[$key]) ? self::$payload[$key] : null;
    }
    
    public static function set($key, $val)
    {
        self::start();
        if( ! isset(self::$payload[$key]) or self::$payload[$key] !== $val)
        {
            self::$payload[$key] = $val;
            self::save();
        }
    }
    
    public static function drop($key)
    {
        self::start();
        if(isset(self::$payload[$key]))
        {
            unset(self::$payload[$key]);
            self::save();
        }
    }
    
    public static function clear()
    {
        self::start();
        if(self::$payload)
        {
            self::$payload = array();
            self::save();
        }
    }
    
    public static function getFlash($key = null)
    {
        self::start();
        if($key === null)
            return self::$flashPayload ?: array();
        return isset(self::$flashPayload[$key]) ? self::$flashPayload[$key] : null;
    }
    
    public static function setFlash($key, $val)
    {
        self::start();
        if( ! isset(self::$setFlash[$key]) or self::$setFlash[$key] !== $val)
        {
            self::$flashPayload[$key] = $val;
            self::$setFlash[$key] = $val;
            self::save();
        }
    }
    
    public static function dropFlash($key)
    {
        self::start();
        if(isset(self::$flashPayload[$key]))
        {
            unset(self::$flashPayload[$key]);
            unset(self::$setFlash[$key]);
            self::save();
        }
    }
    
    public static function clearFlash()
    {
        self::start();
        if(self::$flashPayload)
        {
            self::$flashPayload = array();
            self::$setFlash = array();
            self::save();
        }
    }
    
    public static function destroy()
    {
        self::start();
        
        if(self::$bean)
        {
            R::trash(self::$bean);
            Cookie::drop(Config::get('session.cookie'));
        }
        self::$sid = null;
        self::$token = null;
        self::$payload = null;
        self::$bean = null;
    }
    
    public static function regenerate()
    {
        self::start();
        self::$sid = self::generateSid();
        Cookie::set(Config::get('session.cookie'), self::$sid);
        self::save();
    }
    
    public static function touch()
    {
        self::start();
    }
    
    private static function start()
    {
        // only load once
        if(self::$sid)
            return;
        
        // a chance (from configuration) that expired sessions will be deleted
        self::cleanup();
        
        $sid = Cookie::get(Config::get('session.cookie'));
        $bean = R::findOne('metasession', 'sid = ? AND expire > ?', array($sid, time()));
        
        if($bean)
            self::load($bean);
        else
            self::create();
        
        if(self::$token != self::generateToken())
            self::destroy();
    }
    
    private static function create()
    {
        self::$sid = self::generateSid();
        self::$token = self::generateToken();
        self::$expire = strtotime(Config::get('session.expire'));
        self::$payload = array();
        self::$flashPayload = array();
        self::$setFlash = array();
        self::$setFlash = array();
    }
    
    private static function load($bean)
    {
        self::$sid = $bean->sid;
        self::$token = $bean->token;
        self::$expire = $bean->expire;
        self::$payload = unserialize(base64_decode($bean->payload));
        self::$flashPayload = unserialize(base64_decode($bean->flash_payload));
        self::$setFlash = array();
        self::$bean = $bean;
        
        $bean->expire = strtotime(Config::get('session.expire'));
        $bean->flash_payload = base64_encode(serialize(array()));
        R::store($bean); // updating expire time and removing flash data
    }
    
    private static function save()
    {
        if( ! self::$bean)
        {
            self::$bean = R::dispense('metasession');
            Cookie::set(Config::get('session.cookie'), self::$sid);
        }
        
        $bean = self::$bean;
        $bean->sid = self::$sid;
        $bean->token = self::$token;
        $bean->expire = self::$expire;
        $bean->payload = base64_encode(serialize(self::$payload));
        $bean->flash_payload = base64_encode(serialize(self::$setFlash));
        
        R::store($bean);
    }
    
    private static function generateSid()
    {
        return sha1(uniqid(true).mt_rand());
    }
    
    private static function generateToken()
    {
        return sha1(Request::getUserAgent().Request::getIp());
    }
    
    private static function cleanup()
    {
        $chance = Config::get('session.cleanup_chance');
        $rand = mt_rand(1, 10000)/10000;
        if($rand <= $chance)
        {
            $expiredSessions = R::find('metasession', 'expire < ?', array(time()));
            R::trashAll($expiredSessions);
        }
    }
}