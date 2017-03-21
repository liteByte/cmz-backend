<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_units_coverage extends CI_Migration{
    public function up(){

        $this->dbforge->add_field(array(
            'id_units_coverage' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'id_coverage' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'unit' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  2,
                'unsigned'      =>  TRUE
            ),
            'type_unit' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'honorary' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE,
                'default'       => 100
            ),
            'expenses' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE,
                'default'       => 100
            ),
        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_coverage)              REFERENCES coverage(id_coverage)');
        $this->dbforge->add_key('id_units_coverage', TRUE);
        $this->dbforge->add_field('UNIQUE KEY units_key (id_coverage,unit,type_unit )');
        $this->dbforge->create_table('units_coverage');

        $data = array(
            array(
                'id_coverage'       => 1,
                'unit'              => "A",
                'type_unit'         => "Internacion",
                'honorary'          => 5,
                'expenses'          => 20,
            ),
            array(
                'id_coverage'       => 2,
                'unit'              => "B",
                'type_unit'         => "Ambulatorio",
                'honorary'          => 5,
                'expenses'          => 20,
            )
        );

        $this->db->insert_batch('units_coverage', $data);
    }
    public function down(){
        $this->dbforge->drop_table('units_coverage');
    }
}
