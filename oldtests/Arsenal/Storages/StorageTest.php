<?php
namespace Arsenal\Storages;

use Arsenal\TestFramework\TestFixture;

abstract class StorageTest extends TestFixture
{
    private $storage = null;
    
    abstract protected function getStorage();
    
    public function setUp()
    {
        $this->storage = $this->getStorage();
        $this->storage->set('a', '1');
        $this->storage->set('b', '2');
        $this->storage->set('c', '3');
    }
    
    public function tearDown()
    {
        $this->storage->clear();
    }
    
    public function get()
    {
        $this->assert($this->storage->get('a'))->is('1');
        $this->assert($this->storage->get('b'))->is('2');
        $this->assert($this->storage->get('c'))->is('3');
        $this->assert($this->storage->get('d'))->is(null);
    }
    
    public function set()
    {
        $this->storage->set('z', '9');
        $this->assertTrue($this->storage->hasKey('z'));
        $this->assert($this->storage->get('z'))->is('9');
    }
    
    public function getAll()
    {
        $this->assertArray($this->storage->getAll())->isEqual(array('a'=>'1', 'b'=>'2', 'c'=>'3'));
    }
    
    public function setAll()
    {
        $this->storage->setAll(array('r'=>'5', 's'=>'6'));
        $this->assert($this->storage->get('r'))->is('5');
        $this->assert($this->storage->get('s'))->is('6');
        $this->assertFalse($this->storage->hasKey('a'));
        $this->assertFalse($this->storage->hasKey('b'));
        $this->assertFalse($this->storage->hasKey('c'));
    }
    
    public function getAllKeys()
    {
        $this->assertArray($this->storage->getAllKeys())->isEqual(array('a','b','c'));
    }
    
    public function hasKey()
    {
        $this->assertTrue($this->storage->hasKey('a'));
        $this->assertFalse($this->storage->hasKey('1'));
        $this->assertFalse($this->storage->hasKey('d'));
    }
    
    public function drop()
    {
        $this->storage->drop('c');
        $this->assertFalse($this->storage->hasKey('c'));
        $this->assertNull($this->storage->get('c'));
    }
    
    public function clear()
    {
        $this->storage->clear();
        $this->assertArray($this->storage->getAll())->isEqual(array());
    }
}