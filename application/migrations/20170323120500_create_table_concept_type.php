<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_concept_type extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'concept_type_id'  => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'type_description'       => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            )
        ));
        $this->dbforge->add_key('concept_type_id', TRUE);
        $this->dbforge->create_table('concept_type');

        $data = array(
            array(
                'type_description' => "Débito"
            ),
            array(
                'type_description' => "Crédito"
            )
        );

        $this->db->insert_batch('concept_type', $data);
    }


    public function down(){
        $this->dbforge->drop_table('concept_type');
    }




}
