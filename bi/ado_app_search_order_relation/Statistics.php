<?php

class Statistics{
    private $tffDB;
    private $appDB;
    private $resultFile;

    public function __construct($resultFile){
        $this->resultFile = $resultFile;
        //tffdb 
        $this->tffDB = new mysqli("toursforfunread.mysql.db.ctripcorp.com","uws_tours4fun_r","hslxk96rvardr[idLvjZ","tffdb",55944);
        $this->tffDB->query("set names utf8");
        //app
        $this->appDB = new mysqli("tffappread.mysql.db.ctripcorp.com","uws_tffapp_r","Vkq4vzwwtzp{hr8Rkffk","tffappdb",55944);
        $this->appDB->query("set names utf8");
    }

    /**
     * @brief  process 
     * @return  bool
     **/
    public function getSameOrder($startDate,$endDate){
        for($date=$startDate;$date<=$endDate;$date=$date){
            $dateArr = array(
                        "date" => $date,
                        "num" => 0,
                        "product_list" => array()
                    );
            echo $date."\n";
            $searchProductIdArr = $this->getSearchProductIdArr($date);
            $orderProductIdArr = $this->getOrderProductIdArr($date);
            foreach($orderProductIdArr as $productId => $num){
                $one = array();
                if(isset($searchProductIdArr[$productId])){
                    $dateArr["num"] +=$num;
                    $one["product_id"] = $productId; 
                    $one["num"] = $num; 
                    $dateArr["product_list"][] = $one;
                }
            }
            $time = strtotime($date)+86400;
            $date = date("Y-m-d",$time);
            $line = json_encode($dateArr,JSON_UNESCAPED_UNICODE);
            //var_dump($searchProductIdArr,$orderProductIdArr,$line);exit;
            file_put_contents($this->resultFile,$line."\n",FILE_APPEND);
        }
        return true;
    }

    /**
     * @brief   get product id
     **/
    private function getSearchProductIdArr($date){
        $productIdArr = array();
        $sql = "select word,value from app_search_statistic 
                where type=\"id\" and
                created_at like \"{$date}%\"
                ";
        $queryResult = $this->appDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $productId="";
            $word = $query["word"];
            $value = $query["value"];
            if(!empty($word)){
                $productId=trim($word);
            }elseif(!empty($value)){
                $valueArr = json_decode($value,true);
                if(isset($valueArr["product_id"]) && !empty($valueArr["product_id"])){
                    $productId=trim($valueArr["product_id"]);
                }
            }  
            if(!empty($productId)){
                if(isset($productIdArr[$productId])){
                    $productIdArr[$productId] +=1;
                }else{
                    $productIdArr[$productId] =1;
                }
            } 
        }
        return $productIdArr;
    }

    private function getOrderProductIdArr($date){
        $productIdArr = array();
        $sql = "select op.product_id from `order` as o 
                join order_product as op on o.order_id=op.order_id 
                where o.phonebooking in(5,7)
                and o.created like \"{$date}%\"
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $productId=isset($query["product_id"])? $query["product_id"] :"";
            if(!empty($productId)){
                if(isset($productIdArr[$productId])){
                    $productIdArr[$productId] +=1;
                }else{
                    $productIdArr[$productId] =1;
                }
            } 
        }
        return $productIdArr;

    }
       
}

