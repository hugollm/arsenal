<?php
namespace Arsenal\Misc;

class ErrorHandler
{
    private $focused = array();
    private $unfocused = array();
    private $keepBuffer = false;
    private $listenShutdown = true;
    
    public function addFocus($path)
    {
        $this->focused[] = $path;
    }
    
    public function removeFocus($path)
    {
        $this->unfocused[] = $path;
    }
    
    public function setKeepBuffer($keepBuffer)
    {
        $this->keepBuffer = $keepBuffer;
    }
    
    public function register($errors = true, $exceptions = true, $shutdown = true)
    {
        if($errors and $exceptions and $shutdown)
            error_reporting(0);
        
        if($errors)
            set_error_handler(array($this, 'handleError'));
        if($exceptions)
            set_exception_handler(array($this, 'handleException'));
        if($shutdown)
            register_shutdown_function(array($this, 'handleShutdown'));
    }
    
    public function handleError($code, $message, $filename, $line)
    {
        $e = new \ErrorException($message, $code, 0, $filename, $line);
        $this->printException($e);
    }
    
    public function handleException($e)
    {
        $this->printException($e);
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();
        $handle = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
        
        if($error and in_array($error['type'], $handle))
        {
            $e = new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
            $this->printException($e, true);
        }
    }
    
    public function printException($e, $fatal = false)
    {
        ob_start();
        self::printCss();
        
        $title = get_class($e);
        $trace = $e->getTrace();
        $isFocused = $this->isFocused($e->getFile());
        $printTrace = true;
        
        if($title == 'ErrorException')
        {
            array_shift($trace);
            $title = $this->getErrorType($e->getCode());
        }
        if($fatal)
        {
            $isFocused = true;
            $printTrace = false;
        }
        
        ?>
            <div class="eh-box">
                <div class="title"><?php echo $title; ?></div>
                <div class="desc"><?php echo $e->getMessage(); ?></div>
                <?php if($printTrace) echo $this->getPrettyTrace($trace); ?>
                <div class="file-line<?php if( ! $isFocused) echo ' dark'; ?>"><?php echo $e->getFile().'('.$e->getLine().')'; ?></div>
            </div>
        <?php
        
        $output = ob_get_contents();
        ob_end_clean();
        
        $this->send($output);
    }
    
    private function getErrorType($code)
    {
        $types = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_ALL => 'E_ALL',
        );
        return isset($types[$code]) ? $types[$code] : 'UNKNOWN ERROR';
    }
    
    private function send($output)
    {
        if( ! $this->keepBuffer)
            $this->clearAllBuffers();
        
        // error output
        header('x', true, 500);
        header('Content-Type: text/html; charset=utf-8');
        echo $output;
        die;
    }
    
    private function getPrettyTrace(array $trace)
    {
        $trace = array_reverse($trace);
        
        $string = '<table class="trace">';
        array_unshift($trace, array('function'=>'{start}'));
        foreach($trace as $i => $step)
        {
            $file = isset($step['file']) ? $step['file'] : null;
            $line = isset($step['line']) ? $step['line'] : null;
            $class = isset($step['class']) ? $step['class'] : null;
            $type = isset($step['type']) ? $step['type'] : null;
            $function = isset($step['function']) ? $step['function'] : null;
            
            $cssClass = '';
            if( ! $this->isFocused($file))
                $cssClass = 'dark';
            
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
    
    private function isFocused($file)
    {
        if( ! $file)
            return false;
        
        $file = realpath($file);
        $isFocused = false;
        
        foreach($this->focused as $path)
            if(strpos($file, realpath($path)) === 0)
            {
                $isFocused = true;
                break;
            }
            
        foreach($this->unfocused as $path)
            if(strpos($file, realpath($path)) === 0)
            {
                $isFocused = false;
                break;
            }
            
        return $isFocused;
    }
    
    private function clearAllBuffers()
    {
        while(ob_get_level() > 1)
            ob_end_clean();
        ob_clean();
    }
    
    private function printCss()
    {
        ?>
            <style type="text/css">
                
                html body div.eh-box {
                    margin: 0 !important;
                    padding: 15px !important;
                    border: 0 !important;
                    font-size: 100% !important;
                    vertical-align: baseline !important;
                    background-color: #f5f5f5 !important;
                    font: 13px/13px 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
                    color: #333 !important;
                }

                html body div.eh-box * {
                    margin: 0 !important;
                    padding: 0 !important;
                    border: 0 !important;
                    font-size: 100% !important;
                    font: inherit !important;
                    vertical-align: baseline !important;
                    color: #333 !important;
                }
                
                html body div.eh-box .dark, html body div.eh-box .dark * {
                    color: #aaa !important;
                }
                
                html body div.eh-box div.title {
                    font-size: 30px !important;
                    line-height: 30px !important;
                    margin-left: -2px !important;
                }
                
                html body div.eh-box div.desc {
                    font-size: 20px !important;
                    line-height: 20px !important;
                    color: #444 !important;
                    margin-top: 15px !important;
                }
                
                html body div.eh-box table.trace {
                    margin-top: 15px !important;
                    font-family: consolas, monospace !important;
                    font-size: 12px !important;
                    line-height: 18px !important;
                    margin-bottom: 15px !important;
                }
                
                html body div.eh-box table.trace td {
                    padding-right: 30px !important;
                }
                
                html body div.eh-box div.file-line {
                    font-family: monospace !important;
                    font-size: 13px !important;
                    line-height: 18px !important;
                    margin-top: 15px !important;
                }
                
            </style>
        <?php
    }
}