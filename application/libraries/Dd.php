<?php

    class Dd {
        public function __construct(){
            
        }

        public function dd($string){
            echo '<pre>';
                var_dump($string);
            echo '</pre>';
            exit();
        }
    }
?>