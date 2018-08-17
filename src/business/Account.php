<?php
/**
 * User: lixd
 * Date: 2018/08/10
 * Time: 10:00
 * description:Account
 */
namespace src\business;

 
use src\business\Base;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBType;
use src\keypair\Bytes; 

class Account extends Base
{
    
     public function __construct(){
        parent::__construct();

     }

  
     /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getInfo($address){
        if(!$address){
          $ret['status'] = -2;
          return $ret;
        }
        $ret = $this->requestInfo($address);
        return $ret;
     }
    /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getNonceInfo($address){
        $this->logger->addNotice("Account,getNonceInfo:$address");
        $nonce = $this->getNonce($address);
        // var_dump($nonce);exit;
        if($nonce>=0){
          $ret['status'] = 0;
          $ret['data']['nonce'] = $nonce;
        }
        else{
          $ret['status'] = -1;
        }
        return $ret;
     }

     /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getBalanceInfo($address){
        $this->logger->addNotice("Account,getBalanceInfo:$address");
        $ret = $this->requestInfo($address);
        if($ret['status']==0){
            //success
            $retData['status'] = 0;
            $retData['data']['balance'] = $ret['data']->balance;
        }
        else{
            //fail
            $retData['status'] = -1;
        }
        return $retData;

     }

     /**
      * [isAddresscheck description]
      * @return boolean [description]
      */
     public function checkAddress($address){
        $this->logger->addNotice("Account,checkAddress:$address");
        $ret = $this->checkAddressEn($address);
        // var_dump($ret);exit;
        if($ret==0){
            //success
            $retData['status'] = 0;
        }
        else{
            //fail
            $retData['status'] = -1;
        }
        return $retData;
     } 

     /**
      * [isAddresscheck description]
      * @return boolean [description]
      */
     public function checkPublicKey($publicKey){
        $this->logger->addNotice("Account,checkPublicKey:$publicKey");
        $ret = $this->checkPublicKeyEn($publicKey);
        if($ret==0){
            //success
            $retData['status'] = 0;
        }
        else{
            //fail
            $retData['status'] = -1;
        }
        return $retData;
      
     }
     /**
      * [getTransactionHistory description]
      * @param  [type] $transactionHash [description]
      * @return [type]                  [description]
      */
     public function getTransactionHistory($transactionHash){
        // http://seed1.bumotest.io:26002/getTransactionHistory?hash=0326e8822d5e28d2790b6fdc8cfb3519bd9923560f58e2cfae3c4459db2c3cc3
     }
     
    // /**
    //   * [isAddresscheck description]
    //   * @return boolean [description]
    //   */
    //  public function checkPirvateKey($privateKey){
    //     $this->logger->addNotice("Account,checkPirvateKey:$privateKey");
    //     $ret = $this->checkPirvateKeyEn($privateKey);
    //     if($ret==0){
    //         return $this->responseJson(null,0);
    //     }
    //     else{
    //         return $this->responseJson(null,3003);
    //     }
      
    //  }
 

}

?>