<?php
namespace Arsenal\Storages;

use Arsenal\Database\Database;

class DatabaseStorage implements Storage
{
    private $db = null;
    private $table = null;
    
    public function __construct(Database $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }
    
    public function get($key, $default = null)
    {
        $r = $this->db->sql('SELECT :? FROM :? WHERE :? = ? LIMIT 1', 'val', $this->table, 'key', $key)->queryOne();
        if( ! $r)
            return null;
        $val = $this->decryptVal($r->val);
        return $val;
    }
    
    public function set($key, $val)
    {
        $val = $this->encryptVal($val);
        
        if($this->hasKey($key))
            $this->db->sql('UPDATE :? SET :? = ? WHERE :? = ? LIMIT 1', $this->table, 'val', $val, 'key', $key)->exec();
        else
            $this->db->sql('INSERT INTO :? (:?+) VALUES (?+)', $this->table, array('key', 'val'), array($key, $val))->exec();
    }
    
    public function getAll()
    {
        $results = $this->db->sql('SELECT :?+ FROM :?', array('key', 'val'), $this->table)->query();
        $items = array();
        foreach($results as $r)
            $items[$r->key] = $this->decryptVal($r->val);
        return $items;
    }
    
    public function setAll(array $items)
    {
        $this->clear();
        $sql = $this->db->sql('INSERT INTO :? (:?+) VALUES', $this->table, array('key', 'val'));
        foreach($items as $key=>$val)
            $sql->add('(?+)', array($key, $this->encryptVal($val)))->add(',');
        $sql->back()->exec();
    }
    
    public function getAllKeys()
    {
        $results = $this->db->sql('SELECT :? FROM :?', 'key', $this->table)->query();
        $keys = array();
        foreach($results as $r)
            $keys[] = $r->key;
        return $keys;
    }
    
    public function hasKey($key)
    {
        $r = $this->db->sql('SELECT :? FROM :? WHERE :? = ? LIMIT 1', 'key', $this->table, 'key', $key)->queryOne();
        return (bool)$r;
    }
    
    public function drop($key)
    {
        $this->db->sql('DELETE FROM :? WHERE :? = ?', $this->table, 'key', $key)->exec();
    }
    
    public function clear()
    {
        $this->db->sql('DELETE FROM :?', $this->table)->exec();
    }
    
    private function encryptVal($val)
    {
        return base64_encode(serialize($val));
    }
    
    private function decryptVal($val)
    {
        return unserialize(base64_decode($val));
    }
}