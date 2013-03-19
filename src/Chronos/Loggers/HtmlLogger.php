<?php
namespace Chronos\Loggers;

class HtmlLogger extends Logger
{
    protected function commit($level, $message)
    {
        $paddedLevel = str_pad($level, 8, ' ', STR_PAD_LEFT);
        $message = strtoupper($paddedLevel).': '.$message;
        
        if($level == 'debug')
            $color = '#ddf';
        if($level == 'info')
            $color = '#88f';
        if($level == 'notice')
            $color = '#7d7';
        if($level == 'warning')
            $color = '#fc6';
        if($level == 'error')
            $color = '#f99';
        if($level == 'critical')
            $color = '#f66';
        
        echo '<pre style="font-family:monospace;background-color:'.$color.';margin:0;margin-bottom:1px;padding:2px;padding-left:4px;">'.$message.'</pre>';
    }
}