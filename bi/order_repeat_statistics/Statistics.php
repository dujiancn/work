<?php

class Statistics{
    private $tffDB;
    private $repeatFile;    
    private $resultFile;    
    private $phoneBooking;    
    private $repeatOrderidArr;

    public function __construct($phoneBooking,$repeatFile,$resultFile){
        $this->repeatFile = $repeatFile;
        $this->resultFile = $resultFile;
        $this->phoneBooking = $phoneBooking;
        $this->repeatOrderidArr = array();
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
    }

    /**
     * @brief   findRepeatOrder
     * @return  bool
     **/
    public function findRepeatOrder(){
        $minOrderId = 0; 
        $maxOrderId = $this->getMaxOrderid(); 
        $totalNum = $this->getOrderNum();
        while($minOrderId!=$maxOrderId){
            $orderIdArr = $this->getOrderIdArr($minOrderId); 
            $minOrderId = end($orderIdArr);
            foreach($orderIdArr as $orderId){
                if(in_array($orderId,$this->repeatOrderidArr)){
                    continue;
                }
            
                $repeatOrderIdArr = $this->getRepeatOrderIdArr($orderId);
                $this->repeatOrderidArr = array_merge($this->repeatOrderidArr,$repeatOrderIdArr);
            }
        }
        $repeatNum = count($this->repeatOrderidArr);
        $singleNum = $totalNum -$repeatNum;
        //写入结果文档
        $line = "total_num\trepeat_num\tsingle_num\n{$totalNum}\t{$repeatNum}\t{$singleNum}";
        file_put_contents($this->resultFile,$line);
        foreach($this->repeatOrderidArr as $orderId){
            file_put_contents($this->repeatFile,"{$orderId}\n",FILE_APPEND);
        }
        return true;
    }

    /**
     * @brief   getRepeatOrderIdArr
     * @brief   orderId
     * @return  array
     **/  
    private function getRepeatOrderIdArr($orderId){
		$repeatOrderIdArr = array();
        $originOrderProductArr = array();
        //获取原始订单的产品信息和电子票信息
        $sql = "SELECT
				op.product_departure_date,
				op.product_code,
				op.order_id,
				op.product_id,
				ope.guest_name,
				ope.guest_email
			 FROM
				order_product AS op, `order` AS o, order_product_eticket AS ope
			 WHERE
				o.order_id = op.order_id AND
				op.order_id = ope.order_id AND
				op.order_product_id = ope.order_product_id AND
				op.order_id = {$orderId}";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $originOrderProductArr[$query["product_id"]] = $query;
        }
		if(empty($originOrderProductArr)){
            return $repeatOrderIdArr;
        }
        
