<?php
namespace Arsenal\Http;

class PathPattern
{
    private $pattern = '';
    private $type = 'simple';
    
    public function __construct($pattern)
    {
        if(strpos($pattern, '~') === 0)
        {
            $pattern = substr($pattern, 1);
            $this->type = 'regex';
        }
        else
        {
            $pattern = trim($pattern, '/');
        }
        $this->pattern = $pattern;
    }
    
    public function match($path, array &$matches = array())
    {
        if($this->type === 'simple')
            return $this->simpleMatch($path, $matches);
        else
            return $this->regexMatch($path, $matches);
    }
    
    private function simpleMatch($path, array &$matches)
    {
        $path = trim($path, '/');
        
        $patternChunks = $this->pattern ? explode('/', $this->pattern) : array();
        $pathChunks = $path ? explode('/', $path) : array();
        
        while($patternChunks)
        {
            $a = array_shift($patternChunks);
            $b = array_shift($pathChunks);
            
            $normal = $this->chunkIsNormal($a);
            $placeholder = $this->chunkIsPlaceholder($a);
            $optional = $this->chunkIsOptional($a);
            
            if($normal and ($a !== $b))
                return false;
            
            if( ! $optional and ! $b)
                return false;
            
            if($placeholder)
                $matches[] = $b ?: null;
        }
        
        if($pathChunks)
            return false;
        
        return true;
    }
    
    private function regexMatch($path, array &$matches)
    {
        $isMatch = (bool) preg_match('#^'.$this->pattern.'$#', $path, $matches);
        array_shift($matches);
        return $isMatch;
    }
    
    private function chunkIsNormal($chunk)
    {
        return ! $this->chunkIsPlaceholder($chunk);
    }
    
    private function chunkIsPlaceholder($chunk)
    {
        return (strpos($chunk, '{') === 0) and (strrpos($chunk, '}') === strlen($chunk)-1);
    }
    
    private function chunkIsOptional($chunk)
    {
        return $this->chunkIsPlaceholder($chunk) and (strpos($chunk, '?}') === strlen($chunk)-2);
    }
}