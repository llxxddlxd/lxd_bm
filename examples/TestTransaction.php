<?php
namespace examples;
use src\business\Account;

class TestTransaction{
	public function test($priKey,$pubKey,$address,$rawPivateKey,$rawPublicKey){
        //激活账号
        $Account = new Account();
        $Account->active($priKey,$pubKey,$address,$rawPivateKey,$rawPublicKey);
	}

    public function addressInfo($address){
        $Account = new Account();
        $Account->getInfo($address);

    }
    public function nonceInfo($address){
        $Account = new Account();
        $Account->getNonceInfo($address);

    }
    public function balanceInfo($address){
        $Account = new Account();
        $Account->getBalanceInfo($address);

    }  

    public function checkAddress($address){
        $Account = new Account();
        $Account->checkAddress($address);

    }
     public function checkPublicKey($address){
        $Account = new Account();
        $Account->checkPublicKey($address);

    } 

    // public function checkPirvateKey($address){
    //     $Account = new Account();
    //     $Account->checkPirvateKey($address);

    // }
	
}


?>