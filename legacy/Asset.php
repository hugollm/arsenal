<?php

class Asset
{
    private static $groups = array();
    
    public static function add($group, $file)
    {
        self::$groups[$group][] = $file;
    }
    
    public static function dumpCss($group, $bundle = false, $bundleFolder = 'bundles')
    {
        if(empty(self::$groups[$group]))
            return null;
        
        if($bundle)
            return '<link rel="stylesheet" href="'.self::getBundle($group, 'css', $bundleFolder).'">';
        
        $tags = '';
        foreach(self::$groups[$group] as $file)
            $tags .= '<link rel="stylesheet" href="'.$file.'">'."\n";
        return $tags;
    }
    
    public static function dumpJs($group, $bundle = false, $bundleFolder = 'bundles')
    {
        if(empty(self::$groups[$group]))
            return null;
        
        if($bundle)
            return '<script src="'.self::getBundle($group, 'js', $bundleFolder).'"></script>';
        
        $tags = '';
        foreach(self::$groups[$group] as $file)
            $tags .= '<script src="'.$file.'"></script>'."\n";
        return $tags;
    }
    
    private static function getBundle($group, $ext, $folder = 'bundles')
    {
        if(empty(self::$groups[$group]))
            return null;
        
        $filename = 'bundle.'.$group.'.'.$ext;
        $filepath = 'app/static/'.$folder.'/'.$filename;
        
        // creating/updating bundles
        if(Config::get('asset.fluid'))
        {
            $contents = '';
            foreach(self::$groups[$group] as $file)
                $contents .= file_get_contents('app/static/'.$file)."\n\n";
            
            if( ! is_file($filepath) or sha1($contents) !== sha1_file($filepath))
            {
                if( ! is_dir('app/static/'.$folder))
                    mkdir('app/static/'.$folder, 0777, true);
                file_put_contents($filepath, $contents);
            }
        }
        
        return $folder.'/'.$filename;
    }
}