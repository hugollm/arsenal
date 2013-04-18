<?php

class R extends RedBean_Facade
{
    public static function syncSchema($from,$to) { return RedBean_Plugin_Sync::syncSchema($from,$to); }
    public static function log($filename) { $tl = new RedBean_Plugin_TimeLine($filename); self::$adapter->addEventListener('sql_exec',$tl);}
    public static function graph($array,$filterEmpty=false) { $c = new RedBean_Plugin_Cooker(); $c->setToolbox(self::$toolbox);return $c->graph($array,$filterEmpty);}
}


R::setup(
  Config::get('database.dsn'),
  Config::get('database.username'),
  Config::get('database.password'));

if( ! Config::get('database.fluid'))
  R::freeze();

if(Config::get('database.debug'))
  R::debug(true);