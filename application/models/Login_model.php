<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Login_model extends CI_Model{

	public function __construct(){
		parent::__construct();
	}
	
	public function getUser($username){
		$this->db->where('email_user', $username);
		$query = $this->db->get('users');
		return $query->row();
	}
	
}



