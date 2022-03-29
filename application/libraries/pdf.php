<?php

require_once('fpdf/fpdf.php');

class PDF extends FPDF {
	
	
	
	// Page header
	function Header()
	{

	

			
			$this->Image(base_url('img/logo_pms.png'),20,10,17,17);
			$this->SetFont('Arial','B',11);
			$this->Cell(0,10,utf8_decode('Secretaria de Administração'),0,0,'C');
			$this->Ln(5);
			$this->SetFont('Arial','B',12);
			$this->Cell(0,10,utf8_decode('Divisão de Gestão de Tecnologia da Informação'),0,0,'C');
			$this->Ln(5);
			$this->SetFont('Arial','',11);
			$this->Cell(0,10,utf8_decode('Seção de Suporte Técnico'),0,0,'C');
			$this->Ln(5);
			$this->SetFont('Arial','I',10);
			$this->Cell(0,10,'Prefeitura Municipal de Sorocaba',0,0,'C');
			$this->Ln(20);


		
	
	}

	// Page footer
	function Footer()
	{
	// Position at 1.5 cm from bottom
	$this->SetY(-15);
	// Arial italic 8
	$this->SetFont('Arial','I',8);
	// Page number
	$this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
	}
}
	  
?>