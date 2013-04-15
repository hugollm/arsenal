<?php
namespace Arsenal\Storages;

class ArrayStorageTest extends StorageTest
{
    protected function getStorage()
    {
        return new ArrayStorage();
    }
}