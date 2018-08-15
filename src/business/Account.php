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
     public function active($priKey,$pubKey,$destAddress,$sourceAddress,$rawPivateKey,$rawPublicKey){
        if(!$sourceAddress){ //没有传，取配置文件中的参数
            $conf = new confController();
            $info = $conf->getConfig();   
            $this->logger->addNotice("getNonce,config",$info);
            $sourceAddress = $info['base']['sourceAddress'];
        }
      // $destAddress,$initBalance,$sourceAddress,$metadata
        $this->logger->addNotice("Account,active,sourceAddress:$sourceAddress");
        $nonce = $this->getNonce($sourceAddress);
        $this->logger->addNotice("Account,addressNonce".$nonce);
        if($nonce<0){
            return $this->responseJson(null,3001);
        }
        //1Transaction
        $this->logger->addNotice("Transaction start");
        $tran = new \Protocol\Transaction();
        // $this->logger->addNotice("Transaction new");
        $tran->setNonce($nonce+1);
        $tran->setSourceAddress($sourceAddress);
        $tran->setMetadata("test");
        $tran->setGasPrice(1000);
        $tran->setFeeLimit(10000);
        // $this->logger->addNotice("Transaction end");
        //2Operation
        $opers = new RepeatedField(GPBType::MESSAGE, \Protocol\Operation::class);
        $oper = new \Protocol\Operation();
        $oper->setSourceAddress($sourceAddress);
        $oper->setMetadata("test");
        $oper->setType(1);/*          CREATE_ACCOUNT = 1;*/
        $this->logger->addNotice("opers end");
        //3该数据结构用于创建账户
        $createAccount = new \Protocol\OperationCreateAccount();
        $createAccount->setDestAddress($destAddress);
        $createAccount->setInitBalance(123456789);
        $accountThreshold = new \Protocol\AccountThreshold();
        $accountThreshold->setTxThreshold(1);
        $accountPrivilege = new \Protocol\accountPrivilege();
        $accountPrivilege->setMasterWeight(1);
        $accountPrivilege->setThresholds($accountThreshold);
        $this->logger->addNotice("createAccount end");

        $createAccount->setPriv($accountPrivilege);
        //4填充到operation中
        $oper->setCreateAccount($createAccount);
        $opers[] = $oper;
        $tran->setOperations($opers);
        $this->logger->addNotice("start serialize");
        //5序列化，转16进制
        $serialTran = bin2hex($tran->serializeToString());
        // echo bin2hex($serialTran);exit;
        $ByteOb = new \src\keypair\Bytes();
        $this->logger->addNotice("Account,serialTran:".($serialTran));
        //解析用
        // $tranParse = new \Protocol\Transaction();Parses a protocol buffer contained in a string.
        // $tranParse->mergeFromString($serialTran);
        // var_dump($tranParse->getOperations()[0]);
      
        //6通过私钥对交易（transaction_blob）签名。
        $rawPivateKeyByte = $ByteOb->toStr($rawPivateKey);
        $rawPublicKeyByte = $ByteOb->toStr($rawPublicKey);
        
        $signData = $this->ED25519Sign($serialTran,$rawPivateKeyByte,$rawPublicKeyByte);
        $signDataHex = bin2hex($signData);
        $this->logger->addNotice("Account,signData:$signDataHex");
        
        //7填充数据
        $fill_data = $this->fillData($serialTran,$signDataHex,$pubKey);
        $this->logger->addNotice("Account,fill_data",$fill_data);

        //8发送
        $conf = new confController();
        $confinfo = $conf->getConfig();

        $transactionUrl = $confinfo['base']['testUrl'] . "submitTransaction" ;
        $this->logger->addNotice("Account,transactionUrl:$transactionUrl");
        // var_dump($fill_data);exit;
        $ret = $this->request_post($transactionUrl,$fill_data);
        $this->logger->addNotice("Account,ret_end:".$ret);
        $retArr = json_decode($ret,true);
        var_dump($retArr);exit();
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