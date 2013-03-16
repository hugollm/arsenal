<?php

class FileStore
{
    public static function get($filename)
    {
        $file = R::findOne('metafile', 'name = ?', array($filename));
        if($file)
            return base64_decode($file->payload);
        else
            return false;
    }
    
    public static function getAllNames()
    {
        return R::getCol('SELECT name FROM metafile');
    }
    
    public static function set($contents, $extension)
    {
        $file = R::dispense('metafile');
        $file->name = sha1(mt_rand()).'.'.$extension;
        $file->payload = base64_encode($contents);
        
        R::store($file);
        return $file->name;
    }
    
    public static function drop($filename)
    {
        $file = R::findOne('metafile', 'name = ?', array($filename));
        if($file)
            R::trash($file);
        else
            throw new RunTimeException('FileStore::drop: file not found: '.$filename);
    }
}