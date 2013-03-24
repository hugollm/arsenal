<?php

use Arsenal\Database\Database;
use Arsenal\Database\MappedObject;

$db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');

$obj = new MappedObject($db, '_users');
dump($obj);
methods($obj, true);

$obj->fill(array('username'=>'hugollm', 'password'=>'123456', 'foo'=>'bar'), 'username, age');

dump($obj);

$obj->save();

dump('saved!!!');
dump($obj);
methods($obj, true);

$obj->drop();