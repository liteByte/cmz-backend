<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Contact extends CI_Model{

  private $denomination;
  private $sector;
  private $phone_number;
  private $email;

  public function __construct(){
      parent::__construct();
  }

  //Creates the contact in 'contacts'
  public function save($denomination, $sector, $phone_number, $email){

      $data = array(
                  'denomination'  => $denomination,
                  'sector'        => $sector,
                  'phone_number'  => $phone_number,
                  'email'         => $email,
                  'active'        => 'active'
      );

      $this->db->insert('contacts', $data);

      return true;

  }

  //Updates the contact in 'contacts'
  public function update($denomination, $sector, $phone_number, $email, $id, $userID){

      $now = date('Y-m-d H:i:s');

      $data = array(
                    'denomination'    => $denomination,
                    'sector'          => $sector,
                    'phone_number'    => $phone_number,
                    'email'           => $email,
                    'active'          => 'active',
                    'update_date'     => $now,
                    'modify_user_id'  => $userID
      );

      $this->db->where('contact_id', $id);
      $this->db->update('contacts', $data);

      return true;

  }

  //Get all contacts
  public function getContacts(){

      $result = array();

      $query = $this->db->get_where('contacts', array('active' => "active"));

      foreach ($query->result_array('Contact') as $row){
         array_push($result,$row);
      }

      return $result;

  }

  //Get a specific contact information
  public function getContactById($contactID){

      $result = array();

      $query = $this->db->get_where('contacts', array("contact_id" => $contactID));

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
