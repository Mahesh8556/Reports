<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
//error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/ProfitLoss.jrxml';

$data = [];
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$date = $_GET['date'];
$branch_code = $_GET['branchCode'];
$branchName = $_GET['branchName'];
// print_r($date);
// print_r($branch_code);

$bankName1 = str_replace("'" , "" , $bankName);

$myObj = new stdClass();
$myObj->date = $date;
$myObj->branch_code = $branch_code;
$ch = curl_init();
$arr = array();
$arr['date'] = $date;
$obj = (object)$myObj;

curl_setopt($ch, CURLOPT_URL, 'http://' . $IPADDD . ':' . $port .'/reports/profitLoss');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);
// print_r($valueData);



curl_close($ch);
$count = count($valueData);

$filteredItems = array_filter($valueData, function ($item) {
    return $item['Egrandtotal'] > 0;
});
// print_r($filteredItems);
foreach($filteredItems as $key => $item){
  
    $Egrandtotal = $item['Egrandtotal'];
    $Igrandtotal = $item['Igrandtotal'];
    }


echo $Igrandtotal;

// $Egrandtotal = 0 ;
// $lastIndex=$valueData[$count-1];
// $Egrandtotal = $valueData[$count - 1]['Egrandtotal'];
// $Igrandtotal = $valueData[$count - 1]['Igrandtotal'];


for ($i = 0; $i <= count($valueData); $i++) {
    $ehead_name = '';
    $ihead_name = '';
    $esub_head = '';
    $isub_head = '';
    $eledger = '';
    $iledger = '';
    $eheadtotal = '';
    $iheadtotal = '';
    $enetprofit = '';
    $inetprofit = '';
    if ($valueData[$i]['Iposition'] == 'I'  &&        $valueData[$i]['IisHead'] == '1') {
        $ihead_name = '<b>' . $valueData[$i]['Ihead_name'] . '</b>';
        $iheadtotal = '<b>' . $valueData[$i]['Ihead_total']  . '</b>';
        $iledger = '';
    }
    if ($valueData[$i]['Eposition'] == 'E'  &&        $valueData[$i]['EisHead'] == '1') {
        $ehead_name = '<b>' . $valueData[$i]['Ehead_name'] . '</b>';
        $eheadtotal = '<b>' . $valueData[$i]['Ehead_total'] . '</b>';
        // $eheadtotal =  $valueData[$i]['Ehead_total'];
        $eledger = '';
    }

    if ($valueData[$i]['Eposition'] == 'E' && $valueData[$i]['EisHead'] == '0') {
        $ehead_name = '<span>' . $valueData[$i]['Esub_head'] . '</span>';
        $eledger =  $valueData[$i]['Eledger_balance'];
        $eheadtotal = '';
    }
    if ($valueData[$i]['Iposition'] == 'I' && $valueData[$i]['IisHead'] == '0') {
        $ihead_name = '<span>' . $valueData[$i]['Isub_head'] . '</span>';
        $iledger =  $valueData[$i]['Iledger_balance'];
    }
    if ($valueData[$i]['Ehead_name'] == 'Net Profit' && $valueData[$i]['Eposition'] == 'E') {
        $enetprofit = $valueData[$i]['Ehead_total'];
    }
    if ($valueData[$i]['Ihead_name'] == 'Net Loss' && $valueData[$i]['Iposition'] == 'I') {
        $inetprofit = $valueData[$i]['Ihead_total'];
    }
    $tmp = [
        // We can check the condition (isset) if their is value then show otherwise display null
        'ehead_name' => isset($valueData[$i]['Ehead_name']) ? $ehead_name : null,
        'ihead_name' => isset($valueData[$i]['Ihead_name']) ? $ihead_name : null,
        'eheadtotal' => isset($valueData[$i]['Ehead_total']) ? $eheadtotal : null,
        'iheadtotal' => isset($valueData[$i]['Ihead_total']) ? $iheadtotal : null,
        'eledger' => isset($valueData[$i]['Eledger_balance']) ? $eledger : null,
        'iledger' => isset($valueData[$i]['Iledger_balance']) ? $iledger : null,
        'position' =>   isset($valueData[$i]['position']) ? $valueData[$i]['position'] : null,
        'isHead' =>   isset($valueData[$i]['isHead']) ? $valueData[$i]['isHead'] : null,
        'Egrandtotal' => sprintf("%.2f",($Egrandtotal) + 0.0),
        'Igrandtotal' => sprintf("%.2f",($Igrandtotal) + 0.0),
        // 'Igrandtotal' => $Igrandtotal,
        'inetprofit' => $inetprofit,
        'enetprofit' => $enetprofit,
        'date' => $date,
        'bankName' => $bankName1,
        'branch_code' => $branch_code,
        'branchName' => $branchName,
    ];
    $data[$i] = $tmp;
}

ob_end_clean();

$config = ['driver' => 'array', 'data' => $data];
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');
