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

include $baseurl."Protocol/Transaction.php";
include $baseurl."Protocol/Operation.php";
include $baseurl."Protocol/OperationCreateAccount.php";
include $baseurl."Protocol/AccountThreshold.php";
include $baseurl."Protocol/AccountPrivilege.php";

//log
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use src\keypair\Bytes;

//配置文件
use conf\confController;
use conf\errDesc;
class Base{
    public $logger;
    private $alphabet;
    // private $privKey;
    // private $pubKey;
    // private $address;  

    public function __construct()
    {
        
        // $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'; //传统
        $this->alphabet = '123456789AbCDEFGHJKLMNPQRSTuVWXYZaBcdefghijkmnopqrstUvwxyz'; //bumo   
        // create a log channel
        $log = new Logger('name');
        $date = date("Ymd");
        $filepath= dirname(dirname(dirname(__FILE__))).'/log/'.$date.'.log';
        $log->pushHandler(new StreamHandler( $filepath, Logger::DEBUG));

        // add records to the log
        // $log->addError('Bar');
        $this->logger = $log;        
        
    }
    // /**
    //  * [setPriKey description]
    //  * @param [type] $key [description]
    //  */
    //  public function setPriKey($key){
    //     $this->logger->addNotice("business-base,privatekey:$key");
    //     $this->privKey = $key;
    //  }
    //  /**
    //   * [setPubKey description]
    //   * @param [type] $key [description]
    //   */
    //  public function setPubKey($key){
    //     $this->logger->addNotice("business-base,publickey:$key");
    //     $this->pubKey = $key;
    //  }
    //  /**
    //   * [setaddress description]
    //   * @param  [type] $address [description]
    //   * @return [type]          [description]
    //   */
    //  public function setaddress($address){
    //     $this->logger->addNotice("business-base,address:$address");
    //     $this->address = $address;
    //  } 
     
	 
    /**
     * [getNonce description]
     * @return [type] [description]
     */
    public function getNonce($address=''){
        $conf = new confController();
        $info = $conf->getConfig();   
        $this->logger->addNotice("getNonce,config",$info);
        if($address){
            $sourceAddress = $address;
        }
        else{
            $sourceAddress = $info['base']['sourceAddress'];
        }
        $baseUrl = $info['base']['testUrl'];
        $baseUrl .= "getAccount?address="  .$sourceAddress;

        $ret = $this->requestInfo($baseUrl);
        if($ret['status'] == 0){
            if(isset($ret['data']->result->nonce))
                return $ret['data']->result->nonce;
            else
                return 0;
        }
        else{
            return -1;
        } 


    }
    /**
     * [requestInfo description]
     * @param  [type] $baseUrl [description]
     * @return [type]          [description]
     */
    public function requestInfo($baseUrl){
        $this->logger->addNotice("requestInfo,baseUrl:$baseUrl");
        $result = $this->request_get($baseUrl);
        $this->logger->addNotice("requestInfo,result:$result");
        $ret = array();
        $ret['status'] = -1;
        if($result){
            $resultArr = json_decode($result);
            $error_code = isset($resultArr->error_code)?$resultArr->error_code:"";
            if($error_code===0){
                $ret['status'] = 0;
                $ret['data'] = $resultArr;

            } 
        }
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
     * [responseJson description]
     * @param  [type]  $data   [description]
     * @param  integer $status [description]
     * @return [type]          [description]
     */
    public function responseJson($data = null, $status = 0){
        $content = null;
        if(is_array($data) || is_null($data)){
            $respArr = Array(
                "status" => $status,
                "desc" => errDesc::getErrorDesc($status)
            );
            if($data){
                $respArr = array_merge($respArr, $data);
            }
            $this->logger->addNotice("response success data",$respArr);
            header("Content-Type:text/html;charset=utf-8");
            echo urldecode(json_encode($respArr));
        }else{
            $this->logger->addError("response unknown data type");
            // header("Content-Type:text/html;charset=utf-8");
            // echo urldecode(json_encode($result));
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
        // $ret['transaction_blob'] = "0a2462755173757248314d34726a4c6b666a7a6b7852394b584a366a537532723978424e4577100718c0843d20e8073a37080122330a246275516f50326552796d4163556d33757657675138526e6a7472536e58425866417a73561a0608011a0208012880ade204";
        // $temp['sign_data'] = "9C86CE621A1C9368E93F332C55FDF423C087631B51E95381B80F81044714E3CE3DCF5E4634E5BE77B12ABD3C54554E834A30643ADA80D19A4A3C924D0B3FA601";
        // $temp['public_key'] = "b00179b4adb1d3188aa1b98d6977a837bd4afdbb4813ac65472074fe3a491979bf256ba63895";
        // $ret["signatures"] = $temp;        
        $ret['transaction_blob'] = $transaction_blob;
        $temp['sign_data'] = $sign_data;
        $temp['public_key'] = $public_key;
        $ret["signatures"] = $temp;
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
}



?>