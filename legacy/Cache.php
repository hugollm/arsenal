<?php

class Cache
{
    public static function get($key = null)
    {
        self::cleanup();
        
        if(function_exists('apc_add'))
            return self::apcGet($key);
        else
            return self::dbGet($key);
    }
    
    public static function set($key, $val, $expire = null)
    {
        if($expire)
            $expire = new DateTime($expire);
        else
            $expire = new DateTime('+5 years');
        
        if(function_exists('apc_add'))
            self::apcSet($key, $val, $expire);
        else
            self::dbSet($key, $val, $expire);
    }
    
    public static function exec($key, $expire, $callback)
    {
        $cached = self::get($key);
        if($cached !== null)
            return $cached;
        
        if( ! is_callable($callback))
            throw new InvalidArgumentException('Cache::exec needs a valid callback as first argument.');
        
        $val = $callback();
        Cache::set($key, $val, $expire);
        return $val;
    }
    
    public static function clear()
    {
        if(function_exists('apc_add'))
            self::apcClear();
        else
            self::dbClear();
    }
    
    public static function clearExpired()
    {
        if(function_exists('apc_add'))
            self::apcClearExpired();
        else
            self::dbClearExpired();
    }
    
    private static function cleanup()
    {
        $chance = Config::get('cache.cleanup_chance');
        $rand = mt_rand(1, 10000)/10000;
        if($rand <= $chance)
            self::clearExpired();
    }
    
    private static function dbGet($key = null)
    {
        if($key === null)
        {
            $beans = R::find('metacache', 'expire > ?', array(time()));
            $vals = array();
            foreach($beans as $bean)
                $vals[$bean->cid] = unserialize(base64_decode($bean->payload));
            return $vals;
        }
        
        $bean = R::findOne('metacache', 'cid = ? AND expire > ?', array($key, time()));
        return $bean ? unserialize(base64_decode($bean->payload)) : null;
    }
    
    private static function dbSet($key, $val, DateTime $expire)
    {
        $cache = R::findOne('metacache', 'cid = ? AND expire > ?', array($key, time()));
        if( ! $cache)
            $cache = R::dispense('metacache');
        $cache->cid = $key;
        $cache->payload = base64_encode(serialize($val));
        $cache->expire = $expire->getTimestamp();
        
        R::store($cache);
    }
    
    private static function dbClear()
    {
        R::wipe('metacache');
    }
    
    private static function dbClearExpired()
    {
        R::exec('DELETE FROM metacache WHERE expire < ?', array(time()));
    }
    
    private static function apcGet($key = null)
    {
        $array = apc_fetch('metacache');
        if($array === false)
            $array = array();
        
        // get all
        if($key === null)
        {
            $return = array();
            foreach($array as $key=>$cache)
                if($cache['expire'] > time())
                    $return[$key] = $cache['val'];
            return $return;
        }
        
        if( ! isset($array[$key]))
            return null;
        
        if($array[$key]['expire'] < time())
            unset($array[$key]);
        
        return isset($array[$key]['val']) ? $array[$key]['val'] : null;
    }
    
    private static function apcSet($key, $val, DateTime $expire)
    {
        $array = apc_fetch('metacache');
        if($array === false)
            $array = array();
        
        $cache = array('val'=>$val, 'expire'=>$expire->getTimestamp());
        $array[$key] = $cache;
        
        apc_store('metacache', $array);
    }
    
    private static function apcClear()
    {
        apc_delete('metacache');
    }
    
    private static function apcClearExpired()
    {
        $array = apc_fetch('metacache');
        if($array === false)
            $array = array();
        
        foreach($array as $key=>$cache)
            if($cache['expire'] < time())
                unset($array[$key]);
        
        apc_store('metacache', $array);
    }
}