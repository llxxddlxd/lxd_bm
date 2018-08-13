<?php
/**
 * 
 */
namespace conf;
 
class confController 
{
	private $confiure ;
	
	public function __construct()
	{
		// echo __DIR__ . "/conf/conf.php";exit;
		$this->confiure = require __DIR__ . "/conf.php";
		// var_dump($this->confiure);exit;
	}

	public function getConfig(){
		return $this->confiure;
	}

}

?>