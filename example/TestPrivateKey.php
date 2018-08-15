<?php
namespace example;
use src\keypair\GenKey;
use src\business\Account;

class TestPrivateKey{
	public function testKeyPair(){ 

		$genKey = new GenKey;
		$genKey->genPairKey();
		$priKey = $genKey->getPrivateKey();
		$pubKey = $genKey->getPublicKey();
		$address = $genKey->getAddress();
		
        $rawPivateKey = $genKey->getRawPrivateKey();
        $rawPublicKey = $genKey->getRawPublicKey();
		$ret['priKey'] = $priKey;
		$ret['pubKey'] = $pubKey;
		$ret['address'] = $address;
		$ret['rawPivateKey'] = $rawPivateKey;
		$ret['rawPublicKey'] = $rawPublicKey;
		return $ret;
	}
}


?>