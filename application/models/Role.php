<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Role extends CI_Model{

  private $name;
  private $permissions;

	public function __construct(){
		parent::__construct();
	}

  //Get all roles
  public function getRoles(){

    $result = array();

    $query = $this->db->get('roles');

    foreach ($query->result_array('Role') as $row)
    {
      //Get permissions associated with each role
      $this->db->select('name,permissions.permission_id');
      $this->db->from('permissions');
      $this->db->join('role_permissions', 'role_permissions.permission_id = permissions.permission_id');
      $this->db->where('role_permissions.role_id', $row['role_id']);
      $permissionQuery = $this->db->get();

      $row['permissions'] = $permissionQuery->result_array();

      array_push($result,$row);
    }

    return $result;


    /*$result = array();

    $query = $this->db->get('roles');

    foreach ($query->result_array('Role') as $row)
    {
       array_push($result,$row);
    }

    return $result;*/
  }

  //Save a role
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

  //Update a role's permissions
  public function updatePermissions($permissions,$roleID){

    //Delete old roles from the user
    $this->db->delete('role_permissions', array('role_id' => $roleID));

    //Add new permissions to the role
    foreach ($permissions as $permission) {
      $this->db->insert('role_permissions', array('role_id' => $roleID,'permission_id' => $permission->permission_id));
    }

    return true;

  }

  //Validate repeated role name
  public function validateData($name){

    //Name validation
    $query = $this->db->get_where('roles', array('name' => $name));
    if ($query->num_rows() > 0) return "El rol ya existe";

    return "OK";

  }


}
