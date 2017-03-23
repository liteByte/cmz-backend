<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_concept_movement extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'concept_movement_id'  => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'movement_description' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            )
        ));
        $this->dbforge->add_key('concept_movement_id', TRUE);
        $this->dbforge->create_table('concept_movement');

        $data = array(
            array(
                'movement_description' => "Fijo"
            ),
            array(
                'movement_description' => "Porcentaje"
            )
        );

        $this->db->insert_batch('concept_movement', $data);
    }


    public function down(){
        $this->dbforge->drop_table('concept_movement');
    }




}
