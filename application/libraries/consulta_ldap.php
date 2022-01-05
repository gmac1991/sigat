<?php

class Consulta_LDAP {

    private $usuario = '';
    private $senha = '';
    private $servidor = '172.16.1.8';
    private $dn = "DC=PREFEITURA,DC=LOCAL";
    private $ad = NULL;

    function __construct($usr,$pass)
    {
        $this->$usuario = $usr;
        $this->$senha = $pass;
        
        $this->ad = ldap_connect($this->servidor)
        or die( "Não foi possível se conectar" );

        ldap_set_option($this->ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ad, LDAP_OPT_REFERRALS, 0);

    }

    function validaLogin() {

        $bind = ldap_bind($this->ad, $this->usuario, $this->senha);
    
        if ($bind) {

            $usuario = array();

            $usuario['id_usuario'] = $busca->row()->id_usuario;
            $usuario['nome_usuario'] = $busca->row()->nome_usuario;

            $this->load->library('encryption');
            $this->encryption->initialize(array('driver' => 'openssl'));
            
            $this->load->helper('cookie');
            set_cookie("usi",$this->encryption->encrypt($username));
            set_cookie("psi",$this->encryption->encrypt($password));


            return $usuario;
        }

        else {

            return NULL;
        }
    }

    function buscaSolicitantes($termo) {

        $lista = array();

        $filtro = "(&(objectClass=user)(objectCategory=user)(name=*".$termo."*))";

        $attrs = array("displayname","mail");

        $busca = ldap_search($this->ad, $this->dn, $filtro,$attrs);

        $resultados = ldap_get_entries($this->ad, $busca);

       if ($resultados["count"] > 0) {
            for ($i = 0; $i < $resultados["count"]; $i++) {
                foreach ($resultados[$i]["displayname"] as $linha) {
                    array_push($lista,$linha);
                }
            }
        }

        ldap_unbind($this->ad);

        return $lista;

    }




}

?>