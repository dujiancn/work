<?php

class Statistics{
    private $tffDB;
    private $biDB;
    private $resultFile;    

    public function __construct($resultFile){
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli();
        $this->tffDB->query("set names utf8");
        $this->biDB = new mysqli(");
        $this->biDB->query("set names utf8");
    }

    /**
     * @brief   find result file and result child file
     * @return  bool
     **/
    public function getOrderId(){
        $titleArr = array("order_id",'type','location');        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderIdArr = $this->getNewAndOldOrderId();
        foreach($orderIdArr['old'] as $one){
            $line = $one['order_id']."\told\t".$one['location'];
            file_put_contents($this->resultFile,$line."\n",FILE_APPEND); 
        }
        foreach($orderIdArr['new'] as $one){
            $line = $one['order_id']."\tnew\t".$one['location'];
            file_put_contents($this->resultFile,$line."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   
     * @param
     **/
    private function getNewAndOldOrderId(){
        $result = array('new' => array(), 'old' => array());
        //get origin order id array
        $orderIdArr = array();
        $sql = "select distinct(order_id) from order_product 
                where order_item_purchase_date>='2015-09-01' 
                and (product_id in(select product_id from product where region_id in (52,15)) or
                product_id in(select product_id from product where region_id in(select region_id from region where country_id in(select country_id from country where continent_id=5))))";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $orderIdArr[] = $query;
        }
        //添加用户id信息
        foreach($orderIdArr as $index => $one){
            $orderId = $one['order_id'];
            $sql = "select customer_id from `order` where order_id={$orderId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $orderIdArr[$index]['customer_id'] = $query['customer_id'];
                break;
            } 
        } 
        //删除非2015.9.1之后的非首单订单
        foreach($orderIdArr as $index => $one){
            $orderId = $one['order_id'];
            $customerId = $one['customer_id'];
            $sql = "select min(created) as created from `order` where customer_id={$customerId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if($query['created']<'2015-09-01'){
                    unset($orderIdArr[$index]);
                }
                break;
            } 
        }
        $orderIdArr = array_values($orderIdArr);
        $maxIndex = count($orderIdArr) - 1;
        //删除用户后面下的重复订单
        foreach($orderIdArr as $index => $one){
            $customerId = $one['customer_id'];
            for($i=$index+1; $i <= $maxIndex; $i++){
                if(isset($orderIdArr[$i])){
                    $posixCustomerId = $orderIdArr[$i]['customer_id'];
                    if($posixCustomerId == $customerId){
                        unset($orderIdArr[$i]);
                    }
                }
            }
        }
        //check if an old order
        foreach($orderIdArr as $one){
            $customerId = $one['customer_id'];
            $orderId = $one['order_id'];
            $sql = "select o.order_id from `order` as o 
                    left join order_product as op on o.order_id=op.order_id 
                    where op.product_id in (select product_id from product 
                    where region_id in(select region_id from region where country_id in (select country_id from country where continent_id in(2,4,9))) 
                    and is_tff=1 and active=1) 
                    and o.order_id<{$orderId}
                    and o.customer_id={$customerId}";
            $queryResult = $this->tffDB->query($sql);
            $query = mysqli_fetch_assoc($queryResult);
            if(isset($query['order_id'])){
                $result['old'][]['order_id'] = $orderId;
            } else {
                $result['new'][]['order_id'] = $orderId;
            }
        }
        //append location
        foreach($result as &$arr){
            foreach($arr as &$one){
                $orderId = $one['order_id'];
                $sql = "select vu.location from visitor_user as vu 
                        left join tran_order as tor on vu.visitor_user_id=tor.visitor_user_id 
                        where tor.order_id={$orderId}";
                $queryResult = $this->biDB->query($sql);
                $query = mysqli_fetch_assoc($queryResult);
                $location = isset($query['location']) ? $query['location'] : '';
                $one['location'] = $location;
            }
        }
        return $result;
    }

}
