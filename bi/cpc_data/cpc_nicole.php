<?php
require_once dirname(__FILE__)."/Statistics.php";
$dirPath = dirname(__FILE__)."/data/";
$resultFile = $dirPath."result.txt";
$startDate = "2016-06-01";
$endDate = "2016-07-01";

$statObj = new Statistics($resultFile);
$statObj->getCpcData($startDate,$endDate);
