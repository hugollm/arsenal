<?php
namespace Arsenal\Http;

use Arsenal\Http\Request;

class ControllerMatcher
{
    public function match(Request $request)
    {
        $pi = $request->getPathInfo();
        $chunks = $this->splitPathInfo($pi);
        $httpMethod = strtolower($request->getMethod());
        $tests = $this->getPossibilities($chunks, $request->getMethod());
        
        foreach($tests as $test)
        {
            $method = $this->dashToCamel($test[1]);
            
            $route['class'] = $this->dashToStudly($test[0]);
            $route['method'] = '_'.$httpMethod.'_'.$method;
            $route['args'] = $test[2];
            
            if($this->routeExists($route['class'], $route['method'], $route['args']))
                return $route;
            
            $route['method'] = '_any_'.$method;
            if($this->routeExists($route['class'], $route['method'], $route['args']))
                return $route;
        }
        return false;
    }
    
    private function splitPathInfo($pathInfo)
    {
        $pathInfo = trim($pathInfo, '/');
        while(strpos($pathInfo, '//'))
            $pathInfo = str_replace('//', '/', $pathInfo);
        $chunks = explode('/', $pathInfo);
        return array_filter($chunks);
    }
    
    private function getPossibilities($chunks, $httpMethod)
    {
        $tests = array();
        if(count($chunks) >= 2)
            $tests[] = array($chunks[0], $chunks[1], array_slice($chunks, 2));
        if(count($chunks) >= 1)
        {
            $tests[] = array($chunks[0], 'index', array_slice($chunks, 1));
            $tests[] = array('index', $chunks[0], array_slice($chunks, 1));
        }
        $tests[] = array('index', 'index', $chunks);
        return $tests;
    }
    
    private function routeExists($class, $method, array $args)
    {
        // class exists with this exact name?
        if( ! class_exists($class))
            return false;
        $rClass = new \ReflectionClass($class);
        if($class !== $rClass->getName())
            return false;
        
        // method exists with this exact name?
        if( ! $rClass->hasMethod($method))
            return false;
        $rMethod = $rClass->getMethod($method);
        if($method !== $rMethod->getName())
            return false;
        
        // arguments are compatible?
        if($rMethod->getNumberOfParameters() < count($args))
            return false;
        if($rMethod->getNumberOfRequiredParameters() > count($args))
            return false;
        
        return true;
    }
    
    private function dashToCamel($chunk)
    {
        $pieces = explode('-', $chunk);
        $camel = array_shift($pieces);
        foreach($pieces as $p)
            $camel .= ucfirst($p);
        return $camel;
    }
    
    private function dashToStudly($chunk)
    {
        return ucfirst($this->dashToCamel($chunk));
    }
}