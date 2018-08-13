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
      * [active description]
      * @param  [type] $destAddress   [description]
      * @param  [type] $sourceAddress [description]
      * @return [type]                [description]
      */
     public function active($priKey,$pubKey,$destAddress){
      // $destAddress,$initBalance,$sourceAddress,$metadata
        $this->logger->addNotice("Account,active");
        $ret = $this->getNonce();
        $this->logger->addNotice("Account,ret",$ret);
        if($ret['status']===0){
            $nonce = $ret['nonce'];
            // echo $nonce;
            //1Transaction
            $tran = new \Protocol\Transaction();
            $tran->setNonce($nonce);
            $tran->setSourceAddress("buQiu6i3aVP4SXBNmPsvJZxwYEcEBHUZd4Wj");
            $tran->setMetadata("test-active");
            $tran->setGasPrice(1000);
            $tran->setFeeLimit(10000);
            //2Operation
            $opers = new RepeatedField(GPBType::MESSAGE, \Protocol\Operation::class);
            $oper = new \Protocol\Operation();
            $oper->setSourceAddress("buQiu6i3aVP4SXBNmPsvJZxwYEcEBHUZd4Wj");
            $oper->setMetadata("test-active");
            $oper->setType(1);/*          CREATE_ACCOUNT = 1;*/
            //3该数据结构用于创建账户
            $createAccount = new \Protocol\OperationCreateAccount();
            $createAccount->setDestAddress($destAddress);
            $createAccount->setInitBalance(123456789);
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
            //5序列化
            $serialTran = $tran->serializeToString();
            // $bytesOb = new Bytes();
            // var_dump($bytesOb->getBytes($serialTran));exit;
            $this->logger->addNotice("Account,serialTran:$serialTran");
            // echo '----'.strlen($serialTran) .'------';exit;

            // $tranParse = new \Protocol\Transaction();Parses a protocol buffer contained in a string.
            // $tranParse->mergeFromString($serialTran);
            // var_dump($tranParse->getOperations()[0]);
          // return $this->responseJson($ret,0);
          
            //6通过私钥对交易（transaction_blob）签名。
            $signData = $this->ED25519Sign($serialTran,$priKey,$pubKey);
            $this->logger->addNotice("Account,signData:$signData");
            //7填充数据
            $fill_data = $this->fillData($serialTran,$signData,$pubKey);
            $this->logger->addNotice("Account,fill_data",$fill_data);
            //8发送
            $transactionUrl = $this->getTransactionUrl();
            $this->logger->addNotice("Account,transactionUrl:$transactionUrl");
            $ret = $this->request_post($this->transactionUrl,json_encode($fill_data));
            $this->logger->addNotice("Account,ret:".$ret);
            var_dump($ret);exit;
        }
        else{
          echo "fail";
        }
     }
 
     /**
      * [getInfo description]
      * @param  [type] $address [description]
      * @return [type]          [description]
      */
     public function getInfo($address){
     	  echo 1;exit;
     }
}

?>