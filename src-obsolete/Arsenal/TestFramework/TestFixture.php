<?php
namespace Arsenal\TestFramework;

use Arsenal\TestFramework\Assertions\Assertion;
use Arsenal\TestFramework\Assertions\BoolAssertion;
use Arsenal\TestFramework\Assertions\StringAssertion;
use Arsenal\TestFramework\Assertions\NumberAssertion;
use Arsenal\TestFramework\Assertions\ArrayAssertion;
use Arsenal\TestFramework\Assertions\ObjectAssertion;

class TestFixture
{
    final protected function pass($message = null)
    {
        throw new TestException('pass', 'PASS', $message);
    }
    
    final protected function fail($message = null)
    {
        throw new TestException('fail', 'FAIL', $message);
    }
    
    final protected function skip($message = null)
    {
        throw new TestException('skip', 'SKIP', $message);
    }
    
    final protected function assert($val, $message = null)
    {
        return new Assertion($val, $message);
    }
    
    final protected function assertBool($val, $message = null)
    {
        return new BoolAssertion($val, $message);
    }
    
    final protected function assertTrue($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isTrue();
    }
    
    final protected function assertFalse($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isFalse();
    }
    
    final protected function assertTrueStrict($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isTrue(true);
    }
    
    final protected function assertFalseStrict($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isFalse(true);
    }
    
    final protected function assertNull($val, $message = null)
    {
        $assertion = new BoolAssertion($val, $message);
        return $assertion->isNull();
    }
    
    final protected function assertString($val, $message = null)
    {
        return new StringAssertion($val, $message);
    }
    
    final protected function assertNumber($val, $message = null)
    {
        return new NumberAssertion($val, $message);
    }
    
    final protected function assertArray($val, $message = null)
    {
        return new ArrayAssertion($val, $message);
    }
    
    final protected function assertObject($val, $message = null)
    {
        return new ObjectAssertion($val, $message);
    }
    
    final public function _run(SessionResults $results)
    {
        $methods = $this->getTestMethods();
        foreach($methods as $method)
        {
            try
            {
                $this->runSetup();
                call_user_func(array($this, $method));
                throw new TestException('pass', null);
            }
            catch(TestException $e)
            {
                $result = new TestResult(get_class($this), $method, $e->getStatus(), $e->getAssertion(), $e->getMessage(), $e->getTestFile(), $e->getTestLine());
                $results->addResult($result);
            }
            $this->runTeardown();
        }
    }
    
    private function getTestMethods()
    {
        $testMethods = array();
        $rClass = new \ReflectionClass($this);
        $rMethods = $rClass->getMethods();
        foreach($rMethods as $rMethod)
        {
            $name = $rMethod->getName();
            $isPublic = $rMethod->isPublic();
            $isConstructor = $rMethod->isConstructor();
            $isSetup = strtolower($name) === 'setup';
            $isTeardown = strtolower($name) === 'teardown';
            $startsWithUnderline = strpos($rMethod->getName(), '_') === 0;
            
            if($isPublic and ! $isConstructor and ! $isSetup and ! $isTeardown and ! $startsWithUnderline)
                $testMethods[] = $name;
        }
        return $testMethods;
    }
    
    private function runSetup()
    {
        if(method_exists($this, 'setUp'))
            $this->{'setUp'}();
    }
    
    private function runTeardown()
    {
        if(method_exists($this, 'tearDown'))
            $this->{'tearDown'}();
    }
}