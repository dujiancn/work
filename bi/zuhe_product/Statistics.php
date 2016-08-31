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
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   find zuhe file and zuhe child file
     * @return  bool
     **/
    public function findZuheProduct(){
        $productIdArr = $this->getProductIdArr();
        $zuheTitleArr = array("product_id","subtour_code","subtour_code note","subtour_code duration","min_guest_num","max_guest_num","created");
        $zuheChildTitleArr = array("parent_product_id","product_id","min_guest_num","max_guest_num","upgrade","departure");
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
                //获取组合子产品的详细信息
                $line = "{$productId}\t";
                foreach($subIdArr as $subId){
                    $childProductInfo = $this->getChildProductInfo($subId);
                    $line .= implode("\t",$childProductInfo);
                    file_put_contents($this->zuheChildFile,$line."\n",FILE_APPEND);
                    $line = "\t";
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
        $sql = "select product_id,subcode,subcode_note,subcode_duration,min_num_guest,max_num_guest,created
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
            $upgradesArr = array();
            $upgradesInfo = "";
            $sql = "select pa.product_option_id,pa.product_option_value_id, pod.name as pod_name, povd.name as povd_name from product_attribute as pa,product_option_description as pod,product_option_value_description as povd where pa.product_option_id=pod.product_option_id and pa.product_option_value_id=povd.product_option_value_id and pa.product_id={$productId} and pod.language_id=3 and povd.language_id=3 group by pa.product_option_id,pa.product_option_value_id;";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $upgradesArr[] = $query; 
            }
            $upgradesInfo = json_encode($upgradesArr,JSON_UNESCAPED_UNICODE);
            $productInfo["upgrade"] = $upgradesInfo;
            unset($query,$queryResult);
            
            $departuresArr = array();
            $departuresInfo = "";
            $sql = "select pd.time,pdd.region,pdd.address,pdd.full_address from product_departure as pd join product_departure_description as pdd where pd.product_id={$productId} and pd.product_departure_id=pdd.product_departure_id and pdd.language_id=3 order by pd.time;";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $departuresArr[] = $query; 
            }
            $departuresInfo = json_encode($departuresArr,JSON_UNESCAPED_UNICODE);
            $productInfo["departure"] = $departuresInfo;
            
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

