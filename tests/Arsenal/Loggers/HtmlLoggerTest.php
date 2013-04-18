<?php
namespace Arsenal\Loggers;

class HtmlLoggerTest extends OutputLoggerTest
{
    protected function getLogger()
    {
        return new HtmlLogger;
    }
}