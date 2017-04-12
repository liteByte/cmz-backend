<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_cdconcept extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'concept_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'code' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  4,
                    'unsigned'      =>  TRUE,
                    'unique'        =>  TRUE,
                    'null'          =>  FALSE
                ),
                'concept_description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  70,
                    'null'          =>  FALSE
                ),
                'concept_group_id'  => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  FALSE
                ),
                'concept_type_id'  => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  FALSE
                ),
                'concept_movement_id'  => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  FALSE
                ),
                'value' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '16,3',
                    'null'          =>  FALSE
                ),
                'applies_liquidation' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  1,
                    'null'          =>  FALSE
                ),
                'receipt_legend' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  70,
                    'null'          =>  TRUE
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
        $this->dbforge->add_key('concept_id', TRUE);
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (concept_group_id)         REFERENCES concept_group(concept_group_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (concept_type_id)          REFERENCES concept_type(concept_type_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (concept_movement_id)      REFERENCES concept_movement(concept_movement_id)');
        $this->dbforge->create_table('credit_debit_concepts');

    }


    public function down(){

        $this->dbforge->drop_table('credit_debit_concepts');

    }









}
