<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Permission extends CI_Model{

  private $name;

	public function __construct(){
		parent::__construct();
	}

  //Get all permissions
  public function getPermissions(){
    $result = array();

    $query = $this->db->get('permissions');

    foreach ($query->result_array('Permission') as $row)
    {
       array_push($result,$row);
    }

    return $result;
  }

  //Save a permission
  public function save($name){
    $data = array('name' => $name);
    $this->db->insert('permissions', $data);

    return true;
  }

  //Validate repeated permission name
  public function validateData($name){

    //Name validation
    $query = $this->db->get_where('permissions', array('name' => $name));
    if ($query->num_rows() > 0) return "Permission already exists";

    return "OK";

  }


}
