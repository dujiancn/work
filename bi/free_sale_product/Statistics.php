<?php

class Statistics{
    private $tffDB;
    private $detailFile;
    private $durationType;

    public function __construct($detailFile){
        $this->detailFile = $detailFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->durationType = array(0 => " days",1 => " hours", 2=> " minutes"); 
    }

    /**
     * @brief   get free sale id
     * @return  bool
     **/
    public function getFreeProduct(){
        //首先从product_free_sale数据表中找出free_sale产品id
        $productFreeSaleIdArr = $this->getIdFromProductFreeSale();
        //然后从provider_free_sale数据表中查出免费的provider_id，然后查找对应的product_id
        $providerFreeSaleIdArr = $this->getIdFromProviderFreeSale();
        $result = array_merge($productFreeSaleIdArr,$providerFreeSaleIdArr);
        $result = array_unique($result);
        //写入结果文件 
        $header = "<html>\n\t<body>\n\t\t<table border=\"1\">\n";
        $header .= "<tr>\n<th>product_id</th>\n<th>duration</th>\n<th>provider_id</th>\n<th>name</th>\n<th>chinese_name</th>\n<th>how_to_book</th>\n<th>reservation_note</th></tr>\n";
        file_put_contents($this->detailFile,$header);
        foreach($result as $productId){
            $productDetail = $this->getProductDetail($productId);
            $line = "\n\t\t\t<tr>\n\t\t\t\t<td>";
            $line .= implode("</td>\n\t\t\t\t<td>",$productDetail);
            $line .= "\n\t\t\t\t<td>\n\t\t\t</tr>";
            file_put_contents($this->detailFile,"{$line}\n",FILE_APPEND);
        }
        $end = "\t\t</table>\n\t</body>\n</html>";
        file_put_contents($this->detailFile,$end,FILE_APPEND);
        return true;
    }

    /**
     * @brief   get product detail
     * @param   $productId
     **/
    private function getProductDetail($productId){
        $result = array();
        $sql = "SELECT p.product_id,p.duration,p.duration_type,p.provider_id,pr.name,pr.chinese_name,pr.how_to_book,reservation_note FROM product p LEFT JOIN provider pr ON p.provider_id=pr.provider_id WHERE p.product_id={$productId} ";
        //$queryResult = $this->tffDB->query("set names utf8");
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            if(!empty($query)){
                $query["duration"] = $query["duration"].$this->durationType[$query["duration_type"]];
                unset($query["duration_type"]);
            }
            $result = $query;
        }
        foreach($result as &$value){
            $value = str_replace("\t"," ",$value);
            $value = str_replace("\n"," ",$value);
        }
        return $result;
        
    }    


    /**
     * @brief   get from product_free_sale
     * @return  array
     **/  
    private function getIdFromProductFreeSale(){
        $result = array();
        $sql = "SELECT product_id FROM product_free_sale where is_free_sale=1";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $result[] = $query["product_id"];
        }
        return $result;
    }
    
    /**
     * @brief   get from provider_free_sale
     * @return  array
     **/
    private function getIdFromProviderFreeSale(){
        $result = array();
        $sql = "SELECT p.product_id FROM product p LEFT JOIN `provider_free_sale` pfs ON pfs.`provider_id`=p.`provider_id` where pfs.is_free_sale=1";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $result[] = $query["product_id"];
        }
        foreach($result as $index => $productId){
            $sql = "SELECT is_free_sale FROM product_free_sale where product_id={$productId}";
            $queryResult = $this->tffDB->query($sql);
            while($query = mysqli_fetch_assoc($queryResult)){
                if(isset($query["is_free_sale"]) && empty($query["is_free_sale"])){
                    unset($result[$index]);
                }
            } 
        }
        $result = array_values($result);
        return $result;
    }   

}
