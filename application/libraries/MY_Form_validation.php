<?php
class MY_Form_validation extends CI_Form_validation{

    function __construct($config = array()){
        parent::__construct($config);
    }

    public function custom_validation($value){
        if($value == "algo"){
            $this->set_message('custom_validation', 'Valor no correcto');
            return false;
        }else{
            return true;
        }
    }

    function error_array()    {
        if (count($this->_error_array) === 0)
            return FALSE;
        else
            return $this->_error_array;
    }
}