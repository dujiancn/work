<?php

class Statistics{
    private $tffDB;
    private $resultFile;    
    private $payResultFile;    

    public function __construct($resultFile,$payResultFile){
        $this->resultFile = $resultFile;
        $this->payResultFile = $payResultFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   findPhone
     * @return  bool
     **/
    public function findPhone(){
        $minOrderId = 0; 
        $maxOrderId = 522159; 
        echo "the biggest order_id:{$maxOrderId}\n";
        file_put_contents($this->resultFile,"phone\n");
        file_put_contents($this->payResultFile,"phone\n");
        while($minOrderId<=$maxOrderId){
            echo "already order_id:{$minOrderId}\n";
            $limit = 1000;       
            $phoneArr = $this->getPhoneArr($minOrderId,$limit);
            $payPhoneArr = $this->getPayPhoneArr($minOrderId,$limit);
            $minOrderId +=$limit;
            foreach($phoneArr as $phone){
                file_put_contents($this->resultFile,$phone."\n",FILE_APPEND);
            } 
            foreach($payPhoneArr as $payPhone){
                file_put_contents($this->payResultFile,$payPhone."\n",FILE_APPEND);
            } 
        }
        return true;
    }

    /**
     * @brief   getRepeatOrderIdArr
     * @brief   orderId
     * @return  array
     **/  
    private function getPhoneArr($minOrderId,$limit=1000){
		$phoneArr = array();
        $maxOrderId = $minOrderId+$limit;
        $sql = "SELECT guest_email from order_product_eticket where order_id>={$minOrderId} and order_id<{$maxOrderId}";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $guestEmail = $query["guest_email"];
            $phones = $this->parseGuestPhone($guestEmail);
            if(!empty($phones)){
                $phoneArr = array_merge($phoneArr,$phones);
            }
        }
        $phoneArr = array_unique($phoneArr);
        return $phoneArr;
    }
    
    /**
     * @brief   getPayPhoneArr
     * @brief   minorderId
     * @return  array
     **/  
    private function getPayPhoneArr($minOrderId,$limit=1000){
		$phoneArr = array();
        $maxOrderId = $minOrderId+$limit;
        $sql = "SELECT ope.guest_email from order_product_eticket as ope left join `order` as o on ope.order_id=o.order_id where o.order_id>={$minOrderId} and o.order_id<{$maxOrderId} and o.status in (100002,100006,100009,100012,100023,100036,100040,100062,100078)";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $guestEmail = $query["guest_email"];
            $phones = $this->parseGuestPhone($guestEmail);
            if(!empty($phones)){
                $phoneArr = array_merge($phoneArr,$phones);
            }
        }
        $phoneArr = array_unique($phoneArr);
        return $phoneArr;
    }
    
    /**
     * @brief   parse guest phone
     * @param   $guestEmail
     * @return  array
     **/
    private function parseGuestPhone($guestEmail){    
        $phones = array();
        $guestemails_array = explode('<::>',$guestEmail);
        foreach ($guestemails_array as $gkey=>$gval) {
            if (trim($gval) != '') {
                $guest_full_email = explode('|##|',$gval);
                $phones[] = isset($guest_full_email[2]) ? strtolower($guest_full_email[2]) : ""; 
                break;
            }    
        }    
        return $phones; 
    }
}



