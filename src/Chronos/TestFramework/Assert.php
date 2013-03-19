<?php
namespace Chronos\TestFramework;

use Chronos\TestFramework\Assertions\Assertion;
use Chronos\TestFramework\Assertions\BoolAssertion;
use Chronos\TestFramework\Assertions\StringAssertion;
use Chronos\TestFramework\Assertions\ArrayAssertion;

class Assert
{
    public static function pass($message = null)
    {
        throw new TestException('pass', 'PASS', $message);
    }
    
    public static function skip($message = null)
    {
        throw new TestException('skip', 'SKIP', $message);
    }
    
    public static function fail($message = null)
    {
        throw new TestException('fail', 'FAIL', $message);
    }
    
    public static function isTrue($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isTrue();
    }
    
    public static function isFalse($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isFalse();
    }
    
    public static function isString($val, $message = null)
    {
        return new StringAssertion($val, $message);
    }
    
    public static function isArray($val, $message = null)
    {
        return new ArrayAssertion($val, $message);
    }
}