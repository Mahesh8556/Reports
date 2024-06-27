<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ComparativeDepositeStat.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");


$startDate = "'28/02/2021'";
$endDate = "'03/03/2021'";
$dateformate = "'DD/MM/YYYY'";
// variables
// $startDate = $_GET['startDate'];
// $endDate = $_GET['endDate'];
// $dateformate = "'DD/MM/YYYY'";

$c = "'C'";
$d = "'D'";

$query = ' SELECT COUNT(*),0 as Count1,
           coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) +
           coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) as Amount,
           0 as Balance,dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE" FROM
           (SELECT  
           coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) +
           coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) as Amount,
           0 as Balance,dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE"
           FROM schemast,dpmaster
           Where cast(dpmaster."AC_OPDATE" as date) = '.$startDate.'::date )schemast,dpmaster where
           dpmaster."AC_CLOSEDT" IS NULL OR cast(dpmaster."AC_CLOSEDT" as date) = '.$startDate.'::date
           AND cast(dpmaster."AC_OPDATE" as date) <= '.$startDate.'::date 
           GROUP BY dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE",dpmaster."AC_OP_CD",dpmaster."AC_OP_BAL"
           Union
           SELECT COUNT(*) as Count1,0 as COUNT,
           coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) +
           coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) as Balance,
           0 as Amount,dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE" FROM
           (SELECT  
           coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) +
           coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as integer) else 0 end, 0) as Balance,
           0 as Amount,dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE"
           FROM schemast,dpmaster
           Where cast(dpmaster."AC_OPDATE" as date) = '.$endDate.'::date )schemast,dpmaster where
           dpmaster."AC_CLOSEDT" IS NULL OR cast(dpmaster."AC_CLOSEDT" as date) = '.$endDate.'::date
           AND cast(dpmaster."AC_OPDATE" as date) <= '.$endDate.'::date 
           GROUP BY dpmaster."AC_ACNOTYPE",schemast."S_APPL",schemast."S_NAME",
           dpmaster."AC_CLOSEDT",dpmaster."AC_OPDATE",dpmaster."AC_OP_CD",dpmaster."AC_OP_BAL",dpmaster."AC_OPDATE" ';
          
$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_ATOTAL = 0 ;
$DIFFERNC = 0 ;
$GRAND_BTOTAL = 0 ;
$GRAND_DIFF = 0 ;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){ 

    $GRAND_ATOTAL = $GRAND_ATOTAL + $row['amount']; 
    $DIFFERNC = $row['amount'] - $row['balance'];
    $GRAND_BTOTAL = $GRAND_BTOTAL + $row['balance']; 
    $GRAND_DIFF = $GRAND_DIFF + $DIFFERNC; 

    $tmp=[
        'S_NAME' => $row['S_NAME'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'S_APPL'=> $row['S_APPL'],
        'balance'=> $row['balance'],
        'amount'=> $row['amount'],
        'grandatot'=> $GRAND_ATOTAL,
        'grandbtot'=> $GRAND_BTOTAL,
        'difference'=> $DIFFERNC,
        'granddiffrnc'=> $GRAND_DIFF,
        'NAME'=> $row['NAME'],
        'count'=> $row['count'],
        'count1'=> $row['count1'],
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
    

