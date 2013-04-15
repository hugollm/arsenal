<?php
namespace Arsenal\Storages;

interface Storage
{
    public function get($key, $default = null);
    public function set($key, $val);
    public function getAll();
    public function setAll(array $items);
    public function getAllKeys();
    public function hasKey($key);
    public function drop($key);
    public function clear();
}