<?php
require_once dirname(__FILE__)."/Statistics.php";
$dirPath = dirname(__FILE__)."/data/";
$resultFile = $dirPath."result.txt";
$startDate = "2016-07-20";
$endDate = "2016-08-01";

$statObj = new Statistics($resultFile);
$statObj->getRnvData($startDate,$endDate);
