<?php
/**
 * User: lixd
 * Date: 2018/08/10
 * Time: 10:00
 * description:Base
 */
namespace src\business;

//probuf
$baseurl = dirname(dirname(__DIR__)) ."/protobuf/";

require $baseurl."vendor/autoload.php";
require $baseurl."GPBMetadata/Common.php";
require $baseurl."GPBMetadata/Chain.php";


//log
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use src\keypair\Bytes;

//配置文件
use conf\confController;

class Base{
    public $logger;
    private $alphabet; 
    public $baseBumoUrl = "http://seed1.bumotest.io:26002/";

    public function __construct()
    {
        
        $this->alphabet = '123456789AbCDEFGHJKLMNPQRSTuVWXYZaBcdefghijkmnopqrstUvwxyz'; //bumo   
        // create a log channel
        $log = new Logger('name');
        $date = date("Ymd");
        $filepath= dirname(dirname(dirname(__FILE__))).'/log/'.$date.'.log';
        $log->pushHandler(new StreamHandler( $filepath, Logger::DEBUG));

        $this->logger = $log;        
        
    }
 
	 
    /**
     * [getNonce description]
     * @return [type] [description]
     */
    public function getNonce($address){
        $ret = $this->requestInfo($address);
        if($ret['status'] == 0){
            if(isset($ret['data']->nonce))
                return $ret['data']->nonce;
            else
                return 0;
        }
        else{
            return -1;
        } 


    }
  
  
      /**
     * [ED25519 description]
     */
    public function ED25519($byteStr){
        //进来是32位字节 字符串，返回也是32位字符串
        return  ed25519_publickey($byteStr);
        // return $byteStr;
    }


    /**
     * [ED25519Sign description]
     */
    public function ED25519Sign($message, $mySecret, $myPublic){
        // return $message;
        $signature = ed25519_sign($message, $mySecret, $myPublic);
        return $signature;
    } 

    /**
     * [ED25519Check description]
     */
    public function ED25519Check($byteStr){
        // $status = ed25519_sign_open($message,  $myPublic, $signature);
        // if($status==TRUE){
        // success
        // }
        // else{
        // fail
        // }
    }


    public function fillData($transaction_blob,$sign_data,$public_key){
        $temp['sign_data'] = $sign_data;
        $temp['public_key'] = $public_key;
        $ret["signatures"] = array();
        array_push($ret["signatures"], $temp);
        $ret['transaction_blob'] = $transaction_blob;
        return $ret;
    }

    

    /**
     * [base58_decode description]
     * @param  [type] $base58 [description]
     * @return [type]         [description]
     */
    public function base58Decode($base58)
    {
        if (is_string($base58) === false) {
            return false;
        }
        if (strlen($base58) === 0) {
            return '';
        }
        $indexes = array_flip(str_split($this->alphabet));
        $chars = str_split($base58);
        foreach ($chars as $char) {
            if (isset($indexes[$char]) === false) {
                return false;
            }
        }
        $decimal = $indexes[$chars[0]];
        for ($i = 1, $l = count($chars); $i < $l; $i++) {
            $decimal = bcmul($decimal, 58);
            $decimal = bcadd($decimal, $indexes[$chars[$i]]);
        }
        $output = '';
        while ($decimal > 0) {
            $byte = bcmod($decimal, 256);
            $output = pack('C', $byte) . $output;
            $decimal = bcdiv($decimal, 256, 0);
        }
        foreach ($chars as $char) {
            if ($indexes[$char] === 0) {
                $output = "\x00" . $output;
                continue;
            }
            break;
        }
        return $output;
    }
 
    /**
     * [SHA256 description]
     * @param [type] $str [description]
     */
    public function SHA256($str){
        $re=hash('sha256', $str, true);
        return $re;
    }


