<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/BnkABTypeBALDeposit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// variables
$startDate = "'01/01/2000'";
$endDate = "'01/12/2022'";
$dateformate = "'DD/MM/YYYY'";

$repotype = $_GET['repotype'];
$type ="'A'";

//get data from table records

$query ='SELECT "NAME","AC_MEMBNO","AC_MEMBTYPE","AC_NO","AC_NAME","AC_SCHMAMT","AC_EXPDT","AC_OPDATE","AC_ACNOTYPE","AC_CLOSEDT"
        FROM
        (
        SELECT dpmaster."AC_MEMBNO",dpmaster."AC_MEMBTYPE",dpmaster."AC_NO",dpmaster."AC_NAME",
               dpmaster."AC_SCHMAMT",dpmaster."AC_EXPDT",dpmaster."AC_OPDATE",dpmaster."AC_ACNOTYPE",
        cast(dpmaster."AC_CLOSEDT" as date),
        ownbranchmaster."NAME" from dpmaster
        INNER JOIN ownbranchmaster on dpmaster."BRANCH_CODE" =ownbranchmaster."id"
        where 
        cast(dpmaster."AC_OPDATE" as date) 
        between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
        UNION
        SELECT pgmaster."AC_MEMBNO",pgmaster."AC_MEMBTYPE",pgmaster."AC_NO",pgmaster."AC_NAME",
               pgmaster."AC_SCHMAMT",pgmaster."AC_EXPDT",pgmaster."AC_OPDATE",pgmaster."AC_ACNOTYPE",
        cast(pgmaster."AC_CLOSEDT" as date),
        ownbranchmaster."NAME" FROM pgmaster
        INNER JOIN ownbranchmaster on pgmaster."BRANCH_CODE" =ownbranchmaster."id"
        where 
        cast(pgmaster."AC_OPDATE" as date) 
        between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
        UNION
        SELECT cast(shmaster."AC_NO" as character varying) as "AC_MEMBNO",
               cast (shmaster."AC_TYPE" as character varying) as "AC_MEMBTYPE",
               shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_INSTALLMENT" as AC_SCHMAMT,
               shmaster."AC_EXPDT",shmaster."AC_OPDATE",shmaster."AC_ACNOTYPE",
        cast(shmaster."AC_RETIRE_DATE" as date)as "AC_CLOSEDT",
        ownbranchmaster."NAME" FROM shmaster
        INNER JOIN ownbranchmaster on shmaster."BRANCH_CODE" =ownbranchmaster."id"
        where 
        cast(shmaster."AC_OPDATE" as date) 
        between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
        ) results
        ORDER BY "AC_ACNOTYPE" ';

$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0 ;
$GROUP_TOTAL = 0 ;
$type = '';

while($row = pg_fetch_assoc($sql)){
    // grand-total
    // $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_SCHMAMT'];
    $GRAND_TOTAL = $GRAND_TOTAL + 10;

    // group-total 
    $vartype=$row['AC_ACNOTYPE'];
    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        // $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SCHMAMT'];
        $GROUP_TOTAL = $GROUP_TOTAL + 10;
    }else{
        $type = $row['AC_ACNOTYPE'];
        $GROUP_TOTAL = 0 ;
        // $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SCHMAMT'];
        $GROUP_TOTAL = $GROUP_TOTAL +10;
    }

        $tmp=[
        'AC_MEMBNO'=> $row['AC_MEMBNO'],
        'AC_MEMBTYPE'=> $row['AC_MEMBTYPE'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_SCHMAMT' => $row['AC_SCHMAMT'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_ODDATE'=>$row['AC_ODDATE'],
        'AC_ACNOTYPE'=>$row['AC_ACNOTYPE'],
        'NAME'=>$row['NAME'],
        'AC_CLOSEDT'=>$row['AC_CLOSEDT'],
        'balance'=>10,
        'GrandTotal' =>  $GRAND_TOTAL ,
        'schemewiseTotal' =>  $GRAND_TOTAL ,
        'START_DATE' =>  $startDate ,
        'END_DATE' =>  $endDate ,

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
    

