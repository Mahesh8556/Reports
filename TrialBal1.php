<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/TrialBal1.jrxml';
$data = [];
$faker = Faker\Factory::create('en_US');

$dateformate = "'DD/MM/YYYY'";
// $branchName = $_GET['branched'];
$bankName = $_GET['bankName'];
$startdate1 = $_GET['startdate'];
$endDate1 = $_GET['endDate'];
$branched = $_GET['branched'];
$c = "'C'";
$d = "'D'";
$ConAcName = "'CASH IN HAND'";
$dateformate="'DD/MM/YYYY'";

$trans="'1'";


$branch = str_replace("'", "" , $branched );
$bankName = str_replace("'", "" , $bankName);
// $startdate1 = str_replace("'","", $startdate);
// $endDate1 = str_replace("'","", $endDate);

$schemeCode = "'980'";


$query ='SELECT ACMASTER."AC_NO", ACMASTER."AC_TYPE", ACMASTER."AC_NAME",ACMASTER."BRANCH_CODE",
COALESCE((COALESCE(ACCOTRAN."DR_TRANAMOUNT",0) + COALESCE(DAILYTRAN."DR_DAILYAMOUNT",0)),0) DR_AMOUNT  ,
COALESCE((COALESCE(ACCOTRAN."CR_TRANAMOUNT",0) + COALESCE(DAILYTRAN."CR_DAILYAMOUNT",0)),0) CR_AMOUNT 
 FROM ACMASTER
LEFT OUTER JOIN (SELECT TRAN_GLACTYPE, "TRAN_GLACNO" 
,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$d.' THEN "TRAN_AMOUNT" ELSE 0 END),0) "DR_DAILYAMOUNT" 
,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$c.' THEN "TRAN_AMOUNT" ELSE 0 END),0) "CR_DAILYAMOUNT" 
FROM VWDETAILDAILYTRAN WHERE CAST("TRAN_GLACNO" AS CHARACTER VARYING) <> (SELECT "CASH_IN_HAND_ACNO" FROM SYSPARA) 
AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$startdate1.','.$dateformate.') 
				  AND CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$endDate1.','.$dateformate.')
				 AND "TRAN_STATUS" = '.$trans.' 
GROUP BY TRAN_GLACTYPE,"TRAN_GLACNO"  ) DAILYTRAN ON ACMASTER."AC_TYPE" = DAILYTRAN.TRAN_GLACTYPE
 AND ACMASTER."AC_NO" = DAILYTRAN."TRAN_GLACNO" 
 LEFT OUTER JOIN ( SELECT "TRAN_ACTYPE", "TRAN_ACNO" ,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$d.' THEN "TRAN_AMOUNT" ELSE 0 END),0) "DR_TRANAMOUNT"  
,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$c.' THEN "TRAN_AMOUNT" ELSE 0 END),0) "CR_TRANAMOUNT"  
FROM ACCOTRAN WHERE CAST("TRAN_ACNO" AS CHARACTER VARYING) <> (SELECT "CASH_IN_HAND_ACNO" FROM SYSPARA)  
AND CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$startdate1.','.$dateformate.')
				  AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$endDate1.','.$dateformate.')
				  AND NOT ( 
					  CAST("TRAN_DATE" AS DATE) = TO_DATE('.$endDate1.', '.$dateformate.') 
						   AND 
				 COALESCE("CLOSING_ENTRY",0) >= 0 )  
GROUP BY "TRAN_ACTYPE","TRAN_ACNO" ) ACCOTRAN ON ACMASTER."AC_TYPE" = ACCOTRAN."TRAN_ACTYPE"
AND ACMASTER."AC_NO" = ACCOTRAN."TRAN_ACNO"  
WHERE
ACMASTER."BRANCH_CODE"='.$branched.'';

// echo $query;

$sql =  pg_query($conn, $query);
$i = 0;
$CREDIT_total = 0;
$DEBIT_total = 0;
$type = '';

if (pg_num_rows($sql) == 0) {
  include "errormsg.html";
} else {

  while ($row = pg_fetch_assoc($sql)) {
    // if ($row['balance'] != 0) {
      $row['balance'] < 0 ?  $row['cramt'] = abs($row['balance']) :  $row['cramt'] = null;
      $row['balance'] > 0 ? $row['dramt'] = $row['balance'] : $row['dramt'] = null;
      // grand-total
      $CREDIT_total = $CREDIT_total + ($row['cramt']);
      $DEBIT_total = $DEBIT_total +  $row['dramt'];

      $tmp = [
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'cramt' => ($row['cramt']),
        'dramt' => $row['dramt'],
        'NAME' => $branch,
        'CREDIT_total' =>  $CREDIT_total,
        'DEBIT_total' =>  $DEBIT_total,

        'bankName' =>$bankName,
        'startDate' => $startdate,
        'endDate' => $endDate,
        'NAME' => $branch,
      ];
      $data[$i] = $tmp;
      $i++;
    // }
  }
  // echo     $DEBIT_total;
  // echo "\n";
  // echo $CREDIT_total;
  // print_r($data);

  // for clean previous execution
  ob_end_clean();
  // 
  $config = ['driver' => 'array', 'data' => $data];
  // for pdf conversion of report
  $report = new PHPJasperXML();
  $report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');
}
