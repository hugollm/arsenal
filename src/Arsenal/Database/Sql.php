<?php
namespace Arsenal\Database;
use Arsenal\Misc\Debugger;

class Sql
{
    private $db;
    private $sql = array();
    private $params = array();
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    public function add($sql)
    {
        $this->sql[] = $sql;
        return $this;
    }
    
    public function back($n = 1)
    {
        for($i=0; $i<$n; $i++)
            array_pop($this->sql);
        return $this;
    }
    
    public function vbind($needle, $param)
    {
        $this->bindOperation($needle, '?', array($param));
        return $this;
    }
    
    public function vbinds($needle, array $params)
    {
        $rep = implode(', ', array_fill(0, count($params), '?'));
        $this->bindOperation($needle, $rep, $params);
        return $this;
    }
    
    public function ibind($needle, $param)
    {
        if( ! $this->isValidIdentifier($param))
            throw new \InvalidArgumentException('Trying to bind invalid identifier "'.$param.'"');
        
        $this->bindOperation($needle, '`'.$param.'`');
        return $this;
    }
    
    public function ibinds($needle, array $params)
    {
        $rep = '';
        foreach($param as $param)
        {
            if( ! $this->isValidIdentifier($param))
                throw new \InvalidArgumentException('Trying to bind invalid identifier "'.$param.'"');
            $rep .= "`$param`, ";
        }
        $rep = substr($rep, 0, -2);
        
        $this->bindOperation($needle, $rep);
        return $this;
    }
    
    public function nbind($needle, $param)
    {
        if( ! is_numeric($param))
            throw new \InvalidArgumentException('Trying to bind invalid number "'.$param.'"');
        
        $this->bindOperation($needle, $param);
        return $this;
    }
    
    public function nbinds($needle, array $params)
    {
        $rep = '';
        foreach($param as $param)
        {
            if( ! is_numeric($param))
                throw new \InvalidArgumentException('Trying to bind invalid number "'.$param.'"');
            $rep .= "$param, ";
        }
        $rep = substr($rep, 0, -2);
        
        $this->bindOperation($needle, $rep);
        return $this;
    }
    
    public function query()
    {
        return $this->db->query(implode(' ', $this->sql), $this->params);
    }
    
    public function queryOne()
    {
        return $this->db->queryOne(implode(' ', $this->sql), $this->params);
    }
    
    public function exec()
    {
        return $this->db->exec(implode(' ', $this->sql), $this->params);
    }
    
    public function dump()
    {
        Debugger::printContents(implode(' ', $this->sql));
        Debugger::printContents($this->params);
        return $this;
    }
    
    private function bindOperation($needle, $replacement, array $params = array())
    {
        if(strpos($needle, ':') !== 0)
            $needle = ':'.$needle;
        
        foreach($this->sql as $k=>$sql)
        {
            $pos = strpos($sql, $needle);
            if($pos !== false)
            {
                $this->sql[$k] = substr_replace($sql, $replacement, $pos, strlen($needle));
                $this->params = array_merge($this->params, $params);
                return;
            }
        }
        throw new \RuntimeException('Bind needle "'.$needle.'" not found');
    }
    
    private function isValidIdentifier($i)
    {
        return (bool)preg_match('|^[a-z-A-Z_][a-zA-Z0-9_]*(\.[a-z-A-Z_][a-zA-Z0-9_]*)?$|', $i);
    }
}