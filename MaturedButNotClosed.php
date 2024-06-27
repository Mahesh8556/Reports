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

$bankName = $_GET['bankName'];
$branch = $_GET['branch'];
$scheme = $_GET['scheme'];
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $startDate = "'30/09/2022'";

$query = ' SELECT DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE",DPMASTER."AC_NO",DPMASTER."AC_NAME",
           DPMASTER."AC_SCHMAMT",DPMASTER."AC_MATUAMT",DPMASTER."AC_EXPDT",DPMASTER."AC_OPDATE",
           DPMASTER."AC_REF_RECEIPTNO",DPMASTER."AC_CLOSEDT",SCHEMAST."S_APPL",SCHEMAST."S_NAME",
           COALESCE(cast(DPMASTER."AUTO_MATURED_PAYABLEAMT" as integer),0) as PAYABLE_AMOUNT, 
           COALESCE(cast(DPMASTER."AUTO_MATURED_INTERESTAMT" as integer),0) as PAYABLE_INTEREST,
           OWNBRANCHMASTER."NAME"
           FROM DPMASTER
           INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
           INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
           WHERE ( DPMASTER."AC_OPDATE" IS NULL OR cast(DPMASTER."AC_OPDATE" as date) <= '.$enddate.' ::date) 
           AND (DPMASTER."AC_CLOSEDT" IS NULL OR cast(DPMASTER."AC_CLOSEDT" as date) > '.$enddate.' ::date) 
           AND DPMASTER."BRANCH_CODE" = '.$branch.'
           AND DPMASTER."AC_TYPE" ='.$scheme.'
           AND cast(DPMASTER."AC_EXPDT" as date) >= '.$startdate.' ::date 
           AND cast(DPMASTER."AC_EXPDT" as date) <= '.$enddate.' ::date ';
 
echo $query;

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
        
        'bankName' => $bankName,
        'branch' => $branch, 
        'scheme' => $scheme,
        'startdate' => $startdate,
        'enddate' => $enddate,
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
    
}
?>

