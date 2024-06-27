<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 0);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/account_statement.jrxml';

$toDate = $_GET['todate'];
$fromDate = $_GET['fromdate'];
$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$fromacc = $_GET['fromacc'];
$acno = $_GET['AC_ACNOTYPE'];
$name = $_GET['name'];

$toDate1 = str_replace("'", "", $toDate);
$fromDate1 = str_replace("'", "", $fromDate);
$bankName1 = str_replace("'", "", $bankName);
$branchName1 = str_replace("'", "", $branchName);
$acno1 = str_replace("'", "", $acno);
$name1 = str_replace("'", "", $name);

$data = [];
$myObj = new stdClass();
$myObj->fromDate = $fromDate;
$myObj->toDate = $toDate;
$myObj->tranacnotype = $acno1;
$myObj->BANKACNO = $fromacc;
$ch = curl_init();
$obj = (object)$myObj;
curl_setopt($ch, CURLOPT_URL, 'http://' . $IPADDD . ':' . $port .'/customer-app/ledgerView');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);

// print_r($valueData);

// $query = 'SELECT "BANK_NAME" FROM syspara';
// $BAcno = substr($BankAcNo,3,3);

// $query1 = 'select ac_no,ac_name,"NAME" AS "BRANCHNAME",SCHEMAST."S_NAME" from vwallmaster
// left join ownbranchmaster on ownbranchmaster.id=vwallmaster.branch_code 
// LEFT JOIN SCHEMAST ON SCHEMAST.ID=VWALLMASTER.AC_TYPE where ac_no=cast('.$BankAcNo.' as character varying)';

// $sql =  pg_query($conn,$query);
// while($row = pg_fetch_assoc($sql)){
//     $bankName = $row['BANK_NAME'];
// }

// $sql1 =  pg_query($conn,$query1);
$closetotal = 0;
$crtotal = 0;
$drtotal = 0;
$cramt = 0;
$dramt = 0;
for($i=0;$i<count($valueData);$i++){
  $cramt = ($valueData[$i]['TRAN_DRCR']=='C') ? $valueData[$i]['TRAN_AMOUNT'] : 0.00;
  $dramt = ($valueData[$i]['TRAN_DRCR']=='D') ? $valueData[$i]['TRAN_AMOUNT'] : 0.00;
  $closetotal = $closetotal + $valueData[$i]['closeBalance'];
  $crtotal = $crtotal + $cramt;
  $drtotal = $drtotal +  $dramt;
        $tmp = [       
            'bankName' => $bankName1,
            'account' => $fromacc,
            'branchName' => $branchName1,
            'startDate' => $fromDate1,
            'endDate' => $toDate1,
            'name' => $name1,
            'chequeNo' => isset($valueData[$i]['CHEQUE_NO']) ? $valueData[$i]['CHEQUE_NO'] : null,
            'type' => isset($valueData[$i]['TRAN_TYPE']) ? $valueData[$i]['TRAN_TYPE'] : null,
            'narration' => isset($valueData[$i]['NARRATION']) ? $valueData[$i]['NARRATION'] : null,
            'amount' => isset($valueData[$i]['TRAN_AMOUNT']) ? $valueData[$i]['TRAN_AMOUNT'] : null,
            'closeBalance' => isset($valueData[$i]['closeBalance']) ? $valueData[$i]['closeBalance'] : null,
            'date' => isset($valueData[$i]['TRAN_DATE']) ? $valueData[$i]['TRAN_DATE'] : null,
            'acno' => isset($valueData[$i]['TRAN_ACNO']) ? $valueData[$i]['TRAN_ACNO'] : null,    
            'cramount' => ($valueData[$i]['TRAN_DRCR']=='C') ? $valueData[$i]['TRAN_AMOUNT'] : 0.00,
            'dramount' => ($valueData[$i]['TRAN_DRCR']=='D') ? $valueData[$i]['TRAN_AMOUNT'] : 0.00,
            'closetotal' => sprintf("%.2f",($closetotal + 0.0 ) ),
            'crtotal' => sprintf("%.2f",($crtotal + 0.0 ) ),
            'drtotal' => sprintf("%.2f",($drtotal + 0.0 ) ),
        ];
        $data[$i]=$tmp;
}

print_r($data);

    $ver = [];
    $ver =  $data;
    // echo "<pre>";
    // print_r($ver);
    // echo "</pre>";
    ob_end_clean();
    $config = ['driver' => 'array', 'data' => $ver];
    $report = new PHPJasperXML();
    $report->load_xml_file($filename)
        ->setDataSource($config)
        ->export('Pdf');


// $data = json_decode($responseValue, true);

// $data = json_decode($data, true);
// print_r($data);
// $view = count($data);

curl_close($ch);
