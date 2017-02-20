<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Login_model extends CI_Model{

	public function __construct(){
		parent::__construct();
	}
	
	public function getUser($email){
		$this->db->where('email', $email);
		$query = $this->db->get('user');
		return $query->row();
	}
	
}



