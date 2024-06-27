<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DayBookReport.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $stdate = $_GET['stdate'];
// $bankName = $_GET['bankName'];

 $startDate = "'10/08/2022'";
$dateformate = "'DD/MM/YYYY'";

$schem = "'TR'";
$schem1 = "'JV'";
$schem2 = "'CS'";
$schem3 = "'CL'";
$c = "'C'";
$d = "'D'";
$otheramt = "'OTHER11_AMOUNT'";


 $query = 'SELECT "TRAN_NO", "TRAN_DATE", "TRAN_TIME" , "TRAN_TYPE",
           "TRAN_MODE", "TRAN_DRCR", "TRAN_ACNOTYPE",ownbranchmaster."NAME",  
           "OTHER11_ACNO", "TRAN_GLACNO","AC_NAME",
           "TRAN_ACTYPE", "TRAN_ACNO","OTHER11_AMOUNT","TRAN_AMOUNT", 
           1 RECCOUNTER, DAILYTRAN."USER_CODE", 
           DAILYTRAN."OFFICER_CODE" ,DAILYTRAN."AC_CLOSED",
           coalesce(case when "TRAN_DRCR" ='.$c.' Then
           cast("TRAN_AMOUNT" as float) else 0 end, 0) as crcastamt ,
           coalesce(case when "TRAN_DRCR" = '.$c.' Then
           cast("TRAN_AMOUNT" as float) else 0 end, 0) as crtransamt, 
           coalesce(case when "TRAN_DRCR" ='.$d.' Then
           cast("TRAN_AMOUNT" as float) else 0 end, 0) as drcastamt ,
           coalesce(case when "TRAN_DRCR" = '.$d.' Then
           cast("TRAN_AMOUNT" as float) else 0 end, 0) as drtransamt,
           coalesce(case when "TRAN_TYPE" = '.$schem.' Then cast("OTHER11_AMOUNT" as float) else 0 end, 0)  + 
           coalesce(case when "TRAN_TYPE" = '.$schem1.' Then cast("OTHER11_AMOUNT" as float) else 0 end, 0)TRANSFERAMT  , 
           coalesce(case when "TRAN_TYPE" = '.$schem2.' Then cast("OTHER11_AMOUNT" as float) else 0 end, 0) CASHAMT  , 
           coalesce(case when "TRAN_TYPE" = '.$schem3.' Then cast("OTHER11_AMOUNT" as float) else 0 end, 0) CLEARINGAMT,
           '.$otheramt.' REF_FIELD  
           FROM ACMASTER,DAILYTRAN
           Inner Join ownbranchmaster on
           DAILYTRAN."BRANCH_CODE" = ownbranchmaster."id" 
           where cast(DAILYTRAN."TRAN_DATE" as date) = cast('.$startDate.' as date)';

         
$sql =  pg_query($conn,$query);

$i = 0;

$CASHC_TOTAL = 0 ;
$TRANSC_TOTAL = 0 ;
$CASHD_TOTAL = 0 ;
$TRANSD_TOTAL = 0 ;
$CTOTAL = 0 ;
$DTOTAL = 0 ;

while($row = pg_fetch_assoc($sql)){

    $CASHC_TOTAL = $CASHC_TOTAL + $row['crcastamt'];
    $TRANSC_TOTAL = $TRANSC_TOTAL + $row['crtransamt'];
    $CASHD_TOTAL = $CASHD_TOTAL + $row['drcastamt'];
    $TRANSD_TOTAL = $TRANSD_TOTAL + $row['drtransamt'];
    $CTOTAL = $row['crcastamt'] + $row['crtransamt'];
    $DTOTAL = $row['drcastamt'] + $row['drtransamt'];

    $tmp=[
        'TRAN_NO' => $row['TRAN_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'crcastamt' => $row['crcastamt'],
        'crtransamt' => $row['crtransamt'],
        'drcastamt' => $row['drcastamt'],
        'drtransamt' => $row['drtransamt'],
        'NAME' => $row['NAME'],
        'TotalVoucher' => $row['TotalVoucher'],
        'TotalVoucher1' => $row['TotalVoucher1'],
        'cashtotalc' => $CASHC_TOTAL,
        'ctranstot' => $TRANSC_TOTAL,
        'dcashtot' => $CASHD_TOTAL,
        'dtranstot' => $TRANSD_TOTAL,

        'stdate' => $stdate,
        'bankName' => $bankName,
        'ctotal' => $CTOTAL,
        'dtotal' =>  $DTOTAL,

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

