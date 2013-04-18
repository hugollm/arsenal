<?php

class Handler
{
    
    public static function error($code, $message, $filename, $line)
    {
        throw new ErrorException($message, $code, 0, $filename, $line);
    }
    
    public static function exception($e)
    {
        // $contents = ob_get_contents();
        // ob_clean();
        header('Content-Type: text/html; charset=utf-8');
        self::printCss();
        
        $trace = $e->getTrace();
        if(get_class($e) == 'ErrorException')
            array_shift($trace);
        $trace = array_reverse($trace);
        
        ?>
            <div class="nexception">
                <h1><?php echo get_class($e); ?></h1>
                <h2><?php echo $e->getMessage(); ?></h2>
                <code><?php echo self::getPrettyTrace($trace); ?></code>
                <br>
                <code class="nexception-dark"><?php echo $e->getFile().'('.$e->getLine().')'; ?></code>
            </div>
        <?php
        
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header("$protocol 500 Internal Server Error", true);
        header("Status: 500 Internal Server Error", true); // for fast_cgi
        // echo $contents;
        die;
    }
    
    public static function shutdown()
    {
        $error = error_get_last();
        if($error and $error['type'] == E_ERROR)
        {
            ob_end_clean();
            self::printCss();
            ?>
                <div class="nexception">
                    <h1>FatalError</h1>
                    <h2><?php echo $error['message']; ?></h2>
                    <code><?php echo $error['file'].'('.$error['line'].')'; ?></code>
                </div>
            <?php
        }
    }
    
    public static function getPrettyTrace(array $trace)
    {
        $string = '<table>';
        array_unshift($trace, array('function'=>'{start}'));
        foreach($trace as $i => $step)
        {
            $file = isset($step['file']) ? $step['file'] : null;
            $line = isset($step['line']) ? $step['line'] : null;
            $class = isset($step['class']) ? $step['class'] : null;
            $type = isset($step['type']) ? $step['type'] : null;
            $function = isset($step['function']) ? $step['function'] : null;
            
            $cssClass = '';
            if( ! $file or strpos(dirname($file), __DIR__) === 0 or basename($file) == 'index.php')
                $cssClass = 'nexception-dark';
            
            $string .= '<tr class="'.$cssClass.'">';
                $string .= "<td>#$i</td>";
                $string .= '<td>'.$class.$type.$function.'</td>';
                if($file and $line)
                    $string .= '<td>'.$file."($line)".'</td>';
            $string .= '</tr>';
            
        }
        $string .= '</table>';
        
        return $string;
    }
    
    protected static function printCss()
    {
        static $printed = false;
        if( ! $printed)
            echo '
                <style type="text/css">
                    
                    .nexception {
                        font-family: arial, sans-serif;
                        margin-bottom: 20px;
                        
                        background-color: #fff;
                        margin: 1px;
                        padding: 10px;
                        font:12px/18px arial, sans-serif;
                        color: #000;
                    }
                    
                    .nexception td {
                        padding-right: 20px;
                    }
                    
                    .nexception .nexception-light {color: #000;}
                    .nexception .nexception-dark {color: #aaa;}
                    
                </style>
            ';
        $printed = true;
    }
}