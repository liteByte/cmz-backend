<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Concept extends CI_Model{

  public function __construct(){
    parent::__construct();
  }

  public function getByDescriptionLike($description){

        $this->db->select('C.concept_id,C.description');
        $this->db->from ('concepts C');
        $this->db->like('C.description', $description);
        $this->db->order_by("C.description", "asc");
        $this->db->limit(15);
        $query = $this->db->get();

        if (!$query)                 return [];
        if ($query->num_rows() == 0) return [];

        return $query->result_array();

    }

}
