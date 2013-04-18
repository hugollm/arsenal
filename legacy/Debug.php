<?php

class Debug
{
    static function printContents()
    {
        self::printCss();
        
        foreach(func_get_args() as $val)
        {
            echo '<div class="ndump">';
            echo self::getPrettyVarRecursive($val, 10);
            echo '</div>';
        }
    }
    
    static function printMethods($obj, $tryExec = false)
    {
        self::printCss();
        
        $publics = array();
        $protecteds = array();
        $privates = array();
        
        $rClass = new \ReflectionClass($obj);
        foreach($rClass->getMethods() as $rMethod)
        {
            if($rMethod->isPublic())
                $publics[] = $rMethod;
            if($rMethod->isProtected())
                $protecteds[] = $rMethod;
            if($rMethod->isPrivate())
                $privates[] = $rMethod;
        }
        
        echo '<div class="ndump">';
        echo self::getPrettyVar($obj);
        echo '<br>';
        
        if($publics)
            echo self::getSpan('public', 'keyword').'<br>';
        foreach($publics as $rMethod)
        {
            echo self::getIndent(1);
            echo self::getPrettyMethod($rMethod, $obj, $tryExec);
            echo '<br>';
        }
        
        if($protecteds)
            echo self::getSpan('protected', 'keyword').'<br>';
        foreach($protecteds as $rMethod)
        {
            echo self::getIndent(1);
            echo self::getPrettyMethod($rMethod, $obj, $tryExec);
            echo '<br>';
        }
        
        if($privates)
            echo self::getSpan('private', 'keyword').'<br>';
        foreach($privates as $rMethod)
        {
            echo self::getIndent(1);
            echo self::getPrettyMethod($rMethod, $obj, $tryExec);
            echo '<br>';
        }
        
        echo '</div>';
    }
    
    protected static function getPrettyVar($var)
    {
        if($var === null)
            return self::getSpan('null', 'null');;
        if(is_bool($var) and $var === true)
            return self::getSpan('true', 'bool');
        if(is_bool($var) and $var === false)
            return self::getSpan('false', 'bool');
        if(is_int($var) or is_float($var))
            return self::getSpan($var, 'number');
        if(is_array($var))
            return self::getSpan('array('.count($var).')', 'array');
        if(is_object($var))
        {
            $class = get_class($var);
            $count = ($var instanceof \IteratorAggregate) ? count($var) : count(get_object_vars($var));
            $id = self::getObjectId($var);
            return self::getSpan($class, 'object').self::getSpan("($count)#$id", 'dark');
        }
        if(is_string($var))
        {
            $var = htmlspecialchars($var);
            $var = str_replace(' ', '&nbsp;', $var);
            $var = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $var);
            $var = nl2br($var);
            return self::getSpan("'".$var."'", 'string');
        }
    }
    
    protected static function getPrettyVarRecursive($var, $maxDepth = null)
    {
        static $depth = 0;
        
        $str = self::getPrettyVar($var);
        if(is_array($var) or is_object($var))
        {
            if($maxDepth and $depth == $maxDepth)
            {
                $str .= ' [...]';
                return $str;
            }
            
            $depth++;
            foreach($var as $k=>$v)
            {
                $str .= '<br>';
                $str .= self::getIndent($depth);
                $str .= self::getPrettyVar($k);
                $str .= '&nbsp;=>&nbsp;';
                $str .= self::getPrettyVarRecursive($v, $maxDepth);
            }
            $depth--;
        }
        return $str;
    }
    
    protected static function getPrettyMethod(\ReflectionMethod $rMethod, $obj = null, $tryExec = false)
    {
        if($rMethod->isPublic())
            $visibility = 'public';
        if($rMethod->isProtected())
            $visibility = 'protected';
        if($rMethod->isPrivate())
            $visibility = 'private';
        
        $modifiers = '';
        if($rMethod->isFinal())
            $modifiers .= ' final';
        if($rMethod->isStatic())
            $modifiers .= ' static';
        
        $args = '';
        foreach($rMethod->getParameters() as $rParam)
        {
            $hint = self::getparameterHint($rParam);
            if($hint)
                $args .= self::getSpan($hint, 'dark').'&nbsp;';
            if($rParam->isPassedByReference())
                $args .= self::getSpan('&', 'keyword');
            $args .= self::getSpan('$'.$rParam->getName(), 'var');
            if($rParam->isDefaultValueAvailable())
                $args .= ' = '.self::getPrettyVar($rParam->getDefaultValue());
            $args .= ', ';
        }
        $args = substr($args, 0, -2);
        
        // try executing methods if requested
        if($tryExec and $rMethod->getNumberOfRequiredParameters() === 0 and $rMethod->isPublic() and preg_match('/^(get|is|has)/', $rMethod->getName()))
            $result = self::getSpan('&nbsp;=>&nbsp;', 'dark').self::getPrettyVar($rMethod->invoke($obj));
        else
            $result = '';
        
        return self::getSpan($modifiers, 'dark').'&nbsp;'.self::getSpan($rMethod->getName(), 'method').'&nbsp;('.$args.')'.$result;
    }
    
    protected static function getIndent($depth)
    {
        $str = '';
        for($i=0; $i<$depth; $i++)
            $str .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        return $str;
    }
    
    protected static function getObjectId($obj)
    {
        $id = spl_object_hash($obj);
        $id = sha1($id);
        $id = substr($id, 0, 5);
        $id = base_convert($id, 16, 36);
        return $id;
    }
    
    protected static function getParameterHint(\ReflectionParameter $rParam)
    {
        // dump((string)$rParam);
        $matches = array();
        preg_match('@>\s+([^$&].+?)\s+&?\$[a-zA-Z]@', (string)$rParam, $matches);
        // dump($matches);
        array_shift($matches);
        $hint = array_shift($matches);
        return trim($hint);
    }
    
    protected static function getSpan($string, $class)
    {
        return '<span class="ndump-'.$class.'">'.$string.'</span>';
    }
    
    protected static function printCss()
    {
        static $printed = false;
        if( ! $printed)
            echo '
                <style type="text/css">
                    
                    .ndump {
                        background-color: #000;
                        margin: 1px;
                        padding: 10px;
                        font:12px/18px consolas, "courier new", monospace;
                        color: #ccc;
                        outline: 1px solid #666;
                        overflow: auto;
                    }
                    
                    .ndump .ndump-light {color: #fff;}
                    .ndump .ndump-dark {color: #666;}
                    
                    .ndump .ndump-null {color: cyan;}
                    .ndump .ndump-bool {color: cyan;}
                    .ndump .ndump-number {color: yellow;}
                    .ndump .ndump-string {color: #f70;}
                    .ndump .ndump-array {color: #6d6;}
                    .ndump .ndump-object {color: violet;}
                    
                    .ndump .ndump-var {color: teal;}
                    .ndump .ndump-method {color: white;}                  
                    .ndump .ndump-keyword {color: cyan;}
                </style>
            ';
        $printed = true;
    }
}