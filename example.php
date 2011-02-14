<?php
require_once('class.Einzahlungsschein.php');
require_once('../fpdf/fpdf.php');

$amount= "150.20";
$ref="000010000123456";

//Create a new pdf to create your invoice, already using FPDF
//(if you don't understand this part you should have a look at the FPDF documentation)
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(margin,0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(50, 4, "Just some dummy text.");

//now simply include your Einzahlungsschein, sending your pdf instance to the Einzahlungsschein class
$ezs = new createEinzahlungsschein(196, 0, $pdf);
$ezs->setBankData("Berner Kantonalbank AG", "3001 Bern", "01-200000-7");
$ezs->setRecipientData("My Company Ltd.", "Exampleway 61", "3001 Bern", "123456");
$ezs->setPayerData("Heinz Müller", "Beispielweg 23", "3072 Musterlingen");
$ezs->setPaymentData($amount, $ref);
$ezs->createEinzahlungsschein(true);

$pdf->output();


?>