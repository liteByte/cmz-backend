<?php
defined("BASEPATH") or exit("No direct script access allowed");

class Migrate extends CI_Controller {

    public function down()    {
       $this->load->library("migration");

      if(!$this->migration->version(20170220000000)){
        show_error($this->migration->error_string());
      } else {
        echo "All tables deleted succesfully";
      }
    }

    public function up()    {
      $this->load->library('migration');
      if ( ! $this->migration->current()) {
          show_error($this->migration->error_string());
      } else {
          echo "Migration Worked";
      }
    }

}
