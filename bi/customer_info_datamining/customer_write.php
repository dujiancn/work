<?php
require_once dirname(__FILE__)."/CustomerDataMining.php";
$dirPath = dirname(__FILE__)."/data/temp/";
$writeCustomerFile = $dirPath."customer_write_result.txt";
$writeStatisticsFile = $dirPath."customer_write_statistics.txt";
$customerFileArr = array();
foreach(range("a","z") as $name){
    $customerFileArr[] = $dirPath."customera".$name;
    $writeCustomerFileArr[] = $dirPath."customera".$name."_result.txt";
}
$customerObj = new CustomerDataMining();
foreach($customerFileArr as $key => $customerFile){
    $customerObj->write($customerFile,$writeCustomerFile,$writeStatisticsFile);
    echo "write {$customerFile} finish\n";
}
