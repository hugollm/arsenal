<?php
namespace Arsenal\Storages;

use Arsenal\Database\Database;
use Arsenal\Loggers\JsConsoleLogger;

class DatabaseStorageTest extends StorageTest
{
    protected function getStorage()
    {
        $db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
        $db->setLogger(new JsConsoleLogger);
        return new DatabaseStorage($db, '_storage');
    }
}