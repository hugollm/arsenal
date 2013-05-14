<?php
namespace Arsenal\Math;

class Simplex
{
    public $objective = array();
    public $constraints = array();
    public $vars = array();
    public $slackCount = 0;
    public $matrix = null;
    
    public function maximize($formula)
    {
        $parsed = $this->parseFormula($formula);
        $this->vars = array_keys(array_merge(array_flip($this->vars), $parsed['vars']));
        $this->objective = $parsed;
    }
    
    public function minimize($formula)
    {
        $parsed = $this->parseFormula($formula);
        
        // inverting, so we can maximize instead of minimize
        foreach($parsed['vars'] as $k=>$v)
            $parsed['vars'][$k] = (-1)*$v;
        $parsed['equals'] *= -1;
        
        $this->vars = array_keys(array_merge(array_flip($this->vars), $parsed['vars']));
        $this->objective = $parsed;
    }
    
    public function addConstraint($formula)
    {
        $formula = implode('', func_get_args());
        $parsed = $this->parseFormula($formula);
        
        $this->vars = array_keys(array_merge(array_flip($this->vars), $parsed['vars']));
        $this->constraints[] = $parsed;
    }
    
    public function solve()
    {
        $this->mountMatrix();
        $this->matrix->printTable();
    }
    
    public function mountMatrix()
    {
        $this->matrix = new Matrix;
        
        $rows = array_merge(array($this->objective), $this->constraints);
        
        foreach($rows as $rkey=>$row)
        {
            foreach($this->vars as $vkey=>$var)
                $this->matrix->set($rkey, $vkey, isset($row['vars'][$var]) ? $row['vars'][$var] : 0);
            $this->matrix->set($rkey, count($this->vars), $row['equals']);
        }
    }
    
    public function printMatrix()
    {
        echo '<table style="margin:10px"><tr>';
        $headers = array_merge($this->vars, array('_k'));
        foreach($headers as $var)
            echo '<td style="border:1px solid #aaa;background-color:#ccc;width:25px;padding-left:5px;padding-right:5px;text-align:center;">'.$var.'</td>';
        echo '</tr>';
        foreach($this->matrix as $y=>$row)
        {
            echo '<tr>';
            foreach($row as $xy)
                echo '<td style="border:1px solid #ccc;background-color:#eee;width:25px;text-align:right;">'.$xy.'</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    public function isMatrixSolved()
    {
        // removing last column
        $matrix = $this->matrix;
        foreach($matrix as $k=>$row)
            $matrix[$k] = array_slice($row, 0, -1);
        
        $nrows = count($matrix);
        $ncols = count(end($matrix));
        
        // horizontal traversing
        for($x=0; $x<$nrows; $x++)
        {
            $count = 0;
            for($y=0; $y<$ncols; $y++)
                if($this->matrix[$x][$y] != 0)
                    $count++;
            if($count != 1)
                return false;
        }
        
        // vertical traversing
        for($y=0; $y<$ncols; $y++)
        {
            $count = 0;
            for($x=0; $x<$nrows; $x++)
                if($this->matrix[$x][$y] != 0)
                    $count++;
            if($count != 1)
                return false;
        }
        
        return true;
    }
    
    public function getResultsFromMatrix()
    {
        
    }
    
    public function parseFormula($formula)
    {
        $formula = trim($formula);
        $formula = str_replace(' ', '', $formula);
        $formula = str_replace('*', '', $formula);
        
        $left = preg_replace('#^([a-z0-9_.+-]+)(<=|>=|=)[a-z0-9_.+-]+$#i', '$1', $formula);
        $right = preg_replace('#^[a-z0-9_.+-]+(<=|>=|=)([a-z0-9_.+-]+)$#i', '$2', $formula);
        $comp = preg_replace('#^[a-z0-9_.+-]+(<=|>=|=)[a-z0-9_.+-]+$#i', '$1', $formula);
        
        $lp = $this->parseFormulaSide($left);
        $rp = $this->parseFormulaSide($right);
        
        // variables to the "left"
        $parsed['vars'] = $lp['vars'];
        foreach($rp['vars'] as $k=>$v)
            if(isset($parsed['vars'][$k]))
                $parsed['vars'][$k] -= $v;
            else
                $parsed['vars'][$k] = -$v;
            
        // adding slack variables to eliminate innequations
        if($comp === '<=')
            $parsed['vars']['slack_'.(++$this->slackCount)] = 1;
        if($comp === '>=')
            $parsed['vars']['slack_'.(++$this->slackCount)] = -1;
            
        // constants to the "right"
        $parsed['equals'] = $rp['constant'] - $lp['constant'];
        
        return $parsed;
    }
    
    public function parseFormulaSide($formula)
    {
        $matches = array();
        preg_match_all('#(?<o>=|<=|>=|\+|-)?(?<k>[0-9]+(\.[0-9]+)?)?(?<v>[a-z_][a-z0-9_]*)#i', $formula, $matches);
        
        // summing up variables
        $parsed['vars'] = array();
        foreach($matches['v'] as $k=>$v)
            if(isset($parsed['vars'][$v]))
                $parsed['vars'][$v] += (float) ($matches['o'][$k].($matches['k'][$k] ?: 1));
            else
                $parsed['vars'][$v] = (float) ($matches['o'][$k].($matches['k'][$k] ?: 1));
        
        $matches = array();
        preg_match_all('#(?<o>=|<=|>=|\+|-|^)(?<k>[0-9]+(\.[0-9]+)?)(=|<=|>=|\+|-|$)#i', $formula, $matches);
        
        // summing up the constants
        $parsed['constant'] = 0;
        foreach($matches['k'] as $k=>$v)
            $parsed['constant'] += (float) ($matches['o'][$k].$v);
        
        return $parsed;
    }
}