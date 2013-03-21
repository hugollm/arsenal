<?php
namespace Arsenal;

class Benchmark
{
    private $time;
    private $memory;
    private $memoryPeak;
    
    public function __construct()
    {
        $this->time = microtime(true);
        $this->memory = memory_get_usage(true);
        $this->memoryPeak = memory_get_peak_usage(true);
    }
    
    public function getTimeDiffInSeconds()
    {
        $now = microtime(true);
        $diff = $now - $this->time;
        return $diff;
    }
    
    public function getTimeDiffInMicroseconds()
    {
        $now = microtime(true);
        $diff = $now - $this->time;
        $diff = $diff * 1000;
        $diff = round($diff, 2);
        return $diff;
    }
    
    public function getMemoryDiffInBytes()
    {
        $memory = memory_get_usage(true);
        return $memory - $this->memory;
    }
    
    public function getMemoryDiffInMegaBytes()
    {
        $memory = memory_get_usage(true);
        return ($memory - $this->memory) / 1024 / 1024;
    }
    
    public function getMemoryPeakDiffInBytes()
    {
        $memoryPeak = memory_get_peak_usage(true);
        return $memoryPeak - $this->memoryPeak;
    }
    
    public function getMemoryPeakDiffInMegaBytes()
    {
        $memoryPeak = memory_get_peak_usage(true);
        return ($memoryPeak - $this->memoryPeak) / 1024 / 1024;
    }
    
    public function dump()
    {
        echo '<pre><code>';
        echo '   time-diff: '.$this->getTimeDiffInMicroseconds().' ms'."\n";
        echo 'total-memory: '.(memory_get_peak_usage(true)/1024/1024).' MiB'."\n";
        echo ' memory-diff: '.$this->getMemoryPeakDiffInMegaBytes().' MiB'."\n";
        echo '</code></pre>';
    }
}