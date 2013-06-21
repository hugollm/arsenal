<?php
namespace Arsenal\Storages;

use Arsenal\Database\Database;

class DumbDatabaseStorage extends MappedArrayStorage
{
    private $db = null;
    private $table = null;
    private $domain = null;
    
    public function __construct(Database $db, $table, $domain)
    {
        $this->db = $db;
        $this->table = $table;
        $this->domain = $domain;
    }
    
    protected function load()
    {
        $ent = $this->db->entityQuery($this->table)->where('key', '=', $this->domain)->findOne();
        if($ent)
            return unserialize($ent->val);
        else
            return array();
    }
    
    protected function update(array $items)
    {
        $ent = $this->db->entityQuery($this->table)->where('key', '=', $this->domain)->findOne();
        if( ! $ent)
            $ent = $this->db->createEntity($this->table);
        
        $ent->key = $this->domain;
        $ent->val = serialize($items);
        $ent->save();
    }
}