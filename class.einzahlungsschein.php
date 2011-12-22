<?php

/* ------------------------------------------------------------------------          
 * 	class.einzahlungsschein.php											
 *	Eine Klasse um Einzahlungsscheine mit ESR-Nummer als PDF zu erstellen.
 *  A class to create Swiss payment slips with ESR number in pdf format.
 * ------------------------------------------------------------------------ 
 * Manuel Reinhard, manu@sprain.ch
 * Twitter: @sprain
 * Web: www.sprain.ch
 * ------------------------------------------------------------------------ 
 * License:
 * Use this for whatever you want. Private, public, commercial. I don't care.
 * However, be nice and give credits. I'll appreciate it!
 * ------------------------------------------------------------------------ 
 * Installation:
 * - Get FPDF http://fpdf.org/
 * - See example.php and get started
 * ------------------------------------------------------------------------ 
 * Thanks to:
 * http://www.smoke8.net/ for public designs of Einzahlungsscheinen
 * http://www.developers-guide.net/forums/5431,modulo10-rekursiv for Modulo10 function
 * http://ansuz.sooke.bc.ca/software/ocrb.php for OCRB font
 * http://blog.fruit-lab.de/fpdf-font-converter/ for FPDF font converter
 * http://www.fpdf.de/ for the pdf class
 * -----------------------------------------------------------------------
 * History:
 * 2011/12/22 - Manuel Reinhard - got rid of GNU license. Do whatever you want with it.
 * 2011/05/31 - Manuel Reinhard - improved behaviour of $this->ezs_bankingCustomerIdentification, minor bugfixes
 * 2011/02/14 - Manuel Reinhard - added project to Github, again
 * 2010/05/06 - Manuel Reinhard - added project to Github
 * 2010/05/06 - Manuel Reinhard - corrected position on bottom line after feedback from bank
 * 2010/04/24 - Manuel Reinhard - when it all started
 * ------------------------------------------------------------------------ */  


/**************************************
 * Import FPDF-Class
 * You can get the latest version here:
 * http://www.fpdf.de/downloads/releases/
 * or here: http://www.fpdf.org/
 *
 * Adjust path if necessary.
 **************************************/
require_once('../fpdf/fpdf.php');


/**************************************
 * Don't change anything from here on
 * if you don't know what you're doing.
 * Otherwise the earth might disappear
 * in a large black hole. We'll blame you!
 **************************************/
class createEinzahlungsschein {

	//margins in mm
	private $marginTop = 0;
	private $marginLeft = 0;

	//values on payment slip
	private $ezs_bankName = "";
	private $ezs_bankCity = "";
	private $ezs_bankingAccount = "";
	
	private $ezs_recipientName    = "";
	private $ezs_recipientAddress = "";
	private $ezs_recipientCity    = "";
	private $ezs_bankingCustomerIdentification = "";
	
	private $ezs_payerLine1		  = "";
	private $ezs_payerLine2       = "";
	private $ezs_payerLine3       = "";
	private $ezs_payerLine4       = "";
	
	private $ezs_referenceNumber = "";
	private $ezs_amount = 0;
	
	private $pdf = false;
	private $landscapeOrPortrait = "P";
	private $format = "A4";
	
	private $pathToImage = "";
	

	/**
	 * Constructor method for this class
	 */

	public function __construct($marginTop=0, $marginLeft=0, $pdfObject=false, $landscapeOrPortrait="P", $format="A4"){
		
		//set stuff
		$this->marginTop = $marginTop;
		$this->marginLeft = $marginLeft;
		$this->landscapeOrPortrait = $landscapeOrPortrait;
		$this->format = $format;
		
		//
		if($pdfObject != false){
			$this->pdf = $pdfObject;
		}//if
		
	}//function
	
	
	
	/**
	 * Set name, address and banking account of bank
	 * @param string $bankName
	 * @param string $bankCity
	 * @param string $bankingAccount
	 * @return bool
	 */
	 public function setBankData($bankName, $bankCity, $bankingAccount){
	 	$this->ezs_bankName = $bankName;
	 	$this->ezs_bankCity = $bankCity;
	 	$this->ezs_bankingAccount = $bankingAccount;
	 	return true;
	 }//function
	
	
	
	
	/**
	 * Set name and address of recipient of money (= you, I guess)
	 * @param string $recipientName
	 * @param string $recipientAddress
	 * @param string $recipientCity
	 * @param int    $bankingCustomerIdentification
	 * @return bool
	 */
	 public function setRecipientData($recipientName, $recipientAddress, $recipientCity, $bankingCustomerIdentification){
	 
	 	//basic check of bankingCustomerIdentification
	 	if(strlen($bankingCustomerIdentification) > 6){throw new Exception('bankingCustomerIdentification cannot be longer than 6 digits.');}
	 
	 	$this->ezs_recipientName    = $recipientName;
	 	$this->ezs_recipientAddress = $recipientAddress;
	 	$this->ezs_recipientCity    = $recipientCity;
	 	$this->ezs_bankingCustomerIdentification = $bankingCustomerIdentification;	 	
	 	return true;
	 }//function
	
	
	
