<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 0);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/balanceSheet.jrxml';
$branchName = $_GET['branchName'];
// $faker = Faker\Factory::create('en_US');
$bankName = $_GET['bankName'];
$date = $_GET['date'];
$branch_code = $_GET['branch_code'];

$url = 'http://' . $IPADDD . ':' . $port . '/reports/balanceSheet';
$data = ['date' => $date, 'branch_code' => $branch_code];
$data = json_encode($data);

$ch = curl_init();
$curlConfig = array(
    CURLOPT_URL            => $url,
    CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $data
);
curl_setopt_array($ch, $curlConfig);
$result = curl_exec($ch);
curl_close($ch);
$result1 = (json_decode($result));
$josnData = $result1;

@$size = count((array)$result1);
// Check if any error has occurred
$dataset = array();
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    $result2 = json_decode($result, TRUE);
    foreach ($result2 as $x => $val) {
        $lhead_name = '';
        $ahead_name = '';
        $lsub_head = '';
        $asub_head = '';
        $lledger = '';
        $aledger = '';
        $lheadtotal = '';
        $aheadtotal = '';
        $grandtotal = '';
        if ($val['Lposition'] == 'L'  && $val['LisHead'] == 1) {
            $lhead_name = '<b>' .'<span style="font-size: 8px">'. $val['Lhead_name'] . '</b>';
            $lledger = '';
        }
        if ($val['Aposition'] == 'A'  && $val['AisHead'] == 1) {
            $ahead_name = '<b>'.'<span style="font-size: 8px">'. $val['Ahead_name'] .'</span>'. '</b>';
            $aledger = '';
        }
        if ($val['Lposition'] == 'L'  && $val['LisHead'] == 1) {
            $lheadtotal = '<b>' .'<span style="font-size: 8px">'. $val['Lhead_total'] .'</span>'. '</b>';
        }
        if ($val['Aposition'] == 'A'  && $val['AisHead'] == 1) {
            $aheadtotal = '<b>' .'<span style="font-size: 8px">'. $val['Ahead_total'] .'</span>'. '</b>';
        }
        if ($val['Lposition'] == 'L' && $val['LisHead'] == 0) {
            $lhead_name = '<span style="font-size: 8px">' . $val['Lsub_head'] . '</span>';
            $lledger = $val['Lledger_balance'];
            $lheadtotal = '';
            $lsub_head = $val['Lsub_head'];
        }
        if ($val['Aposition'] == 'A' && $val['AisHead'] == 0) {
            $ahead_name = '<span style="font-size: 8px">'. $val['Asub_head'] . '</span>';
            $aledger = $val['Aledger_balance'];
            $aheadtotal = '';
            $asub_head =  $val['Asub_head'];
        }
        // if ($val['Lposition'] == 'L') {            print_r($val);
        // $Lgrandtotal = $val['Lgrand_total'];
        $Lgrandtotal =  isset($val['Lgrand_total']) ? $val['Lgrand_total'] : null;
        // } 
        //  if ($val['Aposition'] == 'A') {
        // $Agrandtotal = $val['Agrand_total'];
        $Agrandtotal =   isset($val['Agrand_total']) ? $val['Agrand_total'] : null;
        // }
        $tmp = [            // We can check the condition (isset) if their is value then show otherwise display null
            'lhead_name' => isset($val['Lhead_name']) ? $lhead_name : null,
            'ahead_name' => isset($val['Ahead_name']) ? $ahead_name : null,
            'lheadtotal' => isset($val['Lhead_total']) ? $lheadtotal : null,
            'aheadtotal' => isset($val['Ahead_total']) ? $aheadtotal : null,
            // 'lsub_head' => isset($val['Lsub_head']) ? $lsub_head : 'NA',
            // 'asub_head' => isset($val['Asub_head']) ? $asub_head : 'NA',
            'lledger' => isset($val['Lledger_balance']) ? $lledger : null,
            'aledger' => isset($val['Aledger_balance']) ? $aledger : null,
            'position' => isset($val['position']) ? $val['position'] : null,
            'isHead' => isset($val['isHead']) ? $val['isHead'] : null,
            'Lgrandtotal' => $Lgrandtotal,
            'Agrandtotal' => $Agrandtotal,
            'date' => $date,
            // 'aheadtotal' => sprintf("%.2f",((float)$aheadtotal + 0.0 ) ) ,
            'bankName' => $bankName,
            'branch_code' => $branch_code,
            'branchName' => $branchName,
        ];
        // $data[$i] = $tmp;
        array_push($dataset, $tmp);
    }

    $ver = [];
    $ver =  $dataset;
    // echo "<pre>";
    // print_r($ver);
    // echo "</pre>";
    ob_end_clean();
    $config = ['driver' => 'array', 'data' => $ver];
    $report = new PHPJasperXML();
    $report->load_xml_file($filename)
        ->setDataSource($config)
        ->export('Pdf');
}

// $data = json_decode($responseValue, true);

// $data = json_decode($data, true);
// print_r($data);
// $view = count($data);

curl_close($ch);
