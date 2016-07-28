<?php

class Statistics{
    private $biDB;
    private $resultFile;

    public function __construct($resultFile){
        $this->resultFile = $resultFile;
        //db 
        $this->biDB = new mysqli("192.168.100.200","root","tufeng1801","analytics_new",3306);
        $this->biDB->query("set names utf8");
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
        //init
        $titleArr = array("渠道","来源","关键字","订单数","提交订单金额","订单id","订单金额","购买日期","用户id","用户所在城市","用户首次登陆","产品id","产品名称","出发时间","出发城市");
        $line = implode("\t",$titleArr);
        file_put_contents($this->resultFile,"{$line}\n");            
    }


    /**
     * @brief   get cpc data 
     **/
    public function getCpcData($startDate,$endDate){
        //从tran_order中获取utm_id，order_id
        $utmOrderArr = array();
        $sql = "select last_utm_id as utm_id,GROUP_CONCAT(order_id SEPARATOR ',') as order_id from tran_order where last_utm_id in (select tran_utm_id from tran_utm where utm_medium='cpc') and create_time>=\"{$startDate}\" and create_time<\"{$endDate}\" group by last_utm_id;";
        $queryResult = $this->biDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $utmOrderArr[$query["utm_id"]] = explode(",",$query["order_id"]);
        }
        //获取订单详情并写入文档
        foreach($utmOrderArr as $utmId => $orderArr){
            $orderArr = array(220000,220002);
            //初始化    
            $orderDetailArr = array();
            $totalOrderNum = 0;
            $totalOrderPrice = 0;
            //获取渠道信息
            $utmInfo = $this->parseUtmId($utmId);
            $line = implode("\t",$utmInfo); 
            //获取订单信息
            foreach($orderArr as $orderId){
                $orderDetail = $this->getOrderDetailInfo($orderId);
                if(!empty($orderDetail)){
                    $totalOrderNum +=1;
                    $totalOrderPrice +=$orderDetail["total"];
                    $orderDetailArr[$orderId] = $orderDetail; 
                }
                unset($orderId,$orderDetail);
            }
            $line .="\t$totalOrderNum\t$totalOrderPrice\t";
            //准备写入数据
            foreach($orderDetailArr as $orderId => $orderInfo){
                $productList = isset($orderInfo["product"])? $orderInfo["product"] : array();
                unset($orderInfo["product"]);
                $line .=implode("\t",$orderInfo);
                if(empty($productList)){
                    file_put_contents($this->resultFile,"{$line}\n",FILE_APPEND);            
                }else{
                    foreach($productList as $product){
                        $line .="\t".implode("\t",$product);
                        file_put_contents($this->resultFile,"{$line}\n",FILE_APPEND);            
                        $line = "\t\t\t\t\t\t\t\t\t\t";
                    }
                }
                $line = "\t\t\t\t\t";
            } 
            var_dump($orderDetailArr);
            exit;
        }
        
    }

    /**
     * @brief   
     */
    private function parseUtmId($utmId){
        $result = array("channel"=>"","from"=>"","word"=>"");
        $sql = "select utm_medium as channel,utm_source as `from`,utm_term as word from tran_utm where tran_utm_id={$utmId}";       
        $queryResult = $this->biDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $result = $query;break;
        }
        return $result;
    }

    /**
     * @brief   获取订单详情，包括关联用户的一些信息
     */
    private function getOrderDetailInfo($orderId){
        $result = array();
        $sql = "select ot.order_id,ot.total,o.created as order_time,o.customer_id,cbs.city,ci.created as first_login 
                from `order` as o 
                left join tran_order as ot on ot.order_id=o.order_id 
                left join customer_base_statistics as cbs on cbs.customer_id=o.customer_id 
                left join customer_info as ci on ci.customer_id=o.customer_id 
                where o.order_id={$orderId};";
        $queryResult = $this->biDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $result = $query;break;
        }
        if(!empty($result)){
            $sql = "select product_id,product_name,product_departure_date,product_departure_location 
                    from order_product
                    where order_id={$orderId};";       
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $result["product"][] = $query;
            }
        }
        return $result;
    }

}


