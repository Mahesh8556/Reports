<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/LoanInterestList1.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');
// $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
// $bankName  = $_GET['bankName'];
$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$date = $_GET['date'];
$ac1 = $_GET['schemecode'];

// $bankName = str_replace("'", "", $bankName);
//  $stdate_ = str_replace(''', '', $stdate);
$date1 = str_replace("'", "", $date);
$Branch = str_replace("'", "", $Branch);

// $ac1 = "'2'";
$dd = "'DD/MM/YYYY'";



$query = 'SELECT lnmaster."BANKACNO", lnmaster."AC_NO", lnmaster."AC_NAME",interesttran."MONTHS",
interesttran."LAST_INTEREST_DATE",interesttran."INTEREST_RATE",
interesttran."DAYS",interesttran."LEDGER_BALANCE",interesttran."TD_SCHEME_AMOUNT",
interesttran."TRAN_AMOUNT",
interesttran."RECPAY_INT_AMOUNT",interesttran."PENAL_INT_AMOUNT",
interesttran."RECEIVABLE_INT_OPENING",interesttran."RECPENAL_INT_OPENING",
interesttran."ODUE_INT_OPENING",
interesttran."ODUE_INT_AMOUNT",
interesttran."TRAN_STATUS",schemast."S_NAME",
interesttran."BRANCH_CODE"
From OWNBRANCHMASTER, lnmaster
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
CAST(lnmaster."BANKACNO" as bigint) = cast(INTERESTTRAN."TRAN_ACNO" as bigint)
inner join SCHEMAST ON
CAST(lnmaster."AC_TYPE" AS integer) = SCHEMAST."id"
WHERE CAST(INTERESTTRAN."TRAN_DATE" AS date) = To_date(' . $date . ',' . $dd . ') 
AND INTERESTTRAN."TRAN_ACTYPE"=' . $ac1 . '
AND OWNBRANCHMASTER.ID = INTERESTTRAN."BRANCH_CODE"
AND INTERESTTRAN."BRANCH_CODE"=' . $branch_code . ' order by lnmaster."BANKACNO"';
// echo $query;
$i = 0;
$totlb = 0;
$totpi = 0;
$totold = 0;
$totc = 0;
$tot = 0;
$totnlb = 0;

$Total=0;
$Net_Ledger_Balance=0;
$STNet_Ledger_Balance=0;
$GTNet_Ledger_Balance=0;
$ODUE_Total=0;
$SODUE_Total =0;
$GODUE_Total=0;
$SINTRESTAMOUNT=0;
$Old=0;
$TOld=0;
$Current=0;
$TCurrent=0;
$TODUE_INT_AMOUNT=0;
$TODUE_INT_OPENING=0;
$GTODUE_INT_OPENING=0;
$GTODUE_INT_AMOUNT=0;
//    $GRAND_TOTAL =0;