        //step.1
        //获取和原始订单有相同原始产品的电子票信息的订单(只要有一个产品相同，则认为是重复订单，后续会进行第二轮校验)
        $mayRepeatOrderIdArr = array();
		foreach($originOrderProductArr as $orderProduct) {
			$order_prod_guest_name_array = $this->parseGuestName($orderProduct['guest_name']);
            $sql = "SELECT
					op.order_id
				FROM
				    order_product AS op, `order` AS o, order_product_eticket AS ope
				WHERE
                    o.phonebooking={$this->phoneBooking} AND 
					o.order_id = op.order_id AND
					op.order_id = ope.order_id AND
					op.order_product_id = ope.order_product_id AND
					op.product_departure_date = '" . $orderProduct['product_departure_date'] . "' AND
					op.product_departure_date != '0000-00-00 00:00:00' AND
					op.product_code = '" . $orderProduct['product_code'] . "' AND
					op.order_id != {$orderId} AND
					op.order_id > 0";
            if(!empty($order_prod_guest_name_array)){
                $sql .= " AND ope.guest_name LIKE '%" . addslashes($order_prod_guest_name_array[0]) . "%'";
            }
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $mayRepeatOrderIdArr[] = $query["order_id"];
            }
		}
		$mayRepeatOrderIdArr = array_unique($mayRepeatOrderIdArr);
        
        //step.2
        //进行订单级别的对比，即可能相同的订单需要和原始订单进行全方位对比，所有的数据都应该完全相同
        foreach($mayRepeatOrderIdArr as $mayRepeatOrderId){
            //如果是结伴同行，则直接过滤掉
            $sql = "select order_travel_companion_id from order_travel_companion where order_id={$mayRepeatOrderId}"; 
            $queryResult = $this->tffDB->query($sql);
            $query = mysqli_fetch_assoc($queryResult);
            if(!empty($query)){
                continue;
            }
            //进行全数据对比
            //抓取数据
            $sql = "SELECT
		    		op.product_departure_date,
		    		op.product_code,
		    		op.order_id,
		    		op.product_id,
		    		ope.guest_name,
		    		ope.guest_email
		    	 FROM
		    		order_product AS op, `order` AS o, order_product_eticket AS ope
		    	 WHERE
		    		o.order_id = op.order_id AND
		    		op.order_id = ope.order_id AND
		    		op.order_product_id = ope.order_product_id AND
		    		op.order_id = {$mayRepeatOrderId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $mayOrderProductArr[$query["product_id"]] = $query;
            }
            //对比数据
            if(count($mayOrderProductArr) == count($originOrderProductArr)){//产品数量必须相同
                $repeatFlag = true;
                foreach($mayOrderProductArr as $productId => $mayOrderProduct){
                    //首先必须有相同的产品id
                    if(!isset($originOrderProductArr[$productId])){
                        $repeatFlag = false;
                        break;
                    }
                    
                    //对相同的产品进行数据对比
                    $originOrderProduct = $originOrderProductArr[$productId];
                    //出行日期
                    if($mayOrderProduct["product_departure_date"]!=$originOrderProduct["product_departure_date"]){
                        $repeatFlag = false;
                        break;
                    }
                    //出行人名称
                    $originGuestNameArr = $this->parseGuestName($originOrderProduct["guest_name"]); 
                    $mayGuestNameArr = $this->parseGuestName($mayOrderProduct["guest_name"]); 
					if (count($originGuestNameArr) != count($mayGuestNameArr) ||  
						count(array_diff($originGuestNameArr, $mayGuestNameArr)) != 0 ||
						count(array_diff($originGuestNameArr, $mayGuestNameArr)) != 0 ){
                        $repeatFlag = false;
                        break;
                    }
                    //第一个出行人email/phone
                    $originGuestEmail = $this->parseGuestEmail($originOrderProduct["guest_email"]); 
                    $mayGuestEmail = $this->parseGuestEmail($mayOrderProduct["guest_email"]); 
					if( !empty($originGuestEmail) && !empty($mayGuestEmail) ){
                        if ( $originGuestEmail["first_guest"]["email"]!=$mayGuestEmail["first_guest"]["email"]&&
					         $originGuestEmail["first_guest"]["phone"]!=$mayGuestEmail["first_guest"]["phone"] ){
                            $repeatFlag = false;
                            break;
                        }
                    }else{
                        if(!(empty($originGuestEmail) && empty($mayGuestEmail))){
                            $repeatFlag = false;
                            break;
                        } 
                    }
                }
                //判断
                if($repeatFlag){
                    $repeatOrderIdArr[] = $mayRepeatOrderId;
                }
            } 
        }
        return $repeatOrderIdArr;
    }
 
    /**
     * @brief   get order id
     * @param   $minOrderId
     **/
    private function getOrderIdArr($minOrderId,$offset=1000){
        $orderIdArr = array();
        $sql = "select order_id from `order` where order_id>{$minOrderId} and phonebooking={$this->phoneBooking} order by order_id limit $offset ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_row($queryResult)){
            $orderIdArr[] = $query[0];
        }
        return $orderIdArr;
    }
    
    /**
     * @brief   get order num
     * @return  $ordernum
     **/
    private function getOrderNum(){
        $sql = "select count(*) from `order` where phonebooking={$this->phoneBooking} ";
        $queryResult = $this->tffDB->query($sql);
        $queryResult = mysqli_fetch_row($queryResult);
        $result = $queryResult[0];
        return $result;
    } 

    /**
     * @brief   get max order id
     * @return  $orderid
     **/
    private function getMaxOrderid(){
        $sql = "select max(order_id) from `order` where phonebooking={$this->phoneBooking} ";
        $queryResult = $this->tffDB->query($sql);
        $queryResult = mysqli_fetch_row($queryResult);
        $result = $queryResult[0];
        return $result;
    } 

    /**
     * @brief   parse guest name string
     * @param   $guestName
     * @return  array
     **/
    private function parseGuestName($guestName){    
        $guest_info_array = array();
        $guestnames_array = explode('<::>',$guestName);
        if (is_array($guestnames_array) && count($guestnames_array)>0) {
            foreach ($guestnames_array as $gkey=>$gval) {
                if (trim($gval) != '') {
                    $guest_full_name = explode('||',$gval);
                    $guest_info_array[] = strtolower($guest_full_name[0]);
                }    
            }    
        }    
        return $guest_info_array;
    } 

    /**
     * @brief   parse guest email
     * @param   $guestEmail
     * @return  array
     **/
    private function parseGuestEmail($guestEmail){    
        $guest_info_array = array();
        $guestemails_array = explode('<::>',$guestEmail);
        foreach ($guestemails_array as $gkey=>$gval) {
            if (trim($gval) != '') {
                $guest_full_email = explode('|##|',$gval);
                $guest_email_array[] = strtolower($guest_full_email[0]);
                $guest_info_array['first_guest']['email'] = strtolower($guest_full_email[0]);
                $guest_info_array['first_guest']['country_id'] = strtolower($guest_full_email[1]);
                $guest_info_array['first_guest']['phone'] = isset($guest_full_email[2]) ? strtolower($guest_full_email[2]) : ""; 
                break;
            }    
        }    
        return $guest_info_array; 
    }
}



