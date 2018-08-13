<?php
namespace example;
use src\keypair\GenKey;

class TestPrivateKey{
	public function testKeyPair(){ 
		$genKey = new GenKey;
		$genKey->genPairKey();
		echo 'private----'.$genKey->getPrivateKey();
		echo "<br>";
		echo 'public----'.$genKey->getPublicKey();
		echo "<br>";
		echo 'address----'.$genKey->getAddress();
		echo "<br>";
	 
	}
}


?>