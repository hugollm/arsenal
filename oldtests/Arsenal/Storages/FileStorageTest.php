<?php
namespace Arsenal\Storages;

class FileStorageTest extends StorageTest
{
    public function __destruct()
    {
        unlink('junk/filestorage');
    }
    
    protected function getStorage()
    {
        return new FileStorage('junk/filestorage');
    }
}