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

  //Get user information to sign in
  public function getUser($dni){
    $this->db->select('*');
    $this->db->where('document_number', $dni);
    $this->db->where('active', 'active');
    $query = $this->db->get('users');
    return $query->row();
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

    return $this->updateRoles($roles,$userID);

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

    return $this->updateRoles($roles,$id);

  }

  //Delete user and role information in 'user_role'
  //TODO: verificar que el usuario no tenga auditorias antes de borrarlo
  public function delete($userID){

    $query = $this->db->get_where('users', array("user_id" => $userID));

    if($query->num_rows()){
      //Delete user
      $this->db->where('user_id', $userID);
      $result = $this->db->delete('users');
      $errors = $this->db->error();
      if($errors['code'] == '1451') return "No se puede eliminar el Usuario, ya que posee información relacionada";
      if(!$result) return "Error al intentar Usuario";
    }else{
      return "El Id del usuario no existe en la base de datos";
    }
    return true;
  }

  //Get a specific user information
  public function getUserById($userID){

    //Get user data
    $query = $this->db->get_where('users', array('active' => "active", "user_id" => $userID));

    $result = $query->row();
    $result->roles = array();

    //Get user roles
    $this->db->select('R.name,UR.role_id');
    $this->db->from('user_role UR');
    $this->db->join('roles R', 'UR.role_id = R.role_id');
    $this->db->where('UR.user_id', $userID);
    $query = $query = $this->db->get();

    foreach ($query->result_array() as $row)
    {
       array_push($result->roles,$row);
    }

    return $result;
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

    $this->db->select('document_type,document_number,name,email,last_name,user_id');
    $query = $this->db->get_where('users', array('active' => "active"));

    foreach ($query->result_array('User') as $row)
    {
       array_push($result,$row);
    }

    return $result;

  }

  //Get all the permissons of an user
  public function getPermissions($document_number){

    //Get user_id
    $this->db->select('user_id');
    $query = $this->db->get_where('users', array('active' => "active",'document_number'=>$document_number));
    $userID = $query->row()->user_id;

    //Get user roles
    $roles = array();
    $query = $this->db->get_where('user_role', array('user_id' => $userID));

    foreach ($query->result_array() as $row)
    {
       array_push($roles,$row['role_id']);
    }

    //If user has no roles, return empty
    if(empty($roles)) return array();

    //Get permissions associated with obtained roles
    $this->db->distinct();
    $this->db->select('name');
    $this->db->from('permissions');
    $this->db->join('role_permissions', 'role_permissions.permission_id = permissions.permission_id');
    $this->db->where_in('role_permissions.role_id', $roles);

    $query = $this->db->get();
    $result = array();

    foreach ($query->result_array() as $row)
    {
       array_push($result,$row['name']);
    }

    //If user has no permissions, return empty
    if(empty($result)) return array();

    return $result;

  }

  //Update an user roles
  public function updateRoles($roles,$userID){

    //Delete old roles from the user
    $this->db->delete('user_role', array('user_id' => $userID));

    //Add new roles to the user
    foreach ($roles as $role) {
      $this->db->insert('user_role', array('user_id' => $userID,'role_id' => $role->role_id));
    }

    return true;

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
    if ($query->num_rows() > 0) return "El email ingresado ya ha sido utilizado";

    //Document validation
    $query = $this->db->get_where('users', array('document_number' => $document_number, 'document_type' => $document_type));
    if ($query->num_rows() > 0) return "El numero de documento ingresado ya esta en uso";

    return "OK";

  }

  public function validateDataOnUpdate($email,$document_number,$document_type,$id){

    //Email validation
    $query = $this->db->get_where('users', array('email' => $email,'user_id !='=>$id));
    if ($query->num_rows() > 0) return "El email ingresado ya ha sido utilizado";

    //Document validation
    $query = $this->db->get_where('users', array('document_number' => $document_number, 'document_type' => $document_type,'user_id !='=>$id));
    if ($query->num_rows() > 0) return "El numero de documento ingresado ya esta en uso";

    return "OK";

  }

}
