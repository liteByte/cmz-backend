<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Bank extends CI_Model{

  private $bank_code;
  private $corporate_name;
  private $address;
  private $location;
  private $phone_number;

  public function __construct(){
      parent::__construct();
  }

  //Creates the bank in 'banks'
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
  public function update($corporate_name,$address,$location,$phone_number,$id){

    $data = array(
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

      $query = $this->db->get_where('banks', array("bank_id" => $bankID));

      if($query->num_rows()){
          //Delete bank
          $this->db->where('bank_id', $bankID);
          $result = $this->db->delete('banks');
          $errors = $this->db->error();
          if($errors['code'] == '1451') return "No se puede eliminar el banco, ya que posee información relacionada";
          if(!$result) return "Error al intentar Banco";
      }else{
          return "El Id del banco no existe en la base de datos";
      }
      return true;
  }

  public function validateData($bank_code){

    //Bank code validation
    $query = $this->db->get_where('banks', array('bank_code' => $bank_code));
    if ($query->num_rows() > 0) return "El código de banco ingresado esta siendo utilizado";

    return "OK";

  }

}
