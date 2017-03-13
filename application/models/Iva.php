<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Iva extends CI_Model{

  private $type;

  public function __construct(){
	  parent::__construct();
  }

  //Get all iva types
  public function getIva(){

    $result = array();

    $query = $this->db->get('iva');

    foreach ($query->result_array('Iva') as $row)
    {
      array_push($result,$row);
    }

    return $result;

  }


}
