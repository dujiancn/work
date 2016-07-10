<?php
    $dirPath = dirname(__FILE__)."/data/"; 
    $originFile = $dirPath."result.txt";
    if(!file_exists($originFile)){
        exit("file {$originFile} not existed!");
    }
    $originArr = file($originFile,FILE_IGNORE_NEW_LINES);
    
    //拆分数据，分别进行统计
    $nationArr = array();  
    $ageArr = array();//age range 0:<=18, 1:19~28, 2:29~38, 3:39~48, 4:49~58, 5:>59, unknown 
    $genderArr = array();  
    $mixArr = array();
    $nationFile = $dirPath."nation.txt";
    $ageFile = $dirPath."age.txt";
    $genderFile = $dirPath."gender.txt";
    $mixFile = $dirPath."mix.txt";
    system("rm {$nationFile}");system("rm {$ageFile}");system("rm {$genderFile}");system("rm {$mixFile}");
    foreach($originArr as $originLine){
        $array = explode("|",$originLine); 
        foreach($array as &$one){
            $one = trim($one);
        }
        $price = $array[1]; 
        $age = !empty($array[2]) ? $array[2] : "unknown";
        if($age != "unknown"){
            if($age<=18){
                $age = "<=18";
            }elseif($age>=19 && $age<=28){
                $age = "19~28";
            }elseif($age>=29 && $age<=38){
                $age = "29~38";
            }elseif($age>=39 && $age<=48){
                $age = "39~48";
            }elseif($age>=49 && $age<=58){
                $age = "49~58";
            }else{
                $age = ">=59";
            }
        }
        $nation = !empty($array[5]) ? $array[5] : "unknown";
        $gender = !empty($array[3]) ? $array[3] : "unknown";
        //国家级别的统计
        if(isset($nationArr[$nation])){
            $nationArr[$nation]["num"] +=1;
            $nationArr[$nation]["price"] +=$price;
        }else{
            $nationArr[$nation]["nation"] = $nation;
            $nationArr[$nation]["num"] =1;
            $nationArr[$nation]["price"] =$price;
        } 
        //年龄级别的统计
        if(isset($ageArr[$age])){
            $ageArr[$age]["num"] +=1;
            $ageArr[$age]["price"] +=$price;
        }else{
            $ageArr[$age]["age"] = $age;
            $ageArr[$age]["num"] =1;
            $ageArr[$age]["price"] =$price;
        } 
        //性别级别的统计
        if(isset($genderArr[$gender])){
            $genderArr[$gender]["num"] +=1;
            $genderArr[$gender]["price"] +=$price;
        }else{
            $genderArr[$gender]["gender"] =$gender;
            $genderArr[$gender]["num"] =1;
            $genderArr[$gender]["price"] =$price;
        }

        //综合数据的统计
        if(isset($mixArr[$nation]["age"][$age])){
            $mixArr[$nation]["age"][$age]["num"] += 1;
            $mixArr[$nation]["age"][$age]["price"] += $price;
        }else{
            $mixArr[$nation]["age"][$age]["age"] = $age;
            $mixArr[$nation]["age"][$age]["num"] = 1;
            $mixArr[$nation]["age"][$age]["price"] = $price;
        }
        if(isset($mixArr[$nation]["gender"][$gender])){
            $mixArr[$nation]["gender"][$gender]["num"] += 1;
            $mixArr[$nation]["gender"][$gender]["price"] += $price;
        }else{
            $mixArr[$nation]["gender"][$gender]["gender"] = $gender;
            $mixArr[$nation]["gender"][$gender]["num"] = 1;
            $mixArr[$nation]["gender"][$gender]["price"] = $price;
        }
         
    }
    //写入文件
    foreach($nationArr as $one){
        $one["price"] = round($one["price"]/$one["num"],2);
        $line = implode("\t\t",$one)."\n";
        file_put_contents($nationFile,$line,FILE_APPEND);
    }  
    foreach($ageArr as $one){
        $one["price"] = round($one["price"]/$one["num"],2);
        $line = implode("\t\t",$one)."\n";
        file_put_contents($ageFile,$line,FILE_APPEND);
    }  
    foreach($genderArr as $one){
        $one["price"] = round($one["price"]/$one["num"],2);
        $line = implode("\t\t",$one)."\n";
        file_put_contents($genderFile,$line,FILE_APPEND);
    }
    foreach($mixArr as $nation => $mix){
        $line = "[{$nation}]\n";
        file_put_contents($mixFile,$line,FILE_APPEND);
        $mixAgeArr = $mix["age"]; 
        $mixGenderArr = $mix["gender"]; 
        $line = "(age)\n";
        file_put_contents($mixFile,$line,FILE_APPEND);
        foreach($mixAgeArr as $age => $one){
            $one["price"] = round($one["price"]/$one["num"],2);
            $line = implode("\t\t",$one)."\n";
            file_put_contents($mixFile,$line,FILE_APPEND);
        }
        $line = "(gender)\n";
        file_put_contents($mixFile,$line,FILE_APPEND);
        foreach($mixGenderArr as $gender => $one){
            $one["price"] = round($one["price"]/$one["num"],2);
            $line = implode("\t\t",$one)."\n";
            file_put_contents($mixFile,$line,FILE_APPEND);
        }
        $line = "====================================\n";
        file_put_contents($mixFile,$line,FILE_APPEND);
    }