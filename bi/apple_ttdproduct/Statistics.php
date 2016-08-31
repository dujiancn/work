<?php

class Statistics{
    private $tffDB;
    private $sourceFile;    
    private $resultFile;    

    public function __construct($sourceFile,$resultFile){
        $this->sourceFile = $sourceFile;
        $this->resultFile = $resultFile;
        //db 
        $this->tffDB = new mysqli("192.168.100.200","root","tufeng1801","tff_2014_06_24",3306);
        $this->tffDB->query("set names utf8");
    }

    /**
     * @brief   find result file and result child file
     * @return  bool
     **/
    public function getProduct(){
        $titleArr = array("product_id","product_name","product_price");        
        $titleStr = implode("\t",$titleArr);
        file_put_contents($this->resultFile,$titleStr."\n"); 
        $productIdArr = file($this->sourceFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        foreach($productIdArr as $productId){
            //补充产品信息
            $one = $this->getProductInfo($productId);    
            $oneStr = implode("\t",$one);
            file_put_contents($this->resultFile,$oneStr."\n",FILE_APPEND); 
        }
        return true;
    }

    /**
     * @brief   append product info
     * @param   &$one
     **/
    private function getProductInfo($productId){
        //append product info
        $one = array();
        $sql = "select p.product_id,pde.name as product_name,p.default_price as price
                from product as p
                left join product_description as pde on p.product_id=pde.product_id
                where p.product_id={$productId}
                and pde.language_id=3
                ";
        $queryResult = $this->tffDB->query($sql);
        while($query = mysqli_fetch_assoc($queryResult)){
            $one = $query;
            break;
        }
        if(isset($one["product_name"])){
            $oneNameArr = explode("\n",$one["product_name"]);
            $one["product_name"] = preg_replace('/\*\*.*\*\*/', '', $oneNameArr[0]);
        } 
        return $one; 
    }
       
}

