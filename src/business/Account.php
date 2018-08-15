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
use conf\confController; 

class Account extends Base
{

     public function __construct(){
        parent::__construct();
     }

     /**
      * [active 激活账户，指将未写在区块链上的账户记录在区块链上，通过getInfo接口可以查询到的账户。前提是待处理的账户必须是未激活账户]
      * @param  [type] $destAddress   [description]
      * @param  [type] $sourceAddress [description]
      * @return [type]                [description]
      */
     public function active($priKey,$pubKey,$destAddress,$rawPivateKey,$rawPublicKey){
        $conf = new confController();
        $confinfo = $conf->getConfig();   
        $this->logger->addNotice("getNonce,config",$confinfo);
        $sourceAddress = $confinfo['base']['sourceAddress'];
        $sourcePriKey = $confinfo['base']['sourcePriKey'];
        //1根据私钥，解析出原数组以及公钥
        $sourceRawPriKeyRet = $this->getRawPrivateKey($sourcePriKey);
        $sourceRawPriKeyString = $sourceRawPriKeyRet['rawKeyString'];
        $sourceRawPriBytes = $sourceRawPriKeyRet['rawKeyBytes'];
        $this->logger->addNotice("getNonce,1:".json_encode($sourceRawPriBytes));
        //$sourceRawPubKey = $this->ED25519($sourceRawPriKey);
        $pub = new \src\keypair\GeneratePublicKey();
        $pub->setRawKey($sourceRawPriBytes);   
        $sourceRawPubKey = $pub->getRawKey();
        $pub->setPubKey();    
        $sourcePubKey = $pub->getPubKey(); 
        $this->logger->addNotice("getNonce,2:".($sourcePubKey));
        //开始
        $this->logger->addNotice("Account,active,sourceAddress:$sourceAddress,destAddress:$destAddress");
        $nonce = $this->getNonce($sourceAddress);
        $this->logger->addNotice("Account,addressNonce".$nonce);
        if($nonce<0){
            return $this->responseJson(null,3001);
        }
        $nonce++;        
        //1Transaction
        $this->logger->addNotice("Transaction start");
        $tran = new \Protocol\Transaction();
        $tran->setNonce($nonce);
        $tran->setSourceAddress($sourceAddress);
        $tran->setMetadata(0x01);
        $tran->setGasPrice(1000);
        $tran->setFeeLimit(10000);
        //2Operation
        $this->logger->addNotice("opers start");
        $opers = new RepeatedField(GPBType::MESSAGE, \Protocol\Operation::class);
        $oper = new \Protocol\Operation();
        $oper->setSourceAddress($sourceAddress);
        $oper->setMetadata(0x01);
        $oper->setType(1);/*          CREATE_ACCOUNT = 1;*/
        //3该数据结构用于创建账户
        $this->logger->addNotice("createAccount start");
        $createAccount = new \Protocol\OperationCreateAccount();
        $createAccount->setDestAddress($destAddress);
        $createAccount->setInitBalance(10000000);
        $accountThreshold = new \Protocol\AccountThreshold();
        $accountThreshold->setTxThreshold(1);
        $accountPrivilege = new \Protocol\accountPrivilege();
        $accountPrivilege->setMasterWeight(1);
        $accountPrivilege->setThresholds($accountThreshold);
        $createAccount->setPriv($accountPrivilege);
        //4填充到operation中
        $oper->setCreateAccount($createAccount);
        $opers[] = $oper;
        $tran->setOperations($opers);
        //5序列化，转16进制
        $this->logger->addNotice("serialize start");
        $serialTran = bin2hex($tran->serializeToString());
        // echo bin2hex($serialTran);exit;
        $this->logger->addNotice("serialize,serialTran:".($serialTran));
        //解析用
        // $tranParse = new \Protocol\Transaction();Parses a protocol buffer contained in a string.
        // $tranParse->mergeFromString($serialTran);
        // var_dump($tranParse->getOperations()[0]);
      
        //6通过私钥对交易（transaction_blob）签名。
        $this->logger->addNotice("sign start");
        $ByteOb = new \src\keypair\Bytes();
        // $rawPivateKeyByte = $ByteOb->toStr($rawPivateKey);
        $sourceRawPubKeyString = $ByteOb->toStr($sourceRawPubKey);
        
        $signData = $this->ED25519Sign($serialTran,$sourceRawPriKeyString,$sourceRawPubKeyString);
        $signDataHex = bin2hex($signData);
        $this->logger->addNotice("sign,signData:$signDataHex");
        
        //7填充数据
        $fill_data = $this->fillData($serialTran,$signDataHex,$sourcePubKey);
        $this->logger->addNotice("fill_data,info".json_encode($fill_data));

        //8发送 
        $transactionUrl = $confinfo['base']['testUrl'] . "submitTransaction" ;
        $this->logger->addNotice("active,transactionUrl:$transactionUrl");
        $realData['items'] = array();
        array_push($realData['items'],$fill_data);
        $ret = $this->request_post($transactionUrl,$realData);
        $this->logger->addNotice("active,ret_end:".$ret);
        echo ($ret);exit();
        $retArr = json_decode($ret,true);
        if($retArr['success_count']==1){
            //success
            return $this->responseJson(null,0);
        }
        else{
            //fail
            return $this->responseJson(null,3001);
        }
     }

     
     /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getInfo($address){
        $this->logger->addNotice("Account,getInfo:$address");
        $conf = new confController();
        $info = $conf->getConfig();
        $this->logger->addNotice("getInfo,config",$info);
        $baseUrl = $info['base']['testUrl'];
        $baseUrl .= "getAccount?address="  .$address;
        $this->logger->addNotice("getInfo,baseUrl:$baseUrl");
        $ret = $this->requestInfo($baseUrl);
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
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getNonceInfo($address){
        $this->logger->addNotice("Account,getNonceInfo:$address");
        $nonce = $this->getNonce($address);
        if($nonce){
            //success
            $retData['data']['nonce'] = $nonce;
            return $this->responseJson($retData,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

     }

     /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getBalanceInfo($address){
        $this->logger->addNotice("Account,getBalanceInfo:$address");

        $conf = new confController();
        $info = $conf->getConfig();   
        $this->logger->addNotice("getBalanceInfo,config",$info);
        $baseUrl = $info['base']['testUrl'];
        $baseUrl .= "getAccount?address="  .$address;

        $ret = $this->requestInfo($baseUrl);
        if($ret['status']==0){
            //success
            // var_dump($ret);exit;
            $retData['data']['balance'] = $ret['data']->result->balance;
            return $this->responseJson($retData,0);
        }
        else{
            //fail
            return $this->responseJson(null,3002);
        }

     }

     /**
      * [isAddresscheck description]
      * @return boolean [description]
      */
     public function checkAddress($address){
       
        $this->logger->addNotice("Account,checkAddress:$address");
        $ret = $this->checkAddressEn($address);
        if($ret==0){
            return $this->responseJson(null,0);
        }
        else{
            return $this->responseJson(null,3002);
        }
     } 

     /**
      * [isAddresscheck description]
      * @return boolean [description]
      */
     public function checkPublicKey($publicKey){
        $this->logger->addNotice("Account,checkPublicKey:$publicKey");
        $ret = $this->checkPublicKeyEn($publicKey);
        if($ret==0){
            return $this->responseJson(null,0);
        }
        else{
            return $this->responseJson(null,3003);
        }
      
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