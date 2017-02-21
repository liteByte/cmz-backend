<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class User extends CI_Model{

  private $name;
  private $document_type;
  private $document_number;
  private $email;
  private $password;

	public function __construct(){
		parent::__construct();
	}

	public function getUser(){

	}

}