$query1 = pg_query($conn, $query);
while ($row = pg_fetch_assoc($query1)) {
  // $GRAND_TOTAL = $GRAND_TOTAL + $row["Short Term"];

  $Net_Ledger_Balance = $row['TRAN_AMOUNT'] +abs($row['LEDGER_BALANCE']);
  $STNet_Ledger_Balance =$STNet_Ledger_Balance+$Net_Ledger_Balance;
  $GTNet_Ledger_Balance =$STNet_Ledger_Balance;
  $ODUE_Total = $row['ODUE_INT_AMOUNT'] + $row['ODUE_INT_OPENING'];
  $SODUE_Total  += $ODUE_Total ;
  $GODUE_Total  = $SODUE_Total ;
  $SINTRESTAMOUNT=  $SINTRESTAMOUNT+$row['TRAN_AMOUNT'];
  $Old =  $Old+ $row['ODUE_INT_OPENING'];
  $TOld= $Old;
  $Current =$Current+ $row['ODUE_INT_AMOUNT'];
  $TCurrent =$Current;
  $TODUE_INT_AMOUNT=$TODUE_INT_AMOUNT+$row['ODUE_INT_AMOUNT'];
  $TODUE_INT_OPENING=$TODUE_INT_OPENING+$row['ODUE_INT_OPENING'];
  $GTODUE_INT_OPENING = $TODUE_INT_OPENING;
  $GTODUE_INT_AMOUNT=$TODUE_INT_AMOUNT;
  $Total=$row['ODUE_INT_AMOUNT'] + $row['ODUE_INT_OPENING'];
  
  $tot = $Total;
  $totlb = $totlb + $row['LEDGER_BALANCE'];
  $totpi = $totpi + $row['PENAL_INT_AMOUNT'];
  $totold = $totold + $row['ODUE_INT_OPENING'];
  $totc = $totc + $row['ODUE_INT_AMOUNT'];
  $tot = $tot + $row['Total'];
  $totnlb = $totnlb + $row['LEDGER_BALANCE'];



  $tmp = [
    'Date' => $row['Date'],
    // 'srno.' => $row['SR_NO'],
    'A/c No.' => $row['AC_NO'],
    'AccountName' => $row['AC_NAME'],
    'Last Interest Date' => $row['LAST_INTEREST_DATE'],

    'Interest Rate' => $row['INTEREST_RATE'],
    ' InterestAmount' =>sprintf("%.2f", ( $row['TRAN_AMOUNT'] + 0.0)),
    'Months' => $row['MONTHS'],
    'Days' => $row['DAYS'],
    'Ledger Balance' =>sprintf("%.2f", ( $row['LEDGER_BALANCE'] + 0.0)),
    // 'LEDGER_BALANCE' => sprintf("%.2f", ($row['LEDGER_BALANCE'] + 0.0)),
  
    'penal Interest' => sprintf("%.2f", ($row['PENAL_INT_AMOUNT'] + 0.0)),
    'Old' =>sprintf("%.2f", ($row['ODUE_INT_OPENING'] + 0.0)),
    'ODUE_INT_OPENING' => sprintf("%.2f", ($row['ODUE_INT_OPENING'] + 0.0)),
    'Current' =>sprintf("%.2f", ($row['ODUE_INT_AMOUNT'] + 0.0)),
    'ODUE_INT_AMOUNT' => sprintf("%.2f", ($row['ODUE_INT_AMOUNT'] + 0.0)),
    'Total' =>sprintf("%.2f", ($row['TRAN_AMOUNT'] + 0.0)),
    'Total' => sprintf("%.2f", ($Total + 0.0)),
    'TRAN_AMOUNT' => sprintf("%.2f", ($row['TRAN_AMOUNT'] + 0.0)),
    'Net_Ledger_Balance' => sprintf("%.2f", ($Net_Ledger_Balance + 0.0)),
    "STNet_Ledger_Balance"=>$STNet_Ledger_Balance,
    "GTNet_Ledger_Balance"=>sprintf("%.2f", ($GTNet_Ledger_Balance + 0.0)),
    "ODUE_Total"=>sprintf("%.2f", ($ODUE_Total + 0.0)),
    "SODUE_Total"=>$SODUE_Total,
    "GODUE_Total"=>sprintf("%.2f", ($SGODUE_Total + 0.0)),
    "SINTRESTAMOUNT"=>sprintf("%.2f", ($SINTRESTAMOUNT + 0.0)),
    "SOld" => $Old,
    "TOld" =>sprintf("%.2f", ($TOld + 0.0)),
    "SCurrent"=> $Current,
    "TCurrent"=>sprintf("%.2f", ($TCurrent + 0.0)),
    "TODUE_INT_AMOUNT"=>$TODUE_INT_AMOUNT,
    "TODUE_INT_OPENING"=>$TODUE_INT_OPENING,
    "GTODUE_INT_OPENING"=>sprintf("%.2f", ($GTODUE_INT_OPENING + 0.0)),
    "GTODUE_INT_AMOUNT"=>sprintf("%.2f", ($GTODUE_INT_AMOUNT + 0.0)),
    'LEDGER_BALANCE' => sprintf("%.2f", ($row['LEDGER_BALANCE'] + 0.0)),

    'Scheme' => $row['S_NAME'],

    "bankName"  => $bankName,
    "Branch" => $Branch,
    "branch_code" => $branch_code,
    'totlb' => sprintf("%.2f", ($totlb + 0.0)),
    'totpi' => sprintf("%.2f", ($totpi + 0.0)),
    'totold' => sprintf("%.2f", ($totold + 0.0)),
    'totc' => sprintf("%.2f", ($totc + 0.0)),
    'tot' => sprintf("%.2f", ($tot + 0.0)),
    'totnlb' => sprintf("%.2f", ($totnlb + 0.0)),
    "date" => $date1,
  ];
  if ($row['TRAN_AMOUNT'] + $row['LEDGER_BALANCE'] < 0) {
    $tmp['Net Ledger Balance'] = $tmp['Net Ledger Balance'] . "     " . "Cr";
  } else if ($row['TRAN_AMOUNT'] + $row['LEDGER_BALANCE'] > 0) {
    $tmp['Net Ledger Balance'] = $tmp['Net Ledger Balance'] . "     " . "Dr";
  }

  $data[$i] = $tmp;
  $i++;
}
ob_end_clean();

// print_r($data);
// echo $query;
$config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)
  ->setDataSource($config)
  ->export('Pdf');
