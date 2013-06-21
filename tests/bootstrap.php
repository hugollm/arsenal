<?php

use Arsenal\Misc\Autoloader;

// autoloading
require __DIR__.'/../src/Arsenal/Misc/Autoloader.php';
$loader = new Autoloader;
$loader->setCheckFileSystem(true);
$loader->addFolder('src');
$loader->addFolder('src-obsolete');
$loader->addFolder('tests');
$loader->register();