    /**
     * [hexDecode description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function hexDecode($string){
        $s = ''; 
        for ($i=0; $i<strlen($string); $i=$i+2) {
            $temp = substr($string, $i,2);
            $temp1 = chr(hexdec($temp));
            $s .= $temp1;
        } 

        return $s; 
    }

    /**
     * [checkPublicKey description]
     * @param  [type] $publicKey [description]
     * @return [type]            [description]
     */
    public function checkPublicKeyEn($publicKey){
        //1 not null
        if(!$publicKey)
            return -1;
        // if (!HexFormat.isHexString($publicKey)) {
        //     throw new EncException("Invalid publicKey");
        // }
        //2 prefix
        $Bytes = new Bytes();
        $buffString = $this->hexDecode($publicKey);
        $buffStringArray = $Bytes->getBytes($buffString);
        // var_dump($buffStringArray);exit;
        if (strlen($buffString) < 6 || $buffStringArray[0] != 176 || $buffStringArray[1] != 1) {
            return -2;
        }
        //3区分checksum
        $len = strlen($buffString);
        $checkSum  = substr($buffString, $len-4);
        $buff = substr($buffString, 0,$len - 4);
        //4
        $firstHash = $this->SHA256($buff);
        $secondHash = $this->SHA256($firstHash); 
        //5
        $hash2 = substr($secondHash,0,4);
        if($checkSum== $hash2){
            return 0;
        }
        else{
            return -3;
        }

    }  

    /**
     * [checkPublicKey description]
     * @param  [type] $publicKey [description]
     * @return [type]            [description]
     */
    public function checkAddressEn($address){
        if(!$address){
            return -1;
        }
        //1解密
        $addressRet = $this->base58Decode($address);
        // var_dump(strlen($addressRet));exit;
        $Byte = new Bytes();
        $addressByteArr = $Byte->getBytes($addressRet);
        // var_dump($addressByteArr);exit;
        //2基本验证
        if (strlen($addressRet) != 27 || $addressByteArr[0] != 1 || $addressByteArr[1] != 86
            || $addressByteArr[2] != 1) {
            return -2;
        }
        //3
        $len = strlen($addressRet);
        $checkSum = substr($addressRet,$len-4);
        $newBuff = substr($addressRet,0,$len-4);
        // echo $len.'  '.strlen($checkSum).'  '.strlen($newBuff);exit;
        $firstHash = $this->SHA256($newBuff);
        $secondHash = $this->SHA256($firstHash);
        $hashData = substr($secondHash, 0,4);
        if($checkSum==$hashData){
            return 0;
        }
        else{
            return -3;
        }

    }


    /**
     * [getRawPrivateKey 通过私钥，获取rawkey]
     * @return [type] [description]
     */
    public function getRawPrivateKey($privateKey){
        $de58 = $this->base58Decode($privateKey);
        $rawKey = substr($de58,4,32);
        $byte = new Bytes();
        $rawKeyBytes = $byte->getBytes($rawKey);
        $ret['rawKeyString'] = $rawKey;
        $ret['rawKeyBytes'] = $rawKeyBytes;
        return $ret;

    }

      /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    public function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $param = json_encode($param);
  
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json")); 
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl

        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
        curl_close($ch);
        $this->logger->addWarning("request_post,code:".$httpCode);
        return $data;
    }

    /**
     * [request_get description]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public function request_get($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;    //返回json对象
    }

    /**
     * [requestInfo description]
     * @return [type] [description]
     */
    public function requestInfo($address){
        $realUrl = $this->baseBumoUrl . "getAccount?address="  . $address;
        $this->logger->addNotice("Base,requestInfo:$realUrl");
        $result = $this->request_get($realUrl);
        $this->logger->addNotice("requestInfo,result:$result");
        $ret = array();
        $ret['status'] = -1;
        if($result){
            $resultArr = json_decode($result);
            $error_code = isset($resultArr->error_code)?$resultArr->error_code:"";
            if($error_code===0){
                $ret['status'] = 0;
                $ret['data'] = $resultArr->result;
            } 
        }
        return $ret;
    }
}



?>