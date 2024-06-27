<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/OtherReceivableBlncList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'01/01/2022'";
$endDate = "'01/03/2022'";
$dateformate = "'DD/MM/YYYY'";

$query = ' SELECT lnmaster."AC_NO",lnmaster."AC_NAME",ownbranchmaster."NAME"
           FROM lnmaster 
           Inner Join ownbranchmaster on
           lnmaster."BRANCH_CODE" = ownbranchmaster."id" ';
          
$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'NAME'=> $row['NAME'],
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

