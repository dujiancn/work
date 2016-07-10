<?php

class Statistics{
    private $biDB;
    private $tffDB;
    private $originFile;    
    private $fullDataFile;    

    public function __construct($originFile,$fullDataFile){
        $this->originFile = $originFile;
        $this->fullDataFile = $fullDataFile;
        //db 
        $this->biDB = new mysqli("192.168.100.200","root","tufeng1801","analytics_2015_4_14",3306);
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
    }

    /**
     * @brief   get the full data
     * @return  full data
     **/
    public function getFullData(){
        //parse origin file
        $originArr = file($this->originFile,FILE_IGNORE_NEW_LINES);
        $customerArr = array();
        foreach($originArr as $one){
            $oneArr = explode("|",$one); 
            $customerId = trim($oneArr[0]);
            $price = trim($oneArr[1]);
            if(!empty($customerId) && !empty($price)){
                $customerArr[$customerId]["customer_id"] = $customerId;
                $customerArr[$customerId]["price"] = $price;
            }
            unset($one);
        }
        //get full data
        foreach($customerArr as $customerId => $price){
            echo "get $customerId's data\n";
            $customerArr[$customerId]["country_id"] = $this->getCountryId($customerId); 
            $customerArr[$customerId]["age"] = $this->getAge($customerId);
            $customerArr[$customerId]["gender"] = $this->getGender($customerId);
            $line = implode("\t|\t",array_values($customerArr[$customerId]))."\n";
            file_put_contents($this->fullDataFile,$line,FILE_APPEND);
        }

        return true;
    }
   
    /**
     * @brief   get country id
     * @param   $customerId
     **/
    private function getCountryId($customerId){
        $countryId = null;
        $sql = "select country_id from customer_base_statistics where customer_id={$customerId}";
        $queryResult = $this->biDB->query($sql);
        $queryResult = mysqli_fetch_assoc($queryResult); 
        if(isset($queryResult["country_id"]) && !empty($queryResult["country_id"])){
            $countryId = $queryResult["country_id"];
        }
        return $countryId;
    }
    
    /**
     * @brief   get Age
     * @param   $customerId
     **/
    private function getAge($customerId){
        $age = null;
        //from bi
        $sql = "select dob from customer_base_statistics where customer_id={$customerId}";  
        $queryResult = $this->biDB->query($sql);
        $queryResult = mysqli_fetch_assoc($queryResult); 
        if(isset($queryResult["dob"])){
            $dob = substr($queryResult["dob"],0,4);
            $age = 2017-$dob;
            if($age>100 || $age<3){
                $age = null;
            }
        }
        //from tff
        if(empty($age)){
            $sql = "select dob from customer where customer_id={$customerId}";  
            $queryResult = $this->tffDB->query($sql);
            $queryResult = mysqli_fetch_assoc($queryResult); 
            if(isset($queryResult["dob"])){
                $dob = substr($queryResult["dob"],0,4);
                $age = 2017-$dob;
                if($age>100 || $age<3){
                    $age = null;
                }
            }
        }
        return $age;
    }

    /**
     * @brief   get gender
     * @param   $customerId
     **/
    private function getGender($customerId){
        $gender = null;
        //from bi
        $sql = "select gender from customer_base_statistics where customer_id={$customerId}";  
        $queryResult = $this->biDB->query($sql);
        $queryResult = mysqli_fetch_assoc($queryResult); 
        if(isset($queryResult["gender"]) && !empty($queryResult["gender"])){
            $gender = $queryResult["gender"];
            if(!in_array($gender,array("m","f"))){
                $gender = null;
            }
        }
        //from tff
        if(empty($gender)){
            $sql = "select gender from customer where customer_id={$customerId}";  
            $queryResult = $this->tffDB->query($sql);
            $queryResult = mysqli_fetch_assoc($queryResult); 
            if(isset($queryResult["gender"]) && !empty($queryResult["gender"])){
                $gender = $queryResult["gender"];
                if(!in_array($gender,array("m","f"))){
                    $gender = null;
                }
            }
        }
        return $gender;
    }

}


