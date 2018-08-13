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
		//2 version 固定？？	
		$version = $this->getVersion();
		array_unshift($randArray,$version);
// array_push($randArray,0);
		//3prefix  固定？？
		$perfix = $this->getPrefix();
		// array_unshift($randArray,0);
		array_unshift($randArray,$perfix[0]);
		array_unshift($randArray,$perfix[1]);
		array_unshift($randArray,$perfix[2]);
		// var_dump($randArray);exit;

		//4checksum ,两次SHA256
	 	$Bytes = new Bytes();
		$f_charStr = $Bytes->toStr($randArray);
		$getCheckSum = $this->getCheckSum($f_charStr);

		array_push($randArray,0);
		foreach ($getCheckSum as $key => $value) {
			array_push($randArray, $value);
		}
		
		// var_dump($randArray);exit;
		//5base58  字符数组转字符串
		// $tmpp = [0xda,0x37,0x9f,0x00,0x01,0xfb,0xdf,0x2a,0x7b,0xe4,0x2b,0x0d,0xd3,0xa0,0xb2,0x0f,0x9a,0xe9,0x7e,0xc8,0x0e,0x1e,0x13,0xea,0x6a,0x20,0xb9,0x0f,0x68,0x06,0xe4,0xb7,0xad,0x7d,0xca,0xb1,0x83,0x24,0x92,0x45,0xea];
		// $randArray = $tmpp; 
		$charStr = $Bytes->toStr($randArray);
		// $charStr = "vVAtScBEuzr1RAJNPMKECxjKxSzgkX0T9d48";
		// echo json_encode($randArray);exit;
		// echo $charStr.'   ,length:'.strlen($charStr);exit;
		$this->privateKey= $this->base58Encode($charStr);
		
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
			"0"=>159,
			"1"=>55,
			"2"=>218,
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