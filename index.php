<?php

use Arsenal\Misc\Autoloader;
use Arsenal\Misc\ErrorHandler;
use Arsenal\Misc\Benchmark;
use Arsenal\Misc\Foo;

/*
    BOOTSTRAP
*/
$startTime = microtime(true);
$startMemory = memory_get_usage(true);
error_reporting(-1);
ini_set('display_errors', true);
date_default_timezone_set('Europe/London');
header('Content-Type: text/html; charset=utf-8');

/*
    AUTOLOADING
*/
require 'src/Arsenal/Misc/Autoloader.php';
$loader = new Autoloader;
$loader->setCheckFileSystem(true);
$loader->addFolder('legacy');
$loader->addFolder('src');
$loader->register();

/*
    ALIASES
*/
function dump(){call_user_func_array('Arsenal\\Misc\\Debugger::printContents', func_get_args());};
function methods(){call_user_func_array('Arsenal\\Misc\\Debugger::printMethods', func_get_args());};

/*
    ERROR HANDLING
*/
$handler = new ErrorHandler;
$handler->setKeepBuffer(true);
$handler->addFocus('src/Arsenal');
$handler->addFocus('play.php');
// $handler->register(true, true, false);
$handler->register();

/*
    PLAYING AROUND
*/
$bm = new Benchmark($startTime, $startMemory);
require 'play.php';
$bm->point();
$bm->dumpSummary();