<?php
namespace Chronos\TestFramework\Assertions;

class ArrayAssertion extends Assertion
{
    public function _hasCount($val, $count)
    {
        return count($val) === $count;
    }
}