<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_concept_group extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'concept_group_id'  => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'group_description'       => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            )
        ));
        $this->dbforge->add_key('concept_group_id', TRUE);
        $this->dbforge->create_table('concept_group');

        $data = array(
            array(
                'group_description' => "Deducciones Legales"
            ),
            array(
                'group_description' => "Deducciones Obligatorias"
            ),
            array(
                'group_description' => "Deducciones Estatutarias"
            ),
            array(
                'group_description' => "Deducciones Voluntarias"
            ),
            array(
                'group_description' => "Deducciones Judiciales"
            ),
            array(
                'group_description' => "Otros Créditos"
            )
        );

        $this->db->insert_batch('concept_group', $data);
    }


    public function down(){
        $this->dbforge->drop_table('concept_group');
    }




}
