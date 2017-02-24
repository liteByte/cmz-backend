<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Role extends CI_Model{

  private $name;
  private $permissions;

	public function __construct(){
		parent::__construct();
	}

  public function getRoles(){
    $result = array();

    $query = $this->db->get('roles');

    foreach ($query->result_array('Role') as $row)
    {
       array_push($result,$row);
    }

    return $result;
  }

  public function save($name,$permissions){

    $data = array('name' => $name);

    $this->db->insert('roles', $data);

    //Obtain last inserted role id
    $roleID = $this->db->insert_id();

    //For each permission, insert a new register in role_permissions table
    foreach ($permissions as $permission) {
      $this->db->insert('role_permissions', array('permission_id' => $permission->id,'role_id' => $roleID));
    }

    return true;

  }

  public function validateData($name){

    //Name validation
    $query = $this->db->get_where('roles', array('name' => $name));
    if ($query->num_rows() > 0) return "Role already exists";

    return "OK";

  }


}
