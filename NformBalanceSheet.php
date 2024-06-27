<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');



// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

 $filename = __DIR__.'/nformbalncsheet.jrxml';
// $filename = __DIR__ . '/nformprofitandloss1.jrxml';



$data = [];
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
 $branchName = $_GET['branchName'];
$afterdate = $_GET['endate'];
$prevdate =  $_GET['stardate'];
$branch_code = $_GET['branched1'];

$branch_code= str_replace("'", "", $branch_code);
$bankName = str_replace("'", "", $bankName);
$afterdate = str_replace("'", "", $afterdate);
$prevdate = str_replace("'", "", $prevdate);
 $branchName = str_replace("'", "", $branchName1);

echo $branchName;

// $myObj = new stdClass();
// $myObj->afterdate = $afterdate1;
// $myObj->prevdate = $prevdate1;
// $myObj->branch_code = $branch_code1;
// $ch = curl_init();
// $arr = array();
// $obj = (object)$myObj;

$myObj = new stdClass();
$myObj->afterdate = $afterdate;
$myObj->prevdate = $prevdate;
$myObj->branch_code = $branch_code;
$ch = curl_init();
$arr = array();
$obj = (object)$myObj;

curl_setopt($ch, CURLOPT_URL, 'http://'. $IPADDD .':' . $port . '/reports/NbalanceSheet');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);

//   print_r($valueData);
curl_close($ch);
$count = count($valueData);

$lgrandTotal = 0;
$agrndTotal = 0;

    //   print_r($valueData);
   // print_r($count);
  
// $lastIndex=$valueData[$count-1];
 $grandtotal = $valueData[$count - 1]['grand_total'];
for ($i = 0; $i <= count($valueData); $i++) {

    $lgrandTotal = $lgrandTotal + $valueData[$i]['Lhead_total'];
    $agrndTotal  = $agrndTotal  + $valueData[$i]['Ahead_total'];
    // print_r($valueData[$i]);

    $lhead_name = '';
    $ahead_name = '';
    $lsub_head = '';
    $asub_head = '';
    $lledger = '';
    $lpledger = '';
    $aledger = '';
    $apledger = '';
    $lheadtotal = '';
    $aheadtotal = '';
    $lpheadtotal = '';
    $apheadtotal = '';
    $lnetprofit = '';
    $anetprofit = '';
    $Apheadtotal = '';
    // $grandtotal = '';

    if (
        $valueData[$i]['Lposition'] == 'L'  &&
        $valueData[$i]['Lhead_total'] > '0'
    ) {
        $lhead_name = $valueData[$i]['Lhead_name'];
    }
   
   
   
   // print_r($valueData[$i]);
    if (
        $valueData[$i]['Aposition'] == 'A'  &&
        $valueData[$i]['Ahead_total'] > '0'
    ) {
        $ahead_name = $valueData[$i]['Ahead_name'];
    }

    if ($valueData[$i]['Lposition'] == 'L' && $valueData[$i]['LisHead'] == '1') {
        $lheadtotal = $valueData[$i]['Lhead_total'];
       $lpheadtotal = $valueData[$i]['LPhead_total'];
    }

    if ($valueData[$i]['Aposition'] == 'A' && $valueData[$i]['AisHead'] == '1') {
        $aheadtotal = $valueData[$i]['Ahead_total'];
        $apheadtotal = $valueData[$i]['APhead_total'];
    }

    if ($valueData[$i]['Lposition'] == 'L' && $valueData[$i]['LisHead'] == '0') {
        $lsub_head = $valueData[$i]['Lsub_head'];
    }
    // else {
    //     $esub_head = '<span style="display:none;">' . $valueData[$i]['Esub_head'] . '</span>';
    // }

    if ($valueData[$i]['Aposition'] == 'A' && $valueData[$i]['AisHead'] == '0') {
        $asub_head = $valueData[$i]['Asub_head'];
    }
    //  else {
    //     $isub_head = '<span style="display:none;">' . $valueData[$i]['Isub_head'] . '</span>';
    // }

    if ($valueData[$i]['Lposition'] == 'L') {
        $lledger = $valueData[$i]['Lledger_balance'];
        $lpledger = $valueData[$i]['LPledger_balance'];
    }

    if ($valueData[$i]['Aposition'] == 'A') {
        $aledger = $valueData[$i]['Aledger_balance'];
        $apledger = $valueData[$i]['APledger_balance'];
    }

    if ($valueData[$i]['Lhead_name'] == 'Net Profit' && $valueData[$i]['Lposition'] == 'L') {
        $Lnetprofit = $valueData[$i]['Lhead_total'];
    }
    if ($valueData[$i]['Ahead_name'] == 'Net Loss' && $valueData[$i]['Aposition'] == 'A') {
        $anetprofit = $valueData[$i]['Ahead_total'];
    }
    // if ($valueData[$i]['position'] == 'E') {
    // $grandtotal = $valueData[$i]['grand_total'];
    // }



    $tmp = [
        // We can check the condition (isset) if their is value then show otherwise display null
        'lhead_name' => isset($valueData[$i]['Lhead_name']) ? $lhead_name : null,
        'ahead_name' => isset($valueData[$i]['Ahead_name']) ? $ahead_name: null,
        'lheadtotal' => isset($valueData[$i]['Lhead_total'])   ? $lheadtotal : null,
        'lpheadtotal' => isset($valueData[$i]['LPhead_total']) ? $lpheadtotal : null,
        'aheadtotal' => isset($valueData[$i]['Ahead_total'])   ? $aheadtotal   : null,
        'apheadtotal' => isset($valueData[$i]['APhead_total']) ? $apheadtotal   : null,
        // 'esub_head' =>  $esub_head,
        'lsub_head' => isset($valueData[$i]['Lsub_head']) ? $lsub_head : null,
        'asub_head' => isset($valueData[$i]['Asub_head']) ? $asub_head : null,
        // 'isub_head' =>  $isub_head,
        'lledger' => isset($valueData[$i]['Lledger_balance']) ? $lledger : null,
        'lPledger_balance' => isset($valueData[$i]['LPledger_balance']) ? $lpledger : null,
        'aledger' => isset($valueData[$i]['Aledger_balance']) ? $aledger : null,
        'aPledger_balance' => isset($valueData[$i]['APledger_balance']) ? $apledger : null,
        // 'position' =>   isset($valueData[$i]['position']) ? $valueData[$i]['position'] : null,
        // 'isHead' =>   isset($valueData[$i]['isHead']) ? $valueData[$i]['isHead'] : null,
        // 'Lgrandtotal' =>   isset($valueData[$i]['Lgrand_total']) ? $valueData[$i]['Lgrand_total'] : null,
        // 'Agrandtotal' =>   isset($valueData[$i]['Agrand_total']) ? $valueData[$i]['Agrand_total'] : null,
        // 'grandtotal' => $grandtotal,
        'anetprofit' => $anetprofit,
        'lnetprofit' => $lnetprofit,
        // 'date' => $date,
        'bankName' => $bankName,
        'branch_code' => $branch_code,
        // 'Lgrandtotal' => $Lgrandtotal,
        // 'Agrandtotal' => $Agrandtotal,
        'branchName' => $branchName1,
        'startdate'=>$prevdate,
        'enddate'=>$afterdate,
        'lgrandTotal' => $lgrandTotal,
        'agrndTotal'  => $agrndTotal,
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
    // print_r($data[$i]);
}
//   print_r($data);
ob_end_clean();
$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?>
    

