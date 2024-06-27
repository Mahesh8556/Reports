<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

// $filename = __DIR__ . '/PigmyHandBook.jrxml';
$filename = __DIR__ . '/pigmyhandbook.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");



$bankName = $_GET['bankName'];
$date = $_GET['date'];
$sdate = $_GET['sdate'];

$scheme = $_GET['scheme'];
$schemeAccountNo = $_GET['schemeAccountNo'];
$branchName = $_GET['branch'];
$branch = $_GET['branch'];

$AG = "'AG'";
$DD = "'DD'";
$CH = "'CH'";
$dateformate = "'DD/MM/YYYY'";






$query = '  SELECT DPMASTER."AC_NAME", PGMASTER."AC_NAME" AS "PIGMYNAME",SCHEMAST."S_APPL",SCHEMAST."S_NAME", TABLE1."AGENT_ACNOTYPE", TABLE1."AGENT_ACTYPE", 
TABLE1."AGENT_ACNO", "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", SUM(AMT1) AMT_1, SUM(AMT2) AMT_2, SUM(AMT3) AMT_3, SUM(AMT4) AMT_4, SUM(AMT5) 
AMT_5, SUM(AMT6) AMT_6, SUM(AMT7) AMT_7, SUM(AMT8) AMT_8, SUM(AMT9) AMT_9, SUM(AMT10) AMT_10, SUM(AMT11) AMT_11, SUM(AMT12) AMT_12, SUM(AMT13) 
AMT_13, SUM(AMT14) AMT_14, SUM(AMT15) AMT_15, SUM(AMT16) AMT_16, SUM(AMT17) AMT_17, SUM(AMT18) AMT_18, SUM(AMT19) AMT_19, SUM(AMT20) AMT_20, SUM(
AMT21) AMT_21, SUM(AMT22) AMT_22, SUM(AMT23) AMT_23, SUM(AMT24) AMT_24, SUM(AMT25) AMT_25, SUM(AMT26) AMT_26, SUM(AMT27) AMT_27, SUM(AMT28) AMT_28,
 SUM(AMT29) AMT_29, SUM(AMT30) AMT_30, SUM(AMT31) AMT_31 FROM (SELECT PIGMYTRAN."TRAN_ACNOTYPE", PIGMYTRAN."TRAN_ACTYPE", PIGMYTRAN."TRAN_ACNO", 
PIGMYTRAN."AGENT_ACNOTYPE", PIGMYTRAN."AGENT_ACTYPE", PIGMYTRAN."AGENT_ACNO", PIGMYTRAN."TRAN_DATE", CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  
' . $DD . ') AS FLOAT) WHEN 1 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT1, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 2 THEN 
PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT2, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 3 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 
END AMT3, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 4 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT4, CASE CAST(TO_CHAR(CAST
("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 5 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT5, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS
 FLOAT) WHEN 6 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT6, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 7 THEN PIGMYTRAN.
"TRAN_AMOUNT" ELSE 0 END AMT7, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 8 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT8, 
CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 9 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT9, CASE CAST(TO_CHAR(CAST(
"TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 10 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT10, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') 
AS FLOAT) WHEN 11 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT11, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 12 THEN 
PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT12, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 13 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0
 END AMT13, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 14 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT14, CASE CAST(TO_CHAR(
CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 15 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT15, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  
' . $DD . ') AS FLOAT) WHEN 16 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT16, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 17 THEN 
PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT17, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 18 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0
 END AMT18, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 19 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT19, CASE CAST(TO_CHAR(
CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 20 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT20, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  
' . $DD . ') AS FLOAT) WHEN 21 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT21, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 22 THEN 
PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT22, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 23 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0
 END AMT23, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 24 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT24, CASE CAST(TO_CHAR(
CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 25 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT25, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  
' . $DD . ') AS FLOAT) WHEN 26 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT26, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 27 THEN 
PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT27, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 28 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0
 END AMT28, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 29 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT29, CASE CAST(TO_CHAR(
CAST("TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 30 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT30, CASE CAST(TO_CHAR(CAST("TRAN_DATE" AS date),  
' . $DD . ') AS FLOAT) WHEN 31 THEN PIGMYTRAN."TRAN_AMOUNT" ELSE 0 END AMT31 FROM PIGMYTRAN WHERE PIGMYTRAN."AGENT_ACNOTYPE" = ' . $AG . ' AND PIGMYTRAN.
"ENTRY_TYPE" = ' . $CH . ' AND CAST(PIGMYTRAN."TRAN_DATE" AS date) >= TO_DATE(' . $sdate . ',  ' . $dateformate . ') AND CAST(PIGMYTRAN."TRAN_DATE" AS date) <= 
TO_DATE(' . $date . ',  ' . $dateformate . ') AND PIGMYTRAN."AGENT_ACNO" = ' . $schemeAccountNo . ' UNION ALL SELECT PIGMYCHARTMASTER."TRAN_ACNOTYPE", 
PIGMYCHARTMASTER."TRAN_ACTYPE", PIGMYCHARTMASTER."TRAN_BANKACNO", PIGMYCHART."AGENT_ACNOTYPE", PIGMYCHART."AGENT_ACTYPE", PIGMYCHART.
"AGENT_BANKACNO", PIGMYCHART."TRAN_DATE", CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 1 THEN PIGMYCHARTMASTER.
"TRAN_AMOUNT" ELSE 0 END AMT1, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 2 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" 
ELSE 0 END AMT2, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 3 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END 
AMT3, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 4 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT4, CASE 
CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 5 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT5, CASE CAST(TO_CHAR(
CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 6 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT6, CASE CAST(TO_CHAR(CAST(
PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 7 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT7, CASE CAST(TO_CHAR(CAST(PIGMYCHART.
"TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 8 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT8, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS
 date),  ' . $DD . ') AS FLOAT) WHEN 9 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT9, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ')
 AS FLOAT) WHEN 10 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT10, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) 
WHEN 11 THEN PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT11, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 12 THEN
 PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT12, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 13 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT13, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 14 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT14, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 15 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT15, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 16 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT16, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 17 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT17, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 18 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT18, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 19 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT19, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 20 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT20, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 21 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT21, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 22 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT22, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 23 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT23, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 24 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT24, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 25 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT25, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 26 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT26, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 27 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT27, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 28 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT28, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 29 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT29, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 30 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT30, CASE CAST(TO_CHAR(CAST(PIGMYCHART."TRAN_DATE" AS date),  ' . $DD . ') AS FLOAT) WHEN 31 THEN 
PIGMYCHARTMASTER."TRAN_AMOUNT" ELSE 0 END AMT31 FROM PIGMYCHARTMASTER LEFT JOIN PIGMYCHART ON PIGMYCHARTMASTER."PIGMYCHARTID" = PIGMYCHART.ID WHERE
 CAST(PIGMYCHART."TRAN_DATE" AS date) >= TO_DATE(' . $date . ',' . $dateformate . ') AND CAST(PIGMYCHART."TRAN_DATE" AS date) <= TO_DATE(' . $sdate . ',' . $dateformate . ') AND PIGMYCHART."AGENT_BANKACNO" = ' . $schemeAccountNo . '  ) TABLE1 LEFT JOIN DPMASTER ON DPMASTER."BANKACNO"=TABLE1."AGENT_ACNO" LEFT JOIN
 SCHEMAST ON SCHEMAST.ID=TABLE1."AGENT_ACTYPE" LEFT JOIN PGMASTER ON PGMASTER."BANKACNO"=TABLE1."TRAN_ACNO" GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE"
, "TRAN_ACNO", TABLE1."AGENT_ACNOTYPE", TABLE1."AGENT_ACTYPE", TABLE1."AGENT_ACNO", DPMASTER."AC_NAME", SCHEMAST."S_APPL",SCHEMAST."S_NAME", 
PGMASTER."AC_NAME" ORDER BY "TRAN_ACNO" ';


// echo $query;


$sql =  pg_query($conn, $query);

$i = 0;

//  $GRAND_TOTAL = 0;

//  if (pg_num_rows($sql) == 0) {
//      include "errormsg.html";
//  }else {
//      while($row = pg_fetch_assoc($sql)){ 

//          $GRAND_TOTAL = $GRAND_TOTAL + $row['depositeamt'];
$total = 0;
$total2 = 0;
$total3 = 0;
$grtotal = 0;

$col1total = 0;
$col2total = 0;
$col3total = 0;
$col4total = 0;
$col5total = 0;
$col6total = 0;
$col7total = 0;
$col8total = 0;
$col9total = 0;
$col10total = 0;

$cttotal = 0;

$col11total = 0;
$col12total = 0;
$col13total = 0;
$col14total = 0;
$col15total = 0;
$col16total = 0;
$col17total = 0;
$col18total = 0;
$col19total = 0;
$col20total = 0;

$ct2total = 0;

$col21total = 0;
$col22total = 0;
$col23total = 0;
$col24total = 0;
$col25total = 0;
$col26total = 0;
$col27total = 0;
$col28total = 0;
$col29total = 0;
$col30total = 0;
$col31total = 0;

$ct3total = 0;

$finalgrtotal = 0;







// $total2 = 
while ($row = pg_fetch_assoc($sql)) {
    $total =  $row['amt_1'] + $row['amt_2'] + $row['amt_3'] + $row['amt_4'] + $row['amt_5'] + $row['amt_6'] + $row['amt_7'] + $row['amt_8'] + $row['amt_9'] + $row['amt_10']+ $row['amt_11']+ $row['amt_12'] + $row['amt_13']+ $row['amt_14']+ $row['amt_15'];
    $total3 =    $row['amt_16'] + $row['amt_17'] + $row['amt_18'] + $row['amt_19'] + $row['amt_20']+
    $row['amt_21'] + $row['amt_22'] + $row['amt_23'] + $row['amt_24'] + $row['amt_25'] + $row['amt_26'] + $row['amt_27'] + $row['amt_28'] + $row['amt_29'] + $row['amt_30'] + $row['amt_31'];

    // $grtotal =  $total + $total2 + $total3;
    $grtotal =  $total + $total3;


    $col1total = $col1total + $row['amt_1'];
    $col2total = $col2total + $row['amt_2'];
    $col3total = $col3total + $row['amt_3'];
    $col4total = $col4total + $row['amt_4'];
    $col5total = $col5total + $row['amt_5'];
    $col6total = $col6total + $row['amt_6'];
    $col7total = $col7total + $row['amt_7'];
    $col8total = $col8total + $row['amt_8'];
    $col9total = $col9total + $row['amt_9'];
    $col10total = $col10total + $row['amt_10'];

    $cttotal = $cttotal + $total;


    $col11total = $col11total + $row['amt_11'];
    $col12total = $col12total + $row['amt_12'];
    $col13total = $col13total + $row['amt_13'];
    $col14total = $col14total + $row['amt_14'];
    $col15total = $col15total + $row['amt_15'];
    $col16total = $col16total + $row['amt_16'];
    $col17total = $col17total + $row['amt_17'];
    $col18total = $col18total + $row['amt_18'];
    $col19total = $col19total + $row['amt_19'];
    $col20total = $col20total + $row['amt_20'];

    // $ct2total = $ct2total + $total2;

    $col21total = $col21total + $row['amt_21'];
    $col22total = $col22total + $row['amt_22'];
    $col23total = $col23total + $row['amt_23'];
    $col24total = $col24total + $row['amt_24'];
    $col25total = $col25total + $row['amt_25'];
    $col26total = $col26total + $row['amt_26'];
    $col27total = $col27total + $row['amt_27'];
    $col28total = $col28total + $row['amt_28'];
    $col29total = $col29total + $row['amt_29'];
    $col30total = $col30total + $row['amt_30'];
    $col31total = $col31total + $row['amt_31'];

    $ct3total = $ct3total + $total3;

    $finalgrtotal = $finalgrtotal + $grtotal;

    $date = str_replace("'", "", $date);



    $tmp = [



        'agno_name' => $row['AGENT_ACNO'] . ' ' . $row['AC_NAME'],
        'date' => $date,
        'bankName' => $bankName,
        'scheme' => $row['S_APPL'] . ' ' . $row['S_NAME'],
        // 'scheme'=>$scheme,
        'branchName' => $branchName,
        'acno' => $row['TRAN_ACNO'],
        'name' => $row['PIGMYNAME'],
        '1st' => $row['amt_1'],
        '2nd' => $row['amt_2'],
        '3rd' => $row['amt_3'],
        '4th' => $row['amt_4'],
        '5th' => $row['amt_5'],
        '6th' => $row['amt_6'],
        '7th' => $row['amt_7'],
        '8th' => $row['amt_8'],
        '9th' => $row['amt_9'],
        '10th' => $row['amt_10'],
        'total' => sprintf("%.2f",($total+ 0.0)),              
        '11th' => $row['amt_11'],
        '12th' => $row['amt_12'],
        '13th' => $row['amt_13'],
        '14th' => $row['amt_14'],
        '15th' => $row['amt_15'],
        '16th' => $row['amt_16'],
        '17th' => $row['amt_17'],
        '18th' => $row['amt_18'],
        '19th' => $row['amt_19'],
        '20' => $row['amt_20'],
        // 'totl' => sprintf("%.2f",($total2+ 0.0)),
        '21' => $row['amt_21'],
        '22' => $row['amt_22'],
        '23' => $row['amt_23'],
        '24' => $row['amt_24'],
        '25' => $row['amt_25'],
        '26' => $row['amt_26'],
        '27' => $row['amt_27'],
        '28' => $row['amt_28'],
        '29' => $row['amt_29'],
        '30' => $row['amt_30'],
        '31' => $row['amt_31'],

        '1sttotal' => sprintf("%.2f",($col1total+ 0.0)),
        '2ndtotal' => $col2total,
        '3rdtotal' => $col3total,
        '4thtotal' => $col4total,
        '5thtotal' => $col5total,
        '6thtotal' => $col6total,
        '7thtotal' => $col7total,
        '8thtotal' => $col8total,
        '9thtotal' => $col9total,
        '10thtotal' => $col10total,
        'cttotal' => sprintf("%.2f",($cttotal+ 0.0)),
        '11thtotal' => $col11total,
        '12thtotal' => $col12total,
        '13thtotal' => $col13total,
        '14thtotal' => $col14total,
        '15thtotal' => $col15total,
        '16thtotal' => $col16total,
        '17total' => $col17total,
        '18total' => $col18total,
        '19total' => $col19total,
        '20total' => $col20total,
        // 'tc1total' => $ct2total,
        '21total' => $col21total,
        '22total' => $col22total,
        '23total' => $col23total,
        '24total' => $col24total,
        '25total' => $col25total,
        '26total' => $col26total,
        '27total' => $col27total,
        '28total' => $col28total,
        '29total' => $col29total,
        '30total' => $col30total,
        '31total' => $col31total,

        'tc2total' => sprintf("%.2f",($ct3total+ 0.0)),
        'cgtotal' =>  sprintf("%.2f",($finalgrtotal+ 0.0)),

        'tottal' => sprintf("%.2f",( $total3+ 0.0)),
        'grtotal' => sprintf("%.2f",($grtotal+ 0.0)), 
    ];
    $data[$i] = $tmp;
    // print_r($data[$i]);
    $i++;
}

ob_end_clean();

$config = ['driver' => 'array', 'data' => $data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');
