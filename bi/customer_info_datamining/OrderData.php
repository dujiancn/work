<?php

class OrderData{
    private $customerId;
    private $tffDB;
    private $orderIdList;
    private $nameList;
    private $emailList;
    private $nationList;

    public function __construct($customerId){
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
        $this->customerId = $customerId;
        $this->orderIdList = array();
        $this->nameList = array();
        $this->emailList = array();
        $this->nationList = array();
        $this->__init($customerId);
    }
    
    public function getOrderIdList(){
        return $this->orderIdList;
    }
    
    public function getNameList(){
        return $this->nameList;
    }
    
    public function getEmailList(){
        return $this->emailList;
    }
    
    public function getNationList(){
        return $this->nationList;
    }

    private function __init($customerId){
        $sql = "select order_id,customer_name,customer_email,customer_country from `order` where customer_id={$customerId} order by order_id desc";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $this->orderIdList[] = $query["order_id"];
            $nameArr = array("name"=>"","first"=>"","last"=>"");
            if(!empty($query["customer_name"])){
                $customerName = $query["customer_name"];
                $nameArr = array_merge($nameArr,$this->splitName($customerName));
                $nameArr["name"] = $customerName;
            } 
            $this->nameList[] = $nameArr; 
            $this->emailList[] = $query["customer_email"]; 
            $this->nationList[] = $query["customer_country"];
        }
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
