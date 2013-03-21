<?php
namespace Arsenal\TestFramework;

/*
    Responsible for Running tests and providing statistics about the results.
*/
class TestSession
{
    private $classes = array();
    private $results = null;
    
    public function getLoadedClasses()
    {
        return $this->classes;
    }
    
    public function loadClass($class)
    {
        if( ! $this->isTestClass($class))
            throw new \InvalidArgumentException('Class name must end with: _Test');
        
        if( ! class_exists($class))
            throw new \InvalidArgumentException('Class does not exist: '.$class);
        
        $this->classes[] = $class;
    }
    
    public function loadFile($path)
    {
        if( ! is_file($path))
            throw new \InvalidArgumentException('Invalid file: '.$path);
        
        $classesBefore = get_declared_classes();
        require_once $path;
        $loadedClasses = array_diff(get_declared_classes(), $classesBefore);
        
        // only loaded classes with valid names
        $classes = array_filter($loadedClasses, array($this, 'isTestClass'));
        
        foreach($classes as $class)
            $this->loadClass($class);
    }
    
    public function loadFolder($path, $recursive = false)
    {
        $files = $this->findFiles($path, $recursive);
        foreach($files as $file)
            $this->loadFile($file);
    }
    
    public function run()
    {
        if( ! $this->classes)
            throw new \RuntimeException('Nothing to run');
        
        $results = new SessionResults;
        foreach($this->classes as $class)
        {
            $obj = new $class;
            $obj->_run($results);
            unset($obj);
        }
        return $results;
    }
    
    private function findFiles($dir, $recursive = false)
    {
        if( ! is_dir($dir))
            throw new \InvalidArgumentException('Invalid directory: '.$dir);
        
        $files = glob("$dir/*.php");
        
        if($recursive)
        {
            $dirs = glob("$dir/*", GLOB_ONLYDIR);
            foreach($dirs as $dir)
                $files = array_merge($this->findFiles($dir, true), $files);
        }
        return $files;
    }
    
    private function isTestClass($class)
    {
        $rClass = new \ReflectionClass($class);
        $isSubclass = $rClass->isSubclassOf(__NAMESPACE__.'\TestFixture');
        $isAbstract = $rClass->isAbstract();
        return $isSubclass and ! $isAbstract;
    }
}