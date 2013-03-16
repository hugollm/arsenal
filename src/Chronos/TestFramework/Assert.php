<?php
namespace Chronos\TestFramework;

use Chronos\TestFramework\Assertions\Assertion;
use Chronos\TestFramework\Assertions\BoolAssertion;
use Chronos\TestFramework\Assertions\StringAssertion;

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
    
    public static function true($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isTrue();
    }
    
    public static function false($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isFalse();
    }
    
    public static function string($val, $message = null)
    {
        return new StringAssertion($val, $message);
    }
}