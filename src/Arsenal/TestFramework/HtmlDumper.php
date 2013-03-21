<?php
namespace Arsenal\TestFramework;

class HtmlDumper
{
    private $classes = array();
    private $html = '';
    
    public function setClass($class, $style)
    {
        $this->classes[$class] = $style;
    }
    
    public function open($tag, $class = null)
    {
        $this->html .= '<'.$tag.' class="html-dumper '.$class.'"'.'>';
        return $this;
    }
    
    public function close($tag)
    {
        $this->html .= '</'.$tag.'>';
        return $this;
    }
    
    public function node($tag, $class = null, $text = null)
    {
        $this->html .= '<'.$tag.' class="html-dumper '.$class.'"'.'>'.$text.'</'.$tag.'>';
        return $this;
    }
    
    public function self($tag)
    {
        $this->html .= '<'.$tag.'>';
        return $this;
    }
    
    public function raw($html)
    {
        $this->html .= $html;
        return $this;
    }
    
    public function dump()
    {
        $dump = $this->getCss();
        $dump .= $this->getHtml();
        echo $dump;
    }
    
    private function getHtml()
    {
        return '<div class="html-dumper dump">'.$this->html.'</div>';
    }
    
    private function getCss()
    {
        $css = '<style type="text/css">';
        $css .= $this->getCssReset();
        foreach($this->classes as $class=>$style)
            $css .= '.html-dumper.'.$class.'{'.$style.'}'."\n";
        $css .= '</style>';
        return $css;
    }
    
    private function getCssReset()
    {
        // return '
        //     .html-dumper {
        //         margin: 0;
        //         padding: 0;
        //         font: 13px/18px arial, sans-serif;
        //         overflow: auto;
        //     }
        // ';
    }
}