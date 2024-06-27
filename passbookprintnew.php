<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

// $filename1 = __DIR__ . '/passbookSaving.jrxml';
// $filename2 = __DIR__ . '/passbookLoan1.jrxml';
$filename1 = __DIR__ . '/passbookSavingVitthal.jrxml';
$filename2 = __DIR__ . '/passbookLoan1Vitthal.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

$AC_NO = $_GET['AC_NO'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$flag = $_GET['flag'];
//   $PS_LINES_PRINTED = $_GET['PS_LINES_PRINTED'];

$AC_NO1 = str_replace("'", "", $AC_NO);
$AC_ACNOTYPE1 = str_replace("'", "", $AC_ACNOTYPE);
$AC_TYPE1 = str_replace("'", "", $AC_TYPE);

$myObj = new stdClass();
$myObj->AC_NO = $AC_NO1;
$myObj->AC_ACNOTYPE = $AC_ACNOTYPE1;
$myObj->AC_TYPE = $AC_TYPE1;
$ch = curl_init();
$obj = (object)$myObj;
curl_setopt($ch, CURLOPT_URL, 'http://' . $IPADDD . ':' . $port . '/voucher/passbookprint');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($obj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);
curl_close($ch);

// print_r($valueData);

// $Cheque_No = 0;

// if ($Cheque_No === 0) {
//     $Cheque_No = null;
// }

$i = 0;

$tdata1 = 0;


// echo $valueData[$i]['CR_AMOUNT'] .'<br>' ;
// echo $valueData[$i]['DR_AMOUNT'] .'<br>';
// echo $tdata1 ;
// else
// {

// }


// echo $valueData[$i]['OP_BALANCE'] .'<br>';



if (count($valueData) == 0) {
    ob_end_clean();
    include "errormsg.html";
} else {
    if ($flag == 1) {
      
        for ($i = 0; $i < count($valueData); $i++) {
if($i==0){
    $tdata1= $valueData[$i]['OP_BALANCE'];
}
// echo $tdata1 .' Before +- <br>';
            if ($valueData[$i]['OP_BALANCE'] > 0) {
                $tdata1 = $tdata1 - floatval($valueData[$i]['DR_AMOUNT']) + floatval($valueData[$i]['CR_AMOUNT']);
            } else {
                $tdata1 = $tdata1 + floatval($valueData[$i]['DR_AMOUNT']) - floatval($valueData[$i]['CR_AMOUNT']);
            }
            // echo $tdata1;

            if ($tdata1 < 0) {
                $CRDR = "CR";
            } else {
                $CRDR = "DR";
            }

            $tmp = [
                "TRAN_DATE"    => isset($valueData[$i]["TRAN_DATE"]) ? $valueData[$i]["TRAN_DATE"] : '',
                "NARRATION"    => isset($valueData[$i]["NARRATION"]) ? $valueData[$i]["NARRATION"] : '',
                "OP_BALANCE"   => isset($valueData[$i]['OP_BALANCE']) ? sprintf("%.2f", $valueData[$i]['OP_BALANCE']) : 0.00,
                "CR_AMOUNT"    => isset($valueData[$i]['CR_AMOUNT']) ? sprintf("%.2f", $valueData[$i]['CR_AMOUNT']) : null,
                "DR_AMOUNT"    => isset($valueData[$i]['DR_AMOUNT']) ? sprintf("%.2f", $valueData[$i]['DR_AMOUNT']) : null,
                "OTHER_AMOUNT" => isset($valueData[$i]['OTHER_AMOUNT']) ? sprintf("%.2f", $valueData[$i]['OTHER_AMOUNT']) : 0.00,
                "Cheque_No" => isset($valueData[$i]['Cheque_No']) ? sprintf($valueData[$i]['Cheque_No']) : null,
                // "CLOSING_BAL"   => isset($valueData[$i]['CLOSING_BAL']) ? sprintf("%.2f", $valueData[$i]['CLOSING_BAL']) : 0.00,
                "CLOSING_BAL"   => $valueData[$i]["NARRATION"] == 'Bal. C/F' ? sprintf("%.2f", abs($tdata1)) . ' ' . $CRDR : ($valueData[$i]['DR_AMOUNT'] == 0  && $valueData[$i]['CR_AMOUNT']  == 0 ? null : sprintf("%.2f", abs($tdata1)) . ' ' . $CRDR)
                // "CRDR"   => $CRDR,



            ];
            $data[$i] = $tmp;
        }

        // echo $tdata1.' After +- <br>';

    } else if ($flag == 2) {
        for ($i = 0; $i < count($valueData); $i++) {
            if($i==0){
                $tdata1= $valueData[$i]['OP_BALANCE'];
            }
            if ($valueData[$i]['OP_BALANCE'] > 0) {
                $tdata1 = $tdata1 + floatval($valueData[$i]['DR_AMOUNT']) - floatval($valueData[$i]['CR_AMOUNT']);
            } else {
                $tdata1 = $tdata1 - floatval($valueData[$i]['DR_AMOUNT']) + floatval($valueData[$i]['CR_AMOUNT']);
            }

            if ($tdata1 > 0) {
                $CRDR = "DR";
            } else {
                $CRDR = "CR";
            }


            $tmp = [
                "TRAN_DATE" => $valueData[$i]["TRAN_DATE"],
                "NARRATION" => $valueData[$i]["NARRATION"],
                "OP_BALANCE" => sprintf("%.2f", (($valueData[$i]['OP_BALANCE']))),
                "CR_AMOUNT"    => isset($valueData[$i]['CR_AMOUNT']) ? sprintf("%.2f", $valueData[$i]['CR_AMOUNT']) : null,
                "DR_AMOUNT"    => isset($valueData[$i]['DR_AMOUNT']) ? sprintf("%.2f", $valueData[$i]['DR_AMOUNT']) : null,
                "OTHER_AMOUNT" => sprintf("%.2f", (($valueData[$i]['OTHER_AMOUNT']))),
                "PENAL_AMOUNT" => sprintf("%.2f", (($valueData[$i]['PENAL_INTEREST']))),
                "Cheque_No" => sprintf((($valueData[$i]['Cheque_No']))),
                "CLOSING_BAL"   => $valueData[$i]["NARRATION"] == 'Bal. C/F' ? sprintf("%.2f", abs($tdata1)) . ' ' . $CRDR : ($valueData[$i]['DR_AMOUNT'] == 0  && $valueData[$i]['CR_AMOUNT']  == 0 ? null : sprintf("%.2f", abs($tdata1)) . ' ' . $CRDR)
                // "CRDR"   => $CRDR,
            ];
            $data[$i] = $tmp;
        }
    }
    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";
    ob_end_clean();
    $config = ['driver' => 'array', 'data' => $data];
    $report = new PHPJasperXML();
    if ($flag  == 1) {
        $report->load_xml_file($filename1)
            ->setDataSource($config)
            ->export('Pdf');
    } else if ($flag == 2) {
        $report->load_xml_file($filename2)
            ->setDataSource($config)
            ->export('Pdf');
    }
}
