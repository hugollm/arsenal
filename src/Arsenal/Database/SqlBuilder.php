<?php
namespace Arsenal\Database;

class SqlBuilder
{
    private $db;
    private $steps = array();
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    public function add($sql = null)
    {
        $params = array_slice(func_get_args(), 1);
        $sql = $this->interpolateParams($sql, $params);
        $this->steps[] = array(
            'sql' => $sql,
            'params' => $params,
        );
        return $this;
    }
    
    public function back($n = 1)
    {
        for($i=0; $i<$n; $i++)
            array_pop($this->steps);
        return $this;
    }
    
    public function clear()
    {
        $this->steps = array();
        return $this;
    }
    
    public function getString()
    {
        $sql = '';
        foreach($this->steps as $step)
            $sql .= $step['sql'].' ';
        $sql = $this->parseIdentifiers($sql);
        return trim($sql);
    }
    
    public function getParams()
    {
        $params = array();
        foreach($this->steps as $step)
            $params = array_merge($params, $step['params']);
        return $params;
    }
    
    public function query()
    {
        return $this->db->query($this->getString(), $this->getParams());
    }
    
    public function exec()
    {
        return $this->db->exec($this->getString(), $this->getParams());
    }
    
    public function validateIdentifier($i)
    {
        return (bool)preg_match('|^[a-z-A-Z_][a-zA-Z0-9_]*(\.[a-z-A-Z_][a-zA-Z0-9_]*)?$|', $i);
    }
    
    /*
        Replaces the placeholders (:?, :?+, ?, ?+) with the correct symbols
        and rebuilds $params array acordingly.
    */
    private function interpolateParams($sql, array &$params)
    {
        $pms = $params;
        $params = array();
        
        $sql = preg_replace_callback('@\:\?\+|\:\?|\?\+|\?@', function($matches) use($sql, &$pms, &$params)
        {
            $match = array_shift($matches);
            $identifierPattern = '|^[a-z-A-Z_][a-zA-Z0-9_]*(\.[a-z-A-Z_][a-zA-Z0-9_]*)?$|';
            $new = '';
            if($match == ':?+')
            {
                $array = array_shift($pms);
                if(empty($array) or ! is_array($array))
                    throw new \InvalidArgumentException(':?+ placeholder expects a non-empty array as parameter.');
                foreach($array as $i)
                {
                    if( ! preg_match($identifierPattern, $i))
                        throw new \InvalidArgumentException(':?+ placeholder expects a non-empty array of identifiers as parameter. Invalid identifier found.');
                    $new .= ':'.$i.', ';
                }
                $new = substr($new, 0, -2);
            }
            if($match == ':?')
            {
                $p = array_shift($pms);
                if(is_array($p) or is_object($p) or ! preg_match($identifierPattern, $p))
                    throw new \InvalidArgumentException(':? placeholder expects a valid identifier as parameter.');
                $new = ':'.$p;
            }
            if($match == '?+')
            {
                $array = array_shift($pms);
                if( ! is_array($array))
                    throw new \InvalidArgumentException('?+ placeholder expects an array as parameter.');
                foreach($array as $i)
                    $new .= '?, ';
                $new = substr($new, 0, -2);
                $params = array_merge($params, $array);
            }
            if($match == '?')
            {
                $p = array_shift($pms);
                if(is_array($p) or is_object($p))
                    throw new \InvalidArgumentException('? placeholder expects a number or string as parameter.');
                $new = '?';
                $params[] = $p;
            }
            return $new;
        },
        $sql);
        return $sql;
    }
    
    private function parseIdentifiers($sql)
    {
        $q = $this->db->getQuoteIdentifierChar();
        $sql = preg_replace_callback('|:([a-z-A-Z_][a-zA-Z0-9_]*)(\.([a-z-A-Z_][a-zA-Z0-9_]*))?|', function($matches) use($sql, $q)
        {
            $identifier = '';
            foreach($matches as $i=>$match)
                if($i % 2 == 1) // odd
                    $identifier .= $q.$match.$q.'.';
            return substr($identifier, 0, -1);
        },
        $sql);
        return $sql;
    }
}