<?php
namespace Arsenal\Storages;

use Arsenal\Database\Database;
use Arsenal\Loggers\JsConsoleLogger;

abstract class DumbDatabaseStorageTest extends StorageTest
{
    protected function getStorage()
    {
        $db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
        $db->setLogger(new JsConsoleLogger);
        return new DumbDatabaseStorage($db, '_storage', 'dumb');
    }
}