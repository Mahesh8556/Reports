<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/div.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');


$Branchname = $_GET['Branchname'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$TRAN_ACTYPE=$_GET['TRAN_ACTYPE'];
$TRAN_ACNOTYPE="'SH'";
$TRAN_STATUS="'PS'";


$query = '
SELECT "AC_ACNOTYPE" , "AC_TYPE" , "AC_NO" , "AC_NAME"  , TRANTABLE."TRAN_DATE" , TRANTABLE."DIVIDEND_AMOUNT", TRANTABLE."BONUS_AMOUNT"
, TRANTABLE."USER_CODE"  FROM SHMASTER , ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" 
,   "OTHER1_AMOUNT" "DIVIDEND_AMOUNT", CAST("OTHER2_AMOUNT" AS INTEGER) "BONUS_AMOUNT", "USER_CODE"  FROM DAILYTRAN 
   WHERE CAST("DIVIDEND_ENTRY" AS INTEGER) <> 0 AND "TRAN_STATUS" = '.$TRAN_STATUS.'  AND DAILYTRAN."TRAN_ACNOTYPE" ='.$TRAN_ACNOTYPE.' AND DAILYTRAN."TRAN_ACTYPE" ='.$TRAN_ACTYPE.' 
   AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) >=  cast('.$sdate.' As date) AND   CAST(DAILYTRAN."TRAN_DATE" AS DATE) <=  cast('.$edate.' As date)
 
UNION ALL
 SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" 
, "TRAN_AMOUNT" "DIVIDEND_AMOUNT", "BONUS_AMOUNT" , "USER_CODE"  FROM  DIVPAIDTRAN WHERE CAST("TRAN_ACNO" AS INTEGER) <> 0  
                                          AND  DIVPAIDTRAN."TRAN_ACNOTYPE" ='.$TRAN_ACNOTYPE.' AND DIVPAIDTRAN."TRAN_ACTYPE" ='.$TRAN_ACTYPE.'
                                          AND CAST(DIVPAIDTRAN."TRAN_DATE" AS DATE) >= cast('.$sdate.' As date)
                                          AND CAST(DIVPAIDTRAN."TRAN_DATE" AS DATE) <= cast('.$edate.' As date)
  ) TRANTABLE WHERE SHMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE" 
    AND CAST(SHMASTER."AC_TYPE" AS character varying)= TRANTABLE."TRAN_ACTYPE" 
    AND CAST(SHMASTER."AC_NO" AS character varying) = TRANTABLE."TRAN_ACNO"  AND ( SHMASTER."AC_OPDATE" IS NULL OR CAST(SHMASTER."AC_OPDATE" AS DATE) <= cast('.$edate.' As date))
    AND SHMASTER."AC_ACNOTYPE" ='.$TRAN_ACNOTYPE.' AND SHMASTER."AC_TYPE" ='.$TRAN_ACTYPE.'

';
     echo $query;
$sql =  pg_query($conn,$query);
$i = 0;
$grandtotal = 0;
$grandtotal1 = 0;
$grandtotal2 = 0;

while($row = pg_fetch_assoc($sql)){
    $grandtotal = $grandtotal + $row["dtotal"];
    $grandtotal1 = $grandtotal1 + $row["ototal"];
    $grandtotal2 = $grandtotal2 + $row["total"];
    $tmp=[
    
         "acno" => $row['AC_NO'],
        "acname" => $row["AC_NAME"],
        "trandate" => $row["TRAN_DATE"],
        "divamount" => $row["DIVIDEND_AMOUNT"],
        "bonusamount" => $row['BONUS_AMOUNT'],
        "total" =>  $row["DIVIDEND_AMOUNT"]+$row['BONUS_AMOUNT'],
        "usercode" => $row["USER_CODE"],
        "dtotal" => $grandtotal ,
        "ototal" => $grandtotal1,
        "gtotal" => $grandtotal2,
        "sdate" => $sdate,
        "edate" => $edate,
        "Branchname" => $Branchname,

    ];
    $data[$i]=$tmp;
    $i++;
  
}
// ob_end_clean();
   
// $config = ['driver'=>'array','data'=>$data];
// //  print_r($data);
// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//      ->setDataSource($config)
//      ->export('Pdf');
  
   
?>