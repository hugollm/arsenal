<?php
namespace Arsenal\Sessions;

abstract class SessionTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function createSession(FakeCookieJar $cj);
    
    protected function createCookieJar()
    {
        $rq = new FakeRequest;
        $rq->setIp('1.1.1.1');
        $rq->setUserAgent('chromados');
        $cj = new FakeCookieJar($rq);
        return $cj;
    }
    
    public function testNormalData()
    {
        $ss = $this->newSession(array('normal' => 'bar'));
        $ss = $this->nextVisit($ss);
        
        $this->assertTrue($ss->getAll() === array('normal' => 'bar'), 'data should persist');
    }
    
    public function testFlashData()
    {
        $ss = $this->newSession(array(), array('flash' => 'bar'));
        $ss = $this->nextVisit($ss);
        
        $this->assertTrue($ss->getAllFlash() === array('flash' => 'bar'), 'data should persist');
        
        $ss = $this->nextVisit($ss);
        $this->assertTrue($ss->getAllFlash() === array(), 'data should not persist this far');
    }
    
    public function testHijack()
    {
        $ss = $this->newSession(array('hijack' => 'bar'));
        $ss = $this->nextVisit($ss);
        $ss->_cj->getRequest()->setIp('255.255.255.255');
        
        $this->assertEmpty($ss->getId(), 'session should have been destroyed');
        $this->assertTrue($ss->getAll() === array(), 'data should not persist');
        
        $ss = $this->newSession(array('hijack' => 'bar'));
        $ss = $this->nextVisit($ss);
        $ss->_cj->getRequest()->setUserAgent('godzilla giroflex');
        
        $this->assertEmpty($ss->getId(), 'session should have been destroyed');
        $this->assertTrue($ss->getAll() === array(), 'data should not persist');
    }
    
    public function testExpiration()
    {
        $ss = $this->newSession();
        $ss->setExpiration('1 second');
        $ss->setCleanupChance(1);
        $ss->set('expiration', 'bar');
        
        $ss = $this->nextVisit($ss);
        
        $ss->setExpiration('1 second');
        $ss->setCleanupChance(1);
        $this->assertTrue($ss->getAll() === array('expiration' => 'bar'), 'data should persist');
        
        sleep(2);
        $ss = $this->nextVisit($ss);
        
        $ss->setExpiration('1 second');
        $ss->setCleanupChance(1);
        $this->assertTrue($ss->getAll() === array(), 'data should not persist');
    }
    
    protected function newSession(array $data = array(), array $flash = array())
    {
        $cj = $this->createCookieJar();
        $ss = $this->createSession($cj);
        $ss->_cj = $cj;
        
        // filling data
        foreach($data as $k=>$v)
            $ss->set($k, $v);
        foreach($flash as $k=>$v)
            $ss->setFlash($k, $v);
        
        return $ss;
    }
    
    protected function nextVisit(Session $ss)
    {
        $cj = $ss->_cj;
        unset($ss);
        $ss = $this->createSession($cj);
        $ss->_cj = $cj;
        return $ss;
    }
}