<?php

use Arsenal\Database\Database;
use Arsenal\Loggers\JsConsoleLogger;
use Arsenal\Loggers\HtmlLogger;
use Arsenal\Misc\Benchmark;
use Arsenal\Http\Request;
use Arsenal\Http\Response;
use Arsenal\Http\PathPattern;
use Arsenal\Http\RequestHandler;
use Arsenal\Http\RequestCallback;
use Arsenal\Math\Simplex;
use Arsenal\Math\Matrix;

use Arsenal\Misc\Foo;
use Arsenal\Misc\Bar;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;




$sx = new Simplex;
$sx->setDebug(true);
$sx->maximize('profit = 2x + 3y + 4z');
$sx->addConstraint('3x + 2y +z <= 10');
$sx->addConstraint('2x + 5y + 3z <= 15');
$results = $sx->solve();





// $sx = new Simplex;
// $sx->setDebug(true);
// // $sx->maximize('profit = 2x + 5y');
// $sx->maximize('production = 4x + 2y');
// $sx->addConstraint('x <= 4');
// $sx->addConstraint('2y <= 12');
// $sx->addConstraint('3x + 2y <= 18');
// // $results = $sx->solve();

// $sx->addConstraint('2x + 5y = 34'); // profit
// $sx->solve();





// $sx = new Simplex;

// $sx->minimize('custo = 5.5 * q11 + 4.5 * q12 + 9.9 * q13 + 2.7 * q14 + 6.4 * q21 + 2.5 * q22 + 3.3 * q23 + 4.2 * q24 + 2.5 * q31 + 4.9 * q32 + 4.6 * q33 + 4.7 * q34');

// $sx->addConstraint('q11 + q12 + q13 = 4');
// $sx->addConstraint('q21 + q22 + q23 = 7');
// $sx->addConstraint('q31 + q32 + q33 = 5');
// $sx->addConstraint('q41 + q42 + q43 = 12');

// $sx->addConstraint('q11 + q21 + q31 + q41 <= 10');
// $sx->addConstraint('q12 + q22 + q32 + q42 <= 9');
// $sx->addConstraint('q13 + q23 + q33 + q43 <= 9');

// $results = $sx->solve();