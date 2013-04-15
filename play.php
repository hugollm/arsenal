<?php

use Arsenal\Database\Database;
use Arsenal\Database\DoctrineWrapper;
use Arsenal\Database\Schema;
use Arsenal\Database\Entity;
use Arsenal\Database\EntityQuery;
use Arsenal\Loggers\JsConsoleLogger;
use Arsenal\Loggers\HtmlLogger;
use Arsenal\Benchmark;
use Arsenal\Storages\DumbDatabaseStorage;
use Arsenal\Storages\DatabaseStorage;

use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

$db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
$logger = new JsConsoleLogger;
$db->setLogger($logger);

$users = $db->exists('user', array('username'=>'tonin'));
dump($users);

// $db->insert('user', array('username'=>'tonin', 'email'=>'daroca@bol.com.br'));
// $db->update('user', array('email'=>'daroca@yahoo.com.br', 'password'=>sha1('')), array('username'=>'tonin'));

$db->upsert('user', array('username'=>'tonin', 'email'=>'daroca@yahoo.com.br', 'password'=>sha1(rand())), array('username'=>'tonin'));