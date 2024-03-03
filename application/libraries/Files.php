<?php

    class Files {

    private $Files        =   null;

    private $SaveTo       =   null;

    private $Length       =   null;

    private $LengthLimit  =   null;

    private $SomaTamanhoArquivos = null;

    private $Response     =   array();

    private $Extensions   =   array();

    public function __construct( $files = '' ){

        $this->Files   =   $files;

    }


    public function setFile( $file ) {

        $this->Files   =   $file;

    }


    public function initialize( $dirToSave, $length, $lengthLimit,$fileExtension = array() ) {

        $this->SaveTo      =  $dirToSave;

        $this->Length      =  $length;

        $this->LengthLimit =  $lengthLimit;

        $this->Extensions  =  $fileExtension;

    }

    public function processMultFiles() {

        if( is_dir( $this->SaveTo ) ) {

            $countFiles   =   count( $this->Files['name'] );

            if( $countFiles > 0 ) {

                for ($i = 0; $i < $countFiles; $i++ ) {

                    if( isset( $this->Files['name'][$i] ) && trim( $this->Files['name'][$i] ) != '' ) {

                        $tmp       =  explode( '.', $this->Files['name'][$i] );
                        $extensao  = strtolower( end( $tmp ) );

                        $length    =  $this->Files['size'][$i];

                        $this->SomaTamanhoArquivos += $length;

                        $name      =  $this->Files['name'][$i];

                        if( $this->Extensions[0] !== '*' ) {
                            if( (array_search($extensao, $this->Extensions ) === false) ) {

                                $this->Response['status']   =   $name . ' - Arquivo não permitido';
    
                                $this->Response['code']     =   102;
    
                                break;
                            }
                        }else if( $length > $this->Length ) {

                            $this->Response['status']   =   'Os arquivo excede o limite de tamanho máximo de: ' . $this->Length / 1048576 . 'MB';

                            $this->Response['code']     =   102;

                            break;

                        } else if( $this->SomaTamanhoArquivos >= $this->LengthLimit ) {
                            $this->Response['status']   =   $name . ' - os anexos excede o limite de tamanho maximo de: ' . $this->LengthLimit . ' KB';

                            $this->Response['code']     =   102;

                            break;
                        }

                    }

                }

                if( !isset( $this->Response['code'] ) ) {
                    for ($i = 0; $i < $countFiles; $i++ ) {

                        if( isset( $this->Files['name'][$i] ) && trim( $this->Files['name'][$i] ) != '' ) {

                            if( !isset( $this->Response['code'] ) ) {

                                $cond  =  false;

                                $name  =  $this->Files['name'][$i];

                                while( !$cond ) {

                                    $name    =   rand(000000000, 9999999999) . '.' . $extensao;

                                    $file    =   $this->SaveTo . $name;

                                    if( !file_exists( $file ) ) {

                                        $cond  =  true;

                                    }

                                }

                                if( move_uploaded_file( $this->Files['tmp_name'][$i], $file ) ) {
                                    $this->Response[$i]['status']  =  100;

                                    $this->Response[$i]['file']    =  $file;

                                    $this->Response[$i]['name_file'] = $this->Files['name'][$i];

                                }else {

                                    $this->Response['status']   =   $name . ' - Ocorreu um erro ao tentar salvar o arquivo';

                                    $this->Response['code']     =   102;

                                    $this->deleteFileProcessed( $this->Response );

                                    break;

                                }

                            }

                        }

                    }

                }

                return $this->Response;

            }

        }else {

            $this->Response['status']   =   'Ocorreu um erro inesperado';

            $this->Response['code']     =   102;

        }

        return $this->Response;

    }


    public function processSingleFile() {

        if( is_dir( $this->SaveTo ) ) {



            $countFiles   =   count( $this->Files['name'] );


            if( $countFiles > 0 ) {

                if( isset( $this->Files['name'] ) && trim( $this->Files['name'] ) != '' ) {

                    $extensao  =  strtolower( end( explode( '.', $this->Files['name'] ) ) );

                    $length    =  $this->Files['size'];

                    $name      =  $this->Files['name'];



                    if( array_search($extensao, $this->Extensions ) === false ) {

                        $this->Response['status']   =   $extensao . ' - Arquivo não permitido';

                        $this->Response['code']     =   102;



                    }else if( $length > $this->Length ) {

                        $this->Response['status']   =   $name . ' - Arquivo excede o limite de tamanho de: ' . $this->Length . ' KB';

                        $this->Response['code']     =   102;



                    }

                }


                if( !isset( $this->Response['code'] ) ) {

                        if( isset( $this->Files['name'] ) && trim( $this->Files['name'] ) != '' ) {


                            if( !isset( $this->Response['code'] ) ) {

                                $cond  =  false;

                                $name  =  $this->Files['name'];

                                while( !$cond ) {

                                    $name    =   rand(000000000, 9999999999) . '.' . $extensao;

                                    $file    =   $this->SaveTo . $name;

                                    if( !file_exists( $file ) ) {

                                        $cond  =  true;

                                    }

                                }



                                if( move_uploaded_file( $this->Files['tmp_name'], $file ) ) {

                                    $this->Response['status']  =  100;

                                    $this->Response['file']    =  $file;

                                }else {

                                    $this->Response['status']   =   $file . ' - Ocorreu um erro ao tentar salvar o arquivo';

                                    $this->Response['code']     =   102;


                                }

                            }

                        }


                }

                return $this->Response;

            }

        }else {

            $this->Response['status']   =   'Ocorreu um erro inesperado';

            $this->Response['code']     =   102;

        }

        return $this->Response;

    }

    public function deleteFileProcessed( $files ) {

        for( $i = 0; $i < count( $files ); $i++ ) {

            $file  =  $files[$i]['file'];

            @unlink( $file );

        }

    }


    }

?>