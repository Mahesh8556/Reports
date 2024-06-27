<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/BudgetView.jrxml';
// $filename = __DIR__.'/blank.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");


//variables

$stdate = $_GET['stdate']; 
$branchName = $_GET['branchName']; 
$bankName = $_GET['bankName']; 
// $pritns = $_GET['pritns']; 
$node = $_GET['node']; 
$flag = $_GET['flag']; 



$stdate_ = str_replace("'", "", $stdate);
$branchName_ = str_replace("'", "", $branchName);
$bankName_ = str_replace("'", "", $bankName);

$checktype;
$flag == 1 ? $checktype = 'true' : $checktype = 'false';
//  echo $checktype;


if($flag == 0) {
    $query = 'SELECT "AC_NO",
    "AC_NAME",
    "BUDGET_AMOUNT",
    "LEDGERBALANCE",
    "EXTRA_AMOUNT", 
    "EXTRA_PER",
    "LOW_AMOUNT",
    "LOW_PER",
    "APROX_AMOUNT",
    "PARENT_NODE"
    FROM BUDGETMASTERDETAILS 
    WHERE "PARENT_NODE" = 3';
    // echo $query;
}
else if($flag == 1) {
    $query = 'SELECT "AC_NO",
    "AC_NAME",
    "BUDGET_AMOUNT",
    "LEDGERBALANCE",
    "EXTRA_AMOUNT", 
    "EXTRA_PER",
    "LOW_AMOUNT",
    "LOW_PER",
    "APROX_AMOUNT",
    "PARENT_NODE"
    FROM BUDGETMASTERDETAILS 
    WHERE "PARENT_NODE" = 4';
    // echo $query;
}


        
$sql =  pg_query($conn, $query);

$i = 0;
$s1 = 0;
$s2 = 0;
$s3 = 0;
$s4 = 0;
$s5 = 0;

while ($row = pg_fetch_assoc($sql)) {


$s1 = $s1 + $row['BUDGET_AMOUNT'];
$s2 = $s2 + $row['LEDGERBALANCE'];
$s3 = $s3 + $row['EXTRA_AMOUNT'];
$s4 = $s4 + $row['LOW_AMOUNT'];
$s5 = $s5 + $row['APROX_AMOUNT'];
  

    $tmp=[
        'bank' => $bankName_,
        'branch' => $branchName_,

        'AcNo' => $row['AC_NO'],
        'AcName' => $row['AC_NAME'],
        'ba24' => $row['BUDGET_AMOUNT'],
        'balance' =>$row['LEDGERBALANCE'],
        'examt' => $row['EXTRA_AMOUNT'],
        'expercent' =>$row['EXTRA_PER'],
        'lessamt' =>$row['LOW_AMOUNT'],
        'lesspercent' =>$row['LOW_PER'],
        'ba25' => $row['APROX_AMOUNT'],
        // 'pnode' => $row['PARENT_NODE'],
        'flag' => $flag,
        't1' => sprintf("%.2f",($s1) + 0.0),
        't2' => sprintf("%.2f",($s2) + 0.0),
        't3' => sprintf("%.2f",($s3) + 0.0),
        't4' => sprintf("%.2f",($s4) + 0.0),
        't5' => sprintf("%.2f",($s5) + 0.0),
    ];
    $data[$i]=$tmp;
    $i++;
}
ob_end_clean();
$config = ['driver'=>'array','data'=>$data];
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');


?> 
