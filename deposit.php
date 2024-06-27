<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

// set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/deposit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");

// variables
 $print_date = $_GET['print_date'];
 $ac_type = $_GET['ac_type'];
 $ac_acnotype = $_GET['ac_acnotype'];
 $sdate = $_GET['sdate'];
 $edate = $_GET['edate'];
 $bank_name = $_GET['bank_name'];
 $branch =$_GET['branch'];
 $BRANCH_CODE = $_GET['BRANCH_CODE'];

 $branch = str_replace("'" , "" , $branch);
// $AC_DAYS = str_replace("'" , "" , $AC_DAYS);
$ac_type1 = str_replace("'", "", $ac_type);
$bank_name = str_replace("'", "", $bank_name);
$sdate = str_replace("'", "", $sdate);
$edate = str_replace("'", "", $edate);
$print_date1 = str_replace("'" , "" , $print_date);
$ac_acnotype0 = str_replace("'" , "" , $ac_acnotype);

// $schemecode = "'TD'";
$dateformat ="'DD/MM/YYYY'";
// $ac_type ="'15'";

$query ='SELECT TDRECEIPTISSUE."PRINT_DATE",
'.$print_date.' "DATE",
TDRECEIPTISSUE."PRINT_TIME",
TDRECEIPTISSUE."REASON_OF_DUPLICATE",
TDRECEIPTISSUE."RECEIPT_NO",
DPMASTER."AC_NAME",
CUSTOMERADDRESS."AC_HONO",
CUSTOMERADDRESS."AC_WARD",
CUSTOMERADDRESS."AC_ADDR",
DPMASTER."AC_NO",
CUSTOMERADDRESS."AC_GALLI",
DPMASTER."AC_ASON_DATE",
DPMASTER."AC_SCHMAMT",
DPMASTER."AC_MONTHS",
DPMASTER."AC_DAYS",
DPMASTER."AC_INTRATE",
DPMASTER."AC_EXPDT",
DPMASTER."AC_MATUAMT",
NOMINEELINK."AC_NNAME" NOMINEE,
DPMASTER."AC_TYPE" AS "SCHEME",
SCHEMAST."S_NAME",
SCHEMAST."S_APPL"
FROM TDRECEIPTISSUE,
DPMASTER
INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.ID
LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = DPMASTER."idmasterID"
LEFT OUTER JOIN NOMINEELINK ON DPMASTER."id" = NOMINEELINK."DPMasterID"
WHERE TDRECEIPTISSUE."AC_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"
AND CAST(TDRECEIPTISSUE."AC_TYPE" AS integer) = DPMASTER."AC_TYPE"
AND CAST(TDRECEIPTISSUE."AC_NO" AS BIGINT) = CAST(DPMASTER."BANKACNO" AS BIGINT)
AND DPMASTER."AC_ACNOTYPE" = '.$ac_acnotype.'
AND DPMASTER."AC_TYPE" = '.$ac_type.'
AND DPMASTER."BRANCH_CODE" = '.$BRANCH_CODE.'
AND DPMASTER."status" = 1
AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL'; 

//   echo $query;
          
$sql =  pg_query($conn,$query);

 $i = 0;
 $GRAND_TOTAL = 0;
 $GRAND_TOTAL1 = 0;
 $GRAND_TOTAL2 = 0;
 
 
 while($row = pg_fetch_assoc($sql))
 { 
     $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_SCHMAMT'];
     $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['AC_MATUAMT'];
   
     $tmp=[
        'AC_NO' => $row['AC_NO'],
        'PRINT_TIME' => $row['PRINT_TIME'],
        'REASON_OF_DUPLICATE' => $row['REASON_OF_DUPLICATE'],
        'ac_acnotype' =>$row['S_APPL'] .' '. $row['S_NAME'],
        'nominee' => $row['nominee'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_HONO'=> $row['AC_HONO'],
        'date' => $row['DATE'],
        'RECEIPT_NO' => $row['RECEIPT_NO'],
        'AC_DAYS' => $row['AC_DAYS'],
        'scheme_total1' =>  sprintf("%.2f" , ($GRAND_TOTAL1) + 0.0),
        'AC_WARD' => $row['AC_WARD'] .' '.$row['ADDR'].' '.$row['AC_GALLI'],
        'AC_ASON_DATE' =>  $row['AC_ASON_DATE'] ,
        'AC_SCHMAMT' =>   sprintf("%.2f", ($row['AC_SCHMAMT'] + 0.0)),
        'AC_MONTHS' => $row['AC_MONTHS'] ,
        'AC_INTRATE' => $row['AC_INTRATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_MATUAMT' => sprintf("%.2f", ($row['AC_MATUAMT'] + 0.0)),
        'scheme_total' => sprintf("%.2f" , ($GRAND_TOTAL) + 0.0),
        'scheme1' => sprintf("%.2f" , ($GRAND_TOTAL1)  + 0.0),
        'ac_type' => $ac_type1,
        'sdate' => $sdate,
        'bank_name' => $bank_name,
        'edate' => $edate ,
        'branch' => $branch, 
        'print_date'=>  $print_date1,

    ];
    $data[$i]=$tmp;
    $i++;   
}
ob_end_clean();
 // echo $query;

$config = ['driver'=>'array','data'=>$data];
// echo $filename;
//  print_r($data)
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
