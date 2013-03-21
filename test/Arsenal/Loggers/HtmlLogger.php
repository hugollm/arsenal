<?php
namespace Arsenal\Loggers;

class HtmlLoggerTest extends loggerWithOutputTest
{
    protected function getLogger()
    {
        return new HtmlLogger;
    }
}