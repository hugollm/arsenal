<?php
namespace Arsenal\Misc;

class AssetBundler
{
    private $root = null;
    private $fluid = true;
    private $bundles = array();
    
    public function __construct($root)
    {
        $this->root = realpath($root);
    }
    
    public function setFluid($bool)
    {
        $this->fluid = $bool;
    }
    
    public function bundle($bundle, array $files)
    {
        $this->bundles[$bundle] = $files;
    }
    
    public function getCssTag($bundle, $media = 'all')
    {
        return '<link rel="stylesheet" href="'.$this->getBundle($bundle).'" media="'.$media.'">';
    }
    
    public function getJsTag($bundle)
    {
        return '<script type="text/javascript" src="'.$this->getBundle($bundle).'"></script>';
    }
    
    public function getBundle($bundle)
    {
        if( ! isset($this->bundles[$bundle]))
            throw new \InvalidArgumentException('Bundle "'.$bundle.'" not found');
        
        if($this->fluid)
            $this->makeBundle($bundle);
        
        return $bundle;
    }
    
    private function makeBundle($bundle)
    {
        $files = $this->bundles[$bundle];
        $string = '';
        $d = DIRECTORY_SEPARATOR;
        foreach($files as $file)
            $string .= file_get_contents($this->root.$d.$file)."\n\n";
        $string = substr($string, 0, -2);
        file_put_contents($this->root.$d.$bundle, $string);
    }
}