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
	

	public function GenPairKey(){
		$this->logger->addNotice("GenKey,GenPairKey");
		// $str = explode(',', "218,55,159,1,17,236,24,183,207,250,207,180,108,87,224,39,189,99,246,85,138,120,236,78,228,233,41,192,124,109,156,104,235,66,194,24,30,19,80,117");
		// 1 私钥
		$priv = new GeneratePrivateKey();

		$pri = $priv->setPriKey();		
		$this->privateKey = $priv->getPriKey();
		$this->logger->addNotice("privateKey",['privateKey:'=>$this->privateKey]);
		// echo strlen($priv->getPriKey()) ."<br>";
		$rawKey = $priv->getRawKey();
		$this->logger->addNotice("rawKey",$rawKey);
		//2 公钥
		$pub = new GeneratePublicKey();
		$pub->setRawKey($rawKey);		
		$pub->setPubKey();		
		$this->publicKey = $pub->getPubKey();	
		$this->logger->addNotice("publicKey",['publicKey:'=>$this->publicKey]);	
		// echo strlen($pub->getPubKey())."<br>";		
		//3 地址
		$rawPubKey = $pub->getRawKey();
		// var_dump($rawPubKey)."<br>";	
		$add = new GenerateAddress($rawPubKey);	
		$add->setAddress();
		$this->address = $add->getAddress();	
		$this->logger->addNotice("publicKey",['address:'=>$this->address]);		
		// echo strlen($add->getAddress())."<br>";		
	}
	public function getPublicKey(){
		return $this->publicKey;
	}
	public function getPrivateKey(){
		return $this->privateKey;
	}
	public function getAddress(){
		return $this->address;
	}

}


?>