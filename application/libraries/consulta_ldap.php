<?php

class Consulta_LDAP {

    public $usuario = '';
    private $senha = '';
    private $servidor = '10.28.10.13';
    private $dn = "DC=PREFEITURA,DC=LOCAL";
    private $ad = NULL;

    function __construct($usr = '',$pass = '')
    {
        $this->usuario = 'prefeitura\\' . $usr;
        $this->senha = $pass;
        
        $this->ad = ldap_connect($this->servidor)
        or die( "Não foi possível se conectar" );

        ldap_set_option($this->ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ad, LDAP_OPT_REFERRALS, 0);

    }

    function validaLogin() {

        $bind = ldap_bind($this->ad, $this->usuario, $this->senha);
    
        if ($bind) {

            ldap_unbind($this->ad);

            return TRUE;
        }

        else {

            return NULL;
        }
    }

    function buscaSolicitantes($termo) {

        if ($this->ad) {
            $bind = ldap_bind($this->ad, $this->usuario, $this->senha);

            if ($bind) {
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

    }




}

?>