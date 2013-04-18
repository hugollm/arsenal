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
    
    public function listenErrors()
    {
        set_error_handler(array($this, 'handleError'));
    }
    
    public function listenExceptions()
    {
        set_exception_handler(array($this, 'handleException'));
    }
    
    public function listenShutdown()
    {
        register_shutdown_function(array($this, 'handleShutdown'));
    }
    
    public function listen($errors = true, $exceptions = true, $shutdown = true)
    {
        if($errors)
            $this->listenErrors();
        if($exceptions)
            $this->listenExceptions();
        if($shutdown)
            $this->listenShutdown();
    }
    
    public function handleError($code, $message, $filename, $line)
    {
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }
    
    public function handleException($e)
    {
        ob_start();
        self::printCss();
        
        // some trace treatments
        $trace = $e->getTrace();
        if(get_class($e) == 'ErrorException')
            array_shift($trace);
        $trace = array_reverse($trace);
        
        ?>
            <div class="eh-box">
                <div class="title"><?php echo get_class($e); ?></div>
                <div class="desc"><?php echo $e->getMessage(); ?></div>
                <?php echo $this->getPrettyTrace($trace); ?>
                <div class="file-line<?php if( ! $this->isFocused($e->getFile())) echo ' dark'; ?>"><?php echo $e->getFile().'('.$e->getLine().')'; ?></div>
            </div>
        <?php
        
        $output = ob_get_contents();
        ob_end_clean();
        
        $this->send($output);
    }
    
    public function handleShutdown()
    {
        $error = error_get_last();
        if($error and $error['type'] == E_ERROR)
        {
            ob_start();
            $this->printCss();
            ?>
                <div class="eh-box">
                    <div class="title">FatalError</div>
                    <div class="desc"><?php echo $error['message']; ?></div>
                    <div class="file-line"><?php echo $error['file'].'('.$error['line'].')'; ?></div>
                </div>
            <?php
            $output = ob_get_contents();
            ob_end_clean();
            $this->send($output);
        }
    }
    
    private function send($output)
    {
        if( ! $this->keepBuffer)
            ob_clean();
        
        // error output
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header("$protocol 500 Internal Server Error", true);
        header("Status: 500 Internal Server Error", true); // for fast_cgi
        header('Content-Type: text/html; charset=utf-8');
        echo $output;
        die;
    }
    
    private function getPrettyTrace(array $trace)
    {
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
    
    private function printCss()
    {
        static $printed = false;
        if( ! $printed)
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
        $printed = true;
    }
}