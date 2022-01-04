<?php

class LDAP {

    private $usuario = 'PREFEITURA\gxmacedo';
    private $senha = 'Gustavo91';
    private $servidor = '172.16.1.8';
    private $dn = "DC=PREFEITURA,DC=LOCAL";
    private $ad = NULL;

    function __construct()
    {
        $this->ad = ldap_connect($this->servidor)
        or die( "Não foi possível se conectar" );

        ldap_set_option($this->ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ad, LDAP_OPT_REFERRALS, 0);

        ldap_bind($this->ad, $this->usuario, $this->senha)
        or die ("Não foi possível autenticar");
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