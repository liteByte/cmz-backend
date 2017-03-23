<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Scope extends CI_Model{

  private $description;

  public function __construct(){
	  parent::__construct();
  }

  //Get all scopes
  public function getScopes(){

    $result = array();

    $query = $this->db->get('scopes');

    foreach ($query->result_array('Scope') as $row){
      array_push($result,$row);
    }

    return $result;

  }


}
