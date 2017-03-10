<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Validator{

    public function validatePassword($password){
        return strlen($password)>=8;
    }

    public function validateDocument($document_type,$document_number){
      switch ($document_type) {
          case "DNI":
              return strlen($document_number)<=9;
              break;
          case "LE":
              return strlen($document_number)<=8;
              break;
          case "LC":
              return strlen($document_number)<=8;
              break;
          default:
              return false;
      }
    }

    public function validateSpecialityLength($code){
        return strlen($code)<=2;
    }

    public function validateBankLength($code){
        return strlen($code)<=2;
    }

}
