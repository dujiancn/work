<?php
require_once dirname(__FILE__)."/CustomerDataMining.php";
$resultFile = dirname(__FILE__)."/data/result.txt";

$customerObj = new CustomerDataMining();
$customerObj->get($resultFile);
