<?php

class Statistics{
    private $tffDB;
    private $appDB;
    private $detailFile;

    public function __construct($detailFile){
        $this->detailFile = $detailFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
        $this->appDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->appDB->query("set names utf8");
    }

    /**
     * @brief   get free sale id
     * @return  bool
     **/
    public function getFreeProduct(){
        //首先获取所有的多日游产品id
        $allProductIdArr = $this->getAllProductId();
        //获取所有生效的即时确认产品
        $jishiProducts = $this->getJishiProduct();
        $jishiProductIdArr = array_keys($jishiProducts);

        //写入结果文件 
        foreach($allProductIdArr as $productId){
            $productDetail = $this->getProductDetail($productId,true);
            $productDetail['jishi'] = 'null';
            if(in_array($productId,$jishiProductIdArr)){
                $productDetail['jishi'] = $jishiProducts[$productId];
            }
            $line = implode('\t',$productDetail);
            file_put_contents($this->detailFile,"{$line}\n",FILE_APPEND);
        }
            
        return true;
    }

    /**
     * @brief   get product detail
     * @param   $productId
     **/
    private function getProductDetail($productId, $freeSale = false){
        $result = array();
        $sql = "SELECT p.product_id, pd.name as product_name, pmd.price_single_cost as cost, pmd.price_single as price
                from product as p
                left join product_description as pd on p.product_id=pd.product_id
                left join product_multi_day as pmd on pd.product_id=pmd.product_id
                WHERE p.product_id={$productId} and pd.language_id=3";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(!empty($query)){
                $oneNameArr = explode("\n",$query["product_name"]);
                $query["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
            }
            $result = $query;
        }
        return $result;
        
    }    


    /**
     * @brief   get from product_free_sale
     * @return  array
     **/  
    private function getAllProductId(){
        $result = array();
        $sql = "SELECT product_id FROM product 
                where product_entity_type=1 and active=1 and is_tff=1";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $result[] = $query["product_id"];
        }
        return $result;
    }

    private function getJishiProduct(){
        $result = array();
        $sql = "select obj_id,description from opsp_obj_tags where
                tag_id=177 and ( ( start_time='0000-00-00' and end_time='0000-00-00') 
                or ( start_time='0000-00-00' and end_time>'2016-10-17' )
                or ( end_time='0000-00-00' and start_time<'2016-10-16' )
                or ( start_time<'2016-10-16' and end_time>'2016-10-17' ) )";
        $queryResult = $this->appDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $productId = $query['obj_id'];
            $des = $query['description'];
            $result[$productId] = $des;
        }
        return $result;
    }    
}
