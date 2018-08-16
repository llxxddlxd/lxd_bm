<?php
include_once __DIR__ . "/autoload.php";
set_time_limit(30);


//1测试 创建账号
$type = isset($_GET['type'])?$_GET['type']:0;
switch($type){
    case 0: //生成公私钥地址  创建未激活账户，指未写在区块链上的账户信息，通过getInfo接口无法查询到的账户。
        $t = new examples\TestPrivateKey();
        $ret = $t->testKeyPair();
        echo json_encode($ret);
        break; 
    case 1: //创建账号 
        $t = new examples\TestPrivateKey();
        $ret = $t->testKeyPair();
        $ob = new examples\TestTransaction();
        $priKey = $ret['priKey'];
        $pubKey = $ret['pubKey'];
        $address = $ret['address'];
        $rawPivateKey = $ret['rawPivateKey'];
        $rawPublicKey = $ret['rawPublicKey'];
        $ob->test($priKey,$pubKey,$address,$rawPivateKey,$rawPublicKey);
        break;
    case 2: //查询账号
        $t = new examples\TestTransaction();
        $address = "buQsurH1M4rjLkfjzkxR9KXJ6jSu2r9xBNEw";
        $t->addressInfo($address);
        break;    
    case 3: //查询nonce
        $t = new examples\TestTransaction();
        $address = "buQsurH1M4rjLkfjzkxR9KXJ6jSu2r9xBNEw";
        $t->nonceInfo($address);
        break;
    case 4: //查询balance
        $t = new examples\TestTransaction();
        $address = "buQsurH1M4rjLkfjzkxR9KXJ6jSu2r9xBNEw";
        $t->balanceInfo($address);
        break;
    case 5: //查询地址是否合法
        $t = new examples\TestTransaction();
        $address = "buQsurH1M4rjLkfjzkxR9KXJ6jSu2r9xBNEw";
        $t->checkAddress($address);
        break;
    case 6: //查询公钥是否合法
        $t = new examples\TestTransaction();
        $address = "b001bfd0bf2244a323f4175e91e1f6383147fd03784e79e853a316a6afe64c653f9f01681df8";
        $t->checkPublicKey($address);
        break;
    // case 7: //查询私钥是否合法
    //     $t = new examples\TestTransaction();
    //     $address = "privbUcAxSxkGWY4mw1P3zQMZthdGFtF17jhW15kYGKxoU36JCfXg13T";
    //     $t->checkPirvateKey($address);
    //     break;
} 

exit;      
