<?php

include_once("phpmailer/Phpmailer.php");

class Mailer extends PHPMailer {

    public function __construct()
    {
        $this->SMTPDebug = SMTP::DEBUG_OFF;                         //Enable verbose debug output
        $this->isSMTP();                                            //Send using SMTP
        $this->Host       = 'webmail.sorocaba.sp.gov.br';           //Set the SMTP server to send through
        $this->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->Username   = "sigat";                                 //SMTP username
        $this->Password   = "A12345678";                            //SMTP password
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
        $this->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $this->setFrom('sigat@sorocaba.sp.gov.br', 'SIGAT');
        $this->addAddress('gmacedo@sorocaba.sp.gov.br');          //Add a recipient
    }

    
}