<?php

class Statistics
{
    private $resultFile;
    private $tffDB;

    public function __construct($resultFile)
    {
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli();
        $this->tffDB->query("set names utf8");
        //init
        $titleArr = array("订单id", "购买日期", "产品id", "产品名称", "出发时间", "供应商id", "供应商名称");
        $line = implode("\t", $titleArr);
        file_put_contents($this->resultFile, "{$line}\n");            
    }


    /**
     * @brief   get Rerservation not vailable data 
     **/
    public function getRnvData(
        $startDate,
        $endDate
    ) {
        //从order表中获取order_id，要求该order的历史状态有为100004的
        $orderIdArr = array();
        $sql = "select distinct(o.order_id) from `order` as o 
                left join order_status_history as osh on o.order_id=osh.order_id 
                where o.created>=\"{$startDate}\" 
                and o.created<\"{$endDate}\" 
                and osh.order_status_id=100004";
        $queryResult = $this->tffDB->query($sql);
        while ($query = mysqli_fetch_assoc($queryResult)) {
            $orderIdArr[] = $query["order_id"];
        }
        //获取订单详情并写入文档
        foreach ($orderIdArr as $orderId) {
            //获取订单信息
            $orderDetail = $this->getOrderDetailInfo($orderId);
            if (!empty($orderDetail)) {
                $createTime = $orderDetail["order_time"];
                $line = "$orderId\t{$createTime}";     
                foreach ($orderDetail["product"] as $product) {
                    $line .="\t".implode("\t", $product);
                    file_put_contents($this->resultFile, "{$line}\n", FILE_APPEND);            
                    $line = "\t";
                }
                if (!empty(trim($line))) {
                    file_put_contents($this->resultFile, "{$line}\n", FILE_APPEND);            
                }
            }
        }
        
    }

    /**
     * @brief   获取订单详情，包括产品，供应商的一些信息
     */
    private function getOrderDetailInfo($orderId)
    {
        $result = array();
        $sql = "select o.order_id,o.created as order_time 
                from `order` as o 
                where o.order_id={$orderId};";
        $queryResult = $this->tffDB->query($sql);
        while ($query = mysqli_fetch_assoc($queryResult)) {
            $result = $query;
            break;
        }
        if (!empty($result)) {
            $sql = "select op.product_id,op.product_name,op.product_departure_date,pv.provider_id,pv.name 
                    from order_product as op
                    left join provider as pv on pv.provider_id=op.provider_id
                    where order_id={$orderId};";       
            $queryResult = $this->tffDB->query($sql);
            while ($query = mysqli_fetch_assoc($queryResult)) {
                foreach ($query as &$one) {
                    $one = str_replace("\t"," ",$one);
                    $one = str_replace("\n"," ",$one);
                }   
                $result["product"][] = $query;
            }
        }
        return $result;
    }

}

?>
