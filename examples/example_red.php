<?php
require __DIR__.'/../vendor/autoload.php';

use Gridonic\ESR\CreditSlip;
use fpdf\FPDF;

define('FPDF_FONTPATH', __DIR__.'/../src/Gridonic/ESR/Resources/font');

$amount= "150.20";

//Create a new pdf to create your invoice, already using FPDF
//(if you don't understand this part you should have a look at the FPDF documentation)
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(0,0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(50, 4, "Example red Einzahlungsschein (see bottom of this pdf)");

//now simply include your Einzahlungsschein, sending your pdf instance to the Einzahlungsschein class
$ezs = new CreditSlip(191, 0, $pdf);
$ezs->setType('red');
$ezs->setPaymentReason('Invoice 34345');
$ezs->setBankData("Berner Kantonalbank AG", "3001 Bern", "01-200000-7");
$ezs->setRecipientData("My Company Ltd.", "Exampleway 61", "3001 Bern", "CH100023000A109822346");
$ezs->setPayerData("Heinz Müller", "Beispielweg 23", "3072 Musterlingen");
$ezs->setPaymentData($amount);
$ezs->createEinzahlungsschein(false, true);

$pdf->output();
