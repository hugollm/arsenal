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
        
        $placeholder = '#'.preg_quote(preg_quote('/{')).'[^'.preg_quote(preg_quote('/{}?')).']+'.preg_quote(preg_quote('}')).'#';
        $optional = '#'.preg_quote(preg_quote('/{')).'[^'.preg_quote(preg_quote('/{}?')).']+'.preg_quote(preg_quote('?}')).'#';
        $asterisk = '#'.preg_quote(preg_quote('*')).'#';
        $borderAsterisk = '#'.preg_quote(preg_quote('/*')).'#';
        
        $pattern = preg_replace($optional, '(?:/([^/]+))?', $pattern);
        $pattern = preg_replace($placeholder, '/([^/]+)', $pattern);
        $pattern = preg_replace($borderAsterisk, '(?:/.*)?', $pattern);
        $pattern = preg_replace($asterisk, '.*', $pattern);
        
        // dump($pattern, $path);
        
        $isMatch = (bool) preg_match('#^'.$pattern.'$#', $path, $matches);
        array_shift($matches);
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