<?php
namespace Arsenal\Sessions;

class FileSessionTest extends SessionTest
{
    private $folder = 'tests/tmp/sessions';
    
    protected function createSession(FakeCookieJar $cj = null)
    {
        if( ! is_dir($this->folder))
            mkdir($this->folder);
        
        if( ! $cj)
            $cj = $this->createCookieJar();
        
        return new FileSession($cj, $this->folder);
    }
    
    public function __destruct()
    {
        $files = $this->getAllFiles();
        foreach($files as $file)
            unlink($this->folder.'/'.$file);
    }
    
    public function teardown()
    {
        $this->testFileNames();
    }
    
    public function testFileNames()
    {
        $this->newSession(array('foo' => 'bar'));
        $files = $this->getAllFiles();
        foreach($files as $file)
            $this->assertTrue((bool)preg_match('|^[a-z0-9]+\.[0-9]+$|i', $file), 'file name shoud be in format: id.time');
    }
    
    private function getAllFiles()
    {
        $dir = opendir($this->folder);
        $files = array();
        while($file = readdir($dir))
            if(is_file($this->folder.'/'.$file))
                $files[] = $file;
        return $files;
    }
}