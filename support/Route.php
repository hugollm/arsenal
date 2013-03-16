<?php

class Route
{
    public static $filtersBefore = array();
    public static $routes = array();
    public static $filtersAfter = array();
    
    public static $currentRoute = null;
    
    public static function get($pattern, $callback, array $requirements = array())
    {
        self::addRoute('GET', $pattern, $callback, $requirements);
    }
    
    public static function post($pattern, $callback, array $requirements = array())
    {
        self::addRoute('POST', $pattern, $callback, $requirements);
    }
    
    public static function put($pattern, $callback, array $requirements = array())
    {
        self::addRoute('PUT', $pattern, $callback, $requirements);
    }
    
    public static function delete($pattern, $callback, array $requirements = array())
    {
        self::addRoute('DELETE', $pattern, $callback, $requirements);
    }
    
    public static function any($pattern, $callback, array $requirements = array())
    {
        self::addRoute('ANY', $pattern, $callback, $requirements);
    }
    
    public static function before($pattern, $callback, array $requirements = array())
    {
        self::addFilter('before', $pattern, $callback, $requirements);
    }
    
    public static function after($pattern, $callback, array $requirements = array())
    {
        self::addFilter('after', $pattern, $callback, $requirements);
    }
    
    public static function run($method, $uri)
    {
        $before = self::findFilters('before', $uri);
        $route = self::findRoute($method, $uri);
        $after = self::findFilters('after', $uri);
        
        if($route)
        {
            self::$currentRoute = $route;
            
            foreach($before as $filter)
                call_user_func($filter['callback'], $filter['matches']);
            call_user_func($route['callback'], $route['matches']);
            foreach($after as $filter)
                call_user_func($filter['callback'], $filter['matches']);
        }
        else
            Response::send(404);
    }
    
    public static function match($uri)
    {
        if( ! self::$currentRoute)
            throw new RuntimeException('There\'s no current route running.');
        
        return self::$currentRoute['pattern']->match($uri);
    }
    
    public static function clear()
    {
        self::$filtersBefore = array();
        self::$routes = array();
        self::$filtersAfter = array();
    }
    
    private static function findRoute($method, $uri)
    {
        $matches = array();
        foreach(self::$routes as $key=>$route)
            if(($route['method'] === 'ANY' or $method === $route['method']) and $route['pattern']->match($uri, $matches))
                return array_merge($route, array('matches'=>$matches));
        return false;
    }
    
    private static function findFilters($type, $uri)
    {
        if($type == 'before')
            $filters = self::$filtersBefore;
        else if($type == 'after')
            $filters = self::$filtersAfter;
        else
            throw new InvalidArgumentException('Filter type must be "before" or "after".');
        
        $resultFilters = array();
        $matches = array();
        foreach($filters as $key=>$filter)
            if($filter['pattern']->match($uri, $matches))
                $resultFilters[$key] = array_merge($filter, array('matches'=>$matches));
            
        return $resultFilters;
    }
    
    private static function matchesRequirements($matches, $requirements)
    {
        foreach($requirements as $key=>$pattern)
            if( ! empty($matches[$key]) and ! preg_match('@^'.$pattern.'$@', $matches[$key]))
                return false;
        return true;
    }
    
    private static function addRoute($method, $pattern, $callback, array $requirements)
    {
        if( ! is_callable($callback))
            throw new InvalidArgumentException('Invalid $callback argument.');
        
        $route = array(
            'method' => $method,
            'pattern' => new UriPattern($pattern, $requirements),
            'callback' => $callback,
        );
        self::$routes[] = $route;
    }
    
    private static function addFilter($type, $pattern, $callback, array $requirements)
    {
        if( ! is_callable($callback))
            throw new InvalidArgumentException('Invalid $callback argument.');
        
        $filter = array(
            'pattern' => new UriPattern($pattern, $requirements),
            'callback' => $callback,
        );
        if($type == 'before')
            self::$filtersBefore[] = $filter;
        if($type == 'after')
            self::$filtersAfter[] = $filter;
    }
}


