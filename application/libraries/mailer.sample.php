<?php

include_once("phpmailer/phpmailer.php");

class Mail extends PHPMailer {

    public function __construct()
    {
        $this->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->isSMTP();                                            //Send using SMTP
        $this->Host       = "";                                     //Set the SMTP server to send through
        $this->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->Username   = "";                                     //SMTP username
        $this->Password   = "";                                     //SMTP password
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
        $this->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $this->setFrom('sigat@localhost', 'SIGAT');
    }

    
}