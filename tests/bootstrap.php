<?php

use Arsenal\Misc\Autoloader;

// autoloading
require __DIR__.'/../src/Arsenal/Misc/Autoloader.php';
$loader = new Autoloader;
$loader->setFileSystemCheck(true);
$loader->addFolder('src');
$loader->addFolder('tests');
$loader->register();