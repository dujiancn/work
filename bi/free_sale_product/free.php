<?php
    require_once dirname(__FILE__)."/Statistics.php";
    $dirPath = dirname(__FILE__)."/data/";
    $detailFile = $dirPath."detail.html";

    $statisticsObj = new Statistics($detailFile);
    //get free sale id
    $statisticsObj->getFreeProduct();
