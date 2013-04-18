<?php

class Log
{
    public static function critical($message)
    {
        self::commit(0, $message);
    }
    
    public static function error($message)
    {
        self::commit(1, $message);
    }
    
    public static function warning($message)
    {
        self::commit(2, $message);
    }
    
    public static function notice($message)
    {
        self::commit(3, $message);
    }
    
    public static function info($message)
    {
        self::commit(4, $message);
    }
    
    public static function debug($message)
    {
        self::commit(5, $message);
    }
    
    public static function get($level = 5, $time = null)
    {
        if($time)
        {
            $time = new DateTime($time);
            return R::find('metalog', 'level <= ? AND timestamp >= ?', array($level, $time->getTimestamp()));
        }
        else
            return R::find('metalog', 'level <= ?', array($level));
    }
    
    private static function commit($level, $message)
    {
        $log = R::dispense('metalog');
        $log->level = $level;
        $log->message = $message;
        $log->timestamp = time();
        R::store($log);
    }
}