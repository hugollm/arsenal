<?php
namespace Arsenal\Storages;

interface Cacher
{
    public function setUntil($key, $val, $expire = null);
    public function clearExpired();
}