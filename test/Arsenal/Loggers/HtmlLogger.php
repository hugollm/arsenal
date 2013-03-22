<?php
namespace Arsenal\Loggers;

class HtmlLoggerTest extends LoggerWithOutputTest
{
    protected function getLogger()
    {
        return new HtmlLogger;
    }
}