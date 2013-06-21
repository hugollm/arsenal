<?php
namespace Arsenal\Sessions;

/*
    APC apparently was not available in
    script mode, so the test is disabled (abstract)
*/
abstract class ApcSessionTest extends SessionTest
{
    protected function createSession(FakeCookieJar $cj = null)
    {
        if( ! $cj)
            $cj = $this->createCookieJar();
        
        return new ApcSession($cj);
    }
}