<?php

class Feedback
{
    private static $feedbacks = null;
    private static $setFeedbacks = null;
    
    public static function add($type, $message)
    {
        self::start();
        self::$feedbacks[] = array('type'=>$type, 'message'=>$message);
        self::$setFeedbacks[] = array('type'=>$type, 'message'=>$message);
        self::save();
    }
    
    public static function dump($format)
    {
        self::start();
        $dump = '';
        foreach(self::$feedbacks as $fb)
        {
            $string = str_replace('{type}', $fb['type'], $format);
            $string = str_replace('{message}', $fb['message'], $string);
            $dump .= $string;
        }
        return $dump;
    }
    
    private static function start()
    {
        if(self::$feedbacks !== null) // only once
            return;
        self::$feedbacks = Session::getFlash('feedbacks') ?: array();
        self::$setFeedbacks = array();
    }
    
    private static function save()
    {
        Session::setFlash('feedbacks', self::$setFeedbacks);
    }
}