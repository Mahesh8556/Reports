<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML; 

$filename = __DIR__.'/pigmy1.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US'); 
// $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$bankName  = $_GET['bankName'];
$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$date = $_GET['date'];
$ac1 = $_GET['schemecode'];

$bankName = str_replace("'", "", $bankName);
//  $stdate_ = str_replace(''', '', $stdate);
$date1 = str_replace("'", "", $date);
$Branch = str_replace("'", "", $Branch);

// $ac1="'9'";
$dd="'DD/MM/YYYY'";
$query = 'SELECT pgmaster."BANKACNO", pgmaster."AC_NO", pgmaster."AC_NAME",interesttran."MONTHS",
           interesttran."LAST_INTEREST_DATE",interesttran."INTEREST_RATE",
           interesttran."DAYS",interesttran."LEDGER_BALANCE",interesttran."TD_SCHEME_AMOUNT",
		   interesttran."TRAN_AMOUNT",
           interesttran."RECPAY_INT_AMOUNT",interesttran."PENAL_INT_AMOUNT",INTERESTTRAN."PAYABLE_INT_OPENING",
           interesttran."TRAN_STATUS",schemast."S_NAME",
		   interesttran."BRANCH_CODE"
           From OWNBRANCHMASTER, pgmaster
           Inner Join (SELECT
     "TRAN_NO"
    , "SERIAL_NO"
    , "TRAN_DATE"
    , "TRAN_TIME"
    , "TRAN_TYPE"
    , "TRAN_MODE"
    , "TRAN_DRCR"
    , "BRANCH_CODE"
    , "TRAN_ACNOTYPE"
    , "TRAN_ACTYPE"
    , CAST("TRAN_ACNO" AS CHARACTER VARYING)
    , "TRAN_GLACNO"
    , "TRAN_AMOUNT"
    , "INTEREST_GLACNO"
    , "INTEREST_AMOUNT"
    , "RECPAY_INT_GLACNO"
    , "RECPAY_INT_AMOUNT"
    , "REC_PENAL_INT_GLACNO"
    , "REC_PENAL_INT_AMOUNT"
    , "ODUE_INT_GLACNO"
    , "ODUE_INT_AMOUNT"
    , "PENAL_INT_GLACNO"
    , "PENAL_INT_AMOUNT"
    , "OD_INT_AMOUNT"
    , "TRAN_STATUS"
    , "INTEREST_DATE"
    , "LAST_INTEREST_DATE"
    , "INTEREST_RATE"
    , "TD_SCHEME_AMOUNT"
    , "LEDGER_BALANCE"
    , "TOTAL_PRODUCTS"
    , "AC_OPEN_DATE"
    , "EXPIRY_DATE"
    , "MONTHS"
    , "DAYS"
    , "NARRATION"
    , "USER_CODE"
    , "OFFICER_CODE"
    , "POST_TO_INDIVIDUAL_AC"
    , "RECPAY_INT_OPENING" AS "PAYABLE_INT_OPENING"
    , "RECPENAL_INT_OPENING"
    , "ODUE_INT_OPENING"
  FROM INTERESTTRAN
 UNION ALL
  SELECT
     "TRAN_NO"
    , "SERIAL_NO"
    , "TRAN_DATE"
    , "TRAN_TIME"
    , "TRAN_TYPE"
    , "TRAN_MODE"
    , "TRAN_DRCR"
    , "BRANCH_CODE"
    , "TRAN_ACNOTYPE"
    , "TRAN_ACTYPE"
    , CAST("TRAN_ACNO" AS CHARACTER VARYING)
    , "TRAN_GLACNO"
    , CAST("TRAN_AMOUNT" AS FLOAT)
    , "INTEREST_GLACNO"
    , CAST("INTEREST_AMOUNT" AS FLOAT)
    , "RECPAY_INT_GLACNO"
    , CAST("RECPAY_INT_AMOUNT" AS FLOAT)
    , "REC_PENAL_INT_GLACNO"
    , CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
    , "ODUE_INT_GLACNO"
    , CAST("ODUE_INT_AMOUNT" AS FLOAT)
    , "PENAL_INT_GLACNO"
    , CAST("PENAL_INT_AMOUNT" AS FLOAT)
    , CAST("OD_INT_AMOUNT" AS FLOAT)
    , "TRAN_STATUS"
    , "INTEREST_DATE"
    , "LAST_INTEREST_DATE"
    , CAST("INTEREST_RATE" AS FLOAT)
    , CAST("TD_SCHEME_AMOUNT" AS FLOAT)
    , CAST("LEDGER_BALANCE" AS FLOAT)
    , "TOTAL_PRODUCTS"
    , "AC_OPEN_DATE"
    , "EXPIRY_DATE"
    , "MONTHS"
    , "DAYS"
    , "NARRATION"
    , "USER_CODE"
    , "OFFICER_CODE"
    , "POST_TO_INDIVIDUAL_AC"
    , CAST("RECPAY_INT_OPENING" AS FLOAT) AS "PAYABLE_INT_OPENING"
    , CAST("RECPENAL_INT_OPENING" AS FLOAT)
    , CAST("ODUE_INT_OPENING" AS FLOAT)
  FROM INTHISTORYTRAN ) INTERESTTRAN ON
           CAST(pgmaster."BANKACNO" as bigint) = cast(INTERESTTRAN."TRAN_ACNO" as bigint)
           inner join SCHEMAST ON
           CAST(PGMASTER."AC_TYPE" AS integer) = SCHEMAST."id"
           WHERE CAST(INTERESTTRAN."TRAN_DATE" AS date) = To_date ('.$date.','.$dd.') 
           AND OWNBRANCHMASTER.ID = INTERESTTRAN."BRANCH_CODE"
	       AND INTERESTTRAN."TRAN_ACTYPE"='.$ac1.'
		   AND INTERESTTRAN."BRANCH_CODE"='.$branch_code.' ORDER BY pGmaster."BANKACNO"';

          //  echo $query;

          $i = 0;
          $totcpi=0;
          $tottpi=0;
          $totnb=0;
          $totda=0;
          $tottpii=0;
          $totoppi=0;
          
  $GRAND_TOTAL =0;

 	$query = pg_query($conn,$query)	;
   while($row = pg_fetch_assoc($query)){
    // $total_value = $row['TOTAL_WEIGHT_GMS'] * $row['RATE'];
    
    $TotalPayableInterest = $row['PAYABLE_INT_OPENING'] + $row['RECPAY_INT_AMOUNT'];
    $totcpi=$totcpi+ $row['RECPAY_INT_AMOUNT'];
    $tottpi=$tottpi+ $row['PAYABLE_INT_OPENING'];
    $totnb=$totnb+$row['LEDGER_BALANCE'];
    $totda=$totda+$row['TD_SCHEME_AMOUNT'];
    $tottpii=$tottpii+$row['TRAN_AMOUNT'];
    $totoppi=$totoppi+$row['PAYABLE_INT_OPENING'];
    // $tot=$tot+ $row['TOTAL_VALUE'];
    $tmp=[
      'Date' => $row['date'],
    //   'Sr.No.' => $row['SR_NO'],
       'Ac/No.' => $row['AC_NO'],
       'Accountname' => $row['AC_NAME'],
       'Lastinterestdate' => $row['LAST_INTEREST_DATE'],
    
       'interestRate' => $row['INTEREST_RATE'],
       'months' => $row['MONTHS'],
       'days' => $row['DAYS'],
       'Totalpaidinterest' => $row['TRAN_AMOUNT'], 
       'TRAN_AMOUNT' =>sprintf("%.3f",($row['TRAN_AMOUNT'] + 0.0)),
       'DepositAmount' =>$row['TD_SCHEME_AMOUNT'],
       'TD_SCHEME_AMOUNT' =>sprintf("%.3f",($row['TD_SCHEME_AMOUNT'] + 0.0)),
       'OldProvisionPayableInterest' => $row['PAYABLE_INT_OPENING'],
       'PAYABLE_INT_OPENING' =>sprintf("%.3f",($row['PAYABLE_INT_OPENING'] + 0.0)),
       'CurrentPayableInterest ' => $row['RECPAY_INT_AMOUNT'],
       'PAYABLE_INT_OPENING' =>sprintf("%.3f",($row['PAYABLE_INT_OPENING'] + 0.0)),
       'RECPAY_INT_AMOUNT' =>sprintf("%.3f",($row['RECPAY_INT_AMOUNT'] + 0.0)),
        'TotalPayableInterest'=> $row['TotalPayableInterest'],
        'NetLedgerBalance'=> $row['TRAN_AMOUNT']+$row['LEDGER_BALANCE'],
        'LEDGER_BALANCE' =>sprintf("%.3f",($row['LEDGER_BALANCE'] + 0.0)),
        'scheme'=> $row['S_NAME'],
        

        "bankName"  => $bankName,
        "Branch" => $Branch,
        "branch_code" => $branch_code,
        "date" => $date1,
        'totcpi'=> sprintf("%.2f", ($totcpi + 0.0)),
        'totnb'=> sprintf("%.2f", ($totnb + 0.0)),
        'tottpi'=> sprintf("%.2f", ($tottpi + 0.0)),
        'totda'=> sprintf("%.2f", ($totda + 0.0)),
        'tottpii'=> sprintf("%.2f", ($tottpii + 0.0)),
        'totoppi'=> sprintf("%.2f", ($totoppi + 0.0)),
        ];
        if($row['TRAN_AMOUNT']+$row['LEDGER_BALANCE'] < 0)
        {
          $tmp['NetLedgerBalance']=$tmp['NetLedgerBalance']."     "."Cr";
        }
        else if($row['TRAN_AMOUNT']+$row['LEDGER_BALANCE'] > 0)
        {
          $tmp['NetLedgerBalance']=$tmp['NetLedgerBalance']."     "."Dr";
        }

    $data[$i]=$tmp;
    $i++;   
}    
 ob_end_clean();

// print_r($data);
// //  //echo $query;
  $config = ['driver' => 'array', 'data' => $data];
 $repandt = new PHPJasperXML();
 $repandt->load_xml_file($filename)    
     ->setDataSource($config)   ->export('Pdf');
?>					