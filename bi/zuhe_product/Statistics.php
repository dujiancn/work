<?php

class Statistics{
    private $tffDB;
    private $zuheFile;    
    private $zuheChildFile;    

    public function __construct($zuheFile,$zuheChildFile){
        $this->zuheFile = $zuheFile;
        $this->zuheChildFile = $zuheChildFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
    }

    /**
     * @brief   find zuhe file and zuhe child file
     * @return  bool
     **/
    public function findZuheProduct(){
        $productIdArr = $this->getProductIdArr();
        $zuheTitleArr = array("product_id","subtour_code","subtour_code note","min_guest_num","max_guest_num","created");
        $zuheChildTitleArr = array("parent_product_id","product_id","subtour_code","subtour_code note","min_guest_num","max_guest_num","created");
        $line = implode("\t",$zuheTitleArr);
        file_put_contents($this->zuheFile,$line."\n");
        $line = implode("\t",$zuheChildTitleArr);
        file_put_contents($this->zuheChildFile,$line."\n");
        foreach($productIdArr as $productId => $subIdArr){
            echo "get {$productId}'s data\n";
            //获取组合产品本身的信息
            $parentProductInfo = $this->getParentProductInfo($productId);
            if(!empty($parentProductInfo)){
                $line = implode("\t",$parentProductInfo);
                file_put_contents($this->zuheFile,$line."\n",FILE_APPEND);
                //获取被组合产品的详细信息
                foreach($subIdArr as $subId){
                    $childProductInfo = $this->getChildProductInfo($productId);
                    exit;
                } 
            }
        }
        return true;
    }

    /**
     * @brief   get product id
     **/
    private function getProductIdArr(){
        $productIdArr = array();
        $sql = "select product_id,subcode from product 
                where subcode!='' 
                and active=1
                and is_tff=1
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $subIdArr = $this->parseSubcode($query["subcode"]);
            if(count($subIdArr) > 0){
                $productId = $query["product_id"];
                $productIdArr[$productId] = $subIdArr;
            }
        }
        return $productIdArr;
    }

    /**
     * @brief   get parent product info
     * @param   $productId
     **/
    private function getParentProductInfo($productId){
        $productInfo = array();
//        $sql="select pd.time,pdd.region,pdd.address,pdd.full_address,pdd.tips from product_departure as pd left join product_departure_description as pdd on pd.product_departure_id=pdd.product_departure_id where pd.product_id=102 and pdd.address!=''";
        $sql = "select product_id,subcode,subcode_note,min_num_guest,max_num_guest,created
                from `product`
                where product_id={$productId}
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $productInfo = $query;
            break;
        }
        return $productInfo;
   }    
    
    /**
     * @brief   get child product info
     * @param   $productId
     **/
    private function getChildProductInfo($productId){
        $productInfo = array();
        $sql = "select product_id,min_num_guest,max_num_guest
                from `product`
                where product_id={$productId}
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $productInfo = $query;
            unset($query,$queryResult);
            $departureInfo = "";
            $sql="select pd.time,pdd.region,pdd.address,pdd.full_address,pdd.tips from product_departure as pd left join product_departure_description as pdd on pd.product_departure_id=pdd.product_departure_id where pd.product_id=102 and pdd.address!=''";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $departureArr[] = $query; 
            }
            $departureInfo = json_encode($departureArr,JSON_UNESCAPED_UNICODE);
            unset($query,$queryResult);
            break;
        }
        return $productInfo;
    }
    
    /**
     * @brief   parse subcode
     * @param   $subcode
     **/
    private function parseSubcode($subcode){
        $subIdArr = array();
        $subArr = explode(";",$subcode);
        foreach($subArr as $one){
            if(empty($one)){
                continue;
            }
            $pattern="/[0-9]+$/";
            $matches = array();
            preg_match($pattern,$one,$matches);
            if(isset($matches[0]) && !empty($matches[0])){
                $subIdArr[] = $matches[0];
            }
        }
        return $subIdArr;
    }
       
}

