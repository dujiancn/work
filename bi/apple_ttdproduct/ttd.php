<?php

$dir = dirname(__FILE__)."/";
require_once($dir."Statistics.php");
$sourceFile = $dir."data/a.txt";
$resultFile = $dir."data/result.txt";

$stat = new Statistics($sourceFile,$resultFile);
$stat->getProduct();
