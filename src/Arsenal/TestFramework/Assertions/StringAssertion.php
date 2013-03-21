<?php
namespace Arsenal\TestFramework\Assertions;

class StringAssertion extends Assertion
{
    public function _isEmpty($val)
    {
        return empty($val);
    }
}