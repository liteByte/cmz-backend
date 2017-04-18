<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_fiscal_data extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'id_fiscal_data' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'cuit' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 13,
                'unique'        => TRUE,
                'null'          => FALSE
            ),
            'date_start_activity' => array(
                'type'          => 'DATE',
                'null'          => FALSE
            ),
            'iibb' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 15,
                'null'          => FALSE
            ),
            'iibb_percentage' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE
            ),
            'gain' => array(
                'type'          => 'BOOLEAN',
                'null'          => FALSE
            ),
            'iva_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  2,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'retention_vat' => array(
                'type'          => 'BOOLEAN',
                'null'          => FALSE
            ),
            'retention_gain' => array(
                'type'          => 'BOOLEAN',
                'null'          => FALSE
            ),

        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (iva_id)       REFERENCES iva(iva_id)');
        $this->dbforge->add_key('id_fiscal_data', TRUE);
        $this->dbforge->create_table('fiscal_data');

    }

    public function down(){
        $this->dbforge->drop_table('fiscal_data');
    }


}