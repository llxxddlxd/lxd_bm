<?php
include_once __DIR__ . "/autoload.php";

//1测试生成
// use example\TestPrivateKey;
// echo 'test'."<br>";
// $t = new TestPrivateKey();
// $t->testKeyPair();
// exit;
// 模拟的数据
$priKey = 'privbi9xepr8n747JJBkRAV3hgUyh1dRB9Vw3btxGRoV8TwXephm61dx';echo $priKey."<br>";
$pubKey = 'b01fdfbc8174877b8384b14eb17fd6a1acd6927cedcf24ca1560373b5a806488144435e6d6';echo $pubKey."<br>";
$address = 'buQdxCQJUaj2Rp4Vi1F3TG5uimcctV6Rc1aP';echo $address."<br>";
$randArr = [40,252,120,59,198,156,192,162,45,8,173,128,118,119,24,206,57,162,88,4,141,192,70,25,11,138,58,133,202,55,10,65];
use src\keypair\Bytes;
$bytes = new Bytes();
$rawPivateKey = $bytes->toStr($randArr);
// echo $rawPivateKey;exit;

//2测试业务逻辑
use example\TestTransaction;
$transaction = new TestTransaction();
$transaction->test($priKey,$rawPivateKey,$pubKey,$address);