	/**
	 * Set name and address of payer (very flexible four lines of text)
	 * @param string $payerLine1
	 * @param string $payerLine2
	 * @param string $payerLine3
	 * @param string $payerLine4
	 * @return bool
	 */
	 public function setPayerData($payerLine1, $payerLine2, $payerLine3="", $payerLine4=""){
	 	$this->ezs_payerLine1 = $payerLine1;
	 	$this->ezs_payerLine2 = $payerLine2;
	 	$this->ezs_payerLine3 = $payerLine3;
	 	$this->ezs_payerLine4 = $payerLine4;
	 	return true;
	 }//function
	 
	 
	 
	/**
	 * Set payment data
	 * @param float $amount
	 * @param int   $referenceNumber (
	 * @return bool
	 */
	 public function setPaymentData($amount, $referenceNumber){
	 	$this->ezs_amount 		   = sprintf("%01.2f",$amount);
	 	$this->ezs_referenceNumber = $referenceNumber;
	 	return true;
	 }//function
	 
	
	
	
	/**
	 * Does the magic!
	 * @param bool $doOutput
	 * @param string $filename
	 * @param string $saveAction (I, D, F, or S -> see http://www.fpdf.de/funktionsreferenz/?funktion=Output)
	 * @return string or file
	 */
	 public function createEinzahlungsschein($doOutput=true, $displayImage=false, $fileName="", $saveAction=""){
	 
	 	//Set basic stuff
	 	if(!$this->pdf){
	 		$this->pdf = new FPDF($this->landscapeOrPortrait,'mm',$this->format);
			$this->pdf->AddPage();
			$this->pdf->SetAutoPageBreak(margin,0);
	 	}//if
	    
	    
	    //Place image
	    if($displayImage){
	    	$this->pdf->Image($this->pathToImage."ezs_orange.gif", $this->marginLeft, $this->marginTop, 210, 106, "GIF");
	    }//if
	    
	    //Set font
		$this->pdf->SetFont('Arial','',9);
		
		
		//Place name of bank (twice)
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+8); 
		$this->pdf->Cell(50, 4,$this->ezs_bankName);
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+12); 
		$this->pdf->Cell(50, 4,$this->ezs_bankCity);
		
		$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+8); 
		$this->pdf->Cell(50, 4,$this->ezs_bankName);
		$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+12); 
		$this->pdf->Cell(50, 4,$this->ezs_bankCity);
		
		
		//Place baninkg account (twice)
		$this->pdf->SetXY($this->marginLeft+27, $this->marginTop+43); 
		$this->pdf->Cell(30, 4,$this->ezs_bankingAccount);

		$this->pdf->SetXY($this->marginLeft+90, $this->marginTop+43); 
		$this->pdf->Cell(30, 4,$this->ezs_bankingAccount);


		//Place money amount (twice)
		if($this->ezs_amount > 0){
			$amountParts = explode(".", $this->ezs_amount);
			
			$this->pdf->SetXY($this->marginLeft+5, $this->marginTop+50.5); 
			$this->pdf->Cell(35, 4,$amountParts[0], 0, 0, "R");
			$this->pdf->SetXY($this->marginLeft+50, $this->marginTop+50.5); 
			$this->pdf->Cell(6, 4,$amountParts[1], 0, 0, "C");
	
			$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+50.5); 
			$this->pdf->Cell(35, 4,$amountParts[0], 0, 0, "R");
			$this->pdf->SetXY($this->marginLeft+111, $this->marginTop+50.5); 
			$this->pdf->Cell(6, 4,$amountParts[1], 0, 0, "C");
		}//if
		
		
		//Place name of receiver (twice)
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+23); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientName));
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+27); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientAddress));
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+31); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientCity));
		
		$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+23); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientName));
		$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+27); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientAddress));
		$this->pdf->SetXY($this->marginLeft+66, $this->marginTop+31); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_recipientCity));
		
		
		//Place name of Payer (twice)
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+64); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine1));
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+68); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine2));
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+72); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine3));
		$this->pdf->SetXY($this->marginLeft+3, $this->marginTop+76); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine4));
		
		
		$this->pdf->SetXY($this->marginLeft+125, $this->marginTop+48); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine1));
		$this->pdf->SetXY($this->marginLeft+125, $this->marginTop+52); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine2));
		$this->pdf->SetXY($this->marginLeft+125, $this->marginTop+56); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine3));
		$this->pdf->SetXY($this->marginLeft+125, $this->marginTop+60); 
		$this->pdf->Cell(50, 4,utf8_decode($this->ezs_payerLine4));
		
		
		
		
		//Create complete reference number
		$completeReferenceNumber = $this->createCompleteReferenceNumber();
		
		//Place Reference Number (twice)	
		$this->pdf->SetXY($this->marginLeft+125, $this->marginTop+33.5); 
		$this->pdf->Cell(80, 4, $this->breakStringIntoBlocks($completeReferenceNumber));
		
		$this->pdf->SetFont('Arial','',7);
		$this->pdf->SetXY($this->marginLeft+2, $this->marginTop+60); 
		$this->pdf->Cell(50, 4, $this->breakStringIntoBlocks($completeReferenceNumber));
		
		
		//Set bottom line
		$this->pdf->AddFont('OCRB10');
		$this->pdf->SetFont('OCRB10','',10);
		$this->pdf->SetXY($this->marginLeft+64, $this->marginTop+84); 
		$this->pdf->Cell(140,4,$this->createBottomLineString(), 0, 0, "R");
		
		//Output
		if($doOutput){
			$this->pdf->Output($fileName, $saveAction);
			if($fileName != ""){
				return $fileName;
			}//if
		}//if

	 }//function
	 
	 
	 
	/**
	* Creates Modulo10 recursive check digit
	*
	* as found on http://www.developers-guide.net/forums/5431,modulo10-rekursiv
	* (thanks, dude!)
	*
	* @param string $number
	* @return int
	*/
	private function modulo10($number) {
		$table = array(0,9,4,6,8,2,7,1,3,5);
		$next = 0;
		for ($i=0; $i<strlen($number); $i++) {
			$next = $table[($next + substr($number, $i, 1)) % 10];
		}//for		
		return (10 - $next) % 10;
	}//function
	


	/**
	* Creates complete reference number
	* @return string
	*/
	private function createCompleteReferenceNumber() {
	
		//get reference number and fill with zeros
		$completeReferenceNumber = str_pad($this->ezs_referenceNumber, 20 ,'0', STR_PAD_LEFT);
	
		//add customer identification code
		$completeReferenceNumber = str_pad($this->ezs_bankingCustomerIdentification, 6, '0', STR_PAD_RIGHT).
								   $completeReferenceNumber;
		
		//add check digit
		$completeReferenceNumber .= $this->modulo10($completeReferenceNumber);
		
		//return
		return $completeReferenceNumber;
	}//function



	/**
	* Creates bottom line string
	* @return string
	*/
	private function createBottomLineString() {
	
		//start it, baby!
		$bottomLineString = "";
	
		//EZS with amount or not?
		if($this->ezs_amount == 0){
			$bottomLineString .= "042>";
		}else{
			$amountParts = explode(".", $this->ezs_amount);
			$bottomLineString .= "01";
			$bottomLineString .= str_pad($amountParts[0], 8 ,'0', STR_PAD_LEFT);
			$bottomLineString .= str_pad($amountParts[1], 2 ,'0', STR_PAD_RIGHT);
			$bottomLineString .= $this->modulo10($bottomLineString);
			$bottomLineString .= ">";
		}//if
		
		//add reference number
		$bottomLineString .= $this->createCompleteReferenceNumber();
		$bottomLineString .= "+ ";
		
		//add banking account
		$bankingAccountParts = explode("-", $this->ezs_bankingAccount);
		$bottomLineString .= str_pad($bankingAccountParts[0], 2 ,'0', STR_PAD_LEFT);
		$bottomLineString .= str_pad($bankingAccountParts[1], 6 ,'0', STR_PAD_LEFT);
		$bottomLineString .= str_pad($bankingAccountParts[2], 1 ,'0', STR_PAD_LEFT);
		$bottomLineString .= ">";
		
		//done!
		return $bottomLineString;
		
	}//function

	


	/**
	* Displays a string in blocks of a certain size.
	* Example: 00000000000000000000 becomes more readable 00000 00000 00000

	* @param string $string
	* @param int $blocksize
	* @return int
	*/
	private function breakStringIntoBlocks($string, $blocksize=5) {
		
		//lets reverse the string (because we want the block to be aligned from the right)
		$newString = strrev($string);
		
		//chop it into blocks
		$newString = chunk_split($newString, $blocksize);
		
		//re-reverse
		$newString = strrev($newString);

		
		return $newString;
		
	}//function



}//class


?>