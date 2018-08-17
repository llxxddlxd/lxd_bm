<?php
namespace examples;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use conf\confController;
use conf\errDesc;

class Base {
    
    public $logger;
    // private $alphabet; 

    public function __construct()
    {
        
        // $this->alphabet = '123456789AbCDEFGHJKLMNPQRSTuVWXYZaBcdefghijkmnopqrstUvwxyz'; //bumo   
        // create a log channel
        $log = new Logger('name');
        $date = date("Ymd");
        $filepath= dirname(dirname((__FILE__))).'/log/'.$date.'.log';
        $log->pushHandler(new StreamHandler( $filepath, Logger::DEBUG));

        $this->logger = $log;        
        
    }

    /**
     * [responseJson description]
     * @param  [type]  $data   [description]
     * @param  integer $status [description]
     * @return [type]          [description]
     */
    public function responseJson($data = null, $status = 0){
        $content = null;
        if(is_array($data) || is_null($data)){
            $respArr = Array(
                "status" => $status,
                "desc" => errDesc::getErrorDesc($status)
            );
            if($data){
                $respArr = array_merge($respArr, $data);
            }
            $this->logger->addNotice("response success data",$respArr);
            header("Content-Type:text/html;charset=utf-8");
            echo urldecode(json_encode($respArr));
        }else{
            $this->logger->addError("response unknown data type");
            // header("Content-Type:text/html;charset=utf-8");
            // echo urldecode(json_encode($result));
        }
    }


}
?>