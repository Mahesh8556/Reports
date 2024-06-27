<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LoanReceivableInterestList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'30/09/2022'";
// $endDate = "'31/12/2006'";
// $dateformate = "'DD/MM/YYYY'";

$query = ' SELECT lnmaster."AC_NO", lnmaster."AC_NAME",interesttran."MONTHS",
           interesttran."LAST_INTEREST_DATE",interesttran."INTEREST_RATE",
           interesttran."DAYS",interesttran."LEDGER_BALANCE",interesttran."TRAN_AMOUNT",
           interesttran."RECPAY_INT_AMOUNT",interesttran."PENAL_INT_AMOUNT",
           interesttran."RECPAY_INT_OPENING",
           interesttran."TRAN_STATUS",schemast."S_NAME"
           From lnmaster
           Inner Join interesttran on
           cast(lnmaster."BANKACNO" as bigint) = cast(interesttran."TRAN_ACNO" as bigint)
           Inner Join schemast on
           cast(lnmaster."AC_TYPE" as integer) = schemast."id"
           WHERE cast(interesttran."LAST_INTEREST_DATE" as date) <= '.$startDate.'::date 
           AND cast(interesttran."TRAN_STATUS" as integer) = 1 ';
 
echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$NETLEDGERBAL = 0;

while($row = pg_fetch_assoc($sql))  
{
    // $NETLEDGERBAL = $row['RECPAY_INT_AMOUNT']+ $row['RECPAY_INT_OPENING'];
    $TOTALPAYABLE = $row['RECPAY_INT_AMOUNT']+ $row['RECPAY_INT_OPENING'];
  

  if($type == ''){
    $type = $row['to_char'];
  }
  if($type == $row['to_char']){
    $SCHM_MTOTAL = $SCHM_MTOTAL + $row['TRAN_AMOUNT'];
  }else{
    $type = $row['to_char'];
    $SCHM_MTOTAL = 0;
    $SCHM_MTOTAL = $SCHM_MTOTAL + $row['TRAN_AMOUNT'];
  }

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'MONTHS' => $row['MONTHS'],
        'S_NAME'=> $row['S_NAME'],
        'AC_NAME'=> $row['AC_NAME'],
        'LAST_INTEREST_DATE'=> $row['LAST_INTEREST_DATE'],
        'INTEREST_RATE' => $row['INTEREST_RATE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'DAYS' => $row['DAYS'],
        'PENAL_INT_AMOUNT' => $row['PENAL_INT_AMOUNT'],
        'LEDGER_BALANCE' => $row['LEDGER_BALANCE'],
        'TD_SCHEME_AMOUNT' => $row['TD_SCHEME_AMOUNT'],
        'RECPAY_INT_AMOUNT' => $row['RECPAY_INT_AMOUNT'],
        'RECPAY_INT_OPENING' => $row['RECPAY_INT_OPENING'],
        
    ];
    $data[$i]=$tmp;
    $i++;
}
// ob_end_clean();

// $config = ['driver'=>'array','data'=>$data];

// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');
    
 ?>

