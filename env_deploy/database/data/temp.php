<?php
$variableNum = 4;
$tempFile = "./temp.txt";
//$desFile = "./variable_description_data.sql";
$desFile = "./variable_data.sql";

$lineArr = array();
$tempFileArr = file($tempFile,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$one = array();
foreach($tempFileArr as $index => $value){
    if($index%$variableNum==0){
        if(!empty($one)){
            $line = implode(",",$one); 
            $lineArr[] = "({$line})";
            $one = array();
        }
    }
    $one[] = "'$value'";
}
if(!empty($one)){
    $line = implode(",",$one); 
    $lineArr[] = "({$line})";
}
$resultStr = implode(",",$lineArr);
file_put_contents($desFile,$resultStr);

?>
