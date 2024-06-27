<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/MaturedButNotClosed.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'30/09/2022'";

$query = ' SELECT SIZEWISEBALANCE."AMOUNT_FROM", SIZEWISEBALANCE."AMOUNT_TO",SCHEMAST."S_NAME", 	
           DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE",DPMASTER."AC_NO",DPMASTER."AC_CUSTID", 	
           DPMASTER."AC_NAME",DPMASTER."AC_SCHMAMT",DPMASTER."AC_CLOSEDT" as TRAN_DATE
           FROM SCHEMAST, DPMASTER, SIZEWISEBALANCE, 	
              ( 	
              SELECT DEPOTRAN."TRAN_ACNOTYPE",DEPOTRAN."TRAN_ACTYPE",DEPOTRAN."TRAN_ACNO", 	
              (SUM(CASE DEPOTRAN."TRAN_DRCR"  WHEN 'C' THEN cast(DEPOTRAN."TRAN_AMOUNT" as integer)  ELSE 0 END) + 	
              SUM(DEPOTRAN."RECPAY_INT_AMOUNT")) INT_PAID, 	
              SUM(CASE DEPOTRAN."TRAN_DRCR"  WHEN 'D' THEN  cast(DEPOTRAN."INTEREST_AMOUNT" as integer)  ELSE 0 END) INT_AMOUNT 	
              From 	
              DPMASTER, DEPOTRAN 	
              Where DPMASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE" 	
              AND DPMASTER."AC_TYPE" = cast(DEPOTRAN."TRAN_ACTYPE" as integer) 	
              AND DPMASTER."AC_NO" = cast(DEPOTRAN."TRAN_ACNO" as bigint) 	
              AND ((DEPOTRAN."IS_INTEREST_ENTRY" <> 0 And (cast(DEPOTRAN."TRAN_AMOUNT" as integer) <> 0 
              Or DEPOTRAN."RECPAY_INT_AMOUNT" <> 0)) OR cast(DEPOTRAN."INTEREST_AMOUNT" as integer) <> 0) 
              AND DEPOTRAN."TRAN_DATE" = DPMASTER."AC_CLOSEDT" 	
              GROUP BY DEPOTRAN."TRAN_ACNOTYPE",DEPOTRAN."TRAN_ACTYPE",DEPOTRAN."TRAN_ACNO" 	
              ) 	
              INTDETAILS 	
           WHERE SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE" 	
           AND SCHEMAST."S_APPL" = DPMASTER."AC_TYPE" 	
           AND SIZEWISEBALANCE."ACNOTYPE" = 'TD' AND  	
           SIZEWISEBALANCE."SLAB_TYPE" = 'AMOUNT' 	
           AND DPMASTER."AC_SCHMAMT" > SIZEWISEBALANCE."AMOUNT_FROM" 	
           AND DPMASTER."AC_SCHMAMT" <= SIZEWISEBALANCE."AMOUNT_TO" 	
           AND DPMASTER."AC_ACNOTYPE" = INTDETAILS."TRAN_ACNOTYPE"  	
           AND DPMASTER."AC_TYPE" = cast(INTDETAILS."TRAN_ACTYPE" as integer)	
           AND DPMASTER."AC_NO" = cast(INTDETAILS."TRAN_ACNO" as bigint)	
           AND DPMASTER."AC_ACNOTYPE" = 'TD' 
           AND cast(DPMASTER."AC_CLOSEDT" as date) between 
           to_date('16-01-2010','DD/MM/YYYY') and to_date('16-09-2022','DD/MM/YYYY') ';
 

$sql =  pg_query($conn,$query);

$i = 0;

$NETLEDGERBAL = 0;
$LEDGBALGTOT = 0;
$INTAMTGTOT = 0;
$RECEVINTGTOT = 0;
$PENALINTGTOT = 0;
$NETLEDGTOT = 0;

if (pg_num_rows($sql) == 0) {
  include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql))  
{

    $NETLEDGERBAL = $row['RECPAY_INT_AMOUNT']+ $row['PENAL_INT_AMOUNT'];
    $LEDGBALGTOT = $LEDGBALGTOT + $row['LEDGER_BALANCE'];
    $INTAMTGTOT = $INTAMTGTOT + $row['TRAN_AMOUNT']; 
    $RECEVINTGTOT = $RECEVINTGTOT + $row['RECPAY_INT_AMOUNT'];
    $PENALINTGTOT = $PENALINTGTOT + $row['PENAL_INT_AMOUNT'];
    $NETLEDGTOT = $NETLEDGTOT + $NETLEDGERBAL;

  // grand-total
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
        'AC_NAME'=> $row['AC_NAME'],
        'S_NAME'=> $row['S_NAME'],
        'LAST_INTEREST_DATE'=> $row['LAST_INTEREST_DATE'],
        'INTEREST_RATE' => $row['INTEREST_RATE'],
        'DAYS' => $row['DAYS'],
        'LEDGER_BALANCE' => $row['LEDGER_BALANCE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'RECPAY_INT_AMOUNT' => $row['RECPAY_INT_AMOUNT'],
        'PENAL_INT_AMOUNT' => $row['PENAL_INT_AMOUNT'],
        'NETLEDGERBAL' => $NETLEDGERBAL, 
        'LEDGBALGTOT' => $LEDGBALGTOT,
        'INTAMTGTOT' => $INTAMTGTOT,
        'RECEVINTGTOT' => $RECEVINTGTOT,
        'PENALINTGTOT' => $PENALINTGTOT,
        'NETLEDGTOT' => $NETLEDGTOT,
    ];
    $data[$i]=$tmp;
    $i++;
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}
?>


