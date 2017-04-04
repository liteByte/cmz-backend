<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_category_femeba extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'id_category_femeba' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'description_femeba'  => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          => FALSE
            )
        ));
        $this->dbforge->add_key('id_category_femeba', TRUE);
        $this->dbforge->create_table('category_femeba');

        $data = array(
            array(
                'description_femeba' => "A-Básica"
            ),
            array(
                'description_femeba' => "B1-Básica 1"
            ),
            array(
                'description_femeba' => "B2-Especialista"
            ),
            array(
                'description_femeba' => "D-Jerarquizado"
            )
        );

        $this->db->insert_batch('category_femeba', $data);
    }


    public function down(){
        $this->dbforge->drop_table('category_femeba');
    }




}
