<?php
$path = dirname(__FILE__);
require_once $path."/Statistics.php";
$zuheFile = $path."/data/zuhe.txt";
$zuheChildFile = $path."/data/zuheChild.txt";
$statObj =new Statistics($zuheFile,$zuheChildFile);
$statObj->findZuheProduct();
