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
                'description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  40,
                    'null'          =>  FALSE
                ),
                'type' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  2,
                    'null'          =>  FALSE
                )
        ));
        $this->dbforge->add_key('iva_id', TRUE);
        $this->dbforge->create_table('iva');

        $data = array(
            array(
                'description' => "EXCENTO",
                'type'        => "EX"
            ),
            array(
                'description' => "RESP. INSCRIPTO",
                'type'        => "RI"
            ),
            array(
                'description' => "RESP. NO INSCRIPTO",
                'type'        => "NI"
            ),
            array(
                'description' => "CONSUMIDOR FINAL",
                'type'        => "CF"
            ),
            array(
                'description' => "NO ALCANZADO",
                'type'        => "NA"
            ),
            array(
                'description' => "MONOTRIBUTISTA",
                'type'        => "MT"
            ),
            array(
                'description' => "RESPONS.INSCRIPTO",
                'type'        => "RE"

            )
        );

        $this->db->insert_batch('iva', $data);
    }


    public function down(){

        $this->dbforge->drop_table('iva');

    }









}
