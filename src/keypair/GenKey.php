<?php
/**
 * User: lixd
 * Date: 2018/08/08
 * Time: 10:00
 * description:GenKey
 */
namespace src\keypair;

use src\keypair\Base;	
use src\keypair\GeneratePrivateKey;
use src\keypair\GeneratePublicKey;
use src\keypair\GenerateAddress;
/**
* 
*/
class GenKey  extends Base
{
	private $privateKey;
	private $publicKey;
	private $address;
	private $privateRawKey;
	private $publicRawKey;
	
	public function __construct()
	{
		parent::__construct();	
	}

	/**
	 * [getVersion description]
	 * @return [type] [description]
	 */
	public function getVersion(){
	}
	/**
	 * [getPrefix description]
	 * @return [type] [description]
	 */
	public function getPrefix(){

	}
	
	/**
	 * [GenPairKey description]
	 */
	public function GenPairKey(){
		$this->logger->addNotice("GenKey,GenPairKey");
		// $str = explode(',', "218,55,159,1,17,236,24,183,207,250,207,180,108,87,224,39,189,99,246,85,138,120,236,78,228,233,41,192,124,109,156,104,235,66,194,24,30,19,80,117");
		// 1 私钥
		$priv = new GeneratePrivateKey();
		$pri = $priv->setPriKey();		
		$this->privateKey = $priv->getPriKey();
		$this->logger->addNotice("privateKey",['privateKey:'=>$this->privateKey]);
		// echo strlen($priv->getPriKey()) ."<br>";
		$this->privateRawKey = $priv->getRawKey();
		$this->logger->addNotice("rawKey",$this->privateRawKey);

		//2 公钥
		$pub = new GeneratePublicKey();
		$pub->setRawKey($this->privateRawKey);		
		$this->publicRawKey = $pub->getRawKey();		
		$pub->setPubKey();		
		$this->publicKey = $pub->getPubKey();	
		$this->logger->addNotice("publicKey",['publicKey:'=>$this->publicKey]);	
		// echo strlen($pub->getPubKey())."<br>";		
		
		//3 地址
		$this->publicRawKey = $pub->getRawKey();
		// $this->publicRawKey = [21,118,76,208,23,224,218,117,50,113,250,38,205,82,148,81,162,27,130,83,208,1,240,212,54,18,225,158,198,50,87,10];
		// var_dump($this->publicRawKey)."<br>";	
		$add = new GenerateAddress($this->publicRawKey);	
		$add->setAddress();
		$this->address = $add->getAddress();	
		$this->logger->addNotice("address",['address:'=>$this->address]);		
		// echo strlen($add->getAddress())."<br>";		
	}
	public function getPublicKey(){
		return $this->publicKey;
	}
	public function getPrivateKey(){
		return $this->privateKey;
	}
	public function getRawPrivateKey(){
		return $this->privateRawKey;
	}
	public function getRawPublicKey(){
		return $this->publicRawKey;
	}
	public function getAddress(){
		return $this->address;
	}

}


?>