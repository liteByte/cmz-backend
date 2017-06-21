<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_concepts extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'concept_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  10,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'description' => array(
              'type'          =>  'VARCHAR',
              'constraint'    =>  100,
              'null'          =>  FALSE
            ),
            'code' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  10,
                'null'          =>  FALSE
            )

        ));

        $this->dbforge->add_key('concept_id', TRUE);
        $this->dbforge->create_table('concepts');

    }

    public function down(){

        $this->dbforge->drop_table('concepts');

    }









}
