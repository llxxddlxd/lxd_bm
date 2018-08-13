<?php
namespace example;
use src\business\Account;

class TestTransaction{
	public function test($priKey,$rawPivateKey,$pubKey,$address){
		$Account = new Account();
		$Account->active($priKey,$rawPivateKey,$pubKey,$address);
	}
	
}


?>