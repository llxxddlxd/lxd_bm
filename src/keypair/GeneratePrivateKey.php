<?php
/**
 * User: lixd
 * Date: 2018/08/08
 * Time: 10:00
 * description:GeneratePrivateKey
 */
namespace src\keypair;
use src\keypair\Base;	
use src\keypair\Bytes;

class GeneratePrivateKey extends Base{
	private $privateKey;
	private $privateRawKey;
	private $fixedPrefix;

	public function __construct(){
		parent::__construct();
		$this->privateKey='';
		$this->fixedPrefix = "";
		$this->setRawKey();
	}
	/**
	 * [setRawKey description]
	 */
	public function setRawKey(){
		$this->privateRawKey = $this->genRandNum();
	}
	/**
	 * [getRawKey description]
	 * @return [type] [description]
	 */
	public function getRawKey(){
		return $this->privateRawKey;
	}
	/**
	 * [getPriKey description]
	 * @return [type] [description]
	 */
	public function getPriKey(){
		return $this->privateKey;
	}

	public function setPriKey(){
		//1 字节数组，256位，32个字节的数组
		$randArray = $this->privateRawKey;
		$this->logger->addNotice("setPriKey",$randArray);
		// var_dump($randArray);
		
		//2 version 固定
		$version = $this->getVersion();
		array_unshift($randArray,$version);
		
		//3 prefix  固定
		$perfix = $this->getPrefix(); 
		$randArray = array_merge($perfix,$randArray);
		// var_dump($randArray);exit;

		array_push($randArray,0); //特殊字符
		
		//4 checksum ,两次SHA256
	 	$Bytes = new Bytes();
		$f_charStr = $Bytes->toStr($randArray);
		$getCheckSum = $this->getCheckSum($f_charStr);
		$randArray = array_merge($randArray,$getCheckSum);
		// echo json_encode($randArray);exit;

		//5 base58  字符数组转字符串
		// $tmpp = [218,55,159,1,17,236,24,183,207,250,207,180,108,87,224,39,189,99,246,85,138,120,236,78,228,233,41,192,124,109,156,104,235,66,194,24,0,30,19,80,117];
		$this->logger->addNotice("randArray",$randArray);
		// $randArray = $tmpp; 
		$charStr = $Bytes->toStr($randArray);
		// echo $charStr.'   ,length:'.strlen($charStr);exit;
		$this->privateKey= $this->base58Encode($charStr);
		// echo $this->privateKey;exit;
	}

	/**
	 * [setPubKey description]
	 */
	public function setPubKey(){

	}


	/**
	 * [getVersion description]
	 * @return [type] [description]
	 */
	public function getVersion(){
		return 1;		
	}
	/**
	 * [getPrefix description]
	 * @return [type] [description]
	 */
	public function getPrefix(){
		$arrayName = [
			"0"=>218,
			"1"=>55,
			"2"=>159,
		];
		return $arrayName;
	}
	

	

	/**
	 * [生成一个256位的随机数]
	 * @return [type] [  字节数组：例如：21,118,76,208,23,224,218,117,50,113,250,38,205,82,148,81,162,27,130,83,208,1,240,212,54,18,225,158,198,50,87,10
	 * 1byte = 8bit  , 32byte = 256bit]
	 */
	public function genRandNum(){


// PHP 7
// $mySecret = random_bytes(32);

// <= PHP 5.6
// $mySecret = openssl_random_pseudo_bytes(32);
// var_dump($mySecret);

		$randArray= array();
		for($i = 0;$i<32;$i++){
			$number = rand(0,255);
			array_push($randArray, $number);
		}
		return $randArray;
		
	}

	 


}




?>