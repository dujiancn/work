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
    public function getHawaii(){
        $titleArr = array("product_type","product_id","product_name","start_city","visit_city","num","departure_date","end_date");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderProductIdArr = $this->getOrderProductIdArr();
        foreach($orderProductIdArr as $one){
            //补充产品信息
            $one = $this->process($one);    
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getOrderProductIdArr(){
        $resultArr = array();
        $sql = "select order_id,order_product_id,product_id,total_room_adult_child_info,date_format(product_departure_date,\"%Y-%m-%d\") as product_departure_date
                from order_product
                where product_id in (select product_id from product where region_id=1 or (active=1 and is_tff=1))
                and order_item_purchase_date>='2015-01-01' 
                and order_item_purchase_date<'2016-08-01' 
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["product_id"]) && !empty($query["product_id"])){
                $resultArr[] = $query;
            }
        }
        return $resultArr;
    }

    /**
     * @brief   append product info
     * @param   &$one
     **/
    private function process($one){
        $productId = $one["product_id"];
        //append product info
        $sql = "select p.product_entity_type,p.duration_type,p.duration,pde.name
                from product as p
                left join product_description as pde on p.product_id=pde.product_id
                where p.product_id={$productId}
                and pde.language_id=3
                "; 
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $one = array_merge($one,$query);
            break;
        }
        //append start city
        $sql = "select group_concat(distinct(tcd.name)) as start_city 
                from product_departure_city as pvc 
                left join tour_city_description as tcd 
                on pvc.tour_city_id=tcd.tour_city_id 
                where pvc.product_id={$productId} and tcd.language_id=3;";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $one = array_merge($one,$query);
            break;
        }
        //append visited city
        $sql = "select group_concat(distinct(tcd.name)) as visit_city 
                from product_visited_city as pvc 
                left join tour_city_description as tcd 
                on pvc.tour_city_id=tcd.tour_city_id 
                where pvc.product_id={$productId} and tcd.language_id=3;";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $one = array_merge($one,$query);
            break;
        }
        //process info
        $productType = [ 
        0 => "oneday-tour",
        1 => "multiday-tour",
        2 => "cruise-tour",
        3 => "hotel",
        4 => "transportation",
        5 => "simcard",
        6 => "europebus-tour",
        ];
        $result = array(
                    "product_type" => "",
                    "product_id" => "",
                    "product_name" => "",
                    "start_city" => "",
                    "visit_city" => "",
                    "num" => "0",
                    "departure_date" => "",
                    "end_date" => "",
                );        
        if(isset($one['product_entity_type']) && isset($productType[$one["product_entity_type"]])){
            $result["product_type"] = $productType[$one["product_entity_type"]];
        }
        if(isset($one["product_id"])){
            $result["product_id"] = $one["product_id"];
        } 
        if(isset($one["name"])){
            $oneNameArr = explode("\n",$one["name"]);
            $result["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
        } 
        if(isset($one["start_city"])){
            $result["start_city"] = $one["start_city"];
        } 
        if(isset($one["visit_city"])){
            $result["visit_city"] = $one["visit_city"];
        } 
        if(isset($one["product_departure_date"])){
            $result["departure_date"] = $one["product_departure_date"];
        }
        if(isset($one["duration_type"]) && isset($one["duration"])){
            $duration=0;
            switch($one["duration_type"]){
                case 0:
                    $duration = $one["duration"];
                    break;
                case 1: 
                    $duration = $one["duration"]/24;
                    break;
                case 2: 
                    $duration = $one["duration"]/86400;
                    break;
            }
            if($duration>=1){//day
                $result["end_date"] = date('Y-m-d', strtotime("+{$duration} day",strtotime($result["departure_date"])));
            }
        }
        if(isset($one["total_room_adult_child_info"])){
            $num=0;
            $oneArr = explode("###",$one["total_room_adult_child_info"]);
            unset($oneArr[0]);
            foreach($oneArr as $room){
                $roomArr = explode("!!",$room);
                $num+=$roomArr[0]+$roomArr[1];
            }
            $result["num"] = $num;
        }
        var_dump($result);exit;
        return $result; 
    }
       
}

