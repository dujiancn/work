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
    public function getOneday(){
        $titleArr = array("product_id","product_name","activity_type","type_id","provider_id","start_city","has_order");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $productArr = $this->getProductArr();
        foreach($productArr as $one){
            //implode产品信息
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get product info
     **/
    private function getProductArr(){
        $resultArr = array();
        
        //get product array
        $productArr = array();
        $sql = "select p.product_id, pd.name as product_name, ptd.name as activity_type, p.product_type_id as type_id, p.provider_id
                from product as p left join product_description as pd on p.product_id=pd.product_id
                left join product_type_description as ptd on ptd.product_type_id=p.product_type_id
                where p.product_entity_type=0  and p.active=1 and p.is_tff=1 and pd.language_id=3 and
                (p.region_id in(52,15) or p.region_id in (select region_id from region where country_id in (select country_id from country where continent_id in (2,4,5,9) ) ) )
                and ptd.language_id=3";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["product_id"]) && !empty($query["product_id"])){
                $oneNameArr = explode("\n",$query["product_name"]);
                $query["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
                $productArr[] = $query;
            }
        }
    
        //append other infomation
        foreach($productArr as $product){
            $productId = $product["product_id"];
            $one = array("product_id" => $product["product_id"],
                         "product_name" => $product["product_name"],
                         "activity_type" => $product["activity_type"],
                         "type_id" => $product["type_id"],
                         "provider_id" => $product["provider_id"],
                         "start_city" => "",
                         "has_order" => "0",
                );
            //append start city
            $sql = "select group_concat(distinct(tcd.name)) as start_city 
                    from product_departure_city as pvc 
                    left join tour_city_description as tcd 
                    on pvc.tour_city_id=tcd.tour_city_id 
                    where pvc.product_id={$productId} and tcd.language_id=3;";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $one["start_city"] = $query["start_city"];
                break;
            }
            //has order
            $sql = " select count(*) as num from order_product where product_id={$productId}"; 
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if($query["num"]>0){
                    $one["has_order"] = 1;
                }
                break;
            }
            $resultArr[] = $one;
        }
         
        return $resultArr;
    }
       
}

