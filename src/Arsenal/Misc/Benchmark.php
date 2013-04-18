<?php
namespace Arsenal\Misc;

class Benchmark
{
    private $points = array();
    
    public function __construct($startTime = null, $startMemory = null)
    {
        if( ($startTime and ! $startMemory) or ( ! $startTime and $startMemory) )
            throw new \InvalidArgumentException('If you are providing a starting point, you should provide both time and memory');
        
        if($startTime and $startMemory)
            $this->savePoint('start', $startTime, $startMemory, $startMemory);
        else
            $this->point('start');
    }
    
    public function point($name = null)
    {
        if( ! $name)
            $name = 'point#'.(count($this->points)+1);
        
        if(isset($this->points[$name]))
            throw new \InvalidArgumentException('Point name "'.$name.'" is already in use');
        
        $this->savePoint($name, microtime(true), memory_get_usage(true), memory_get_peak_usage(true));
    }
    
    public function dump()
    {
        dump($this->points);
    }
    
    public function dumpSummary()
    {
        // $this->point('end');
        
        $points = $this->points;
        $first = current($points);
        $last = end($points);
        
        $time = $last['time'] - $first['time'];
        $time *= 1000;
        $time = number_format($time, 2, '.', ',');
        
        $memory = $last['memory'];
        $memory = $memory / 1024 / 1024;
        $memory = number_format($memory, 2, '.', ',');
        
        $peak = $last['peak'];
        $peak = $peak / 1024 / 1024;
        $peak = number_format($peak, 2, '.', ',');
        
        echo '<pre><code>';
        echo '  total-time: '.$time.' ms'."\n";
        echo '      memory: '.$memory.' MiB'."\n";
        echo ' memory-peak: '.$peak.' MiB'."\n";
        echo '</code></pre>';
    }
    
    private function savePoint($name, $time, $memory, $peak)
    {
        $this->points[$name] = array(
            'time' => $time,
            'memory' => $memory,
            'peak' => $peak,
        );
    }
}