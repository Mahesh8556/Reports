<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
//error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DayBookSummary.jrxml';

$data = [];
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$Date = $_GET['Date'];
$Branch = $_GET['Branch'];
$branchName = $_GET['branchName'];

$myObj = new stdClass();
$myObj->date = $Date;
$myObj->branch = $Branch;
$ch = curl_init();
$arr = array();
$credit_data = array();
$debit_data  = array();
$arr['date'] = $Date;
$obj = (object)$myObj;
curl_setopt($ch, CURLOPT_URL, 'http://localhost:'.$port.'/daybook');
// curl_setopt($ch, CURLOPT_URL, 'http://139.59.63.215:7276/daybook');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($myObj));
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);
$total = $valueData['total'];
$view = $valueData['view'];
curl_close($ch);

for ($i = 0; $i <= count($view); $i++) {
    
    // Apply condition for bold and underline the Head 
    $cr_head = '';
    $dr_head = '';

    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_head = '<b>'.'<u>'.'<span style="font-size: 9px">'.$view[$i]['CR_NARRATION'].'</span>'.'</u>'.'</b>';
    }else{
        $cr_head = $view[$i]['CR_NARRATION'];
    }

    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_head = '<b>'.'<u>'.'<span style="font-size: 9px">'.$view[$i]['DR_NARRATION'].'</span>'.'</u>'.'</b>';
    }else{
        $dr_head = $view[$i]['DR_NARRATION'];
    }

    $cr_cash = '';
    $dr_cash = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_cash = '<b>'.'<span style="font-size: 9px">'.$view[$i]['CR_CASHAMT'].'</span>'.'</b>';
    }else{
        $cr_cash = $view[$i]['CR_CASHAMT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_cash = '<b>'.'<span style="font-size: 9px">'.$view[$i]['DR_CASHAMT'].'</span>'.'</b>';
    }else{
        $dr_cash = $view[$i]['DR_CASHAMT'];
    }

    $cr_tran = '';
    $dr_tran = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_tran = '<b>'.'<span style="font-size: 9px">'.$view[$i]['CR_TRANSFERAMT'].'</span>'.'</b>';
    }else{
        $cr_tran = $view[$i]['CR_TRANSFERAMT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_tran = '<b>'.'<span style="font-size: 9px">'.$view[$i]['DR_TRANSFERAMT'].'</span>'.'</b>';
    }else{
        $dr_tran = $view[$i]['DR_TRANSFERAMT'];
    }

    $cr_tranamt = '';
    $dr_tranamt = '';
    if($view[$i]['CR_TRAN_NO'] == '0'){
        $cr_tranamt = '<b>'.'<span style="font-size: 9px">'.$view[$i]['CR_TRAN_AMOUNT'].'</span>'.'</b>';
    }else{
        $cr_tranamt = $view[$i]['CR_TRAN_AMOUNT'];
    }
    if($view[$i]['DR_TRAN_NO'] == '0'){
        $dr_tranamt = '<b>'.'<span style="font-size: 9px">'.$view[$i]['DR_TRAN_AMOUNT'].'</span>'.'</b>';
    }else{
        $dr_tranamt = $view[$i]['DR_TRAN_AMOUNT'];
    }
   
    if($view[$i]['CR_TRAN_NO'] == 0 || $view[$i]['DR_TRAN_NO'] == 0){ 
        if($view[$i]['CR_TRAN_NO'] == 0 && $view[$i]['CR_CASHAMT'] != ''){
            $dataset = new stdClass();
            $dataset->CR_TRAN_GLACNO = $view[$i]['CR_TRAN_GLACNO'];
            $dataset->CR_TRAN_ACNO = $view[$i]['CR_TRAN_ACNO'];
            $dataset->CR_CASHAMT = $view[$i]['CR_CASHAMT'];
            $dataset->CR_TRANSFERAMT = $view[$i]['CR_TRANSFERAMT'];
            $dataset->CR_CLEARINGAMT = $view[$i]['CR_CLEARINGAMT'];
            $dataset->CR_TRAN_AMOUNT = $view[$i]['CR_TRAN_AMOUNT'];
            $dataset->CR_NARRATION = $view[$i]['CR_NARRATION'];
            $dataset->CR_ENTRY = $view[$i]['CR_ENTRY'];
            $dataset->CR_TRAN_NO = $view[$i]['CR_TRAN_NO'];
            $obj = $dataset;
            array_push($credit_data , $dataset);
        }
        if($view[$i]['DR_TRAN_NO'] == 0 && $view[$i]['DR_CASHAMT'] != ''){
            $dataset1 = new stdClass();
            $dataset1->DR_TRAN_GLACNO = $view[$i]['DR_TRAN_GLACNO'];
            $dataset1->DR_TRAN_ACNO = $view[$i]['DR_TRAN_ACNO'];
            $dataset1->DR_CASHAMT = $view[$i]['DR_CASHAMT'];
            $dataset1->DR_TRANSFERAMT = $view[$i]['DR_TRANSFERAMT'];
            $dataset1->DR_CLEARINGAMT = $view[$i]['DR_CLEARINGAMT'];
            $dataset1->DR_TRAN_AMOUNT = $view[$i]['DR_TRAN_AMOUNT'];
            $dataset1->DR_NARRATION = $view[$i]['DR_NARRATION'];
            $dataset1->DR_ENTRY = $view[$i]['DR_ENTRY'];
            $dataset1->DR_TRAN_NO = $view[$i]['DR_TRAN_NO'];
            $obj1 = (object)$dataset1;
            array_push($debit_data, $dataset1);
        }
 
    }
   
    
}


