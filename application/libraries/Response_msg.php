<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 24/02/2017
 * Time: 13:30
 */
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
        http_response_code($this->error_code);
        $result_json = $this->msg;
        $json= json_encode($result_json, JSON_UNESCAPED_UNICODE);
        echo $json;
        exit;
    }


}
