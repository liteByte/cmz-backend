<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_payment_type extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'id_payment_type' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'description_payment_type'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          => FALSE
            )
        ));

        $this->dbforge->add_key('id_payment_type', TRUE);
        $this->dbforge->create_table('payment_type');

        $data = array(
            array(
                'description_payment_type' => "Efectivo"
            ),
            array(
                'description_payment_type' => "Caja de Ahorro"
            ),
            array(
                'description_payment_type' => "Cheque"
            ),
            array(
                'description_payment_type' => "Cuenta Corriente"
            ),
            array(
                'description_payment_type' => "Transferencia"
            ),
        );

        $this->db->insert_batch('payment_type', $data);
    }


    public function down(){
        $this->dbforge->drop_table('payment_type');
    }

}