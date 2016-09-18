<?php
require_once dirname(__FILE__)."/Statistics.php";

$dataPath = dirname(__FILE__)."/data/";
$dataFile = $dataPath."product_id.txt";
$resultFile = $dataPath."result.txt";
$statObj = new Statistics($dataFile, $resultFile);
$statObj->process();


?>
