<?php

class Statistics{
    private $tffDB;
    private $resultFile;    
    private $startDate;
    private $endDate;

    public function __construct($resultFile,$startDate,$endDate){
        $this->resultFile = $resultFile;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        //db 
        $this->tffDB = new mysqli("toursforfunread.mysql.db.ctripcorp.com","uws_tours4fun_r","hslxk96rvardr[idLvjZ","tffdb",55944);
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   find result file and result child file
     * @return  bool
     **/
    public function getData(){
        $titleArr = array("customer_name","customer_phone","customer_email","customer_number","city","agency_name","order_id","departure_date","creator","contact_type","tour_type","retail");
        $titleStr = implode("|",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $orderArr = $this->getOrderArr();
        foreach($orderArr as $one){
            //补充产品信息
            $oneStr = implode("|",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getOrderArr(){
        $resultArr = array();
        $contactTypeArr = array(
            0 => '邮件',
            1 => '电话',
            2 => '老客户',
            3 => '客户介绍',);
        $tourTypeArr = array(
            0 => '中介',
            1 => '家庭',
            2 => '商务',
            3 => '培训',
            4 => '夏令营',
            5=> 'VIP高端',);
        $sql = "select po.customer_name,po.customer_phone,po.customer_email,po.customer_number,po.city,pa.name as agency_name,po.order_id,po.departure_date,
                concat_ws(' ',u.first_name,u.last_name) as creator,po.contact_type,po.tour_type,pot.retail 
                from pt_order as po 
                left join user as u on u.user_id=po.created_by 
                left join pt_agency  as pa on po.pt_agency_id=pa.pt_agency_id 
                left join pt_order_total as pot on po.pt_order_id=pot.pt_order_id 
                where po.created between \"{$this->startDate}\" and \"{$this->endDate}\"";
        
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(!empty($query)){
                $contactType = $query["contact_type"];
                $query["contact_type"] = isset($contactTypeArr[$contactType]) ? $contactTypeArr[$contactType] : $contactType;
                $tourType = $query["tour_type"];
                $query["tour_type"] = isset($tourTypeArr[$tourType]) ? $tourTypeArr[$tourType] : $tourType;
                $resultArr[] = $query;
            }
        }
        return $resultArr;
    }

       
}

