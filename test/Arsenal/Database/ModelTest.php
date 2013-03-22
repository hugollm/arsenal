<?php
namespace Arsenal\Database;

use Arsenal\TestFramework\Assert;

class ModelTest extends DatabaseTest
{
    public function __destruct()
    {
        self::$db->sql('DELETE FROM :_users')->exec();
    }
    
    public function lifeTime()
    {
        $user = new GenericModel(self::$db, '_users');
        
        $this->assertFalse($user->isSaved());
        $this->assertFalse($user->isUpToDate());
        
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->save();
        
        $this->assertTrue($user->isSaved());
        $this->assertTrue($user->isUpToDate());
        
        $user->username = 'jonasdoe';
        
        $this->assertTrue($user->isSaved());
        $this->assertFalse($user->isUpToDate());
        
        $user->save();
        
        $this->assertTrue($user->isSaved());
        $this->assertTrue($user->isUpToDate());
        
        $user->drop();
        
        $this->assertFalse($user->isSaved());
        $this->assertFalse($user->isUpToDate());
        $this->assertFalse($user->id);
    }
    
    public function fromDB()
    {
        $user = new GenericModel(self::$db, '_users');
        $user->email = 'johndoe@gmail.com';
        $user->username = 'johndoe';
        $user->password = sha1('123456');
        $user->save();
        unset($user);
        
        $query = self::$db->sql('SELECT * FROM :_users LIMIT 1')->query();
        $user = current($query);
        $user = new GenericModel(self::$db, '_users', $user->id, (array)$user);
        
        $this->assertTrue($user->isSaved());
        $this->assertTrue($user->isUpToDate());
    }
    
    public function fill()
    {
        $user = new GenericModel(self::$db, '_users');
        $post = array('name' => 'johndoe', 'age' => 22, 'is_admin' => true);
        $user->fill($post, 'name, age');
        
        $this->assertTrue(isset($user->name));
        $this->assertTrue(isset($user->age));
        $this->assertFalse(isset($user->is_admin));
    }
    
    public function idSetManually()
    {
        $user = new GenericModel(self::$db, '_users');
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
            $this->assertTrue(get_class($e) === 'RuntimeException');
        }
    }
    
    public function idChanged()
    {
        $user = new GenericModel(self::$db, '_users');
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
}