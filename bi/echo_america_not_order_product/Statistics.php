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
     * @brief   get product info
     * @return  bool
     **/
    public function getProductInfo(){
        $titleArr = array("product_id","product_name","start_city","duration","created","provider_id","provider_name","stock_status");        
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
        $sql = "select product_id from product 
                where active=1 
                and product_entity_type=1
                and is_tff=1
                and region_id in (select region_id from region where country_id in (select country_id from country where continent_id in (2,4,9)))";
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
            $one = array('product_id' => $productId,
                         'product_name' => '',
                         'start_city' => '',
                         'duration' => '',
                         'created' => '',
                         'provider_id' => '',
                         'provider_name' => '',
                         'stock_status' => 0,    
                        );
            $sql = "select p.duration, p.created, p.stock_status, pd.name as product_name, pv.provider_id, pv.name as provider_name 
                    from product as p 
                    left join product_description as pd on p.product_id=pd.product_id 
                    left join provider as pv on p.provider_id=pv.provider_id 
                    where pd.language_id=3 
                    and pv.language_id=3 
                    and p.product_id={$productId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["product_name"])){
                    $oneNameArr = explode("\n",$query['product_name']);
                    $one['product_name'] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
                }
                $one['duration'] = $query['duration'];
                $one['created'] = $query['created'];
                $one['provider_id'] = $query['provider_id'];
                $one['provider_name'] = $query['provider_name'];
                $one['stock_status'] = $query['stock_status'];
            }
            //start city
            $sql = "select group_concat(distinct(tcd.name)) as start_city 
                    from product_departure_city as pvc 
                    left join tour_city_description as tcd on pvc.tour_city_id=tcd.tour_city_id 
                    where pvc.product_id={$productId} and tcd.language_id=3;";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $one['start_city'] = $query['start_city'];
                break;
            }
            $sql = "";
            $resultArr[] = $one;
        } 
        return $resultArr;
    }
       
}

