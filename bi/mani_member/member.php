<?php

    $path=dirname(__FILE__)."/";
    require_once($path."Statistics.php");
    $memberLevelArr = array("platinum","diamond");

    foreach($memberLevelArr as $memberLevel){
        $resultFile = "{$path}data/{$memberLevel}.txt";
        $memObj = new Statistics($memberLevel,$resultFile);
        $memObj->getOrder();
        print $resultFile;
    }
