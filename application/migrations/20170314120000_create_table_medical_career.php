<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_medical_career extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'id_category_circle' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'description_category_circle'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          => FALSE
            )
        ));
        $this->dbforge->add_key('id_category_circle', TRUE);
        $this->dbforge->create_table('medical_career');

        $data = array(
            array(
                'description_category_circle' => "básica"
            ),
            array(
                'description_category_circle' => "especialista"
            ),
            array(
                'description_category_circle' => "Jerarquizado"
            )
        );

        $this->db->insert_batch('medical_career', $data);
    }


    public function down(){
        $this->dbforge->drop_table('medical_career');
    }




}