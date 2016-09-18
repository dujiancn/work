<?php

class Statistics{
    private $tffDB;
    private $dataFile;    
    private $resultFile;    

    public function __construct($dataFile, $resultFile){
        $this->dataFile = $dataFile;
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   find result file and result child file
     * @return  bool
     **/
    public function process(){
        $titleArr = array("email","phone","guest_num","departure_date");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderProductIdArr = $this->getOrderProductIdArr();
        foreach($orderProductIdArr as $one){
            //补充产品信息
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getOrderProductIdArr(){
        $productIdArr = array();
        $resultArr = array();
        $productFileArr = file($this->dataFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $line = $productFileArr[0];
        $productIdArr = explode(",",$line);
        
        $sql = "select o.customer_email, o.customer_phone, op.total_room_adult_child_info as num, op.product_departure_date
                from order_product as op
                left join `order` as o on op.order_id=o.order_id
                where op.product_departure_date>='2016-09-25'
                and op.product_departure_date<'2016-10-11'
                and op.product_id in ({$line})";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["num"])){
                $query["num"] = $this->analyzeRoom($query["num"]);
                $resultArr[] = $query;
            }
        }
        return $resultArr;
    }

    /**
     * @brief   append product info
     * @param   &$one
     **/
    private function analyzeRoom($line){
        $num=0;
        $oneArr = explode("###",$line);
        foreach($oneArr as $room){
            $roomArr = explode("!!",$room);
            $num+=$roomArr[0]+$roomArr[1];
        }
        return $num; 
    }
       
}

?>
