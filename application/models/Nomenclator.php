<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Nomenclator extends CI_Model{

    private $type;
    private $code;
    private $class;
    private $description;
    private $unity;
    private $speciality_unity;
    private $helpers;
    private $help_unity;
    private $anesthetist_unity;
    private $spending_unity;

  public function __construct(){
      parent::__construct();
  }

  //Creates the nomenclator in 'nomenclators'
  public function save($type, $code, $class, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity){

      $data = array(
                  'type'               => $type,
                  'code'               => $code,
                  'class'              => strtoupper($class),
                  'description'        => $description,
                  'unity'              => strtoupper($unity),
                  'speciality_unity'   => $speciality_unity,
                  'helpers'            => $helpers,
                  'help_unity'         => $help_unity,
                  'anesthetist_unity'  => $anesthetist_unity,
                  'spending_unity'     => $spending_unity,
                  'active'             => 'active'
      );

      $this->db->insert('nomenclators', $data);

      return true;

  }

  //Updates the nomenclator in 'nomenclators'
  public function update($type, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity, $id, $userID){

      $now = date('Y-m-d H:i:s');

      $data = array(
                    'type'               => $type,
                    'description'        => $description,
                    'unity'              => strtoupper($unity),
                    'speciality_unity'   => $speciality_unity,
                    'helpers'            => $helpers,
                    'help_unity'         => $help_unity,
                    'anesthetist_unity'  => $anesthetist_unity,
                    'spending_unity'     => $spending_unity,
                    'active'             => 'active',
                    'update_date'        => $now,
                    'modify_user_id'     => $userID
      );

      $this->db->where('nomenclator_id', $id);
      $this->db->update('nomenclators', $data);

      return true;

  }

  //Get all nomenclators
  public function getNomenclators(){

      $result = array();

      $this->db->where(array('active' => "active"));
      $this->db->order_by("type", "asc");
      $this->db->order_by("code", "asc");
      $query = $this->db->get('nomenclators');

      foreach ($query->result_array('Nomenclator') as $row) {
          array_push($result, $row);
      }

      return $result;

  }

  //Get a specific contact information
  public function getNomenclatorById($nomenclatorID){

      $result = array();

      $query = $this->db->get_where('nomenclators', array("nomenclator_id" => $nomenclatorID));

      return $query->row();
  }

  //Delete contact information in 'contacs'
  //TODO:El sistema valida que la prestación del Nomenclador al ser eliminada no este asociada a alguna liquidación actual o histórica.
  public function delete($nomenclatorID,$userID){

      $now = date('Y-m-d H:i:s');

      //Delete contact
      $this->db->where('nomenclator_id', $nomenclatorID);
      $this->db->update('nomenclators', array('active' => 'inactive','modify_user_id' => $userID,'update_date' =>$now));

      return true;

  }

  public function validateData($code, $class){

    //Code+class validation
    $query = $this->db->get_where('nomenclators', array('code' => $code,'class' => $class));
    if ($query->num_rows() > 0) return "La combinación de codigo y clase ingresada ya ha sido utilizada";

    return "OK";

  }

}
