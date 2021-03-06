<?php
namespace examples;
use examples\Base;

use src\business\Account;
use src\business\Transaction;
use conf\confController; 

class TestTransaction extends base{
    private $confInfo;
    public function __construct(){
        parent::__construct();
        $conf = new confController();
        $this->confInfo = $conf->getConfig();
        $this->logger->addNotice("getInfo,config",$this->confInfo);
        
    }

    /**
     * [testActive description
     * 激活账户，指将未写在区块链上的账户记录在区块链上，通过getInfo接口可以查询到的账户。前提是待处理的账户必须是未激活账户]
     * @return [type] [description]
     */
	public function testActivate(){

        //0获取配置文件信息，用来测试
        $sourceAddress = $this->confInfo['base']['sourceAddress'];
        $sourcePriKey = $this->confInfo['base']['sourcePriKey'];
        $this->logger->addNotice("testActivate,start,sourceAddress:$sourceAddress,sourcePriKey:$sourcePriKey");
        $destAddress = "buQhHr2NjYquHCNYtRcWoCPqfYiVKek4CuSD";
        $initBalance = 10000000;

        $Account = new Account();
        $retArr = $Account->activateAddress($sourceAddress,$sourcePriKey,$destAddress,$initBalance);
        return $this->responseJson(null,$retArr['status']);
	}

    /**
     * [addressInfo description]
     * @return [type] [description]
     */
    public function addressInfo(){
        $address = $_GET['address']; 

        $Account = new Account();
        $ret = $Account->getInfo($address);  
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

    }

    /**
     * [nonceInfo description]
     * @return [type] [description]
     */
    public function nonceInfo(){
        $address = $_GET['address'];
        $Account = new Account();
        $ret = $Account->getNonceInfo($address);
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

    }
    /**
     * [balanceInfo description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    public function balanceInfo(){
        $Account = new Account();
        $address = $_GET['address'];
        $ret =$Account->getBalanceInfo($address);
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }
    }  
    /**
     * [checkAddress description]
     * @return [type] [description]
     */
    public function checkAddress(){
        $address = $_GET['address'];
        $Account = new Account();
        $ret =$Account->checkAddress($address); 
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

    }

    /**
     * [checkPublicKey description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
     public function checkPublicKey(){
        $publicKey = $_GET['publicKey'];
        $Account = new Account();
        $ret = $Account->checkPublicKey($publicKey);
        // var_dump($ret);exit;
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

    } 

    /**
     * [checkPublicKey description]
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
     public function checkHash(){
        $hash = $_GET['hash'];
        $Account = new Account();
        $Transaction = new Transaction();
        $ret = $Transaction->checkHash($hash);
        // echo json_encode($ret);exit;
        if($ret['status'] == 0){
            //success
            return $this->responseJson($ret,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

    } 

    /**
     * [设置地址的metadata]
     */
    public function setMetadata(){
        //0获取配置文件信息，用来测试
        $sourceAddress = $this->confInfo['base']['sourceAddress'];
        $sourcePriKey = $this->confInfo['base']['sourcePriKey'];
        $this->logger->addNotice("setMetadata,start,sourceAddress:$sourceAddress,sourcePriKey:$sourcePriKey");
        $Account = new Account();
        $metadataArray['metadataKey'] = '1';
        $metadataArray['metadataValue'] = '2';
        $metadataArray['metadataVersion'] = '3';
        $ret = $Account->setMetadata($sourceAddress,$sourcePriKey,$metadataArray);
        return $this->responseJson(null,$retArr['status']);

    }
	
}


?>