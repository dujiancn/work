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
    public function getAmerica(){
        $titleArr = array("customer_id","name","email","gender","telephone","city","province","country","total_consume","last_consume_time","last_consume_product");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderIdArr = $this->getOrderIdArr();
        foreach($orderIdArr as $orderId){
            //补充产品信息
            echo "deal $orderId\n";
            $one = $this->getInfo($orderId);    
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
            exit;
        }
        return true;
    }

    /**
     * @brief   get order Id
     **/
    private function getOrderIdArr(){
        $resultArr = array();
        $sql = "select distinct(op.order_id)
                from `order` as o 
                left join order_product as op on o.order_id=op.order_id
                left join product as p on op.product_id=p.product_id 
                where o.status in (100002,100006,100009,100012,100023,100036,100040,100062,100078)
                and o.created>='2015-01-01'
                and o.created<='2015-09-01'
                and p.region_id in (select region_id from region where country_id in ( select country_id from country where continent_id in(2,4,9)))
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(isset($query["order_id"]) && !empty($query["order_id"])){
                $resultArr[] = $query["order_id"];
            }
        }
        foreach($resultArr as $index => $orderId){
            $sql = "select order_id from `order` where order_id>{$orderId} 
                    and customer_id in (select customer_id from `order` where order_id={$orderId})";
            $queryResult = $this->tffDB->query($sql);
            $query = mysqli_fetch_assoc($queryResult);
            if(!empty($query)){
                unset($resultArr[$index]);
            }
        }
        return $resultArr;
    }

    /**
     * @brief   append product info
     * @param   &$one
     **/
    private function getInfo($orderId){
        $result = array(
                        "customer_id" => "",
                        "name" => "",
                        "email" => "",
                        "gender" => "",
                        "telephone" => "",
                        "city" => "",
                        "province" => "",
                        "country" => "",
                        "total_consume" => "",
                        "last_consume_time" => "",
                        "last_consume_product" => "",
                    );
        //get customer info
        $sql = "select o.customer_id,o.customer_name as name,o.customer_email as email,c.gender,o.customer_phone as telephone, o.customer_country as country,o.customer_city as city,o.customer_state as province,o.created as last_consume_time
                from `order` as o
                left join customer as c on o.customer_id=c.customer_id
                where o.order_id={$orderId}
                ";
        $queryResult = $this->tffDB->query($sql);
        $query = mysqli_fetch_assoc($queryResult);
        if(!empty($query)){
            foreach($query as $key => $value){
                $result[$key] = $value;
            }
        }
        //total consume
        $customerId = $result["customer_id"];
        $sql = "select sum(ot.value) as total_consume from
                `order` as o 
                left join order_total as ot on o.order_id=ot.order_id
                where o.customer_id={$customerId}
                and o.status in (100002,100006,100009,100012,100023,100036,100040,100062,100078)
                and ot.class='ot_total'";
        $queryResult = $this->tffDB->query($sql);
        $query = mysqli_fetch_assoc($queryResult);
        if(!empty($query)){
            $result["total_consume"] = $query["total_consume"];
        }
        //last _consume
        $nameArr = array();
        $nameStr = "";
        $sql = "select product_name from order_product where order_id={$orderId}";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $nameArr[] = $query["product_name"];
        }
        foreach($nameArr as $index => $name){
            $oneNameArr = explode("\n",$name);
            $nameArr[$index]= preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
        }
        $result["last_consume_product"] = implode("|",$nameArr);
        return $result; 
    }
       
}

