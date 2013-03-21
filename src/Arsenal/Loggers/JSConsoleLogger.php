<?php
namespace Arsenal\Loggers;

class JsConsoleLogger extends Logger
{
    protected function commit($level, $message)
    {
        $paddedLevel = str_pad($level, 8, ' ', STR_PAD_LEFT);
        $message = json_encode('%c'.strtoupper($paddedLevel).': %c'.$message);
        
        if($level == 'debug')
            $color = '#999';
        if($level == 'info')
            $color = '#66f';
        if($level == 'notice')
            $color = '#090';
        if($level == 'warning')
            $color = '#E07A03';
        if($level == 'error')
            $color = '#D41B03';
        if($level == 'critical')
            $color = '#f00';
        
        $css = json_encode('font-weight:bold;color:'.$color.';');
        $css2 = json_encode('color:#444;');
        echo '<script type="text/javascript">console.log('.$message.', '.$css.', '.$css2.');</script>'."\n";
    }
}