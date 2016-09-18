<?php

class Statistics{
    private $tffDB;
    private $resultFile;    

    public function __construct($resultFile){
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   find result file and result child file
     * @return  bool
     **/
    public function getProductIdName(){
        $titleArr = array("product_id","product_name");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $productArr = $this->getProductArr();
        foreach($productArr as $one){
            //补充产品信息
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getProductArr(){
        $startDate = "2010-01-01";
        $resultArr = array();
        $productIdArr = array();
        //get all active product_id
        $sql = "select product_id from product where active=1 and is_tff=1";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["product_id"]) && !empty($query["product_id"])){
                $productIdArr[] = $query["product_id"];
            }
        }
        //get the order num of the specified product_id
        foreach($productIdArr as $index => $productId){
            echo "deal product_id {$productId}\n";
            $sql = "select count(*) as num from `order_product` where product_departure_date>=\"{$startDate}\" and product_id={$productId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["num"]) && $query["num"]>0){
                    unset($productIdArr[$index]);
                }
            }
        }
        //get the not product id and name
        foreach($productIdArr as $productId){
            $one = array("product_id" => $productId);
            $sql = "select name from product_description where product_id={$productId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["name"])){
                    $oneNameArr = explode("\n",$query["name"]);
                    $one["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
                }
            }
            $resultArr[] = $one;
        } 
        return $resultArr;
    }
       
}

