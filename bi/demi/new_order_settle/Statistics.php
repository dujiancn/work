<?php

class Statistics{
    private $orderDB;
    private $financialDB;
    private $resultFile;
    private $startDate; 
    private $endDate; 

    public function __construct($resultFile, $startDate, $endDate){
        $this->resultFile = $resultFile;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        //db 
        $this->orderDB = new mysqli();
        $this->orderDB->query("set names utf8");
        $this->financialDB = new mysqli();
        $this->financialDB->query("set names utf8");
    }

    /**
     * @brief   find result file
     * @return  bool
     **/
    public function getSettlement(){
        $titleArr = array("order_id","settlement_id","order_value","settlement_date","pay_method","transaction_no","serial_no");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderSettlementArr = $this->getSettlementList();
        foreach($orderSettlementArr as $one){
            //补充产品信息
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getSettlementList(){
        $resultArr = array();
        //get order id list
        $payOrderList = array();
        $sql = "select order_id, settlement_id, need_pay as order_valule 
                from order_pay_item 
                where is_effective=1 
                and pay_finish=1 
                and settlement_id>0 
                and created_at>=\"{$this->startDate}\"
                and created_at<\"{$this->endDate};\"";
        $queryResult = $this->orderDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $payOrderList[] = $query;
        }
        $refundOrderList = array();
        $sql = "select order_id, settlement_id, refund_total*-1 as order_valule 
                from order_pay_refund 
                where is_effective=1 
                and refund_finish=1 
                and settlement_id>0 
                and created_at>=\"{$this->startDate}\"
                and created_at<\"{$this->endDate};\"";
        $queryResult = $this->orderDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $refundOrderList[] = $query;
        }
        $allOrderList = array_merge($payOrderList, $refundOrderList);

        //get all settlement info
        foreach($allOrderList as $orderInfo){
            $one = $orderInfo;
            $orderId = $orderInfo['order_id'];
            $settlementId = $orderInfo['settlement_id'];
            $sql = "select date_format(s.paid_time, '%y-%m-%d') as settlement_date, pm.channel, s.transaction_no, s.serial_no 
                    from settlement as s 
                    left join payment_method as pm on s.payment_method_id=pm.payment_method_id
                    where s.order_id={$orderId} and s.settlement_id={$settlementId}";
            $queryResult = $this->financialDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $one = array_merge($one, $query); 
            }
            $resultArr[] = $one;
        }
        return $resultArr;
    }

       
}

