<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/TermDepositInterestList1.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");
// $conn = pg_connect("host=127.0.0.1 port=5433 dbname=bhairavnath1 user=postgres password=snehal20");
$dateformate = "'DD/MM/YYYY'";
$bankName  = $_GET['bankName'];
$Branch = $_GET['branchName'];
$branch_code = $_GET['branch_code'];
$date = $_GET['date'];
$ac1 = $_GET['schemecode'];

$bankName = str_replace("'", "", $bankName);
//  $stdate_ = str_replace(''', '', $stdate);
$date1 = str_replace("'", "", $date);
$Branch_ = str_replace("'", "", $Branch);

$dd = "'DD/MM/YYYY'";
// $ac1="'8'";

$query = 'SELECT dpmaster."BANKACNO", dpmaster."AC_NO", dpmaster."AC_NAME",interesttran."MONTHS",
interesttran."LAST_INTEREST_DATE",interesttran."INTEREST_RATE",
interesttran."DAYS",interesttran."LEDGER_BALANCE",interesttran."TD_SCHEME_AMOUNT",
interesttran."TRAN_AMOUNT",
interesttran."RECPAY_INT_AMOUNT",interesttran."PENAL_INT_AMOUNT",
interesttran."TRAN_STATUS",schemast."S_NAME",
interesttran."BRANCH_CODE"
From OWNBRANCHMASTER, dpmaster
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
, "RECPAY_INT_OPENING" AS "RECEIVABLE_INT_OPENING"
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
, CAST("RECPAY_INT_OPENING" AS FLOAT) AS "RECEIVABLE_INT_OPENING"
, CAST("RECPENAL_INT_OPENING" AS FLOAT)
, CAST("ODUE_INT_OPENING" AS FLOAT)
FROM INTHISTORYTRAN ) INTERESTTRAN ON
CAST(dpmaster."BANKACNO" as bigint) = cast(INTERESTTRAN."TRAN_ACNO" as bigint)
inner join SCHEMAST ON
CAST(dpmaster."AC_TYPE" AS integer) = SCHEMAST."id"
WHERE CAST(INTERESTTRAN."TRAN_DATE" AS date) = To_date (' . $date . ',' . $dd . ') ::date 
AND INTERESTTRAN."TRAN_ACTYPE"=  ' . $ac1 . '
AND OWNBRANCHMASTER.ID = INTERESTTRAN."BRANCH_CODE"
AND INTERESTTRAN."BRANCH_CODE"=' . $branch_code . ' ORDER BY dpmaster."BANKACNO"';
echo $query;
$i = 0;
$query = pg_query($conn, $query);
$TNetLedgerBalance=0;
$SNetLedgerBalance=0;
$tottpi=0;
$TDepositAmount=0;
$STotalPaidInterest=0;
$TotalPayableInterest=0;
$STotalPayableInterest=0;
$GSTotalPayableInterest=0;
$tottpii=0;
while ($row = pg_fetch_assoc($query)) {


  $TDepositAmount =  $TDepositAmount+$row['TD_SCHEME_AMOUNT'];
  $STotalPaidInterest =$STotalPaidInterest+ $row['TRAN_AMOUNT'];
  $TotalPayableInterest = $row['PAYABLE_INT_OPENING'] + $row['RECPAY_INT_AMOUNT'];
  $STotalPayableInterest += $TotalPayableInterest;
  $GSTotalPayableInterest = $STotalPayableInterest;

  $totcpi = $totcpi + $row['RECPAY_INT_AMOUNT'];
  $tottpi = $tottpi + $row['TRAN_AMOUNT'];
  // $totnb = $totnb + $row['LEDGER_BALANCE'];
  $totda = $totda + $row['LEDGER_BALANCE'];
  $tottpii = $tottpii + $row['TRAN_AMOUNT'];
  $totoppi = $totoppi + $row['PAYABLE_INT_OPENING'];
  $NetLedgerBalance = $row['TRAN_AMOUNT'] + abs($row['LEDGER_BALANCE']);
  $SNetLedgerBalance +=$NetLedgerBalance;

  $TNetLedgerBalance =$TNetLedgerBalance + $NetLedgerBalance;
  $tmp = [
    'Ac/No.' => $row['AC_NO'],
    'AccountName' => $row['AC_NAME'],
    'LastInterestDate' => $row['LAST_INTEREST_DATE'],
    'InterestRate' => $row['INTEREST_RATE'],
    'Months' => $row['MONTHS'],
    'Days' => $row['Days'],
    'DepositAmount' => sprintf("%.2f", (abs($row['LEDGER_BALANCE']) + 0.0)),
    'TotalPaidInterest' =>sprintf("%.2f",  $row['TRAN_AMOUNT']+ 0.0),
    'OldProvisionPayableInterest'  => sprintf("%.2f",$row['PAYABLE_INT_OPENING']+ 0.0),
    
    'PAYABLE_INT_OPENING' => sprintf("%.3f", ($row['PAYABLE_INT_OPENING'] + 0.0)),
    'CurrentPayableInterest'  =>sprintf("%.2f", ($row['RECPAY_INT_AMOUNT'] + 0.0)),
    'PAYABLE_INT_OPENING' => sprintf("%.3f", ($row['PAYABLE_INT_OPENING'] + 0.0)),
    'TotalPayableInterest'  => sprintf("%.2f", (abs($TotalPayableInterest))),
    'NetLedgerBalance' => sprintf("%.2f", (abs($NetLedgerBalance))),

    'LEDGER_BALANCE' => sprintf("%.3f", ($row['LEDGER_BALANCE'] + 0.0)),
    'Scheme' => $row['S_NAME'],

    "bankName"  => $bankName,
    "Branch" => $Branch_,
    "TNetLedgerBalance"=>sprintf("%.2f", (abs($TNetLedgerBalance))),
    "SNetLedgerBalance" =>sprintf("%.2f", (abs($SNetLedgerBalance))),
    "date" => $date1,
    "TDepositAmount" =>sprintf("%.2f", ($TDepositAmount + 0.0)),
    "STotalPayableInterest" =>sprintf("%.2f", ($TotalPayableInterest + 0.0)),
    "TTotalPayableInterest" =>sprintf("%.2f", ($STotalPayableInterest + 0.0)),
    "GSTotalPayableInterest" =>sprintf("%.2f", ($GSTotalPayableInterest + 0.0)),
    'totcpi' => sprintf("%.2f", ($totcpi + 0.0)),
    'tottpi' => sprintf("%.2f", ($tottpi + 0.0)),
    
    'totda' => sprintf("%.2f", abs($totda + 0.0)),
    'tottpii' => sprintf("%.2f", ($tottpii + 0.0)),
    'totoppi' => sprintf("%.2f", ($totoppi + 0.0)),
  ];
  //   if ($NetLedgerBalance < 0) { 
  //     $netType = 'Cr';
  // } else {
  //     $netType = 'Dr';
  // }

  if ($NetLedgerBalance < 0) {
    $tmp['NetLedgerBalance'] = $tmp['NetLedgerBalance'] . "     " . "Dr";
  } else if ($NetLedgerBalance > 0) {
    $tmp['NetLedgerBalance'] = $tmp['NetLedgerBalance'] . "     " . "Cr";
  }
  $data[$i] = $tmp;
  $i++;
}
// ob_end_clean();

// $config = ['driver' => 'array', 'data' => $data];

// $report = new PHPJasperXML();
// $report->load_xml_file($filename)
//   ->setDataSource($config)
//   ->export('Pdf');
