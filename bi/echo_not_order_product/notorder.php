<?php

    $path=dirname(__FILE__)."/";
    require_once($path."Statistics.php");
    $resultFile = $path."data/result.txt";
    
    $hawaii = new Statistics($resultFile);
    $hawaii->getProductIdName();
