<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/UserCounterWorkDelayTime.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");

// variables

$BRANCH = $_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];

$BRANCH = str_replace("'", "" , $BRANCH);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);


 $dateformat ="'DD/MM/YYYY'";

$query = 'SELECT "TRAN_NO" ,"TRAN_TIME" ,"TRAN_TYPE" ,"TRAN_DRCR" ,TRANSFERAMT TRANSFER_AMOUNT ,
          CASHAMT CASH_AMOUNT,CLEARINGAMT CLEARING_AMOUNT,"USER_CODE" ,"TRAN_ACNOTYPE" ,"TRAN_ACTYPE" ,"TRAN_ACNO" 
          From VWDETAILDAILYTRAN 
          WHERE CAST("TRAN_STATUS" AS INTEGER) = 1  AND "BRANCH_CODE" = '.$BRANCH_CODE.'';

        //  echo $query;
          
$sql =  pg_query($conn,$query);

 $i = 0;


while($row = pg_fetch_assoc($sql))
{ 
    

    $tmp=[
        'USER_CODE' => $row['USER_CODE'],
        'TRAN_TIME' => $row['TRAN_TIME'],
        'cash_amount' => sprintf("%.2f", ($row['cash_amount'] + 0.0)),
        'clearing_amount' =>sprintf("%.2f", ($row['clearing_amount'] + 0.0)),
        'TRAN_NO' => $row['TRAN_NO'],
        'TRAN_TYPE' => $row['TRAN_TYPE'],
        'TRAN_DRCR' => $row['TRAN_DRCR'],
        'transfer_amount' =>   sprintf("%.2f", ($row['transfer_amount'] + 0.0)),
        '$TRAN_ACNOTYPE' =>  $row['$TRAN_ACNOTYPE'],
        'BRANCH' => $BRANCH,
        'BANK_NAME' => $BANK_NAME,
        'BRANCH_CODE' => $BRANCH_CODE,
        
        
    ];
    $data[$i]=$tmp;
    $i++;  
}
ob_end_clean();
// echo $query;

$config = ['driver'=>'array','data'=>$data];
// echo $filename;
//print_r($data)
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf')
    
?>