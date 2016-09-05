<?php

class Statistics{
    private $resultFile;
    private $tffDB;

    public function __construct($resultFile){
        $this->resultFile = $resultFile;
        //db 
         $this->tffDB = new mysqli("toursforfunread.mysql.db.ctripcorp.com","uws_tours4fun_r","hslxk96rvardr[idLvjZ","tffdb",55944);
        $this->tffDB->query("set names utf8");
        //init
        $titleArr = array("订单id","购买日期","支付日期","产品id","产品名称","出发时间","供应商id","供应商名称","区域","发起预定时间","供应商回复时间");
        $line = implode("\t",$titleArr);
        file_put_contents($this->resultFile,"{$line}\n");            
    }


    /**
     * @brief   get Rerservation not vailable data 
     **/
    public function getRnvData($startDate,$endDate){
        //从order表中获取order_id，要求该order的历史状态有为100004的
        $orderIdArr = array();
        $sql = "select distinct(o.order_id) from `order` as o 
                left join order_status_history as osh on o.order_id=osh.order_id 
                where o.created>=\"{$startDate}\" 
                and o.created<\"{$endDate}\" 
                and osh.order_status_id=100004";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $orderIdArr[] = $query["order_id"];
        }
        //获取订单详情并写入文档
        foreach($orderIdArr as $orderId){
            //获取订单信息
            $orderDetail = $this->getOrderDetailInfo($orderId);
            if(!empty($orderDetail)){
                $createTime = $orderDetail["order_time"];
                $payTime = $orderDetail["pay_time"];
                $line = "$orderId\t{$createTime}\t{$payTime}";     
                foreach($orderDetail["product"] as $product){
                    $line .="\t".implode("\t",$product);
                    file_put_contents($this->resultFile,"{$line}\n",FILE_APPEND);            
                    $line = "\t\t";
                }
                if(!empty(trim($line))){
                    file_put_contents($this->resultFile,"{$line}\n",FILE_APPEND);            
                }
            }
        }
        
    }

    /**
     * @brief   获取订单详情，包括产品，供应商的一些信息
     */
    private function getOrderDetailInfo($orderId){
        $result = array();
        $sql = "select o.order_id,o.created as order_time 
                from `order` as o 
                where o.order_id={$orderId};";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $query["order_time"] = date("Y/m/d H:i:s",strtotime($query["order_time"]));
            $result = $query;break;
        }
        if(!empty($result)){
            //order pay info
            $sql = "select created as pay_time from order_status_history 
                    where order_id={$orderId}
                    and (order_status_id=100051 or order_status_id=100006)
                    order by created limit 1";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $query["pay_time"] = date("Y/m/d H:i:s",strtotime($query["pay_time"]));
                $result = array_merge($result,$query);break;
            }
            //order product's info
            $productArr = array();
            $sql = "select op.order_product_id,op.product_id,op.product_name,op.product_departure_date,pv.provider_id,pv.name 
                    from order_product as op
                    left join provider as pv on pv.provider_id=op.provider_id
                    where order_id={$orderId};";       
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                foreach($query as &$one){
                    $one = str_replace("\t"," ",$one);
                    $one = str_replace("\n"," ",$one);
                }   
                $productArr[] = $query;
            }
            //append continent info
            foreach($productArr as $index => &$productInfo){
                $productId = $productInfo["product_id"];
                /*    
                $sql = "select cd.name from product as p 
                        left join region as r on p.region_id=r.region_id 
                        left join country as c on r.country_id=c.country_id 
                        left join continent_description as cd on c.continent_id=cd.continent_id 
                        where p.product_id={$productId} 
                        and cd.language_id=3 limit 1;";*/
                $sql = "select bu from product where product_id={$productId}";
                $queryResult = $this->tffDB->query($sql);
                while($query = mysqli_fetch_assoc($queryResult)){
                    $productInfo["continent"] = $query["bu"];
                    break;
                }
            }

            //append pa interactive info
            foreach($productArr as $index => &$productInfo){
                //order product info
                $startTime = "";
                $paTime = "";
                $orderProductId = $productInfo["order_product_id"];
                unset($productInfo["order_product_id"]);
                $sql = "select min(last_updated) as start_time from provider_order_product_status_history 
                        where order_product_id={$orderProductId}
                        and `for`=0"; 
                $queryResult = $this->tffDB->query($sql);
                while($query = mysqli_fetch_assoc($queryResult)){
                    $startTime = date("Y/m/d H:i:s",strtotime($query["start_time"]));
                    break;
                }
                $sql = "select min(last_updated) as pa_time from provider_order_product_status_history 
                        where order_product_id={$orderProductId}
                        and `for`=1"; 
                $queryResult = $this->tffDB->query($sql);
                while($query = mysqli_fetch_assoc($queryResult)){
                    $paTime = date("Y/m/d H:i:s",strtotime($query["pa_time"]));
                    break;
                }
                $productInfo["start_time"] = $startTime; 
                $productInfo["end_time"] = $paTime; 
                $productArr[$index] = $productInfo;
            }
            $result["product"] = $productArr;
        }
        return $result;
    }

}

