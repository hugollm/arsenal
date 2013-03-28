<?php
namespace Arsenal\Database;

class EntityTest extends DatabaseTest
{
    public function __destruct()
    {
        self::$db->sql('DELETE FROM :_users')->exec();
    }
    
    public function lifeTime()
    {
        $user = new Entity(self::$db, '_users');
        
        $this->assertFalse($user->isSaved());
        $this->assertTrue($user->isTainted());
        
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->save();
        
        $this->assertTrue($user->isSaved());
        $this->assertFalse($user->isTainted());
        
        $user->username = 'jonasdoe';
        
        $this->assertTrue($user->isSaved());
        $this->assertTrue($user->isTainted());
        
        $user->save();
        
        $this->assertTrue($user->isSaved());
        $this->assertFalse($user->isTainted());
        
        $user->drop();
        
        $this->assertFalse($user->isSaved());
        $this->assertTrue($user->isTainted());
        $this->assertNull($user->id);
    }
    
    public function fromDB()
    {
        $user = new Entity(self::$db, '_users');
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->save();
        unset($user);
        
        $query = self::$db->sql('SELECT * FROM :_users LIMIT 1')->query();
        $user = current($query);
        $user = new Entity(self::$db, '_users', (array)$user);
        
        $this->assertTrue($user->isSaved());
        $this->assertFalse($user->isTainted());
        $this->assert($user->username)->is('johndoe');
    }
    
    public function fill()
    {
        $user = new Entity(self::$db, '_users');
        $post = array('name' => 'johndoe', 'age' => 22, 'is_admin' => true);
        $user->fill($post, 'name, age');
        
        $this->assertTrue(isset($user->name));
        $this->assertTrue(isset($user->age));
        $this->assertFalse(isset($user->is_admin));
    }
    
    public function idSetManually()
    {
        $user = new Entity(self::$db, '_users');
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->id = 243;
        
        try
        {
            $user->save();
            $this->fail('Should have thrown an exception');
        }
        catch(\RuntimeException $e)
        {
            $this->assertObject($e)->isClass('RuntimeException');
        }
    }
    
    public function idChanged()
    {
        $user = new Entity(self::$db, '_users');
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->save();
        
        $user->id = 341;
        
        try
        {
            $user->save();
            $this->fail('Should have thrown an exception');
        }
        catch(\RuntimeException $e)
        {
            $this->assertObject($e)->isClass('RuntimeException');
        }
    }
    
    public function unknownProperty()
    {
        $user = new Entity(self::$db, '_users');
        $this->assertNull($user->some_property);
    }
    
    public function fillFilterWorks()
    {
        $user = new Entity(self::$db, '_users');
        $post = array(
            'username' => 'johndoe',
            'age' => 22,
            'foo' => 'bar',
        );
        $user->fill($post, 'username, age');
        
        $this->assertObject($user)->not()->hasProperty('foo');
    }
    
    public function fillPrivateCollision()
    {
        $user = new Entity(self::$db, '_users');
        $post = array(
            'db' => 'foo',
            'table' => 'bar',
        );
        $user->fill($post);
        
        $this->assertObject($user)->hasProperty('db')->hasProperty('table');
    }
}