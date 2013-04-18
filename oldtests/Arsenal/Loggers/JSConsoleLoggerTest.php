<?php
namespace Arsenal\Loggers;

use Arsenal\TestFramework\Assert;

class JsConsoleLoggerTest extends LoggerWithOutputTest
{
    protected function getLogger()
    {
        return new JsConsoleLogger;
    }
}