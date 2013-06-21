<?php
namespace Arsenal\Sessions;
use Arsenal\Http\CookieJar;
use Arsenal\Database\Database;

class DatabaseSession extends Session
{
    private $db;
    private $table;
    
    public function __construct(CookieJar $cookies, Database $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
        parent::__construct($cookies);
    }
    
    protected function read($id)
    {
        $session = $this->db->selectOne($this->table, array('ssid' => $id), array('payload'));
        if( ! $session)
            return array();
        return unserialize(base64_decode($session->payload));
    }
    
    protected function write($id, array $payload, \DateTime $dt)
    {
        $this->db->upsert($this->table, array(
            'ssid' => $id,
            'payload' => base64_encode(serialize($payload)),
            'expiration' => $dt->getTimestamp(),
        ), array('ssid' => $id));
    }
    
    protected function delete($id)
    {
        $this->db->delete($this->table, array('ssid' => $id));
    }
    
    protected function revalidate($id, \DateTime $dt)
    {
        $this->db->update($this->table, array('expiration' => $dt->getTimestamp()), array('ssid' => $id));
    }
    
    protected function cleanup()
    {
        $sql = $this->db->sql('DELETE FROM :table WHERE :field < :val');
        $sql->ibind('table', $this->table);
        $sql->ibind('field', 'expiration');
        $sql->vbind('val', time());
        $sql->exec();
    }
}