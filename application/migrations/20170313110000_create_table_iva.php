<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_iva extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'iva_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'type' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  40,
                    'null'          =>  FALSE
                )
        ));
        $this->dbforge->add_key('iva_id', TRUE);
        $this->dbforge->create_table('iva');

        $data = array(
            array(
                'type' => "EXCENTO"
            ),
            array(
                'type' => "RESP. INSCRIPTO"
            ),
            array(
                'type' => "RESP. NO INSCRIPTO"
            ),
            array(
                'type' => "CONSUMIDOR FINAL"
            ),
            array(
                'type' => "NO ALCANZADO"
            ),
            array(
                'type' => "MONOTRIBUTISTA"
            )
        );

        $this->db->insert_batch('iva', $data);
    }


    public function down(){

        $this->dbforge->drop_table('iva');

    }









}
