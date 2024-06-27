<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepositeAmountwiseSummery.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

 $date = "'03/03/2022'";
// $startDate = $_GET['startDate'];
// $endDate = $_GET['endDate'];
// $dateformate = "'DD/MM/YYYY'";


$query = ' SELECT COUNT(*),
           dpmaster."AC_ACNOTYPE", dpmaster."AC_NO", dpmaster."AC_SCHMAMT",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE",schemast."S_NAME",
           sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO"
           FROM
           (
           SELECT dpmaster."AC_ACNOTYPE", dpmaster."AC_NO", dpmaster."AC_SCHMAMT",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE",schemast."S_NAME",
           ownbranchmaster."NAME",sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO"
           FROM dpmaster
           Inner Join schemast on
           dpmaster."AC_ACNOTYPE" = schemast."S_ACNOTYPE"
           Inner Join ownbranchmaster on
           dpmaster."BRANCH_CODE" = ownbranchmaster."id"
           Inner Join sizewisebalance on
           dpmaster."AC_ACNOTYPE" = sizewisebalance."ACNOTYPE") dpmaster 
           Inner Join schemast on
           dpmaster."AC_ACNOTYPE" = schemast."S_ACNOTYPE" 
           Inner Join sizewisebalance on
           dpmaster."AC_ACNOTYPE" = sizewisebalance."ACNOTYPE"
           where
           dpmaster."AC_CLOSEDT" IS NULL OR cast(dpmaster."AC_CLOSEDT" as date) = '.$date.'::date
           AND cast(dpmaster."AC_OPDATE" as date) <= '.$date.'::date 
           Group By dpmaster."AC_ACNOTYPE", dpmaster."AC_NO", dpmaster."AC_SCHMAMT",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE",schemast."S_NAME",
           sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO" ';
          
$sql =  pg_query($conn,$query);

$i = 0;

$AMOUNT_TOTAL = 0;
$NOOF_ACTOT = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $AMOUNT_TOTAL = $AMOUNT_TOTAL + $row['AC_SCHMAMT'];
    $NOOF_ACTOT = $NOOF_ACTOT + $row['count'];


    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $SCHEME_NOAC = $SCHEME_NOAC + $row['count'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $SCHEME_NOAC = 0;
        $SCHEME_NOAC = $SCHEME_NOAC + $row['count'];
    }
     
    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $SCHM_AMT = $SCHM_AMT + $row['AC_SCHMAMT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $SCHM_AMT = 0;
        $SCHM_AMT = $SCHM_AMT + $row['AC_SCHMAMT'];
    }

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AMOUNT_TO'=> $row['AMOUNT_TO'],
        'AMOUNT_FROM'=> $row['AMOUNT_FROM'],
        'S_NAME' => $row['S_NAME'],
        'NAME' => $row['NAME'],
        'count' => $row['count'],
        'schmnoactot' => $SCHEME_NOAC,
        'schmamttot' => $SCHM_AMT,
        'grandnoatot' => $NOOF_ACTOT,
        'grandamttot' => $AMOUNT_TOTAL,
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
    
}
?>

