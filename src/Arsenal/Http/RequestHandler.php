<?php
namespace Arsenal\Http;

class RequestHandler
{
    private $filters = array();
    private $routes = array();
    
    public function addFilter(RequestCallback $filter)
    {
        $this->filters[] = $filter;
    }
    
    public function addRoute(RequestCallback $route)
    {
        $this->routes[] = $route;
    }
    
    public function handle(Request $request)
    {
        foreach($this->filters as $filter)
            $filter->tryAgainst($request);
        foreach($this->routes as $route)
            if($route->tryAgainst($request))
                return;
    }
}