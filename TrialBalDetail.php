<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/TrialBalDetail.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$sdate = $_GET['sdate'];
// $sdate = "'02/06/2022'";
$startdate = $_GET['startdate'];
$preViousStartdate = $_GET['preViousStartdate'];
$endDate = $_GET['endDate'];
$branched = $_GET['branched'];
$branchCode = $_GET['branchCode'];
$branchName = $_GET['branchName'];
$tran = $_GET['tran'];
// variable

    //string replacements
    $bankname1 = str_replace("'", "", $bankName);
    $startdate1 = str_replace("'", "", $sdate);
    $endDate1 = str_replace("'", "", $endDate);
$date_ = str_replace("'", "", $sdate);
$schemeCode = "'980'";
$c = "'C'";
$d = "'D'";


$query =   'select "AC_NO","AC_NAME", (select ledgerbalance (' . $schemeCode . ', cast(ACMASTER."AC_NO" as character varying),' . $startdate . ',1,' . $branchCode . ',0) ) as "opening_BALANCE", (select SUM(CAST(ACCOTRAN."TRAN_AMOUNT" as FLOAT)) from ACCOTRAN where ACCOTRAN."TRAN_DRCR" = ' . $c . ' and ACCOTRAN."TRAN_ACNO"=ACMASTER."AC_NO" and CAST(ACCOTRAN."TRAN_DATE" AS DATE) BETWEEN CAST(' . $startdate . ' AS DATE) AND CAST(' . $endDate . ' as date) AND ACCOTRAN."BRANCH_CODE" =' . $branched . ') as creditBal,(select SUM(CAST(ACCOTRAN."TRAN_AMOUNT" as FLOAT)) from ACCOTRAN where ACCOTRAN."TRAN_DRCR" = ' . $d . ' and ACCOTRAN."TRAN_ACNO"=ACMASTER."AC_NO" and CAST(ACCOTRAN."TRAN_DATE" AS DATE) BETWEEN CAST(' . $startdate . ' AS DATE) AND CAST(' . $endDate . ' as date) AND ACCOTRAN."BRANCH_CODE" =' . $branched . ')as debitBal, (select SUM(CAST(DAILYTRAN."TRAN_AMOUNT" as FLOAT)) from DAILYTRAN where DAILYTRAN."TRAN_DRCR" = ' . $c . ' and cast(DAILYTRAN."TRAN_ACNO" as bigint)=ACMASTER."AC_NO" and CAST(DAILYTRAN."TRAN_DATE" AS DATE) BETWEEN CAST(' . $startdate . ' AS DATE) AND CAST(' . $endDate . ' as date) AND DAILYTRAN."BRANCH_CODE" =' . $branched . ') as creditDAILYTRANBal,(select SUM(CAST(DAILYTRAN."TRAN_AMOUNT" as FLOAT)) from DAILYTRAN where DAILYTRAN."TRAN_DRCR" = ' . $d . ' and cast(DAILYTRAN."TRAN_ACNO" as bigint)=ACMASTER."AC_NO" and CAST(DAILYTRAN."TRAN_DATE" AS DATE) BETWEEN CAST(' . $startdate . ' AS DATE) AND CAST(' . $endDate . ' as date) AND DAILYTRAN."BRANCH_CODE" =' . $branched . ')as debitDAILYTRANBal, (select ledgerbalance (' . $schemeCode . ', cast(ACMASTER."AC_NO" as character varying),' . $endDate . ',1,' . $branchCode . ',0) ) as "CLOSING_BALANCE" FROM ACMASTER ORDER BY "AC_NO"';



// echo $query;

