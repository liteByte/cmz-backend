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
            'desription_femeba'  => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          => FALSE
            )
        ));
        $this->dbforge->add_key('id_category_femeba', TRUE);
        $this->dbforge->create_table('category_femeba');

        $data = array(
            array(
                'desription_femeba' => "básica"
            ),
            array(
                'desription_femeba' => "básica 1"
            ),
            array(
                'desription_femeba' => "especialista"
            ),
            array(
                'desription_femeba' => "jerarquizado"
            )
        );

        $this->db->insert_batch('category_femeba', $data);
    }


    public function down(){
        $this->dbforge->drop_table('category_femeba');
    }




}