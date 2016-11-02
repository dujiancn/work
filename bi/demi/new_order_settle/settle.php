<?php

    $path=dirname(__FILE__)."/";
    require_once($path."Statistics.php");
    $resultFile = $path."data/result.txt";

    $startDate = "2016-09-01";
    $endDate = "2016-11-01";

    $hawaii = new Statistics($resultFile,$startDate,$endDate);
    $hawaii->getSettlement();
