<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');

set_time_limit(500);
ini_set('memory_limit', '1024M');

// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/goldsilversubmreturn.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
  
$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$enddate = $_GET['endDate'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
// echo $enddate;
$bankName   = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
$enddate_   = str_replace("'", "", $enddate);
// $branchName = str_replace("'", "", $branchName);

$dateformate ="'DD/MM/YYYY'";

$query = ' SELECT goldsilver."SUBMISSION_DATE",goldsilver."ITEM_TYPE",goldsilver."BAG_RECEIPT_NO",
           goldsilver."TOTAL_WEIGHT_GMS",goldsilver."CLEAR_WEIGHT_GMS",goldsilver."RATE",
           goldsilver."TOTAL_VALUE",goldsilver."TRAN_STATUS",
           goldsilver."AC_TYPE",goldsilver."AC_ACNOTYPE",goldsilver."AC_NO",
           lnmaster."AC_SANCTION_AMOUNT",ownbranchmaster."NAME" 
           from goldsilver
           inner join lnmaster on cast(lnmaster."AC_NO" as bigint)= cast(goldsilver."AC_NO" as bigint)
           inner join ownbranchmaster on lnmaster."BRANCH_CODE" =ownbranchmaster."id"
           Where cast("SUBMISSION_DATE" as date) 
           between to_date('.$startDate.','.$dateformate.') and to_date('.$enddate.','.$dateformate.')
           and lnmaster."BRANCH_CODE" = '.$BRANCH_CODE.'
           ORDER BY goldsilver."ITEM_TYPE" ';

// echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GROUP_TOTAL = 0 ;
$GRAND_TOTAL = 0 ;

// echo 'Total Num rows'.pg_num_rows($sql);
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    

while ($row = pg_fetch_assoc($sql)) {
           // grand-total
           $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_SANCTION_AMOUNT'];
           // group-total 
           
           if($type == ''){
               $type = $row['AC_SANCTION_AMOUNT'];
           }
           if($type == $row['AC_SANCTION_AMOUNT']){
               $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SANCTION_AMOUNT'];
           }else{
               $type = $row['ITEM_TYPE'];
               $GROUP_TOTAL = 0 ;
               $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SANCTION_AMOUNT'];
           }

    $tmp=[
        
        'SUBMISSION_DATE'=> $row['SUBMISSION_DATE'],
        'ITEM_TYPE'=> $row['ITEM_TYPE'],
        'BAG_RECEIPT_NO' => $row['BAG_RECEIPT_NO'],
        'TOTAL_WEIGHT_GMS' => $row['TOTAL_WEIGHT_GMS'],
        'CLEAR_WEIGHT_GMS' => $row['CLEAR_WEIGHT_GMS'],
        'RATE' => $row['RATE'],
        'NAME' => $row['NAME'],
        'TOTAL_VALUE' => $row['TOTAL_VALUE'],
        'TRAN_STATUS' => $row['TRAN_STATUS'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_SANCTION_AMOUNT' => $row['AC_SANCTION_AMOUNT'],
        'AC_NO' => $row['AC_NO'],

        
        'grouptotal' =>$GROUP_TOTAL,
        'grandtotal' =>$GRAND_TOTAL,
        'startDate_' => $startDate_,
        'enddate_' => $enddate_,
        'bankName' => $bankName,
        'BRANCH_CODE' => $BRANCH_CODE,
        'startDate' => $startDate,
        'enddate' => $enddate,

            
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
}
?>

