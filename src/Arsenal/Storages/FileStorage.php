<?php
namespace Arsenal\Storages;

class FileStorage extends MappedArrayStorage
{
    private $filename = null;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
    
    protected function load()
    {
        return unserialize(file_get_contents($this->filename));
    }
    
    protected function update(array $items)
    {
        file_put_contents($this->filename, serialize($items));
    }
}