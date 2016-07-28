<?php
require_once dirname(__FILE__)."/EticketData.php";
require_once dirname(__FILE__)."/OrderData.php";

class CustomerDataMining{
    private $biDB;

    public function __construct(){
        //db 
        $this->biDB = new mysqli("192.168.100.200","root","tufeng1801","analytics_new",3306);
        $this->biDB->query("set names utf8");
    }

    /**
     * @brief   get the customer data
     **/
    public function get($resultFile){
        //get all customer id
        $customerIdArr = $this->getCustomerId();   
        //get full data
        foreach($customerIdArr as $customerId){
            if(empty($customerId)) continue;
            echo "deal customer {$customerId}\n";
            $customerInfo = $this->getCustomerInfo($customerId);
            $customerInfo = implode("\t",$customerInfo);       
            file_put_contents($resultFile,$customerInfo."\n",FILE_APPEND);
            unset($customerInfo);
        }
        return true;
    }

    /**
     * @brief   write the customer data into db
     **/
    public function write($customerFile,$writeCustomerFile,$writeStatisticsFile){
        //初始化，读取客户数据
        $statisticsArr = array("file"=>$customerFile,"first_name"=>0,"last_name"=>0,"email"=>0,"gender"=>0,"dob"=>0,"country_id"=>0);
        $customerArr = array();
        $customerLineArr = file($customerFile,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach($customerLineArr as $line){
            $lineArr = explode("\t",$line);
            $customerArr[$lineArr[0]]["first_name"] = $lineArr[1];
            $customerArr[$lineArr[0]]["last_name"] = $lineArr[2];
            $customerArr[$lineArr[0]]["email"] = $lineArr[3];
            $customerArr[$lineArr[0]]["gender"] = $lineArr[4];
            $customerArr[$lineArr[0]]["dob"] = $lineArr[5];
            $customerArr[$lineArr[0]]["country_id"] = $lineArr[6];
        }
        //遍历客户，进行数据插入
        foreach($customerArr as $customerId => $customerInfo){
            $originInfo = $this->getOriginCustomerInfo($customerId);
            $diffKeys = $this->diffOriginAndNew($originInfo,$customerInfo);
            if(empty($diffKeys)){
                continue;
            }
            $updateInfo = array_intersect_key($customerInfo,array_flip($diffKeys));
            $this->updateCustomerInfo($customerId,$updateInfo);
            $this->writeChange($customerId,$originInfo,$customerInfo,$diffKeys,$writeCustomerFile);
            foreach($diffKeys as $key){
                $statisticsArr[$key] +=1;
            }    
        }
        //统计最终变化量
        $statisticsLine = implode("\t",$statisticsArr);
        file_put_contents($writeStatisticsFile,$statisticsLine."\n",FILE_APPEND);
    }

    /**
     * @brief   update customer Info
     **/
    private function updateCustomerInfo($customerId,$updateInfo){
        if(!empty($updateInfo)){
            $updateLineArr = array();
            foreach($updateInfo as $key => $value){
                $line = "{$key}=\"{$value}\"";
                $updateLineArr[] = $line;
            }
            $updateLine = implode(",",$updateLineArr);
            $sql = "update customer_base_statistics set $updateLine where customer_id={$customerId}";
            $this->biDB->query($sql);
        }  
    }
   
    /**
     * @brief   write the data change
     **/
    private function writeChange($customerId,$originInfo,$newInfo,$diffKeys,$writeFile){
        if(!empty($diffKeys)){
            $changeArr = array();
            foreach($diffKeys as $key){
                $changeArr[] = array(
                                    "k" => $key,
                                    "f" => $originInfo[$key],
                                    "t" => $newInfo[$key],
                                );
            }
            $line = "{$customerId} | ".json_encode($changeArr,JSON_UNESCAPED_UNICODE); 
            file_put_contents($writeFile,$line."\n",FILE_APPEND);
        }
    } 
 

    /**
     * @brief   get customer info from table customer_base_analytics
     **/
    private function getOriginCustomerInfo($customerId){
        $sql = "select first_name,last_name,email,gender,dob,country_id from customer_base_statistics where customer_id={$customerId};";
        $queryResult = $this->biDB->query($sql);
        $query = mysqli_fetch_assoc($queryResult);
        return $query;
    }

    /**
     * @brief   diff origin and new customer info
     **/
    private function diffOriginAndNew($originInfo,$newInfo){
        $result = array();
        if(!empty($originInfo) && !empty($newInfo)){
            foreach($originInfo as $key => $originValue){
                switch($key){
                    case "first_name":
                    case "last_name":
                        if(empty($originValue) && !empty($newInfo[$key])){
                            $result[] = $key;
                        } 
                        break;
                    case "email":
                        if(!$this->isEmailLegal($originValue) && $this->isEmailLegal($newInfo[$key])){
                            $result[] = $key;
                        } 
                        break;
                    case "gender":
                        if(!$this->isGenderLegal($originValue) && $this->isGenderLegal($newInfo[$key])){
                            $result[] = $key;
                        } 
                        break;
                    case "dob":
                        if(!$this->isAgeLegal($originValue) && $this->isAgeLegal($newInfo[$key])){
                            $result[] = $key;
                        } 
                        break;
                    case "country_id":
                        if(!$this->isCountryLegal($originValue) && $this->isCountryLegal($newInfo[$key])){
                            $result[] = $key;
                        } 
                        break;
                }
            }
        }
        return $result;   
    }

    /**
     * @brief   get customer info by customer id
     * @param   $customerId
     */
    private function getCustomerInfo($customerId){
        $customerInfo = array(
            "customer_id" => $customerId,
            "first_name" => "",
            "last_name" => "",
            "email" => "",
            "gender" => "",
            "dob" => "",
            "country_id" => "",
        );
    
        //get info from customer table firstly
        $this->getInfoFromCustomer($customerInfo);
        //get info from order and eticket then
        $this->getInfoFromOrderAndEticket($customerInfo);
        return $customerInfo;
    }  

    /**
     * @brief   首先从customer table获取 
     * @param   $customerId
     **/
    private function getInfoFromCustomer(&$customerInfo){
        $customerId = $customerInfo["customer_id"];
        $sql = "select * from customer where customer_id={$customerId}";
        $queryResult = $this->biDB->query($sql);
        $query = mysqli_fetch_assoc($queryResult);
        foreach($customerInfo as $key => &$value){
            if(isset($query[$key]) && !empty($query[$key])){
                if("dob"==$key ){
                    $this->isAgeLegal($query[$key]) ? ($value = $query[$key]) : "" ;
                    continue;
                }
                $value = $query[$key];
            }
        }
        $sql = "select country_id from customer_base_statistics where customer_id={$customerId}";
        $queryResult = $this->biDB->query($sql);
        $query = mysqli_fetch_row($queryResult);
        if(isset($query[0]) && !empty($query[0])){
            $customerInfo["country_id"] = $query[0];
        }
    }
    
    /**
     * @brief   之后从order/order_product_eticket进行信息补充 
     * @param   $customerId
     **/
    private function getInfoFromOrderAndEticket(&$customerInfo){
        //初始化，如果从customer能取到信息，则不会再从order表获取信息
        $customerId = $customerInfo["customer_id"];
        $firstName = $customerInfo["first_name"];
        $lastName = $customerInfo["last_name"];
        $email = $customerInfo["email"];
        $gender = $customerInfo["gender"];
        $dob = $customerInfo["dob"];
        $countryId = $customerInfo["country_id"];

        //解析order数据表，并将合法的字段补充给customer
        $orderIdArr = array();
        $orderData = new OrderData($customerId);
        $orderIdArr = $orderData->getOrderIdList();
        $orderNameArr = $orderData->getNameList();
        $orderEmailArr = $orderData->getEmailList();
        $orderNationArr = $orderData->getNationList();
        foreach($orderNameArr as $one){
            $this->diffName($firstName,$lastName,$one);
        }
        foreach($orderEmailArr as $inputEmail){
            $this->diffEmail($email,$inputEmail);
        }
        foreach($orderNationArr as $inputNation){
            $this->diffNation($countryId,$inputNation);
        }
        ($customerInfo["first_name"]!=$firstName) ? ($customerInfo["first_name"]=$firstName) : "";
        ($customerInfo["last_name"]!=$lastName) ? ($customerInfo["last_name"]=$lastName) : "";
        ($customerInfo["email"]!=$email) ? ($customerInfo["email"]=$email) : "";
        ($customerInfo["country_id"]!=$countryId) ? ($customerInfo["country_id"]=$countryId) : "" ;

        //如果完整就退出
        if(!in_array("",$customerInfo) && $this->isAgeLegal($dob) && $this->isCountryLegal($countryId)){
            return true;
        }

        //剩余信息从order_product_eticket中挖掘，这也是信息最丰富的地方
        foreach($orderIdArr as $orderId){
            $eticketData = new EticketData($orderId);
            $eticketNameArr = $eticketData->getNameList();
            $eticketEmailArr = $eticketData->getEmailList();
            $eticketNationArr = $eticketData->getNationList();
            $eticketGenderArr = $eticketData->getGenderList();
            $eticketDobArr = $eticketData->getDobList();
            foreach($eticketNameArr as $one){
                $this->diffName($firstName,$lastName,$one);
            }
            foreach($eticketEmailArr as $inputEmail){
                $this->diffEmail($email,$inputEmail);
            }
            foreach($eticketNationArr as $inputNation){
                $this->diffNation($countryId,$inputNation);
            }
            foreach($eticketGenderArr as $inputGender){
                $this->diffGender($gender,$inputGender);
            }
            foreach($eticketDobArr as $inputDob){
                $this->diffDob($dob,$inputDob);
            }
            ($customerInfo["first_name"]!=$firstName) ? ($customerInfo["first_name"]=$firstName) : "";
            ($customerInfo["last_name"]!=$lastName) ? ($customerInfo["last_name"]=$lastName) : "";
            ($customerInfo["email"]!=$email) ? ($customerInfo["email"]=$email) : "";
            ($customerInfo["country_id"]!=$countryId) ? ($customerInfo["country_id"]=$countryId) : "" ;
            ($customerInfo["gender"]!=$gender) ? ($customerInfo["gender"]=$gender) : "";
            ($customerInfo["dob"]!=$dob) ? ($customerInfo["dob"]=$dob) : "";
        }
    }
    
    /**
     * @brief   对比country
     **/
    private function diffNation(&$countryId,$inputCountryId){
        if(!empty($inputCountryId)){
            if(!$this->isCountryLegal($inputCountryId)){
                $sql = "select country_id from country_description where name=\"{$inputCountryId}\" limit 1";
                $queryResult = $this->biDB->query($sql);
                while($query = mysqli_fetch_row($queryResult)){
                    $inputCountryId = $query[0];
                }
            }
            //如果不在合法范围就将合法范围的值赋值过去
            if(!$this->isCountryLegal($countryId) && $this->isCountryLegal($inputCountryId)){
                $countryId = $inputCountryId;
            }
        }
    }
    
    /**
     * @brief   对比dob
     **/
    private function diffDob(&$dob,$inputDob){
        if(!empty($inputDob)){
            if(strstr($inputDob,"/")){
                $inputDobArr = explode("/",$inputDob);
                if(count($inputDobArr)==3){
                    $inputDob = $inputDobArr[2]."-".$inputDobArr[0]."-".$inputDobArr[1];
                }   
            }
            //如果不在合法范围就将合法范围的值赋值过去
            if(!$this->isAgeLegal($dob) && $this->isAgeLegal($inputDob)){
                $dob = $inputDob;
            }
        }
    }
    
    /**
     * @brief   对比email
     **/
    private function diffEmail(&$email,$inputEmail){
        //如果不在合法范围就将合法范围的值赋值过去
        if(!$this->isEmailLegal($email) && $this->isEmailLegal($inputEmail)){
            $email = $inputEmail;
        }
    }
    
    /**
     * @brief   对比性别
     **/
    private function diffGender(&$gender,$inputGender){
        //如果不在合法范围就将合法范围的值赋值过去
        if(!$this->isGenderLegal($gender) && $this->isGenderLegal($inputGender)){
            $gender = $inputGender;
        }
    }
 
    /**
     * @brief   对比名字
     **/
    private function diffName(&$firstName,&$lastName,$nameArr){
        if(empty($firstName) && empty($lastName)){
            if(isset($nameArr["first"])){
                $firstName = $nameArr["first"];
            }
            if(isset($nameArr["last"])){
                $lastName = $nameArr["last"];
            }
        }
    }
 
    /**
     * @brief   get customer id
     **/
    private function getCustomerId(){
        $customerIdArr = array();
        $sql = "select customer_id from customer_base_statistics where first_name='' or last_name='' or gender not in ('m','f') or email='' or country_id='' or dob>'2006-00-00' or dob<'1910-00-00';";
        $queryResult = $this->biDB->query($sql);
        while($query = mysqli_fetch_row($queryResult)){
            $customerIdArr[] = $query[0];
        }
        return $customerIdArr;
    }
    
    /**
     * @brief   isAgeLegal,判断年龄是否合法，认为注册用户<8岁或者>100岁认为无效
     * @param   $dob
     **/
    private function isAgeLegal($dob){
        $result = false;
        $year = substr($dob,0,4);
        $age = 2017-$dob;
        if($age<=100 && $age>=8){
            $result = true;
        }   
        return $result;
    } 

    /**
     * @brief   isCountryLegal: 国家id<500的认为是合法的
     * @param   $dob
     **/
    private function isCountryLegal($countryId){
        $result = false;
        if(ctype_digit($countryId) && $countryId<500 && $countryId>0){
            $result = true;
        }   
        return $result;
    } 

    /**
     * @brief   email is legal or not
     **/
    private function isEmailLegal($email){
        $result = false;
        if(strstr($email,"@")){
            $result = true;
        }   
        return $result;
    }    
    
    /**
     * @brief   gender is legal or not
     **/
    private function isGenderLegal($gender){
        $result = false;
        $legalGenderArr = array("m","f");
        if(in_array($gender,$legalGenderArr)){
            $result = true;
        }   
        return $result;
    }    

}


