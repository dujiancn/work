<?php

class Statistics{
    private $tffDB;
    private $resultFile;    

    public function __construct($memberLevel, $resultFile){
        $this->memberLevel = $memberLevel;
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli('192.168.100.200','root','tufeng1801','tff_2014_06_24',3306);
        $this->tffDB->query('set names utf8');
    }

    /**
     * @brief   get the customer's order
     * @return  bool
     **/
    public function getOrder(){
        $titleArr = array('customer_id','customer_email','register_time','last_login_time','dob','last_consume_time','consume_times','total_money','order_id','order_retail','product_id','product_name');        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $customerArr = $this->getCustomerArr();
        foreach($customerArr as $customerId => $customerInfo){
            //补充产品信息
            $order = $this->getOrderList($customerId);
            $allInfo = array_merge($customerInfo,$order);
            $orderList = $allInfo['order_list'];
            unset($allInfo['order_list']);
            $allStr = implode("\t",$allInfo);
            foreach($orderList as $order){
                $productList = $order['product_list'];
                unset($order['product_list']);
                $orderStr = implode("\t",$order);
                foreach($productList as $productInfo){
                    $productStr = implode("\t",$productInfo);
                    $line = "{$allStr}\t{$orderStr}\t{$productStr}";
                    file_put_contents($this->resultFile,$line."\n",FILE_APPEND); 
                }
            }
        }
        return true;
    }

    /**
     * @brief   get order product info
     **/
    private function getOrderList($customerId){
        $result = array();
        $sql = "select max(o.created) as last_consume_time, count(o.order_id) as consume_times, sum(ot.value) as total_money 
                from `order` as o 
                left join order_total as ot 
                on o.order_id=ot.order_id 
                where ot.class='ot_total' and ot.value>0 and 
                o.customer_id={$customerId}";
        $queryResult = $this->tffDB->query($sql);
        $query = mysqli_fetch_assoc($queryResult);
        $result = $query;
        if(!empty($result)){
            $result['order_list'] = array();
            $sql = "select o.order_id, ot.value as order_retail 
                    from `order` as o left join order_total as ot 
                    on o.order_id=ot.order_id 
                    where ot.class='ot_total' and ot.value>0 and o.customer_id={$customerId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                $result['order_list'][] = $query;
            }
            foreach($result['order_list'] as &$order){
                $order['product_list'] = array();
                $orderId = $order['order_id'];
                $sql = "select product_id,product_name from order_product where order_id={$orderId}";
                $queryResult = $this->tffDB->query($sql);
                while($query = mysqli_fetch_assoc($queryResult)){
                    $order['product_list'][] = $query;
                }
            }
        }
        return $result;
    }

    /**
     *
     **/
    private function getCustomerArr(){
        $resultArr = array();
        $experienceStr = 'c.experience>10000 and c.experience<20000';
        if($this->memberLevel == 'diamond'){
            $experienceStr = 'c.experience>=20000';
        }
        $sql = "select c.customer_id, c.email as customer_email, ci.created as register_time, ci.last_login as last_login_time, c.dob 
            from customer as c left join customer_info as ci on c.customer_id=ci.customer_id 
            where {$experienceStr}
            and c.email not in ('xcorders@toursforfun.com','axiang.lin@kaiyuan.de','2355615042@qq.com','297971158@qq.com','huyushan@tuniu.com','2355615072@qq.com','g-np-resv@tuniu.com','xiang.xiao@mangocity.com','america@haiwan.com','xuyan001@byecity.com','2604059730@qq.com','wangdandan@tuniu.com','zhengpei@utourworld.com','xiaofang.rausch@caissa.de','493328345@qq.com','zlsorders@toursforfun.com','liqian@lvmama.com','380990252@qq.com','mary@omegauk.net','xiaolu.mo@mangocity.com','qnorders@toursforfun.com','noellewu@gmail.com','aoxintuniu@toursforfun.cn','ouzhoutuniu@toursforfun.com','tnorders@toursforfun.com','omjorders@toursforfun.com','yoyoorders@toursforfun.com','lmmorders@toursforfun.com','mgorders@toursforfun.com','fqorders@toursforfun.com','hworders@toursforfun.com','kszgorders@toursforfun.com','tcorders@toursforfun.com','xhorders@toursforfun.com','aoxinqunar@toursforfun.com','ouzhouxiecheng@toursforfun.com','ouzhouqiongyou@toursforfun.com','ouzhouzoubianouzhou@toursforfun.com','ouzhoutongcheng@toursforfun.com','ouzhoumafengwo@toursforfun.com','ouzhouqunaer@toursforfun.com','ouzhoubaishitong@toursforfun.com','ouzhoumangguowang@toursforfun.com','ouzhoulvmama@toursforfun.com','aoxintongcheng@toursforfun.com','aoxinlvmama@toursforfun.com','gzhlorders@toursforfun.com','bcorders@toursforfun.com','wkorders@toursforfun.com','ksdgorders@toursforfun.com','bstorders@toursforfun.com','xmbyorders@toursforfun.com','2851351337@qq.com','werorders@toursforfun.com','xxorders@toursforfun.com','cdhworders@toursforfun.com','htorders@toursforfun.com','hqglorders@toursforfun.com','jdorders@toursforfun.com','mfworders@toursforfun.com','mdorders@toursforfun.com','winnie.wang@toursforfun.cn','apple.yang@toursforfun.com','selena.yuan@toursforfun.cn','sky.zhou@toursforfun.cn','yuanxin1124@hotmail.com','47007389@qq.com','chuangyi78@126.com','yxorders@toursforfun.com','aoxinxcorders@toursforfun.com','aoxinkaisa@toursforfun.com','alitrip@toursforfun.cn','lcorders@toursforfun.com','aoxinmfw@toursforfun.com','ouzhoulucheng@toursforfun.com','lxwlorders@toursforfun.com','ouzhoulixing@toursforfun.com','yporders@toursforfun.com','aoxinyporders@toursforfun.com','weixin@toursforfun.com','caissa@caissa.com','zxorders@toursforfun.com','viporders@toursforfun.com','91cgorders@toursforfun.com','ouzhoukszg@toursforfun.com','yhlxorders@toursforfun.com','yqforders@toursforfun.com','yborders@toursforfun.com','ouzhouzls@toursforfun.com','zyxxcorders@toursforfun.com','ouzhouyqforders@toursforfun.com','zgorders@toursforfun.com','mzlorders@toursforfun.com','cqyunshangorders@toursforfun.com','gzsforders@toursforfun.com','kevin@TailorATrip.com','dzorders@toursforfun.com','ayorders@toursforfun.com','mhtorders@toursforfun.com','meiyaorders@toursforfun.com') ";        
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $customerId = $query['customer_id'];
            $resultArr[$customerId] = $query;
        }
        return $resultArr;
    } 
}

