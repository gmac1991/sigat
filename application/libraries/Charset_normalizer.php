<?php

class Charset_normalizer {

  private $chars_html_utf = array(
  "Ãƒ" => "&Atilde;", //Ã
  "Ã£" => "&atilde;", //ã
  "Ã¡" => "&aacute;", //á
  "Ã�" => "&Aacute;",//Á
  "Ã¢" => "&acirc;",  //â
  "Ã\x20" => "&agrave;", //à

  
  "ÃŠ" => "&Ecirc;",  //Ê
  "Ã‰" => "&Eacute;", //É
  "Ãª" => "&ecirc;",  //ê
  "Ã©" => "&eacute;", //é

  "Ã\xC2\xAD"  => "&iacute;", //í­

  "Ã“" => "&Oacute;", //Ó
  "Ã”" => "&Ocirc;",  //Ô
  "Ã´" => "&ocirc;",  //ô
  "Ã³" => "&oacute;", //ó
  "Ãµ" => "&otilde;", //õ

  "Ãº" => "&uacute;", //ú

  "Ã‡" => "&Ccedil;", //Ç
  "Ã§" => "&ccedil;", //ç
  
  "Âº" => "&#186;",   //º
  "Â°" => "&#176;",   //°
  "Âª" => "&#170;",   //ª

  //"�" => "ô",

  );

  public function normalize(string $in) : string {

    $out = $in;
    foreach($this->chars_html_utf as $key => $value) {
      $out = str_replace($key, $value, $out);
    }
    return $out;
  }
    
}

    


  