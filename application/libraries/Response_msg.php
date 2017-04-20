<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response_msg{


    private $error_code = 404;
    private $msg = array("msg" =>"Error Undefined");


    public function __construct(){
    }

    public function setResponse($msg = null, $error_code = null){

        if($error_code != ''){
            $this->error_code = $error_code;
        }
        if($msg != ''){
            $this->msg = $msg;
        }
        header('Content-type: application/json');
        http_response_code($this->error_code);
        $result_json = $this->msg;

        if($this->error_code == 404){
            echo  $result_json  =  json_encode(array('error' => $this->msg ));
        }else{
            echo $json= json_encode($result_json, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
