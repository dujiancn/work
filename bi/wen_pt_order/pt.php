<?php

    $path=dirname(__FILE__)."/";
    require_once($path."Statistics.php");
    $resultFile = $path."data/result.txt";
    
    $obj = new Statistics($resultFile,"2014-07-01","2016-12-31");
    $obj->getData();
