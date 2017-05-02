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
    private $table = "nomenclators";

    public function __construct(){
        parent::__construct();
    }

    //Creates the nomenclator in 'nomenclators'
    public function save($type, $code, $class, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity,$surgery){

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
            'surgery'            => $surgery,
            'active'             => 'active'
        );

        $this->db->insert('nomenclators', $data);

        return true;

    }

    //Updates the nomenclator in 'nomenclators'
    public function update($type, $description, $unity, $speciality_unity, $helpers, $help_unity, $anesthetist_unity, $spending_unity, $surgery, $id, $userID){

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
            'surgery'            => $surgery,
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
    public function delete($nomenclatorID){

        $now = date('Y-m-d H:i:s');

//        //Delete contact
//        $this->db->where('nomenclator_id', $nomenclatorID);
//        $this->db->update('nomenclators', array('active' => 'inactive','modify_user_id' => $userID,'update_date' =>$now));

        $query = $this->db->get_where($this->table, array("nomenclator_id" => $nomenclatorID));

        if($query->num_rows()){
            $this->db->where('nomenclator_id', $nomenclatorID);
            $result = $this->db->delete($this->table);
            $errors = $this->db->error();
            if($errors['code'] == '1451') return  "No se puede eliminar el nomenclador, ya que posee información relacionada";
            if(!$result) return "Error al intentar Nomenclador";
        }else{
            return "El Id del Nomenclador no existe en la base de datos";
        }
        return true;
    }

    public function validateData($code, $class){
        //Code+class validation
        $query = $this->db->get_where('nomenclators', array('code' => $code,'class' => $class));
        if ($query->num_rows() > 0) return "La combinación de codigo y clase ingresada ya ha sido utilizada";

        return "OK";

    }


    public function searchData($param){

        $result = [];
        $this->db->select('nomenclator_id, type, code, class, description, surgery');
        $this->db->from ($this->table);
        $this->db->or_like('code', $param);
        $this->db->or_like('description', $param);
        $this->db->or_like('class', $param);

        $this->db->order_by("code ASC, description ASC, class ASC ");
        $this->db->limit(15);
        $query = $this->db->get();

        foreach ($query->result_array() as $row){
            array_push($result, $row);
        }
        return $result;
    }
}

