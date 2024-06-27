<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DayBookDetail.jrxml';

$data = [];
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$Date = $_GET['Date'];
$Branch = $_GET['Branch'];
$branchName = $_GET['branchName'];

// print_r($Date);
// $Date = '10/08/2022';
// $Branch = 1;
$myObj = new stdClass();
$myObj->date = $Date;
$myObj->branch = $Branch;
$ch = curl_init();
$arr = array();
$arr['date'] = $Date;
$obj = (object)$myObj;

// curl_setopt($ch, CURLOPT_URL, 'http://localhost:'.$port.'/daybook');
curl_setopt($ch, CURLOPT_URL, 'http://' . $IPADDD . ':' . $port . '/daybook');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);

//  print_r($valueData);

$total = $valueData['total'];
$view = $valueData['view'];
// print_r($)
curl_close($ch);

for ($i = 0; $i <= count($view); $i++) { 
    // Apply condition for bold and underline the Head 
    $cr_head = '';
    $dr_head = '';

    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_head = '<b>'.'<span style="font-size: 8px">'.$view[$i]['CR_NARRATION'].'</span>'.'</b>';
    }else{
        $cr_head = $view[$i]['CR_NARRATION'];
    }

    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_head = '<b>'.'<span style="font-size: 8px">'.$view[$i]['DR_NARRATION'].'</span>'.'</b>';
    }else{
        $dr_head = $view[$i]['DR_NARRATION'];
    }

    
    $cr_cash = '';
    $dr_cash = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_cash = '<b>'.'<span style="font-size: 8px">'.$view[$i]['CR_CASHAMT'].'</span>'.'</b>';
    }else{
        $cr_cash = $view[$i]['CR_CASHAMT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_cash = '<b>'.'<span style="font-size: 8px">'.$view[$i]['DR_CASHAMT'].'</span>'.'</b>';
    }else{
        $dr_cash = $view[$i]['DR_CASHAMT'];
    }
    
    $cr_tran = '';
    $dr_tran = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_tran = '<b>'.'<span style="font-size: 8px">'.$view[$i]['CR_TRANSFERAMT'].'</span>'.'</b>';
    }else{
        $cr_tran = $view[$i]['CR_TRANSFERAMT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_tran = '<b>'.'<span style="font-size: 8px">'.$view[$i]['DR_TRANSFERAMT'].'</span>'.'</b>';
    }else{
        $dr_tran = $view[$i]['DR_TRANSFERAMT'];
    }

    $cr_tranamt = '';
    $dr_tranamt = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_tranamt = '<b>'.'<span style="font-size: 8px">'.$view[$i]['CR_TRAN_AMOUNT'].'</span>'.'</b>';
    }else{
        $cr_tranamt = $view[$i]['CR_TRAN_AMOUNT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_tranamt = '<b>'.'<span style="font-size: 8px">'.$view[$i]['DR_TRAN_AMOUNT'].'</span>'.'</b>';
    }else{
        $dr_tranamt = $view[$i]['DR_TRAN_AMOUNT'];
    }

   
    $query= 'SELECT
            fn_amttowordenglish(
                '.$total['CLOSING_BALANCE'].'
            )';

//  echo $query;

    $sql =  pg_query($conn,$query);
$balshow='';
    while($bal = pg_fetch_assoc($sql))  
    {
  $balshow=  $bal['fn_amttowordenglish'];
    }
    $tmp = [

        // We can check the condition (isset) if their is value then show otherwise display null
        'CR_TRAN_NO' =>   isset($view[$i]['CR_TRAN_NO']) ? $view[$i]['CR_TRAN_NO'] : null,
        'DR_TRAN_NO' =>  isset($view[$i]['DR_TRAN_NO']) ? $view[$i]['DR_TRAN_NO'] : null,
        'CR_CASHAMT' =>  isset($view[$i]['CR_CASHAMT']) ? ($view[$i]['CR_CASHAMT'] == '0.00' ? null : $cr_cash) : null,
        'DR_CASHAMT' =>  isset($view[$i]['DR_CASHAMT']) ? ($view[$i]['DR_CASHAMT'] == '0.00' ? null : $dr_cash) : null,
        'CR_TRANSFERAMT' =>  isset($view[$i]['CR_TRANSFERAMT']) ? ($view[$i]['CR_TRANSFERAMT'] == '0.00' ? null : $cr_tran) : null,
        'DR_TRANSFERAMT' =>  isset($view[$i]['DR_TRANSFERAMT']) ? ($view[$i]['DR_TRANSFERAMT'] == '0.00' ? null : $dr_tran) : null,
        'CR_TRAN_AMOUNT' => isset($view[$i]['CR_TRAN_AMOUNT']) ? ($view[$i]['CR_TRAN_NO'] == 0 ? $cr_tranamt : null) : null,
        'DR_TRAN_AMOUNT' => isset($view[$i]['DR_TRAN_AMOUNT']) ? ($view[$i]['DR_TRAN_NO'] == 0 ? $dr_tranamt : null) : null,
        'DR_ENTRY' =>  isset($view[$i]['DR_NARRATION']) ? $dr_head : null,
        'CR_ENTRY' => isset($view[$i]['CR_NARRATION']) ? $cr_head : null,
        'DR_TOTAL_AMOUNT' => isset($total['DR_TOTAL_AMOUNT']) ? $total['DR_TOTAL_AMOUNT'] : null,
        'DR_CASH_AMOUNT' => isset($total['DR_CASH_AMOUNT']) ? $total['DR_CASH_AMOUNT'] : null,
        'DR_TRANSFER_AMOUNT' => isset($total['DR_TRANSFER_AMOUNT']) ? $total['DR_TRANSFER_AMOUNT'] : null,
        'DR_CLEARING_AMOUNT' => isset($total['DR_CLEARING_AMOUNT']) ? $total['DR_CLEARING_AMOUNT'] : null,
        'CR_TOTAL_AMOUNT' => isset($total['CR_TOTAL_AMOUNT']) ? $total['CR_TOTAL_AMOUNT'] : null,
        'CR_CASH_AMOUNT' => isset($total['CR_CASH_AMOUNT']) ? $total['CR_CASH_AMOUNT'] : null,
        'CR_TRANSFER_AMOUNT' => isset($total['CR_TRANSFER_AMOUNT']) ? $total['CR_TRANSFER_AMOUNT'] : null,
        'CR_CLEARING_AMOUNT' => isset($total['CR_CLEARING_AMOUNT']) ? $total['CR_CLEARING_AMOUNT'] : null,
        'CR_GRAND_TOTAL' => isset($total['CR_GRAND_TOTAL']) ? $total['CR_GRAND_TOTAL'] : null,
        'DR_GRAND_TOTAL' => isset($total['DR_GRAND_TOTAL']) ? $total['DR_GRAND_TOTAL'] : null,
        'CLOSING_BALANCE' => isset($total['CLOSING_BALANCE']) ? $total['CLOSING_BALANCE'] : null,
        'OPENING_BALANCE' => isset($total['OPENING_BALANCE']) ? $total['OPENING_BALANCE'] : null,
        'FN_AMTTOWORDENGLISH' => $balshow,
        'Date' => $Date,
        'bankName' => $bankName,
        'Branch' => $Branch,
        'branchName' => $branchName,
    ];
    $data[$i] = $tmp;

}
//   echo $data;
 ob_end_clean();

 $config = ['driver' => 'array', 'data' => $data];
// print_r($data);
 $report = new PHPJasperXML();
 $report->load_xml_file($filename)
     ->setDataSource($config)
     ->export('Pdf');

?>