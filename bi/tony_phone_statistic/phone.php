<?php
    require_once dirname(__FILE__)."/Statistics.php";
    $dirPath = dirname(__FILE__)."/data/";
    $resultFile = "{$dirPath}result.txt";
    $payResultFile = "{$dirPath}pay_result.txt";
    $statObj = new Statistics($resultFile,$payResultFile);
    $statObj->findPhone();
