<?php

require_once('fpdf/fpdf.php');

class PDF extends FPDF {
	
	
	
	// Page header
	function Header()
	{

	

			
			$this->Image(base_url('img/logo_pms.png'),50,10,17,17);
			$this->SetFont('Arial','B',16);
			$this->Cell(80);
			$this->Cell(30,10,'Seзгo de Suporte Tйcnico',0,0,'C');
			$this->Ln(10);
			$this->SetFont('Arial','I',12);
			$this->Cell(0,5,'Prefeitura Municipal de Sorocaba',0,0,'C');
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
	$this->Cell(0,10,'Pбgina '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
	  
?>