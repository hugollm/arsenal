<?php
namespace Arsenal\Math;

class Simplex
{
    public $debug = false;
    public $results = array();
    
    public $objective = array();
    public $constraints = array();
    public $vars = array();
    public $slackCount = 0;
    public $matrix = null;
    
    public function setDebug($bool)
    {
        $this->debug = $bool;
    }
    
    public function getResults()
    {
        return $this->results;
    }
    
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
    
    public function solve()
    {
        $this->mountMatrix();
        
        if($this->debug)
        {
            $this->printEquations();
            $this->matrix->printTable();
        }
        
        while( ! $this->isMatrixSolved())
            $this->iterate();
        
        $this->maximizeObjective();
        $this->getResultsFromMatrix();
        return $this->results;
    }
    
    public function mountMatrix()
    {
        $this->matrix = new Matrix;
        
        $rows = array_merge(array($this->objective), $this->constraints);
        // dump($rows);
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
    
    public function iterate()
    {
        list($r, $c) = $this->findPivot();
        $this->dividePivotRow($r, $c);
        $this->clearPivotCol($r, $c);
    }
    
    public function findPivot()
    {
        $pc = $this->findPivotCol();
        $pr = $this->findPivotRow($pc);
        
        if($pr === null or $pc === null)
            throw new \RuntimeException('Pivot not found');
        
        if($this->debug)
            echo 'PIVOT: r'.($pr+1).', c'.($pc+1).' ('.$this->matrix->get($pr, $pc).')<br>';
        
        return array($pr, $pc);
    }
    
    public function findPivotCol()
    {
        $firstRow = $this->matrix->getRow(0);
        $pc = null;
        $pval = 0;
        $firstRow->each(function($x, $r, $c) use(&$pc, &$pval)
        {
            if($x < $pval)
            {
                $pval = $x;
                $pc = $c;
            }
        });
        return $pc;
    }
    
    public function findPivotRow($pc)
    {
        $constraints = $this->matrix->getSubMatrix(1, 0, $this->matrix->getRowCount()-1, $this->matrix->getColCount());
        $pr = null;
        $coeficient = null;
        $constraints->eachRow(function($row, $r) use(&$pr, &$coeficient, $pc)
        {
            $k = $row->get(0, $row->getColCount()-1);
            $x = $row->get(0, $pc);
            if($x != 0)
            {
                $newCoeficient = $k/$x;
                if($coeficient === null or $newCoeficient < $coeficient)
                {
                    $coeficient = $newCoeficient;
                    $pr = $r+1;
                }
            }
        });
        return $pr;
    }
    
    /*
        Divide pivot row by the coeficient of the pivot element. This will
        make the pivot coeficient become 1.
    */
    public function dividePivotRow($pr, $pc)
    {
        
        $pivot = $this->matrix->get($pr, $pc);
        $row = $this->matrix->getRow($pr);
        $row->multiply(1/$pivot);
        $this->matrix->setRow($pr, $row);
        
        if($this->debug)
        {
            echo 'DIVIDING PIVOT ROW<br>';
            $this->matrix->printTable();
        }
    }
    
    /*
        Use multiples of the pivot row to add to the other rows, in order to
        make all elements in the pivot column become 0 (except the pivot
        itself).
    */
    public function clearPivotCol($pr, $pc)
    {
        $prow = $this->matrix->getRow($pr);
        $this->matrix->eachRow(function($row, $r) use($prow, $pr, $pc)
        {
            if($r == $pr)
                return;
            
            $tmprow = clone $prow;
            $tmprow->multiply((-1)*$row->get(0, $pc));
            $row->add($tmprow);
            return $row;
        });
        
        if($this->debug)
        {
            echo 'CLEARING PIVOT COL<br>';
            $this->matrix->printTable();
        }
    }
    
    /*
        The matrix is solved when there is no negative values in the first
        row.
    */
    public function isMatrixSolved()
    {
        $firstRow = $this->matrix->getRow(0);
        $solved = true;
        $firstRow->each(function($x) use(&$solved)
        {
            if($x < 0)
                $solved = false;
        });
        return $solved;
    }
    
    /*
        After all the iterations, all variables that have a coeficient > 0 in
        the first row are considered 0, except the objective and the last
        column (to maximize the objective), so all elements in those columns
        become 0.
    */
    public function maximizeObjective()
    {
        $firstRow = $this->matrix->getRow(0);
        $lastCol = $firstRow->getColCount()-1;
        $zeroCols = array();
        $firstRow->each(function($x, $r, $c) use(&$zeroCols, $lastCol)
        {
            if($c != 0 and $c != $lastCol and $x > 0)
                $zeroCols[] = $c;
        });
        $this->matrix->eachCol(function($col, $c) use($zeroCols)
        {
            if(in_array($c, $zeroCols))
            {
                $col->multiply(0);
                return $col;
            }
        });
        
        if($this->debug)
        {
            echo 'MAXIMIZING<br>';
            $this->matrix->printTable();
        }
    }
    
    /*
        At this point, each row has only one variable, with the respective
        value on the last column.
    */
    public function getResultsFromMatrix()
    {
        $rescol = $this->matrix->getCol($this->matrix->getColCount()-1);
        $results = &$this->results;
        foreach($this->vars as $i=>$v)
        {
            $varcol = $this->matrix->getCol($i);
            $varcol->each(function($x, $r, $c) use($rescol, $v, &$results)
            {
                if($x == 1 and strpos($v, 'slack') !== 0)
                    $results[$v] = $rescol->get($r, 0);
            });
            if( ! isset($results[$v]) and strpos($v, 'slack') !== 0)
                $results[$v] = 0;
        }
        
        if($this->debug)
            foreach($this->results as $k=>$v)
                echo $k.': '.$v.'<br>';
    }
    
    public function printEquations()
    {
        $equations = array_merge(array($this->objective), $this->constraints);
        $str = '';
        foreach($equations as $eq)
            echo $this->makeEquationString($eq['vars'], $eq['equals']).'<br>';
    }

    public function makeEquationString(array $vars, $equals)
    {
        $eq = '';
        foreach($vars as $k=>$v)
        {
            $n = (abs($v) > 1) ? abs($v) : '';
            $s = ($v < 0) ? '-' : '+';
            $eq .= $s.' '.$n.$k.' ';
        }
        $eq = substr($eq, 0, -1);
        if(strpos($eq, '+') === 0)
            $eq = substr($eq, 1);
        $eq .= ' = '.$equals;
        return trim($eq);
    }
}