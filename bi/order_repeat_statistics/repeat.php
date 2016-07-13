<?php
    require_once dirname(__FILE__)."/Statistics.php";
    $dirPath = dirname(__FILE__)."/data/";
    //预定平台
    $phoneBookingArr = array(
    //        0 => 'website',
            1 => 'telephone',
            2 => 'appT4F',
            5 => 'msiteTFF',            
            6 => 'OPENAPI',
            7 => 'APP'
    );
    
    foreach($phoneBookingArr as $phonebooking => $name){
        $repeatFile = "{$dirPath}{$name}_orderid.txt";
        $resultFile = "{$dirPath}{$name}.txt";
        $statObj = new Statistics($phonebooking,$repeatFile,$resultFile);
        $statObj->findRepeatOrder();
    }
