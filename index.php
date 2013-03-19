<?php

use Chronos\TestFramework\TestSession;
use Chronos\Database\Database;
use Chronos\Database\Schema;
use Chronos\Benchmark;

// exposing all errors, setting timezone and charset properly
error_reporting(-1);
date_default_timezone_set('Europe/London');
header('Content-Type: text/html; charset=utf-8');

// autoloading
require 'support/Autoload.php';
Autoload::register('support');
Autoload::register('src');
Autoload::register('test');

// aliases for debugging
function dump(){call_user_func_array('Debug::printContents', func_get_args());};
function methods(){call_user_func_array('Debug::printMethods', func_get_args());};

// handlers
set_error_handler('Handler::error');
set_exception_handler('Handler::exception');
register_shutdown_function( 'Handler::shutdown');

$bm = new Benchmark;

// run all tests
// $testSession = new TestSession;
// $testSession->loadFolder('test', true);
// $results = $testSession->run();
// $results->dump();

// $bm->dump();





$schema = new Schema;

$table = $schema->table('users');
    $table->column('id', 'serial');
    $table->column('email', 'string', 255);
    $table->column('username', 'string', 30);
    $table->column('password', 'string', 64);
$table->primary('id');
$table->unique('email');
$table->unique('username');

$table = $schema->table('posts');
    $table->column('id', 'serial');
    $table->column('user_id', 'ref');
    $table->column('title', 'string', 255);
    $table->column('description', 'string', 1024);
    $table->column('body', 'text');
$table->primary('id');
$table->foreign('user_id', 'users', 'id', 'cascade', 'cascade');

$db = new Database('mysql:host=localhost;dbname=chronos', 'root', '');
// $db = new Database('sqlite:junk/database.sqlite');

// $db->dropAllTables();
$db->migrate($schema);


dump(get_included_files());



$bm->dump();