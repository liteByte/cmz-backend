<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Earnings extends CI_Model{

    private $data;
    private $table = "manage_earnings";
    private $msgError = "Error al al intentar guardar información en base de datos";
    private $msgRemove= "No se ha eliminado la información  base de datos";

    public function save($post){
        $this->data = (array)$post;
        $result = $this->db->insert($this->table, $this->data);
        $errors = $this->db->error();
        if(!$result || $errors['code'] != 0) return $this->msgError;

        return 1;
    }

    public function getAll(){

        $result = [];

        $this->db->from($this->table);
        $this->db->order_by("since ASC");
        $query = $this->db->get();
        if(!$query) return 0;

        foreach ($query->result_array() as $row){
            array_push($result, $row);
        }
        return $result;
    }

    public function getById($id){

        $result = [];
        $this->db->select('id_manage_earnings, since, until,  fixed,  percentage, minimun,  impuni' );
        $query = $this->db->get_where($this->table,["id_manage_earnings" =>  $id]);

        if(!$query) return 0;
        return $query->row();
    }

    public function update($id, $post){

        $this->data  = (array)$post;

        $this->db->where("id_manage_earnings", $id);
        $result = $this->db->update($this->table, $this->data);
        $errors = $this->db->error();

        if(!$result || $errors['code'] != 0) return $this->msgError;
        return 1;
    }
    
    public function delete($id){
        $this->db->where('id_manage_earnings', $id);
        $this->db->delete($this->table);
        $result = $this->db->affected_rows();

        if(!$result) return $this->msgRemove;
        
        return 1;
    }
}