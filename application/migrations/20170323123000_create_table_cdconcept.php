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
                    'unsigned'      =>  TRUE
                ),
                'concept_type_id'  => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'concept_movement_id'  => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'value' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '7,4',
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
        $this->dbforge->create_table('credit_debit_concepts');

        $data = array(
            array(
                'code'                  => "1234",
                'concept_description'   => "Concepto 1",
                'concept_group_id'      => "1",
                'concept_type_id'       => "1",
                'concept_movement_id'   => "1",
                'value'                 => "5,36",
                'applies_liquidation'   => 1,
                'receipt_legend'        => "Una leyenda nueva",
                'active'                => 'active'
            ),
            array(
                'code'                  => "5678",
                'concept_description'   => "Concepto 2",
                'concept_group_id'      => "2",
                'concept_type_id'       => "2",
                'concept_movement_id'   => "2",
                'value'                 => "544,36",
                'applies_liquidation'   => 0,
                'receipt_legend'        => "Otra leyenda nueva",
                'active'                => 'active'
            ),
        );

        $this->db->insert_batch('credit_debit_concepts', $data);
    }


    public function down(){

        $this->dbforge->drop_table('credit_debit_concepts');

    }









}
