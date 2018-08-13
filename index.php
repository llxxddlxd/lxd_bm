<?php
include_once __DIR__ . "/autoload.php";

//1测试
use example\TestPrivateKey;
echo 'test'."<br>";
$t = new TestPrivateKey();
$t->testKeyPair();
exit;      
  