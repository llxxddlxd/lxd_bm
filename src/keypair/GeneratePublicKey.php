<?php
/**
 * User: lixd
 * Date: 2018/08/08
 * Time: 10:00
 * description:GeneratePublicKey
 */
namespace src\keypair;
use src\keypair\Base;	

class GeneratePublicKey extends Base{
	private $publicKey;
	private $publicRawKey;

	public function __construct(){
		parent::__construct();
		$this->publicKey = "";
	}
	/**
	 * [getPubKey description]
	 * @return [type] [description]
	 */
	public function getPubKey(){
		return $this->publicKey;
	}

	/**
	 * [setRawKey description]
	 */
	public function setRawKey($rawPriKey){
	 	$Bytes = new Bytes();
		$f_charStr = $Bytes->toStr($rawPriKey);
		$g_charStr = $this->ED25519($f_charStr); //字符数组转字符串，进行ed25519
		$this->publicRawKey = $Bytes->getBytes($g_charStr);//字符串转字符数组
		// var_dump($this->publicRawKey);exit;
	}
	/**
	 * [getRawKey description]
	 * @return [type] [description]
	 */
	public function getRawKey(){
		return $this->publicRawKey;
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
		return 176;
	}

	/**
	 * [setPubKey description
	 * 通过rawprikey 生成公钥]
	 * @param [type] $rawPriKey [原生的256bit数组，32个元素]
	 */
	public function setPubKey(){
		//1ED25519 转换，生成rawPubkey
		$rawPubkey  = $this->publicRawKey;
		
		//2 version 固定
		$version = $this->getVersion();
		array_unshift($rawPubkey,$version);

		//3prefix  固定
		$perfix = $this->getPrefix();
		array_unshift($rawPubkey,$perfix);

		//4checksum ,两次SHA256
	 	$Bytes = new Bytes();
		$f_charStr = $Bytes->toStr($rawPubkey);
		$getCheckSum = $this->getCheckSum($f_charStr);

		$rawPubkey = array_merge($rawPubkey,$getCheckSum);
		
		// var_dump($rawPubkey);exit;
		//5 16进制编码  字符数组转字符串
		$charStr = $Bytes->toStr($rawPubkey);
		$this->publicKey = $this->hexEncode($charStr);
		
	}
}

?>