$sql =  pg_query($conn, $query);
$i = 0;
$type = '';
$TRAN_DEBIT_TOTAL = 0;
$TRAN_CREDIT_TOTAL = 0;
$OPBAL_CREDIT_TOTAL = 0;
$OPBAL_DEBIT_TOTAL = 0;
$NET_CREDIT_TOTAL = 0;
$NET_DEBIT_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
} else {

    while ($row = pg_fetch_assoc($sql)) {
        // if ($row['CLOSING_BALANCE'] != 0 &&  $row['opening_BALANCE'] != 0 && ($row['creditbal'] + $row['creditdailytranbal']) != 0 &&  ($row['debitbal'] + $row['debitdailytranbal']) != 0) {

            $netbalanced = '';
            $netbalancec = '';
            if ($row['CLOSING_BALANCE'] > 0) {
                $netbalanced = $row['CLOSING_BALANCE'];
                $NET_DEBIT_TOTAL = $NET_DEBIT_TOTAL + abs($row['CLOSING_BALANCE']);
            }
            if ($row['CLOSING_BALANCE'] < 0) {
                $netbalancec = abs($row['CLOSING_BALANCE']);
                $NET_CREDIT_TOTAL = $NET_CREDIT_TOTAL +  abs($row['CLOSING_BALANCE']);
            }

            $ledgerbalD = '';
            $ledgerbalC = '';
            if ($row['opening_BALANCE'] > 0) {
                $ledgerbalD = $row['opening_BALANCE'];
                $OPBAL_DEBIT_TOTAL = $OPBAL_DEBIT_TOTAL +  $row['opening_BALANCE'];
            }
            if ($row['opening_BALANCE'] < 0) {
                $ledgerbalC = abs($row['opening_BALANCE']);
                $OPBAL_CREDIT_TOTAL = $OPBAL_CREDIT_TOTAL + abs($row['opening_BALANCE']);
            }
            if (($row['creditbal'] + $row['creditdailytranbal']) != 0) {
                $trancramt = $row['creditbal'] + $row['creditdailytranbal'];
                $TRAN_CREDIT_TOTAL = $TRAN_CREDIT_TOTAL + $trancramt;
            }
            if (($row['debitbal'] + $row['debitdailytranbal']) != 0) {
                $trandramt = $row['debitbal'] + $row['debitdailytranbal'];
                $TRAN_DEBIT_TOTAL = $TRAN_DEBIT_TOTAL + $trandramt;
            }

            $tmp = [
                'AC_NO' => $row['AC_NO'],
                'AC_NAME' => $row['AC_NAME'],
                'ledgerbal' =>   isset($row['ledgerbal']) ? $row['ledgerbal'] : null,
                'dledgerbal' => $ledgerbalD,
                // 'cledgerbal' => sprintf("%.2f",(+ 0.0)),
                'cledgerbal' =>  sprintf("%.2f",((float)($ledgerbalC) + 0.0)),
                'NET_CREDIT_TOTAL' => sprintf("%.2f",($NET_CREDIT_TOTAL + 0.0)),
                'NAME' => $branchName,
                'trancramt' => sprintf("%.2f",($trancramt + 0.0)),
                'trandramt' => sprintf("%.2f",($trandramt + 0.0)),
                // 'OPBAL_CREDIT' =>  $row['OPBAL_CREDIT'],
                // 'OPBAL_DEBIT' =>  $row['OPBAL_DEBIT'],
                // 'TRAN_CREDIT' =>  $row['TRAN_CREDIT'],
                // 'TRAN_DEBIT' =>  $row['TRAN_DEBIT'],
                'NET_CREDIT' => sprintf("%.2f",((float)($netbalancec) + 0.0)),
                'NET_DEBIT' =>  $netbalanced,
                'OPBAL_CREDIT_TOTAL' =>  $OPBAL_CREDIT_TOTAL,
                'OPBAL_DEBIT_TOTAL' =>  $OPBAL_DEBIT_TOTAL,
                'TRAN_CREDIT_TOTAL' =>  $TRAN_CREDIT_TOTAL,
                'TRAN_DEBIT_TOTAL' =>  $TRAN_DEBIT_TOTAL,
                'NET_DEBIT_TOTAL' =>  $NET_DEBIT_TOTAL,
                'bankName' => $bankname1,
                'sdate' => $startdate1,
                'date_' => $date_,
                'endDate' => $endDate1,
                'startdate' =>  $startdate1,
                'branched' => $branchName,
                'tran' => $row['tran'],
            ];
            $data[$i] = $tmp;
            $i++;
        // }
    }





    // // for clean previous execution
    // ob_end_clean();

    // // echo "<pre>";
    // // print_r($data);
    // // echo "</pre>";
    // $config = ['driver' => 'array', 'data' => $data];
    // $report = new PHPJasperXML();
    // $report->load_xml_file($filename)
    //     ->setDataSource($config)
    //     ->export('Pdf');
}
