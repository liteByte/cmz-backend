<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class User extends CI_Model{

  private $name;
  private $last_name;
  private $document_type;
  private $document_number;
  private $email;
  private $salt;
  private $password;
  private $roles;

	public function __construct($name,$last_name,$document_type,$document_number,$email,$password,$roles){
		parent::__construct();

    $this->name             = $name;
    $this->last_name        = $last_name;
    $this->document_type    = $document_type;
    $this->document_number  = $document_number;
    $this->password         = $this->hash->encrypt($password);
    $this->email            = $email;
    $this->salt             = $newSalt;
    $this->roles            = $roles;

	}

  public static function getUsers(){

    $this->db->select('document_type,document_number,name_user,email_user,last_name_user');
    return $this->db->get_where('users', array('active' => "true"));

  }

  //Creates the user in 'users' and then assigns the roles in user_role
	public function save(){

    $now = date('Y-m-d H:i:s');

    $data = array(
                  'name_user' => $this->name,
                  'last_name_user' => $this->last_name,
                  'document_type' => $this->document_type,
                  'document_number' => $this->document_number,
                  'password' => $this->password,
                  'email_user' => $this->email,
                  'salt' => $this->salt,
                  'active' => 'active',
                  'date_created' => $now
    );

    $this->db->insert('users', $data);

    //Obtain last inserted user id
    $userID = $this->db->insert_id();

    //For each role, insert a new register in user_role table
    foreach ($this->roles as $role) {
      $this->db->insert('user_role', array('user_id' => $userID,'role_id' => $role));
    }

    return true;

	}


  public function validateData(){

    //Email validation
    $query = $this->db->get_where('users', array('email_user' => $this->email));
    if ($query->num_rows() > 0) return array("valid"=>false,"message"=>"Email already in use");;

    return array("valid"=>true,"message"=>"");;

  }

}
