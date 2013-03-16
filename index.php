<?php

use Chronos\TestFramework\TestSession;
use Chronos\TestFramework\HtmlDumper;

// exposing all errors, setting timezone and charset properly
error_reporting(-1);
date_default_timezone_set('Europe/London');
header('Content-Type: text/html; charset=utf-8');

// autoloading
require 'support/Autoload.php';
Autoload::register('support');
Autoload::register('src');

// aliases for debugging
function dump(){call_user_func_array('Debug::printContents', func_get_args());};
function methods(){call_user_func_array('Debug::printMethods', func_get_args());};

// handlers
set_error_handler('Handler::error');
set_exception_handler('Handler::exception');
register_shutdown_function( 'Handler::shutdown');

// run all tests
$testSession = new TestSession;
$testSession->loadFolder('test', true);
$results = $testSession->run();
$results->dump();