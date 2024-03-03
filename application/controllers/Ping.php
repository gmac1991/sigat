<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ping extends CI_Controller {
    function __construct() {
        parent::__construct();
    }
    public function exec_ping() {
        $host = "pms-{$_GET["patrimonio"]}";
        exec("ping -c 1 -w 1 " . $host, $output, $result);
        //var_dump($output);
        if ($result == 0) {
            $ping = array("status" => true);
        } else {
            $ping = array("status" => false);
        }
        
        header('Content-Type: application/json');
        echo json_encode($ping);
        
    }
}