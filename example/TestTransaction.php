<?php
namespace example;
use src\business\Account;

class TestTransaction{
	public function test($priKey,$pubKey,$address){
		$Account = new Account();
		$Account->setPriKey($priKey);
		$Account->setPubKey($pubKey);
		$Account->setaddress($address);
		$Account->active($priKey,$pubKey,$address);
	}
	
}


?>