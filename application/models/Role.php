<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Role extends CI_Model{

  private $name;
  private $permissions;

	public function __construct($name,$permissions){
		parent::__construct();
    $this->name         = $name;
    $this->permissions  = $permissions;
	}

  public static function getRoles(){

    $this->db->select('name');
    return $this->db->get('roles');

  }

  public function save(){

    $data = array('name' => $this->name);

    $this->db->insert('roles', $data);

    //Obtain last inserted role id
    $roleID = $this->db->insert_id();

    //For each permission, insert a new register in role_permissions table
    foreach ($this->permissions as $permission) {
      $this->db->insert('role_permissions', array('permission_id' => $permission,'role_id' => $roleID));
    }

    return true;

  }

  public function validateData(){

    //Name validation
    $query = $this->db->get_where('roles', array('name' => $this->name));
    if ($query->num_rows() > 0) return array("valid"=>false,"message"=>"Role already exists");;

    return array("valid"=>true,"message"=>"");;

  }


}
