<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// // ini_set('display_errors', '1');
// // ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 36000);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/nformprofitandloss1.jrxml';

$data = [];
// $faker = Faker\Factory::create('en_US');

$branch_code = $_GET['branched1'];
$prevdate = $_GET['stardate'];
$afterdate = $_GET['endate'];
$bankName = $_GET['bankName'];
$branchName = $_GET['branchName'];

// echo $branchName ;
// print_r($date);
// print_r($branch_code);
$branchName = str_replace("'", "", $branchName);
$bankName = str_replace("'", "", $bankName);
$prevdate = str_replace("'", "", $prevdate);
$afterdate = str_replace("'", "", $afterdate);
$branch_code = str_replace("'", "", $branch_code);


$myObj = new stdClass();
$myObj->afterdate = $afterdate;
$myObj->prevdate = $prevdate;
$myObj->branch_code = $branch_code;
$ch = curl_init();
$arr = array();
$date = $arr['date'] ;
$obj = (object)$myObj;



curl_setopt($ch, CURLOPT_URL, 'http://'. $IPADDD .':' . $port . '/reports/NprofitLoss');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);
// print_r($valueData);
// $view = count($valueData);
// $view = $valueData['view'];

// var_export($responseValue);
curl_close($ch);
$count = count($valueData);
// $lastIndex=$valueData[$count-1];
$grandtotal = $valueData[$count - 1]['grand_total'];

$expGrandTotal = 0;
$IncGrandTotal = 0;

for ($i = 0; $i <= count($valueData); $i++) {

    // print_r($valueData[$i]);

    $expGrandTotal = $expGrandTotal +  $valueData[$i]['Ehead_total'];
    $IncGrandTotal = $IncGrandTotal + $valueData[$i]['Ihead_total'];

    $ehead_name = '';
    $ihead_name = '';
    $esub_head = '';
    $isub_head = '';
    $eledger = '';
    $epledger = '';
    $iledger = '';
    $ipledger = '';
    $eheadtotal = '';
    $iheadtotal = '';
    $epheadtotal = '';
    $ipheadtotal = '';
    $enetprofit = '';
    $inetprofit = '';
    // $grandtotal = '';

    if (
        $valueData[$i]['Eposition'] == 'E'  &&
        $valueData[$i]['Ehead_total'] > '0'
    ) {
        $ehead_name = $valueData[$i]['Ehead_name'];
    }

    if (
        $valueData[$i]['Iposition'] == 'I'  &&
        $valueData[$i]['Ihead_total'] > '0'
    ) {
        $ihead_name = $valueData[$i]['Ihead_name'];
    }

    if ($valueData[$i]['Eposition'] == 'E' && $valueData[$i]['EisHead'] == '1') {
        $eheadtotal = $valueData[$i]['Ehead_total'];
        $epheadtotal = $valueData[$i]['EPhead_total'];
    }

    if ($valueData[$i]['Iposition'] == 'I' && $valueData[$i]['IisHead'] == '1') {
        $iheadtotal = $valueData[$i]['Ihead_total'];
        $ipheadtotal = $valueData[$i]['IPhead_total'];
    }

    if ($valueData[$i]['Eposition'] == 'E' && $valueData[$i]['EisHead'] == '0') {
        $esub_head = $valueData[$i]['Esub_head'];
    }
    // else {
    //     $esub_head = '<span style="display:none;">' . $valueData[$i]['Esub_head'] . '</span>';
    // }

    if ($valueData[$i]['Iposition'] == 'I' && $valueData[$i]['IisHead'] == '0') {
        $isub_head = $valueData[$i]['Isub_head'];
    }
    //  else {
    //     $isub_head = '<span style="display:none;">' . $valueData[$i]['Isub_head'] . '</span>';
    // }

    if ($valueData[$i]['Eposition'] == 'E') {
        $eledger = $valueData[$i]['Eledger_balance'];
        $epledger = $valueData[$i]['EPledger_balance'];
    }

    if ($valueData[$i]['Iposition'] == 'I') {
        $iledger = $valueData[$i]['Iledger_balance'];
        $ipledger = $valueData[$i]['IPledger_balance'];
    }

    if ($valueData[$i]['Ehead_name'] == 'Net Profit' && $valueData[$i]['Eposition'] == 'E') {
        $enetprofit = $valueData[$i]['Ehead_total'];
    }
    if ($valueData[$i]['Ihead_name'] == 'Net Loss' && $valueData[$i]['Iposition'] == 'I') {
        $inetprofit = $valueData[$i]['Ihead_total'];
    }
    // if ($valueData[$i]['position'] == 'E') {
    // $grandtotal = $valueData[$i]['grand_total'];
    // }



    $tmp = [
        // We can check the condition (isset) if their is value then show otherwise display null
        'ehead_name' => isset($valueData[$i]['Ehead_name']) ? $ehead_name : null,
        'ihead_name' => isset($valueData[$i]['Ihead_name']) ? $ihead_name : null,
        'eheadtotal' => isset($valueData[$i]['Ehead_total']) ? $eheadtotal : null,
        'epheadtotal' => isset($valueData[$i]['EPhead_total']) ? $epheadtotal : null,
        'iheadtotal' => isset($valueData[$i]['Ihead_total']) ? $iheadtotal : null,
        'ipheadtotal' => isset($valueData[$i]['IPhead_total']) ? $ipheadtotal : null,
        // 'esub_head' =>  $esub_head,
        'esub_head' => isset($valueData[$i]['Esub_head']) ? $esub_head : null,
        'isub_head' => isset($valueData[$i]['Isub_head']) ? $isub_head  : null,
        // 'isub_head' =>  $isub_head,
        'eledger' => isset($valueData[$i]['Eledger_balance']) ? $eledger : null,
        'iledger' => isset($valueData[$i]['Iledger_balance']) ? $iledger : null,
        'EPledger_balance' => isset($valueData[$i]['EPledger_balance']) ? $epledger : null,
        'IPledger_balance' => isset($valueData[$i]['IPledger_balance']) ? $ipledger : null,
        'position' =>   isset($valueData[$i]['position']) ? $valueData[$i]['position'] : null,
        'isHead' =>   isset($valueData[$i]['isHead']) ? $valueData[$i]['isHead'] : null,
        'grandtotal' => $grandtotal,
        'inetprofit' => $inetprofit,
        'enetprofit' => $enetprofit,
        // 'date' => $date,
        'bankName' => $bankName,
        'branch_code' => $branch_code,
        'Egrandtotal' => $Egrandtotal,
        'Igrandtotal' => $Igrandtotal,
        'branchname' => $branchName,
        'startDate' => $prevdate,
        'endDate' => $afterdate,
        'date1' => $endDate1,
        'expGrandTotal' => sprintf("%.2f", ($expGrandTotal + 0.0)),
        'IncGrandTotal' => sprintf("%.2f", ($IncGrandTotal + 0.0)),
    ];
    // if (isset($valueData[$i]['Esub_head'])) {
    //     $tmp['esub_head'] = $esub_head;
    // }
    // if (isset($valueData[$i]['Isub_head'])) {
    //     $tmp['isub_head'] = $isub_head;
    // }
    // if (isset($valueData[$i]['Eledger_balance']))
    //     $tmp['eledger'] = $eledger;
    // if (isset($valueData[$i]['Iledger_balance']))
    //     $tmp['iledger'] = $iledger;
    $data[$i] = $tmp;
}

//  print_r($data);
//  echo '<pre>';
//  print_r($data);
//  echo '</pre>';
ob_end_clean();
$config = ['driver' => 'array', 'data' => $data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');

