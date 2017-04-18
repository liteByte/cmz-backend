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
                )
        ));


        $this->dbforge->add_key('iva_id', TRUE);
        $this->dbforge->create_table('iva');

        $data = array(
            array(
                'description' => "IVA Responsable Inscripto",
            ),
            array(
                'description' => "IVA Responsable no Inscripto",
            ),
            array(
                'description' => "IVA no Responsable",
            ),
            array(
                'description' => "IVA Sujeto Exento",
            ),
            array(
                'description' => "Consumidor Final",
            ),
            array(
                'description' => "Responsable Monotributo",
            ),
            array(
                'description' => "Sujeto no Categorizado",
            ),
            array(
                'description' => "Proveedor del Exterior",
            ),
            array(
                'description' => "Cliente del Exterior",
            ),
            array(
                'description' => "IVA Liberado – Ley Nº 19.640",
            ),
            array(
                'description' => "IVA Responsable Inscripto – Agente de Percepción",
            ),
            array(
                'description' => "Pequeño Contribuyente Eventual",
            ),
            array(
                'description' => "Monotributista Social",
            ),
            array(
                'description' => "Pequeño Contribuyente Eventual Social",
            )
        );

        $this->db->insert_batch('iva', $data);
    }


    public function down(){
        $this->dbforge->drop_table('iva');
    }
}
