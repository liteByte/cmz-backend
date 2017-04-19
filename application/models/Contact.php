<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Contact extends CI_Model{

  private $medical_insurance_id;
  private $sector;
  private $phone_number;
  private $email;

  public function __construct(){
      parent::__construct();
  }

  //Creates the contact in 'contacts'
  public function save($medical_insurance_id, $sector, $phone_number, $email){

      $data = array(
                  'medical_insurance_id'    => $medical_insurance_id,
                  'sector'                  => $sector,
                  'phone_number'            => $phone_number,
                  'email'                   => $email,
                  'active'                  => 'active'
      );

      $this->db->insert('contacts', $data);

      return true;

  }

  //Updates the contact in 'contacts'
  public function update($medical_insurance_id, $sector, $phone_number, $email, $id, $userID){

      $now = date('Y-m-d H:i:s');

      $data = array(
                    'medical_insurance_id'      => $medical_insurance_id,
                    'sector'                    => $sector,
                    'phone_number'              => $phone_number,
                    'email'                     => $email,
                    'active'                    => 'active',
                    'update_date'               => $now,
                    'modify_user_id'            => $userID
      );

      $this->db->where('contact_id', $id);
      $this->db->update('contacts', $data);

      return true;

  }

  //Get all contacts
  public function getContacts(){

      $result = array();

      $this->db->select('C.*,MI.denomination');
      $this->db->from('contacts C');
      $this->db->join('medical_insurance MI', 'C.medical_insurance_id = MI.medical_insurance_id');
      $this->db->order_by("MI.denomination", "asc");
      $this->db->where('C.active',"active");
      $query = $this->db->get();

      foreach ($query->result_array() as $row) {
          array_push($result, $row);
      }

      return $result;

  }

  //Get a specific contact information
  public function getContactById($contactID){

      $this->db->select('C.medical_insurance_id as denomination, C.sector, C.phone_number, C.email, C.contact_id');
      $this->db->from('contacts C');
      $this->db->join('medical_insurance MI', 'C.medical_insurance_id = MI.medical_insurance_id');
      $this->db->order_by("MI.denomination", "asc");
      $this->db->where('C.active',"active");
      $this->db->where('C.contact_id',$contactID);
      $query = $this->db->get();

      return $query->row();
  }

  //Delete contact information in 'contacs'
  public function delete($contactID,$userID){

      $now = date('Y-m-d H:i:s');

      //Delete contact
      $this->db->where('contact_id', $contactID);
      $this->db->update('contacts', array('active' => 'inactive','modify_user_id' => $userID,'update_date' =>$now));

      return true;

  }

}
