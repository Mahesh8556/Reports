<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
set_time_limit(500);
ini_set('memory_limit', '1024M');

error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LoanCashAmt.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'01/04/2016'";
$endDate = "'11/11/2022'";
$dateformat ="'DD/MM/YYYY'";

$query = 'SELECT loantran."TRAN_ACNO",loantran."TRAN_DRCR",loantran."TRAN_AMOUNT",lnmaster."AC_NAME"
          from loantran
          inner join lnmaster on 
          cast(loantran."TRAN_ACNO" as bigint) = cast(lnmaster."BANKACNO" as bigint)';

$sql =  pg_query($conn,$query);

$i = 0;

while($row = pg_fetch_assoc($sql)){

    $tmp=[
        'TRAN_ACNO'=> $row['TRAN_ACNO'],
        'AC_NAME'=> $row['TRAN_AMOUNT'],
        'TRAN_DRCR' => $row['TRAN_DRCR'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
            
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
// 
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>

