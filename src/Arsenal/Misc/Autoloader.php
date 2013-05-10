<?php
namespace Arsenal\Misc;

/*
    PSR0 compliant Autoloader.
*/
class Autoloader
{
    private $autoloads = array();
    private $isRegistered = false;
    private $checkFileSystem = false;
    
    /*
        Verifying the file system is useful at development time.
        This is to make sure you won't make mistakes that will not show up
        on Windows (because the files system is case insensitive).
        Should be turned off in production because of the overhead.
    */
    public function setCheckFileSystem($checkFileSystem) // bool
    {
        $this->checkFileSystem = $checkFileSystem;
    }
    
    public function addFolder($folder, $separator = '\\', $ext = '.php')
    {
        if($this->checkFileSystem)
            $this->checkFolder($folder);
        
        $autoload = array('folder' => realpath($folder), 'separator' => $separator, 'ext' => $ext);
        $this->autoloads[$folder] = $autoload;
    }
    
    public function dropFolder($folder)
    {
        unset($this->autoloads[$folder]);
    }
    
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }
    
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }
    
    public function loadClass($class)
    {
        $ds = DIRECTORY_SEPARATOR;
        foreach($this->autoloads as $autoload)
        {
            $file = $autoload['folder'].$ds.str_replace($autoload['separator'], $ds, $class).$autoload['ext'];
            if(is_file($file))
            {
                if($this->checkFileSystem)
                    $this->checkFile($file);
                
                require_once $file;
                break;
            }
        }
    }
    
    private function checkFile($file)
    {
        $fileName = basename($file);
        $realName = basename(realpath($file));
        $class = strstr(basename($file), '.', true);
        if($fileName !== $realName)
            throw new \RuntimeException('Class found but case diverges from file system. Tried "'.$class.'", found "'.$realName.'"');
    }
    
    private function checkFolder($folder)
    {
        if( ! is_dir($folder))
            throw new \RuntimeException('Folder "'.$folder.'" does not exist in file system');
        
        $folderName = basename($folder);
        $realName = basename(realpath($folder));
        if($folderName !== $realName)
            throw new \RuntimeException('Folder case diverges from file system. Tried '.$folder.', found '.$realName);
    }
}