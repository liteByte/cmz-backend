<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Permission extends CI_Model{

  private $name;

	public function __construct($name,$permissions){
		parent::__construct();
    $this->name = $name;
	}

  public static function getPermissions(){

    $this->db->select('name');
    return $this->db->get('permissions');

  }

  public function save(){

    $data = array('name' => $this->name);
    $this->db->insert('permissions', $data);

    return true;

  }

  public function validateData(){

    //Name validation
    $query = $this->db->get_where('permissions', array('name' => $this->name));
    if ($query->num_rows() > 0) return array("valid"=>false,"message"=>"Permission already exists");;

    return array("valid"=>true,"message"=>"");;

  }


}
