<?php
namespace examples;
use src\keypair\GenKey;
use examples\Base;
use conf\confController; 
// use src\business\Account;

class TestPrivateKey extends base {
	
   public function __construct(){
        parent::__construct();
        $conf = new confController();
        $this->confInfo = $conf->getConfig();
        $this->logger->addNotice("getInfo,config",$this->confInfo);
        
    }

	public function testKeyPair(){ 
		$genKey = new GenKey;
		$genKey->genPairKey();
		$ret['priKey'] = $genKey->getPrivateKey();
		$ret['pubKey'] = $genKey->getPublicKey();
		$ret['address'] = $genKey->getAddress();
		$ret['rawPivateKey'] = $genKey->getRawPrivateKey();
		$ret['rawPublicKey'] = $genKey->getRawPublicKey();
		$retData['status'] = 0;
		$retData['data'] = $ret;
        return $this->responseJson($retData,0);
	}
}


?>