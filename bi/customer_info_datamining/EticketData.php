<?php

class EticketData{
    private $orderId;
    private $tffDB;
    private $guestNameList;
    private $guestDobList;
    private $guestEmailList;
    private $guestNationList;
    private $guestGenderList;

    public function __construct($orderId){
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
        $this->orderId = $orderId;
        $this->guestNameList = array();
        $this->guestDobList = array();
        $this->guestEmailList = array();
        $this->guestNationList = array();
        $this->guestGenderList = array();
        $this->__init($orderId);
    }
    
    public function getNameList(){
        return $this->guestNameList;
    }
    
    public function getDobList(){
        return $this->guestDobList;
    }
    
    public function getEmailList(){
        return $this->guestEmailList;
    }
    
    public function getNationList(){
        return $this->guestNationList;
    }
    
    public function getGenderList(){
        return $this->guestGenderList;
    }

    private function __init($orderId){
        $sql = "select guest_name,guest_email,guest_gender from order_product_eticket where order_id={$orderId}";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $guestNameAndDob = $this->parseNameAndDob($query["guest_name"]);
            $guestEmailAndNation = $this->parseEmailAndNation($query["guest_email"]);
            $guestGender = $this->parseGender($query["guest_gender"]);
            #$this->guestDobList[] = array_pop($guestNameAndDob["dob"]);
            $this->guestDobList[] = $guestNameAndDob["dob"];unset($guestNameAndDob["dob"]);
            $this->guestNameList[] = $guestNameAndDob;
            $this->guestEmailList[] = $guestEmailAndNation["email"];
            $this->guestNationList[] = $guestEmailAndNation["country_id"];
            $this->guestGenderList[] = $guestGender;
        } 
    }
    
    /**
     * @brief   解析第一个出行人的名称
     **/
    private function parseNameAndDob($guestName){
        $guest_info_array = array("name"=>"","first"=>"","last"=>"","dob"=>"");
        $guestnames_array = explode('<::>',$guestName);
        if (is_array($guestnames_array) && count($guestnames_array)>0) {
            $gval = $guestnames_array[0];
            if (trim($gval) != '') {
                $guest_full_name = explode('||',$gval);
                //name解析
                if(isset($guest_full_name[0]) && !empty($guest_full_name[0])){
                    $name = $guest_full_name[0];
                    if(ctype_alpha(str_replace(" ","",$name))){
                        $guest_info_array["name"] = $name;
                    }
                    if(!empty($guest_info_array["name"])){
                        $splitRes = $this->splitName($guest_info_array["name"]);
                        $guest_info_array = array_merge($guest_info_array,$splitRes); 
                    }
                }
                //dob
                if(isset($guest_full_name[1]) && !empty($guest_full_name[1])){
                    $guest_info_array["dob"] = $guest_full_name[1];
                }
            }    
        }    
        return $guest_info_array;
    }
    
    /**
     * @brief   解析email
     **/
    private function parseEmailAndNation($guestEmail){
        $guest_info_array = array("email"=>"","country_id"=>"");
        $guestemails_array = explode('<::>',$guestEmail);
        if(!empty($guestemails_array)){
            $gval = $guestemails_array[0];
            if (trim($gval) != '') {
                $guest_full_email = explode('|##|',$gval);
                if(isset($guest_full_email[0]) &&!empty($guest_full_email[0])){
                    $guest_info_array['email'] = $guest_full_email[0];
                }
                if(isset($guest_full_email[1]) &&!empty($guest_full_email[1]) && ctype_digit($guest_full_email[1]) && $guest_full_email[1]<1000 && $guest_full_email[1]>0){
                    $guest_info_array['country_id'] = $guest_full_email[1];
                }
            }
        }
        return $guest_info_array;
    }
    
    /**
     * @brief   解析性别
     **/
    private function parseGender($guestGender){
        $gender = "";
        $guestgender_array = explode('<::>',$guestGender);
        if(!empty($guestgender_array)){
            $gender = $guestgender_array[0];
            $maleArr = array("男","Male","male");
            $femaleArr = array("女","Female","female");
            if(in_array($gender,$maleArr)){
                $gender = "m";
            }elseif(in_array($gender,$femaleArr)){
                $gender = "f";
            }
        }
        return $gender;
    }

    /** 
     * @brief   解析名字，变成first/last
     * @param   $name
     **/
    private function splitName($name){
        $result = array("first" => "", "last" => "");
        $nameArr = explode(" ",$name);
        $result["last"] = array_pop($nameArr);
        $result["first"] = implode(" ",$nameArr);
        return $result;
    }
}
