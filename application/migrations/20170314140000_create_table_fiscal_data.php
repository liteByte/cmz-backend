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
                'constraint'    => 20,
                'unique'        => TRUE,
                'null'          => FALSE
            ),
            'date_start_activity' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 20,
                'null'          => FALSE
            ),
            'iibb' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 20,
                'null'          => FALSE
            ),
            'iibb_percentage' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 20,
                'null'          => FALSE
            ),
            'gain' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 2,
                'null'          => FALSE
            ),
            'iva_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          => FALSE
            ),
            'retention_vat' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 2,
                'null'          => FALSE
            ),
            'retention_gain' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 2,
                'null'          => FALSE
            ),

        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (iva_id)       REFERENCES iva(iva_id)');
        $this->dbforge->add_key('id_fiscal_data', TRUE);
        $this->dbforge->create_table('fiscal_data');

        $data = array(
            array(
                'cuit'  => '85745212458965',
                'date_start_activity' => '01/01/2017',
                'iibb' => '100000',
                'iibb_percentage' => '10',
                'gain' => 'S',
                'iva_id' => '1',
                'retention_vat' => '',
                'retention_gain' => '',
            ),
            array(
                'cuit'  => '85745212458900',
                'date_start_activity' => '02/01/2017',
                'iibb' => '100000',
                'iibb_percentage' => '10',
                'gain' => 'N',
                'iva_id' => '5',
                'retention_vat' => 'S',
                'retention_gain' => 'S',
            ),
        );

        $this->db->insert_batch('fiscal_data', $data);
    }

    public function down(){
        $this->dbforge->drop_table('fiscal_data');
    }


}