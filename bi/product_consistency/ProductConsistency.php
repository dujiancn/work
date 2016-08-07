<?php
class productConsistency{
    private $biDB;
    private $tffDB;
    private $resultFile;

    public function __construct($resultFile){
        //db 
        $this->biDB = new mysqli("192.168.100.200","root","tufeng1801","analytics_new",3306);
        $this->biDB->query("set names utf8");
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
        $this->resultFile = $resultFile;
    }

    /**
     * @brief   process product 
     **/
    public function process(){
        //get all product id
        $biProductIdArr = $this->getProductIdArr("bi");
        foreach($biProductIdArr as $biProductId){
            $detailInfo = $this->getProductInfo($biProductId,"tff");
            $updateInfo = array();
            if(!empty($detailInfo)){
                if(empty($detailInfo["active"])){
                    $updateInfo["active"] = $detailInfo["active"]; 
                }
                if(empty($detailInfo["is_tff"])){
                    $updateInfo["is_tff"] = $detailInfo["is_tff"]; 
                }
            }else{
                $updateInfo["active"] = 0;
                $updateInfo["is_tff"] = 0;
            }
            $this->updateProductInfo($biProductId,$updateInfo);
            if(!empty($updateInfo)){
                $originInfo["active"] = 1;
                $originInfo["is_tff"] = 1;
                $this->writeResult($biProductId,$originInfo,$updateInfo);
            }
        }
        //get all product id
        $tffProductIdArr = $this->getProductIdArr("tff");
        foreach($tffProductIdArr as $tffProductId){
            $detailInfo = $this->getProductInfo($tffProductId,"bi");
            $updateInfo = array();
            if(!empty($detailInfo)){
                if(empty($detailInfo["active"])){
                    $updateInfo["active"] = 1; 
                }
                if(empty($detailInfo["is_tff"])){
                    $updateInfo["is_tff"] = 1; 
                }
            }
            $this->updateProductInfo($tffProductId,$updateInfo);
            if(!empty($updateInfo)){
                $this->writeResult($tffProductId,$detailInfo,$updateInfo);
            }
        }

        return true;
    }
  
    /**
     * @brief   write the result
     **/
    private function writeResult($productId,$originInfo,$updateInfo){
        $keyArr = array_keys($updateInfo);
        $changeArr = array();
        foreach($keyArr as $k){
            $f = isset($originInfo[$k]) ? $originInfo[$k] : null;
            $t = isset($updateInfo[$k]) ? $updateInfo[$k] : null;
            if($f!==$t){
                $one["k"] = $k;
                $one["f"] = $f;
                $one["t"] = $t;
                $changeArr[] = $one;
            } 
        }
        $line = "$productId | ".json_encode($changeArr);
        file_put_contents($this->resultFile,$line."\n",FILE_APPEND); 
    }
 
    /**
     * @brief   get product detail info
     **/
    private function getProductInfo($productId,$db="tff"){
        $productInfo = array();
        $sql = "select product_id,active,is_tff from product where product_id={$productId}";
        if("bi"==$db){
            $queryResult = $this->biDB->query($sql);
        }else{
            $queryResult = $this->tffDB->query($sql);
        }
        while($query = mysqli_fetch_assoc($queryResult)){
            $productInfo = $query;
        }
        return $productInfo;
    }
 
    /**
     * @brief   get product_id
     * @return  array
     **/
    private function getProductIdArr($db="bi"){
        $productIdArr = array();
        $sql = "select product_id from product where active=1 and is_tff=1 order by product_id";
        if("bi"==$db){
            $queryResult = $this->biDB->query($sql);
        }else{
            $queryResult = $this->tffDB->query($sql);
        }
        while($query = mysqli_fetch_assoc($queryResult)){
            $productIdArr[] = $query["product_id"];
        }
        return $productIdArr;
    }

    /**
     * @brief   update bi product Info
     **/
    private function updateProductInfo($productId,$updateInfo){
        if(!empty($updateInfo)){
            $updateLineArr = array();
            foreach($updateInfo as $key => $value){
                $line = "{$key}=\"{$value}\"";
                $updateLineArr[] = $line;
            }
            $updateLine = implode(",",$updateLineArr);
            $sql = "update product set $updateLine where product_id={$productId}";
            $this->biDB->query($sql);
        }  
    }

    /**
     * @breif   diff product info
     **/
    private function diffProduct($productInfo){
        $updateInfo = array();
    }

}
