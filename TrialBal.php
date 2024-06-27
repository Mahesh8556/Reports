<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/TrialBal.jrxml';
$data = [];
$faker = Faker\Factory::create('en_US');

$dateformate = "'DD/MM/YYYY'";
$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$startdate = $_GET['startdate'];
$endDate = $_GET['endDate'];
$branched = $_GET['branched'];
$c = "'C'";
$d = "'D'";
$ConAcName = "'CASH IN HAND'";

$startdate1 = str_replace("'", "", $startdate);
$endDate1 = str_replace("'", "", $endDate);


$schemeCode = "'980'";
$query = 'select "AC_NO","AC_NAME" ,cast((select ledgerbalance (' . $schemeCode . ', cast(ACMASTER."AC_NO" as character varying),' . $endDate . ',1, ' . $branched . ',0)) as float) 
as balance from acmaster  order by "AC_NO"';

echo $query;

$sql =  pg_query($conn, $query);
$i = 0;
$CREDIT_total = 0;
$DEBIT_total = 0;
$type = '';

if (pg_num_rows($sql) == 0) {
  include "errormsg.html";
} else {

  while ($row = pg_fetch_assoc($sql)) {
    if ($row['balance'] != 0) {
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
        'NAME' => $branchName,
        'CREDIT_total' =>  $CREDIT_total,
        'DEBIT_total' =>  $DEBIT_total,

        'bankName' => $bankName,
        'startDate' => $startdate1,
        'endDate' => $endDate1,
        'branched' => $branched,
      ];
      $data[$i] = $tmp;
      $i++;
    }
  }
  // echo     $DEBIT_total;
  // echo "\n";
  // echo $CREDIT_total;
  // print_r($data);

  // for clean previous execution
  // ob_end_clean();
  // // 
  // $config = ['driver' => 'array', 'data' => $data];
  // // for pdf conversion of report
  // $report = new PHPJasperXML();
  // $report->load_xml_file($filename)
  //   ->setDataSource($config)
  //   ->export('Pdf');
}
