<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_nomenclators extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'nomenclator_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'type' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  2,
                    'null'          =>  FALSE
                ),
                'code' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  15,
                    'null'          =>  FALSE
                ),
                'class' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  1,
                    'null'          =>  TRUE
                ),
                'description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  30,
                    'null'          =>  FALSE
                ),
                'unity' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  1,
                    'null'          =>  FALSE
                ),
                'speciality_unity' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '10,2',
                    'null'          =>  FALSE
                ),
                'helpers' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  1,
                    'null'          =>  TRUE
                ),
                'help_unity' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '10,2',
                    'null'          =>  FALSE
                ),
                'anesthetist_unity' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '10,2',
                    'null'          =>  FALSE
                ),
                'spending_unity' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '10,2',
                    'null'          =>  FALSE
                ),
                'active' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  30,
                    'null'          =>  FALSE
                ),
                'update_date'  => array(
                    'type'          =>  'TIMESTAMP',
                    'null'          =>  TRUE,
                ),
                'modify_user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  TRUE
                )
        ));
        $this->dbforge->add_key('nomenclator_id', TRUE);
        $this->dbforge->add_field('UNIQUE KEY nomenclator_key (code,class)');
        $this->dbforge->create_table('nomenclators');

    }


    public function down(){

        $this->dbforge->drop_table('nomenclators');

    }









}
