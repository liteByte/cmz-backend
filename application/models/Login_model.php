<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Login_model extends CI_Model{

	public function __construct(){
		parent::__construct();
	}
	
	public function getUser($dni){
		$this->db->where('document_number', $dni);
		$query = $this->db->get('users');
		return $query->row();
	}
	
}



