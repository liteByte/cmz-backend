<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class User extends CI_Model{

  private $name;
  private $last_name;
  private $document_type;
  private $document_number;
  private $email;
  private $password;
  private $roles;
  private $user_id;
  private $active;
  private $date_updated;
  private $date_created;

	public function __construct(){
		parent::__construct();
    $this->load->library('hash');
	}

  //Creates the user in 'users' and then assigns the roles in 'user_role'
	public function save($name,$last_name,$document_type,$document_number,$email,$password,$roles){

    $now = date('Y-m-d H:i:s');

    $data = array(
                  'name'            => $name,
                  'last_name'       => $last_name,
                  'document_type'   => $document_type,
                  'document_number' => $document_number,
                  'password'        => $this->hash->encrypt($password),
                  'email'           => $email,
                  'active'          => 'active',
                  'date_created'    => $now
    );

    $this->db->insert('users', $data);

    //Obtain last inserted user id
    $userID = $this->db->insert_id();

    //For each role, insert a new register in user_role table
    foreach ($roles as $role) {
      $this->db->insert('user_role', array('user_id' => $userID,'role_id' => $role->role_id));
    }

    return true;

	}

  //Updates the user in 'users', delete old roles and asign new ones
	public function update($name,$last_name,$document_type,$document_number,$email,$id,$roles){

    $now = date('Y-m-d H:i:s');

    $data = array(
                  'name'            => $name,
                  'last_name'       => $last_name,
                  'document_type'   => $document_type,
                  'document_number' => $document_number,
                  'email'           => $email,
                  'date_update'     => $now
    );

    $this->db->where('user_id', $id);
    $this->db->update('users', $data);

    //Delete old roles from the user
    $this->db->delete('user_role', array('user_id' => $id));

    //Add new roles to the user
    foreach ($roles as $role) {
      $this->db->insert('user_role', array('user_id' => $id,'role_id' => $role->role_id));
    }

    return true;

	}

  //Delete user and role information in 'user_role'
  public function delete($userID){

    $now = date('Y-m-d H:i:s');

    //Delete user
    $this->db->where('user_id', $userID);
    $this->db->update('users', array('active' => 'inactive','date_update'=>$now));

    return true;

  }

  //Get a specific user information
  public function getUserById($userID){

    $result = array();

    $query = $this->db->get_where('users', array('active' => "active", "user_id" => $userID));

    return $query->row();
  }

  //Get a specific user information
  public function getUserByDocument($document_type,$document_number){

    $query = $this->db->get_where('users', array('document_type' => $document_type, "document_number" => $document_number));

    if ($query->num_rows() > 0)
    {
       $row = $query->row();
       return array('status'=>'ok','data'=>$row);

    } else {

      return array('status'=>'error','data'=>'No se ha encontrado un usuario con la informacion especificada');

    }
  }

  //Get all users information
  public function getUsers(){

    $result = array();

    $this->db->select('document_type,document_number,name,email,last_name');
    $query = $this->db->get_where('users', array('active' => "active"));

    foreach ($query->result_array('User') as $row)
    {
       array_push($result,$row);
    }

    return $result;

  }

  //Get all the permissons of an user
  public function getPermissions($userID){

  }

  //Change user's password
  public function changePassword($userID,$newPassword){
    $this->db->where('user_id', $userID);
    $this->db->update('users', array('password'=>$this->hash->encrypt($newPassword)));

    return true;
  }

  public function validateData($email,$document_number,$document_type){

    //Email validation
    $query = $this->db->get_where('users', array('email' => $email));
    if ($query->num_rows() > 0) return "Email already in use";

    //Document validation
    $query = $this->db->get_where('users', array('document_number' => $document_number, 'document_type' => $document_type));
    if ($query->num_rows() > 0) return "Document number already in use";

    return "OK";

  }

  public function validateDataOnUpdate($email,$document_number,$document_type,$id){

    //Email validation
    $query = $this->db->get_where('users', array('email' => $email,'user_id !='=>$id));
    if ($query->num_rows() > 0) return "Email already in use";

    //Document validation
    $query = $this->db->get_where('users', array('document_number' => $document_number, 'document_type' => $document_type,'user_id !='=>$id));
    if ($query->num_rows() > 0) return "Document number already in use";

    return "OK";

  }

}
