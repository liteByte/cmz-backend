<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_honoraries extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'honorary_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'unity_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'movement' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  1,
                    'null'          =>  FALSE
                ),
                'value' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '7,4',
                    'null'          =>  FALSE
                ),
                'id_medical_career' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  TRUE
                ),
                'id_category_femeba' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  TRUE
                )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (unity_id)            REFERENCES unities(unity_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_medical_career)   REFERENCES medical_career(id_medical_career)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_category_femeba)  REFERENCES category_femeba(id_category_femeba)');
        $this->dbforge->add_key('honorary_id', TRUE);
        $this->dbforge->create_table('honoraries');

        $data = array(
            array(
                'unity_id'              => "1",
                'movement'              => "F",
                'value'                 => "12",
                'id_medical_career'     => "1",
                'id_category_femeba'    => null
            ),
            array(
                'unity_id'              => "2",
                'movement'              => "F",
                'value'                 => "23",
                'id_medical_career'     => null,
                'id_category_femeba'    => "1"
            ),
            array(
                'unity_id'              => "3",
                'movement'              => "U",
                'value'                 => "34",
                'id_medical_career'     => "2",
                'id_category_femeba'    => null
            ),
            array(
                'unity_id'              => "4",
                'movement'              => "U",
                'value'                 => "45",
                'id_medical_career'     => null,
                'id_category_femeba'    => "2"
            )
        );

        $this->db->insert_batch('honoraries', $data);
    }


    public function down(){

        $this->dbforge->drop_table('honoraries');

    }









}
