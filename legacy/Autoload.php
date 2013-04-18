<?php

/*
    PSR0 compliant Autoloader.
    Also supports Legacy named classes.
*/
class Autoload
{
    private $basepath = null;
    private $namespace = null;
    private $separator = null;
    private $extension = null;
    
    /*
        Builds the context around the load() method.
    */
    private function __construct($basepath, $namespace, $separator, $extension)
    {
        $this->basepath = $this->normalizePath($basepath);
        $this->namespace = $namespace;
        $this->separator = $separator;
        $this->extension = $extension;
    }
    
    /*
        Registers a folder containing PSR0 compliant libraries.
        In a PSR0 compliant library, the namespaces + class names maps
        exactly to the location of the file containing the class.
        ex:
            Nomad\Http\Request
                maps to
            basepath/Nomad/Http/Request.php
    */
    static function register($basepath, $namespace=null)
    {
        $autoload = new self($basepath, $namespace, '\\', '.php');
        spl_autoload_register(array($autoload, 'load'));
    }
    
    /*
        Registers a folder containing legacy named libraries. In this scenario,
        a class name contains underlines to mimic namespaces behavior.
        ex:
            Nomad_Http_Request
                maps to
            basepath/Nomad/Http/Request.php
    */
    static function registerLegacy($basepath, $namespace=null, $separator='_', $extension='.php')
    {
        $autoload = new self($basepath, $namespace, $separator, $extension);
        spl_autoload_register(array($autoload, 'load'));
    }
    
    /*
        The method that is registered with spl_autoload_register().
    */
    private function load($class)
    {
        // it should not try if there's a namespace and it doesn't match the class
        if($this->namespace and $this->namespace != substr($class, 0, strlen($this->namespace)))
            return;
        
        $file = $this->basepath.'/'.$this->classPath($class, $this->separator).$this->extension;
        
        // should test is_file only if no namespace was specified
        if( ! $this->namespace and ! is_file($file))
            return;
        
        require_once $this->basepath.'/'.$this->classPath($class, $this->separator).$this->extension;
    }
    
    /*
        Breaks the class name into a folder structure.
    */
    private function classPath($class, $separator)
    {
        $path = str_replace($separator, '/', $class);
        return $this->normalizePath($path);
    }
    
    /*
        Normalizes a path removing trailing slashes, etc.
    */
    private function normalizePath($path)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $path = trim($path, '/');
        return $path;
    }
}