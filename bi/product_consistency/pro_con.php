<?php

require_once dirname(__FILE__)."/ProductConsistency.php";
$resultFile = dirname(__FILE__)."/data/result.txt";
$consisObj = new ProductConsistency($resultFile);
$consisObj->process();
