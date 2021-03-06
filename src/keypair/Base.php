<?php
/**
 * User: lixd
 * Date: 2018/08/08
 * Time: 10:00
 * description:base
 */
namespace src\keypair;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class Base{
    public $logger;
    private $alphabet;

    function __construct(){

        // $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'; //传统
        $this->alphabet = '123456789AbCDEFGHJKLMNPQRSTuVWXYZaBcdefghijkmnopqrstUvwxyz'; //bumo 字母表

        // create a log channel
        $log = new Logger('name');
        $date = date("Ymd");
        $filepath= dirname(dirname(dirname(__FILE__))).'/log/'.$date.'.log';
        $log->pushHandler(new StreamHandler( $filepath, Logger::DEBUG));

        $this->logger = $log;
    }
	/**
	 * [获取头数组]
	 * @return [type] [description]
	 */
	abstract public function getPrefix();

	/**
	 * [获取版本]
	 * @return [type] [description]
	 */
	abstract public function getVersion();
	 
	/**
     * [SHA256Hex description]
     * @param [type] $str [description]
     */
    public function SHA256Hex($str){
        $re=hash('sha256', $str, true);
        return bin2hex($re);
    }

    /**
	 * [SHA256 description]
	 * @param [type] $str [description]
	 */
	public function SHA256($str){
	    $re=hash('sha256', $str,true);
	    return $re;
	}

	/**
	 * [base58Encode description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public function base58Encode($string)
    {
        $base = strlen($this->alphabet);
        if (is_string($string) === false) {
            return false;
        }
        if (strlen($string) === 0) {
            return '';
        }
        $bytes = array_values(unpack('C*', $string));
        // var_dump($bytes);exit;
        $decimal = $bytes[0];
        for ($i = 1, $l = count($bytes); $i < $l; $i++) {
            $decimal = bcmul($decimal, 256);
            $decimal = bcadd($decimal, $bytes[$i]);
        }

        $output = '';
        while ($decimal >= $base) {
            $div = bcdiv($decimal, $base, 0);
            $mod = bcmod($decimal, $base);
            $output .= $this->alphabet[$mod];
            $decimal = $div;
        }
        
        if ($decimal > 0) {
            $output .= $this->alphabet[$decimal];
        }
        $output = strrev($output);
        foreach ($bytes as $byte) {
            if ($byte === 0) {
                $output = $this->alphabet[0] . $output;
                continue;
            }
            break;
        }
        return (string) $output;
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
            $decimal = bcmul($decimal, $this->base);
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
     * [ED25519 description]
     */
    public function ED25519($byteStr){
        //进来是32位字节 字符串，返回也是32位字符串
        return ed25519_publickey($byteStr);
        return $byteStr;
    }


    /**
    * 将二进制转换成字符串
    * @param type $str
    * @return type
    */
    public function BinToStr($str){
        $arr = explode(' ', $str);
        foreach($arr as &$v){
            $v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }
    
    public function StrToBin($str){
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach($arr as &$v){
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ',$arr);
    }


    /**
	 * [getPrefix description]
	 * @return [type] [description]
	 * @return [num] [取几个字节]
	 * @return [dir] [1从前往后，2从后往前]
	 */
	public function getCheckSum($f_charStr,$num = 4,$dir=1,$times=2){
		//1两次SHA256
        // echo strlen($f_charStr);exit;
        $firstSHA = $this->SHA256($f_charStr); //字符串
        if($times>1){
            $secondSHA = $this->SHA256($firstSHA);//字符串
        }
        else{
            $secondSHA = $firstSHA;
        }
        // echo bin2hex($secondSHA);exit;
        
        $strLength = strlen($secondSHA);
        if($dir==1){ //从前往后取指定个数
            $retString = substr($secondSHA,0,$num);
        }
        else{ //取后面指定个数
            $retString = substr($secondSHA,  $strLength-$num);
        }
        $byteOb = new \src\keypair\Bytes();
        return $byteOb->getBytes($retString);




        // 0815之前做法
  //       // echo strlen($secondSHA);exit;
  //       $lastSHA = bin2hex($secondSHA); //ascii转16进制，
		// // echo strlen($lastSHA);exit;
		// //2前4个字节，32bit
		// $ret = array();
  //       $step = 2;
		// $strLen = $num * $step;
		// if($dir==1){ //前往后
		// 	$shaData = substr($lastSHA, 0,$strLen);
  //           // echo ($shaData);exit;
		// 	for($j =0;$j<$num;$j++){
		// 		$temp = substr($shaData,$j*$step,$step);
		// 		array_push($ret, hexdec($temp));
		// 	}	
  //           // var_dump($ret);exit;
		// }
		// else{ //后往前
		// 	$start = strlen($lastSHA) - 1 - $strLen;
		// 	// echo strlen($f_charStr);exit;
		// 	$shaData = substr($lastSHA, $start);
		// 	for($j =0;$j<$num;$j++){
		// 		$temp = substr($shaData,$j*$step,$step);
		// 		array_push($ret, hexdec($temp));
		// 	}	
  //       // var_dump($ret);exit;
		// }
		// return $ret;
	}
	/**
	 * [hexEncode description]
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	public function hexEncode($string){
 		$s = ''; 
 		for ($i=0; $i<strlen($string); $i++) { 
            $temp = base_convert(ord($string[$i]), 10, 16); 
            if(strlen($temp)<2){
                $temp = "0".$temp;
            }
            // $this->logger->addWarning("hexEncode,i:$i,temp:".$temp);
 			$s .= $temp;
 		} 
// 　　　  $str = "0x" . $s; 
		return $s; 
	}

}



?>