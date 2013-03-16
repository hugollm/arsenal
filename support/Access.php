<?php

class Access
{
    public static function grant($role)
    {
        $roles = self::getRoles();
        if( ! in_array($role, $roles))
        {
            $roles[] = $role;
            Session::set('access.roles', $roles);
        }
    }
    
    public static function revoke($role)
    {
        $roles = self::getRoles();
        $index = array_search($role, $roles, true);
        if($index !== false)
        {
            unset($roles[$index]);
            Session::set('access.roles', $roles);
        }
    }
    
    public static function revokeAll()
    {
        $roles = self::getRoles();
        if($roles)
            Session::drop('access.roles');
    }
    
    public static function getRoles()
    {
        return Session::get('access.roles') ?: array();
    }
    
    public static function isGranted($role)
    {
        $roles = self::getRoles();
        return in_array($role, $roles, true);
    }
    
    public static function demand($role)
    {
        if( ! self::isGranted($role))
            Response::send(403);
    }
}