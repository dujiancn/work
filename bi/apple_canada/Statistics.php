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
    public function getCanada(){
        $titleArr = array("product_id","product_name","adult_cost","child_cost","adult_retail","child_retail");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $productIdArr = $this->getProductIdArr();
        var_dump($productIdArr);exit;
        foreach($productIdArr as $one){
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getProductIdArr(){
        $resultArr = array();
        $sql = "select product_id,product_entity_type
                from product
                where region_id in (2017,2122,14) 
                and (active=1 and is_tff=1) 
                and product_entity_type in (0,3)";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["product_id"]) && !empty($query["product_id"])){
                $resultArr[$query["product_id"]] = $query;
            }
        }
        foreach($resultArr as $productId => $result){
            $productName = "null";
            $sql = "select name as product_name 
                    from product_description 
                    where product_id={$productId} and language_id=3";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["product_name"])){
                    $oneNameArr = explode("\n",$query["product_name"]);
                    $productName = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
                }
            }
            $resultArr[$productId]["product_name"] = $productName; 
            $productEntityType=$result["product_entity_type"];
            unset($resultArr[$productId]["product_entity_type"]);
            if(3==$productEntityType){
                $sql = "select price_single_cost as adult_cost,price_kids_cost as child_cost,
                        price_single as adult_price, price_kids as child_price
                        from hotel
                        where product_id={$productId}";
            }else{
                $sql = "select price_adult_cost as adult_cost,price_kids_cost as child_cost,
                        price_adult as adult_price, price_kids as child_price
                        from product_one_day
                        where product_id={$productId}";
            }
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["adult_cost"])){
                    $resultArr[$productId] = array_merge($resultArr["product_id"],$query); 
                }
            }
        }
        return $resultArr;
    }

       
}

