<?php

use Arsenal\Database\Database;
use Arsenal\Loggers\JsConsoleLogger;
use Arsenal\Loggers\HtmlLogger;
use Arsenal\Misc\Benchmark;

dump('hello world');
dump(get_included_files());

// $db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
// $logger = new HtmlLogger;
// $db->setLogger($logger);

// $users = $db->select('user', array('username'=>'tonin'));
// dump($users);

// $db->insert('user', array('username'=>'tonin', 'email'=>'daroca@bol.com.br'));
// $db->update('user', array('email'=>'daroca@yahoo.com.br', 'password'=>sha1('')), array('username'=>'tonin'));
// $db->upsert('user', array('username'=>'tonin', 'email'=>'daroca@yahoo.com.br', 'password'=>sha1(rand())), array('username'=>'tonin'));



// $b = new Benchmark;
// $b->point();
// $b->point();
// $b->point();
// $b->point();
// $b->point();
// $b->dumpSummary();
// $b->dump();