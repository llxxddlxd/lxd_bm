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

require $baseurl."Protocol/Transaction.php";
require $baseurl."Protocol/Operation.php";
require $baseurl."Protocol/OperationCreateAccount.php";
require $baseurl."Protocol/AccountThreshold.php";
require $baseurl."Protocol/accountPrivilege.php";

//log
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//配置文件
use conf\confController;
use conf\errDesc;
class Base{
    public $logger;
    // private $privKey;
    // private $pubKey;
    // private $address;  

    public function __construct()
    {
        
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
    public function getNonce(){
        $this->logger->addNotice("getNonce");
        $conf = new confController();
        $info = $conf->getConfig();
        $this->logger->addNotice("getNonce,config",$info);
        $baseUrl = $info['base']['testUrl'];
        $sourceAddress = $info['base']['sourceAddress'];
        $baseUrl .= "getAccount?address="  .$sourceAddress;
        $this->logger->addNotice("getNonce,baseUrl:$baseUrl");
        $result = $this->request_get($baseUrl);
        $this->logger->addNotice("getNonce,result:$result");
        $ret = array();
        $ret['status'] = -1;
        if($result){
            $resultArr = json_decode($result);
            $error_code = isset($resultArr->error_code)?$resultArr->error_code:"";
            if($error_code===0){
                $ret['status'] = 0;
                $ret['nonce'] = $resultArr->result->nonce;

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
            // return json($respArr, 200);
        }else{
            $this->logger->addError("response unknown data type");
            // return json([],500);
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


 
}



?>