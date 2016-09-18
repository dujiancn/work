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
    public function getNewzealan($startDate,$endDate){
        $titleArr = array("order_id","retail","person_num","created","product_id","product_name");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderProductIdArr = $this->getOrderProductIdArr($startDate,$endDate);
        foreach($orderProductIdArr as $one){
            $orderId = $one["order_id"];
            $retail = $one["retail"];
            $personNum = $one["person_num"];
            $created = $one["created"];
            $line = "$orderId\t$retail\t$personNum\t$created\t";
            foreach($one["product"] as $product){
                $line .= implode("\t",$product);
                file_put_contents($this->resultFile,$line."\n",FILE_APPEND); 
                $line = "\t\t\t\t";
            }
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getOrderProductIdArr($startDate,$endDate){
        $resultArr = array();
        $orderIdArr = array();
        //get order id arr
        $sql = "select distinct(order_id)
                from order_product
                where product_id in (select product_id from product where region_id=15 and (active=1 and is_tff=1))
                and order_item_purchase_date>='{$startDate}' 
                and order_item_purchase_date<'{$endDate}' 
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["order_id"]) && !empty($query["order_id"])){
                $orderIdArr[] = $query["order_id"];
            }
        }
        //get order info
        foreach($orderIdArr as $orderId){
            $one = array();
            $sql = "select o.order_id,ot.total as retail, o.created 
                    from `order` as o
                    left join `order_total` as ot on o.order_id=ot.order_id
                    where o.order_id={$orderId}
                    and ot.class='ot_total'";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["order_id"])){
                    $one = $query;
                }
            }
            if(!empty($one)) {
                //get product info
                $productArr = array();
                $sql = "select total_room_adult_child_info, product_id, product_name
                        from order_product
                        where order_id={$orderId}";
                $personNum = 0;
                while($query = mysqli_fetch_assoc($queryResult)){
                    if(isset($query["total_room_adult_child_info"])){
                        $personNum += $this->analyzeRoom($query["total_room_adult_child_info"]);
                        unset($query["total_room_adult_child_info"]);
                        $oneNameArr = explode("\n",$query["product_name"]);
                        $query["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
                        $productArr[] = $query;
                    }
                }
                $one["person_num"] = $personNum;
                $one["product"] = $productArr;
                $resultArr[] = $one;
           } 
        }
        return $resultArr;
    }

    /** 
     * @brief   append product info
     * @param   &$one
     **/
    private function analyzeRoom($line)
    {
        $num = 0;
        $oneArr = explode("###",$line);
        foreach($oneArr as $room){
            $roomArr = explode("!!",$room);
            $num+=$roomArr[0]+$roomArr[1];
        }   
        return $num; 
    }
       
}

?>
