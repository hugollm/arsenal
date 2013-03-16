<?php

abstract class Model extends RedBean_SimpleModel
{
    private $errors = array();
    
    public function error($key, $message)
    {
        $this->errors[$key] = $message;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function fill(array $fields, $filter)
    {
        $filter = str_replace(' ', '', $filter);
        $filter = explode(',', $filter);
        foreach($filter as $f)
            if( ! isset($fields[$f]))
                $fields[$f] = null;
        $this->bean->import($fields, $filter);
    }
    
    public function changed($property)
    {
        return $this->bean->hasChanged($property);
    }
    
    final public function update()
    {
        $this->set();
    }
    
    final public function open()
    {
        $this->get();
    }
    
    public function isValid()
    {
        $this->validate();
        return ! (bool)$this->getErrors();
    }
    
    public function set() {}
    public function get() {}
    
    abstract function validate();
}