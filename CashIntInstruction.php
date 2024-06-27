<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CashIntInstruction.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$BranchName = $_GET['BranchName'];
$stdate = $_GET['stdate'];
$edate = $_GET['edate'];
$scheme=$_GET['scheme'];





// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");


$query =  ' SELECT CASHINTINSTRUCTIONS."TRAN_DATE", CASHINTINSTRUCTIONS."INSTRUCTION_NO", CASHINTINSTRUCTIONS."TRAN_ACNOTYPE"  ,
          CASHINTINSTRUCTIONS."TRAN_ACTYPE", CASHINTINSTRUCTIONS."TRAN_ACNO", CASHINTINSTRUCTIONS."TRAN_AMOUNT"  ,
          CASHINTINSTRUCTIONS."PAID_DATE", CASHINTINSTRUCTIONS."PAID_VOUCHER_NO", VWALLMASTER.AC_NAME  
          From CASHINTINSTRUCTIONS  Left outer join VWALLMASTER  
          On cast(CASHINTINSTRUCTIONS."TRAN_ACNO" as bigint) =cast(VWALLMASTER.AC_NO as bigint) WHERE 
          CASHINTINSTRUCTIONS."TRAN_ACTYPE" ='.$scheme.' AND 
          cast(CASHINTINSTRUCTIONS."TRAN_DATE" as date) >= '.$stdate.' AND 
          cast(CASHINTINSTRUCTIONS."TRAN_DATE" as date) <= '.$edate.' AND 
         CASHINTINSTRUCTIONS."PAID_DATE" IS NOT NULL';





$sql =  pg_query($conn,$query);

$i = 0;



    $tmp=[
         'TRAN_DATE' => $row['TRAN_DATE'],
        'INSTRUCTION_NO' => $row['INSTRUCTION_NO'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'PAID_DATE' =>  $row['PAID_DATE'],
        'AC_NAME' => $row['AC_NAME'],
        'PAID_VOUCHER_NO'=> $row['PAID_VOUCHER_NO'],
        'BranchName' => $BranchName,
        'stdate' => $stdate,
        'edate' => $edate,

      
        
    ];
    $data[$i]=$tmp;
    $i++;
  
 ob_end_clean();

 $config = ['driver'=>'array','data'=>$data];

 $report = new PHPJasperXML();
 $report->load_xml_file($filename)    
    ->setDataSource($config)
     ->export('Pdf');
    

?>