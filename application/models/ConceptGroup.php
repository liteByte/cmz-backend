<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class ConceptGroup extends CI_Model{

  private $description;

  public function __construct(){
	  parent::__construct();
  }



  public function  getConcepts(){

    $result = array();

    $query = $this->db->get('concept_group');

    foreach ($query->result_array('ConceptGroup') as $row){
      $result[] = $row;
    }

    return $result;

  }


}
