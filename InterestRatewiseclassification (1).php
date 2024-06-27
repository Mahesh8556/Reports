<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/IntRatewiseDepSummary1.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$StartDate = "'31/03/2008'";
$S="'S'";
$M="'M'";
$L="'L'";
$O="'0'";
$TD="'TD'";
$INTRATE="'INTRATE'";

$query = '
SELECT dpmaster."AC_ACNOTYPE" , dpmaster."AC_TYPE" , dpmaster."AC_NO" ,TERMMASTER."TERM_TYPE",dpmaster."AC_MONTHS",
dpmaster."AC_INTRATE",sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",dpmaster."AC_NAME",SCHEMAST."S_NAME",
case when "TERM_TYPE" = '.$S.' Then ledgerbalance(cast(schemast."S_APPL" as character varying),dpmaster."BANKACNO",dpmaster."AC_OPDATE",0,ownbranchmaster."CODE") else '.$O.' end s_term,
case when "TERM_TYPE" = '.$M.' Then ledgerbalance(cast(schemast."S_APPL" as character varying),dpmaster."BANKACNO",dpmaster."AC_OPDATE",0,ownbranchmaster."CODE") else '.$O.' end m_term,
case when "TERM_TYPE" = '.$L.' Then ledgerbalance(cast(schemast."S_APPL" as character varying),dpmaster."BANKACNO",dpmaster."AC_OPDATE",0,ownbranchmaster."CODE") else '.$O.' end l_term
FROM dpmaster 
INNER JOIN ownbranchmaster ON
 ownbranchmaster."id" = dpmaster."BRANCH_CODE"
 INNER JOIN TERMMASTER ON
 TERMMASTER."AC_ACNOTYPE" = dpmaster."AC_ACNOTYPE" 
INNER JOIN schemast ON
schemast."id" = dpmaster."AC_TYPE"
INNER JOIN SIZEWISEBALANCE ON 
dpmaster."AC_ACNOTYPE" = SIZEWISEBALANCE."ACNOTYPE"
WHERE
cast(dpmaster."AC_MONTHS" as integer) BETWEEN CAST(TERMMASTER."PERIOD_FROM" as INTEGER) 
AND CAST(TERMMASTER."PERIOD_TO" as INTEGER) AND
dpmaster."AC_OPDATE" IS NULL OR cast(dpmaster."AC_OPDATE" as date) = '.$StartDate.'::date AND
sizewisebalance."ACNOTYPE" = '.$TD.' AND sizewisebalance."SLAB_TYPE" = '.$INTRATE.' 
AND CAST(dpmaster."AC_INTRATE" AS FLOAT) >  SIZEWISEBALANCE."AMOUNT_FROM" AND  CAST(dpmaster."AC_INTRATE" AS FLOAT) <= SIZEWISEBALANCE."AMOUNT_TO"
 Group By dpmaster."AC_ACNOTYPE",DPMASTER."AC_TYPE", DPMASTER."AC_NO" ,DPMASTER."AC_INTRATE",SCHEMAST."S_APPL",OWNBRANCHMASTER."CODE",SCHEMAST."S_NAME",
 dpmaster."AC_NAME", DPMASTER."BANKACNO",DPMASTER."AC_OPDATE",sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",termmaster."TERM_TYPE",dpmaster."AC_MONTHS" ';
            
            // Group By dpmaster."AC_ACNOTYPE", dpmaster."AC_NO",termmaster."TERM_TYPE",SCHEMAST."S_APPL",
            // dpmaster."AC_OPDATE",dpmaster."AC_INTRATE",dpmaster."AC_TYPE",DPMASTER."BANKACNO",OWNBRANCHMASTER."CODE",
            // sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",dpmaster."AC_MONTHS"

          
$sql =  pg_query($conn,$query);
$i=0;

$GRAND_TOTAL = 0;
$grand_total=0;
$total = 0;

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['count'];
    $grand_total=$grand_total+$row['total'];
    $total=$$row['s_term']+$row['m_term']+$row['l_term'];

    if($type == ''){
        $type = $row['AC_NO'];
     }
     if($type == $row['AC_NO']){
        $ac_total= $ac_total + $row['count'];
     }else{
        $type = $row['AC_NO'];
        $ac_total = 0;
        $ac_total = $ac_total + $row['count'];
     }

     if($type == ''){
        $type = $row['AC_NO'];
     }
     if($type == $row['AC_NO']){
        $total_amount= $total_amount + $row['total'];
     }else{
        $type = $row['AC_NO'];
        $total_amount = 0;
        $total_amount = $total_amount + $row['total'];
     }
     

    $tmp=[
        'count' => $row['count'],
        'AC_NO' => $row['AC_NO'],
        'AC_ACNOTYPE'=>$row['AC_ACNOTYPE'],
        'S_NAME'=>$row['S_NAME'],
        'AC_NAME'=>$row['AC_NAME'],
        's_term'=>$row['s_term'],
        'm_term'=>$row['m_term'],
        'l_term'=>$row['l_term'],
        'AMOUNT_FROM'=>$row['AMOUNT_FROM'],
        'AMOUNT_TO'=>$row['AMOUNT_TO'],
        'total'=>$row['total'],
        'total'=> $total,
        'Count' => $GRAND_TOTAL,
        'grandtotal'=>$grand_total,
        'ac_total'=>$ac_total,
        'total_amount'=>$total_amount,



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

