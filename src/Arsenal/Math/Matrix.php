<?php
namespace Arsenal\Math;

class Matrix
{
    private $rowCount = 0;
    private $colCount = 0;
    private $matrix = array();
    
    public function get($r, $c)
    {
        if($r+1 > $this->rowCount or $c+1 > $this->colCount)
            throw new \InvalidArgumentException('Trying to get inexistent matrix index: ['.$r.','.$c.']');
        
        return isset($this->matrix[$r][$c]) ? $this->matrix[$r][$c] : 0;
    }
    
    public function getSubMatrix($rStart, $cStart, $nRows, $nCols)
    {
        $r1 = $rStart;
        $c1 = $cStart;
        $r2 = $r1 + ($nRows-1);
        $c2 = $c1 + ($nCols-1);
        
        $sub = new self;
        for($ri=$r1,$rn=0; $ri<=$r2; $ri++,$rn++)
            for($ci=$c1,$cn=0; $ci<=$c2; $ci++,$cn++)
                $sub->set($rn, $cn, $this->get($ri, $ci));
        return $sub;
    }
    
    public function getRow($r)
    {
        return $this->getSubMatrix($r, 0, 1, $this->colCount);
    }
    
    public function getCol($c)
    {
        return $this->getSubMatrix(0, $c, $this->rowCount, 1);
    }
    
    public function getRowCount()
    {
        return $this->rowCount;
    }
    
    public function getColCount()
    {
        return $this->colCount;
    }
    
    public function set($r, $c, $val)
    {
        if($r+1 > $this->rowCount)
            $this->rowCount = $r+1;
        if($c+1 > $this->colCount)
            $this->colCount = $c+1;
        
        $this->matrix[$r][$c] = $val;
    }
    
    public function setRow($r, self $row)
    {
        for($c=0; $c<$row->getColCount(); $c++)
            $this->set($r, $c, $row->get(0, $c));
    }
    
    public function setCol($c, self $col)
    {
        for($r=0; $r<$col->getRowCount(); $r++)
            $this->set($r, $c, $col->get($r, 0));
    }
    
    public function each($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for Matrix->each');
        
        for($r=0; $r<$this->rowCount; $r++)
            for($c=0; $c<$this->colCount; $c++)
            {
                $return = call_user_func($callback, $this->get($r, $c), $r, $c);
                if($return !== null)
                    $this->set($r, $c, $return);
            }
    }
    
    public function eachRow($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for Matrix->eachRow');
        
        for($r=0; $r<$this->rowCount; $r++)
        {
            $row = $this->getRow($r);
            $return = call_user_func($callback, $row, $r);
            if($return instanceof self)
                $this->setRow($r, $return);
        }
    }
    
    public function eachCol($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for Matrix->eachCol');
        
        for($c=0; $c<$this->colCount; $c++)
        {
            $col = $this->getCol($c);
            $return = call_user_func($callback, $col, $c);
            if($return instanceof self)
                $this->setCol($c, $return);
        }
    }
    
    public function multiply($n)
    {
        $this->each(function($x) use($n)
        {
            return $x*$n;
        });
    }
    
    public function add(self $matrix)
    {
        if( ! $this->isSameOrderAs($matrix))
            throw new \InvalidArgumentException('Trying to add matrixes of different orders');
        
        $this->each(function($x, $r, $c) use($matrix)
        {
            return $x + $matrix->get($r, $c);
        });
    }
    
    public function swapRows($r1, $r2)
    {
        $row1 = $this->getRow($r1);
        $row2 = $this->getRow($r2);
        $this->setRow($r1, $row2);
        $this->setRow($r2, $row1);
    }
    
    public function swapCols($c1, $c2)
    {
        $col1 = $this->getCol($c1);
        $col2 = $this->getCol($c2);
        $this->setCol($c1, $col2);
        $this->setCol($c2, $col1);
    }
    
    public function isSameOrderAs(self $matrix)
    {
        if($this->rowCount != $matrix->getrowCount() or $this->colCount != $matrix->getColCount())
            return false;
        return true;
    }
    
    public function printTable()
    {
        echo '<table style="margin:10px;">';
        for($r=0; $r<$this->rowCount; $r++)
        {
            echo '<tr>';
            for($c=0; $c<$this->colCount; $c++)
            {
                $x = $this->get($r, $c);
                if(strpos((string)$x, '.') !== false)
                    $x = number_format($x, 2);
                echo '<td style="border:1px solid #ccc;background-color:#eee;width:25px;text-align:right;">'.$x.'</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
}