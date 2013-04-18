<?php

require 'src/WideImage/WideImage.php';

class Image extends WideImage
{
    public static function getCompression($extension, $jpg = 90)
    {
        if($extension === 'jpg' or $extension === 'jpeg')
            $compression = $jpg;
        elseif($extension === 'png')
            $compression = 9;
        else
            $compression = null;
        
        return $compression;
    }
}