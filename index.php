<?php

use Arsenal\TestFramework\TestSession;
use Arsenal\Benchmark;
use Arsenal\Misc\Autoloader;

// exposing all errors, setting timezone and charset properly
error_reporting(-1);
date_default_timezone_set('Europe/London');
header('Content-Type: text/html; charset=utf-8');

// autoloading
require 'src/Arsenal/Misc/Autoloader.php';
$loader = new Autoloader;
$loader->setFileSystemCheck(true);
$loader->addFolder('support');
$loader->addFolder('src');
$loader->addFolder('test');
$loader->register();

// aliases for debugging
function dump(){call_user_func_array('Debug::printContents', func_get_args());};
function methods(){call_user_func_array('Debug::printMethods', func_get_args());};

// handlers
set_error_handler('Handler::error');
set_exception_handler('Handler::exception');
register_shutdown_function( 'Handler::shutdown');

$bm = new Benchmark;

// run all tests
$testSession = new TestSession;
$testSession->loadFolder('test', true);
$results = $testSession->run();
$results->dump();

// require 'play.php';

$bm->dump();