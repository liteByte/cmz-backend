<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Bank extends CI_Model{

  private $name;
  private $permissions;

  public function __construct(){
      parent::__construct();
  }

  //Creates the user in 'users' and then assigns the roles in 'user_role'
  public function save($bank_code,$corporate_name,$address,$location,$phone_number){

    $data = array(
                  'bank_code'       => $bank_code,
                  'corporate_name'  => $corporate_name,
                  'address'         => $address,
                  'location'        => $location,
                  'phone_number'    => $phone_number,
                  'active'          => 'active'
    );

    $this->db->insert('banks', $data);

    return true;

  }

  //Updates the bank in 'banks'
  public function update($bank_code,$corporate_name,$address,$location,$phone_number,$id){

    $data = array(
                  'bank_code'       => $bank_code,
                  'corporate_name'  => $corporate_name,
                  'address'         => $address,
                  'location'        => $location,
                  'phone_number'    => $phone_number
    );

    $this->db->where('bank_id', $id);
    $this->db->update('banks', $data);

    return true;

  }

  //Get all banks information
  public function getBanks(){

    $result = array();

    $query = $this->db->get_where('banks', array('active' => "active"));

    foreach ($query->result_array('Bank') as $row)
    {
       array_push($result,$row);
    }

    return $result;

  }

  //Get a specific bank information
  public function getBankById($bankID){

    $result = array();

    $query = $this->db->get_where('banks', array("bank_id" => $bankID));

    return $query->row();
  }

  //Delete bank information in 'banks'
  public function delete($bankID){

    //Delete bank
    $this->db->where('bank_id', $bankID);
    $this->db->update('banks', array('active' => 'inactive'));

    return true;

  }

  public function validateData($bank_code){

    //Bank code validation
    $query = $this->db->get_where('banks', array('bank_code' => $bank_code));
    if ($query->num_rows() > 0) return "El codigo de banco ingresado esta siendo utilizado";

    return "OK";

  }

  public function validateDataOnUpdate($bank_code,$id){

    //Bank code validation
    $query = $this->db->get_where('banks', array('bank_code' => $bank_code,'bank_id !='=>$id));
    if ($query->num_rows() > 0) return "El codigo de banco ingresado esta siendo utilizado";

    return "OK";

  }

}
