<?php
namespace Arsenal\Sessions;
use Arsenal\Http\CookieJar;

class FileSession extends Session
{
    private $folder;
    
    public function __construct(CookieJar $cookies, $folder)
    {
        $this->folder = realpath($folder);
        parent::__construct($cookies);
    }
    
    protected function read($id)
    {
        $file = $this->getFileName($id);
        if(is_file($file))
            return unserialize(file_get_contents($file));
        else
            return array();
    }
    
    protected function write($id, array $payload, \DateTime $dt)
    {
        $file = $this->makeFileName($id, $dt);
        file_put_contents($file, serialize($payload));
    }
    
    protected function delete($id)
    {
        $file = $this->getFileName($id);
        if(is_file($file))
            unlink($file);
    }
    
    protected function revalidate($id, \DateTime $dt)
    {
        $file = $this->getFileName($id);
        if( ! is_file($file))
            return;
        
        $newfile = $this->makeFileName($id, $dt);
        rename($file, $newfile);
    }
    
    protected function cleanup()
    {
        $files = glob($this->folder.DIRECTORY_SEPARATOR.'*');
        foreach($files as $file)
        {
            $time = strstr($file, '.');
            $time = substr($time, 1);
            if(is_numeric($time) and time() > $time)
                unlink($file);
        }
    }
    
    private function getFileName($id)
    {
        $files = glob($this->folder.DIRECTORY_SEPARATOR.$id.'.*');
        return array_shift($files);
    }
    
    private function makeFileName($id, \DateTime $dt)
    {
        return $this->folder.DIRECTORY_SEPARATOR.$id.'.'.$dt->getTimestamp();
    }
}