class UriPattern
{
    private $pattern = '';
    private $requirements = array();
    
    public function __construct($pattern, array $requirements = array())
    {
        // preventing sloppy uris
        // if(strpos($pattern, '/') !== 0 and strpos($pattern, '~') !== 0)
        //     throw new \InvalidArgumentException('Pattern must start with slash or tilde');
        if(strpos($pattern, '//') !== false)
            throw new \InvalidArgumentException('Pattern must not contain double slashes "//"');
        if(strlen($pattern) > 1 and strrpos($pattern, '/') === strlen($pattern)-1)
            throw new \InvalidArgumentException('Pattern must not end in slash');
        
        $this->pattern = $pattern;
        $this->requirements = $requirements;
    }
    
    /*
        Test the pattern against an uri and optionally saves the
        matches in an referenced array.
        
        The pattern can be a simple parttern:
        
            /users/:id
            /users/:id?     optionals must be at the end
            /admin/*        * also must be at the end (matches anything)
            
        Or a regex (must start with ~):
        
            ~/admin/.+
            ~/(.+\.js)
    */
    public function match($uri, &$matches = array())
    {
        // preventing sloppy uris
        if(strpos($uri, '//') !== false)
            return false;
        if(strlen($uri) > 1 and strrpos($uri, '/') === strlen($uri)-1)
            return false;
        
        $pattern = $this->pattern;
        
        // pattern starting with '~' should be treated as literal regex
        if(strpos($pattern, '~') === 0)
        {
            $pattern = '@^'.substr($pattern, 1).'$@';
            $match = (bool)preg_match($pattern, $uri, $matches);
            array_shift($matches);
            return ($match and $this->matchesRequirements($matches));
        }
        
        $uri = trim($uri, '/');
        $pattern = trim($pattern, '/');
        $uri = empty($uri) ? array() : explode('/', $uri);
        $pattern = empty($pattern) ? array() : explode('/', $pattern);
        
        while($pattern)
        {
            $p = array_shift($pattern);
            $u = array_shift($uri);
            $p = new PatternChunk($p, ! $pattern);
            
            if($p->isNormal() and $p->getString() != $u)
                return false;
            
            if($p->isArg())
            {
                if( ! $p->isOptional() and ! $u)
                    return false;
                $matches[$p->getArgName()] = $u;
            }
            
            if($p->isAny())
            {
                if($uri)
                    array_unshift($pattern, $p->getString());
                else
                    break;
            }
        }
        
        if($uri)
            return false;
        
        return $this->matchesRequirements($matches);
    }
    
    private function matchesRequirements($matches)
    {
        foreach($this->requirements as $key=>$pattern)
            if( ! empty($matches[$key]) and ! preg_match('@^'.$pattern.'$@', $matches[$key]))
                return false;
        return true;
    }
}




class PatternChunk
{
    private $chunk;
    private $isLast;
    
    public function __construct($chunk, $isLast)
    {
        $chunk = trim($chunk);
        if(strpos($chunk, '/') !== false)
            throw new \InvalidArgumentException('A PatternChunk cannot contain any /');
        $this->chunk = $chunk;
        $this->isLast = (bool)$isLast;
    }
    
    public function getString()
    {
        return $this->chunk;
    }
    
    public function isNormal()
    {
        return ! $this->isArg() and ! $this->isAny();
    }
    
    public function isLast()
    {
        return $this->isLast;
    }
    
    public function getArgName()
    {
        $matches = array();
        preg_match('|^:([a-zA-Z_][a-zA-Z0-9_]*)|', $this->chunk, $matches);
        array_shift($matches);
        return array_shift($matches);
    }
    
    public function isArg()
    {
        return (bool)preg_match('|^:[a-zA-Z_][a-zA-Z0-9_]*|', $this->chunk);
    }
    
    public function isAny()
    {
        return $this->isLast() and $this->chunk == '*';
    }
    
    public function isOptional()
    {
        return $this->isLast() and $this->chunk[strlen($this->chunk)-1] === '?';
    }
}