ob_end_clean();
$data_length = 0;
if(count($credit_data) > count($debit_data)){
    $data_length = count($credit_data);
}else{
    $data_length = count($debit_data);
}
for ($x = 0; $x < $data_length; $x++) {
    if(count($credit_data) > $x){
        $creditvalue = json_decode(json_encode($credit_data[$x]), true);
    }else{
        $creditvalue = '0';
    }

    if(count($debit_data) > $x){
        $debitvalue = json_decode(json_encode($debit_data[$x]), true);
    }else{
        $debitvalue = '0';
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
        'CR_TRAN_NO' =>   $creditvalue == 0 ? null : $creditvalue['CR_TRAN_NO'],
        'DR_TRAN_NO' =>   $debitvalue == 0 ? null : $debitvalue['DR_TRAN_NO'],
        'CR_CASHAMT' =>   $creditvalue == 0 ? null : $creditvalue['CR_CASHAMT'],
        'DR_CASHAMT' =>   $debitvalue == 0 ? null : $debitvalue['DR_CASHAMT'],
        'CR_TRANSFERAMT' =>  $creditvalue == 0 ? null : $creditvalue['CR_TRANSFERAMT'],
        'DR_TRANSFERAMT' =>  $debitvalue == 0 ? null : $debitvalue['DR_TRANSFERAMT'],
        'CR_TRAN_AMOUNT' => $creditvalue == 0 ? null : $creditvalue['CR_TRAN_AMOUNT'],
        'DR_TRAN_AMOUNT' => $debitvalue == 0 ? null : $debitvalue['DR_TRAN_AMOUNT'],
        'DR_ENTRY' =>  $debitvalue == 0 ? null : $debitvalue['DR_NARRATION'], 
        'CR_ENTRY' =>  $creditvalue == 0 ? null : $creditvalue['CR_NARRATION'],
        'DR_TOTAL_AMOUNT' => isset($total['DR_TOTAL_AMOUNT']) ? $total['DR_TOTAL_AMOUNT'] : null,
        'DR_CASH_AMOUNT' =>  isset($total['DR_CASH_AMOUNT']) ? $total['DR_CASH_AMOUNT'] : null,
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
    $data[$x] = $tmp;
}
ob_end_clean();

$config = ['driver' => 'array', 'data' => $data];
// print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');


?>