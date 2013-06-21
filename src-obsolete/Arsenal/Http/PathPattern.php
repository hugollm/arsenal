<?php
namespace Arsenal\Http;

class PathPattern
{
    private $pattern = '';
    
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function match($path, array &$matches = array())
    {
        if($this->isRegex())
            return $this->regexMatch($path, $matches);
        else
            return $this->simpleMatch($path, $matches);
    }
    
    private function simpleMatch($path, array &$matches = array())
    {
        $path = $this->normalizePath($path);
        $pattern = $this->normalizePath($this->pattern);
        $pattern = preg_quote($pattern);
        
        $optional = '#'.preg_quote(preg_quote('/{')).'([a-zA-Z0-9'.preg_quote(preg_quote('_.')).']+)'.preg_quote(preg_quote('?}')).'#';
        $placeholder = '#'.preg_quote(preg_quote('/{')).'([a-zA-Z0-9'.preg_quote(preg_quote('_.')).']+)'.preg_quote(preg_quote('}')).'#';
        $borderAsterisk = '#'.preg_quote(preg_quote('/*')).'#';
        $asterisk = '#'.preg_quote(preg_quote('*')).'#';
        
        $pattern = preg_replace($optional, '(?:/(?P<$1>[^/]+))?', $pattern);
        $pattern = preg_replace($placeholder, '/(?P<$1>[^/]+)', $pattern);
        $pattern = preg_replace($borderAsterisk, '(?:/.*)?', $pattern);
        $pattern = preg_replace($asterisk, '.*', $pattern);
        
        // dump($pattern, $path);
        
        $isMatch = (bool) preg_match('#^'.$pattern.'$#', $path, $matches);
        
        // cleaning unnamed matches
        foreach($matches as $key=>$val)
            if(is_int($key))
                unset($matches[$key]);
        
        return $isMatch;
    }
    
    private function regexMatch($path, array &$matches = array())
    {
        $path = $this->normalizePath($path);
        $pattern = substr($this->pattern, 1); // removing the ~
        
        $isMatch = (bool) preg_match('#^'.$pattern.'$#', $path, $matches);
        array_shift($matches);
        return $isMatch;
    }
    
    private function isRegex()
    {
        return strpos($this->pattern, '~') === 0;
    }
    
    private function normalizePath($path)
    {
        $path = trim($path, '/');
        while(strpos($path, '//') !== false)
            $path = str_replace('//', '/', $path);
        return '/'.$path;
    }
}