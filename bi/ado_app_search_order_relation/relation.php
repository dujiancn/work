<?php
$path = dirname(__FILE__)."/";
require_once($path."Statistics.php");
$resultFile = $path."data/result.txt";
$stat = new Statistics($resultFile);
$startDate = "2016-07-13";
$endDate = "2016-08-12";
$stat->getSameOrder($startDate,$endDate);


