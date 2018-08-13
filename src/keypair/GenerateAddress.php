<?php
/**
 * User: lixd
 * Date: 2018/08/08
 * Time: 10:00
 * description:GenerateAddress
 */
namespace src\keypair;
use src\keypair\Base;	

class GenerateAddress extends Base{
	private $address;
	private $publicRawKey;

	public function __construct($publicRawKey){
		parent::__construct();
		$this->address = "";
		$this->publicRawKey = $publicRawKey;
	}
	/**
	 * [getPubKey description]
	 * @return [type] [description]
	 */
	public function getAddress(){
		return $this->address;
	}


	/**
	 * [setAddress description]
	 */
	public function setAddress(){
		$rawPubkey  = $this->publicRawKey;

		//1checksum ,两次SHA256
	 	$Bytes = new Bytes();
		$f_charStr = $Bytes->toStr($rawPubkey);
		$rawPubkey = $this->getCheckSum($f_charStr,20,2);
		 
		 //2 version 固定？？	
		$version = $this->getVersion();
		array_unshift($rawPubkey,$version);

		//3prefix  固定？？
		$perfix = $this->getPrefix();
		// array_unshift($rawPubkey,0);
		array_unshift($rawPubkey,$perfix[0]);
		array_unshift($rawPubkey,$perfix[1]);
		// var_dump($rawPubkey);exit;

		//4checksum ,两次SHA256
		$f_charStr = $Bytes->toStr($rawPubkey);
		$getCheckSum = $this->getCheckSum($f_charStr);
		foreach ($getCheckSum as $key => $value) {
			array_push($rawPubkey, $value);
		}
		// var_dump($rawPubkey);exit;

		// var_dump($rawPubkey);exit;
		//5 16进制编码  字符数组转字符串
		$charStr = $Bytes->toStr($rawPubkey);
		$this->address= $this->base58Encode($charStr);
		
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
			"0"=>86,
			"1"=>1,
		];
		return $arrayName;	
	}
}

?>