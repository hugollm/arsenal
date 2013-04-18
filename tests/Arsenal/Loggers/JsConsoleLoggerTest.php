<?php
namespace Arsenal\Loggers;

class JsConsoleLoggerTest extends OutputLoggerTest
{
    protected function getLogger()
    {
        return new JsConsoleLogger;
    }
}