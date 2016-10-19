<?php

require_once dirname(__FILE__)."/Statistics.php";

$dataPath = dirname(__FILE__)."/data/";
$dateArr = array(
        0 => array("start"=>"2016-03-01","end"=>"2016-09-01"),
        //1 => array("start"=>"2016-04-01","end"=>"2016-07-01"),
    );
foreach($dateArr as $date){
    $fileName = $dataPath.$date["start"].".txt";
    $statObj = new Statistics($fileName);
    $statObj->getNewzealand($date["start"],$date["end"]);
}
