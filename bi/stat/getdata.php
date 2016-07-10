<?php
require_once(dirname(__FILE__)."/Statistics.php");
$originFile = dirname(__FILE__)."/data/format.txt";
$resultFile = dirname(__FILE__)."/data/result.txt";

if(!file_exists($originFile)){
    exit("{$originFile} not exits!");
}
if(file_exists($resultFile)){
    system("rm $resultFile");
}
$statObj = new Statistics($originFile,$resultFile);
$statObj->getFullData();
exit;

$originArr = file($originFile,FILE_IGNORE_NEW_LINES);
$customerArr = array();
foreach($originArr as $one){
    $oneArr = explode("|",$one); 
    $customerId = trim($oneArr[0]);
    $price = trim($oneArr[1]);
    if(!empty($customerId) && !empty($price)){
        $customerArr[$customerId]["price"] = $price;
    }
    unset($one);
}
//start get the full data
foreach($customerArr as $customerId => $price){
}


