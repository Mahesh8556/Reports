<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CLERKWISE.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');


$branchname = $_GET['branchname'];
$AC_TYPE = $_GET['AC_TYPE'];
$AC_RECOVERY_CLERK = $_GET['AC_RECOVERY_CLERK'];
$sdate = $_GET['sdate'];
$trand="'D'";
$TRAN_STATUS=" 'PS'";





$query = 'SELECT
SCHEMAST."S_NAME", RECOVERYCLEARKMASTER."NAME" CLERK_NAME, LNMASTER."AC_OPDATE", LNMASTER."AC_EXPIRE_DATE", VWRECOVERYCLERKWISELOANLIST."AC_ACNOTYPE",VWRECOVERYCLERKWISELOANLIST."AC_TYPE",
VWRECOVERYCLERKWISELOANLIST."AC_NO",CLOSING_BALANCE,LNMASTER."AC_NAME",GUARANTERDETAILS."AC_NAME" gname, customeraddress."AC_CTCODE" 
FROM ( SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT"
 , (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trand.' THEN cast(LNMASTER."AC_OP_BAL" as integer)  ELSE (-1) * cast(LNMASTER."AC_OP_BAL" as integer) END ,0) +
    coalesce(LOANTRAN.TRAN_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE
 ,  (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trand.' THEN LNMASTER."AC_RECBLEINT_OP"  ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END ,0) + coalesce(LOANTRAN.RECPAY_INT_AMOUNT,0) 
     + coalesce(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 
+ coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trand.' THEN  cast(LNMASTER."AC_RECBLEODUEINT_OP" as integer)ELSE (-1) * cast(LNMASTER."AC_RECBLEODUEINT_OP" as integer)END ,0) 
     + coalesce(LOANTRAN.OTHER10_AMOUNT,0) + 
     coalesce(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT 
    FROM lnmaster,
         ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trand.' THEN  cast("TRAN_AMOUNT" as integer)  ELSE (-1) * cast("TRAN_AMOUNT" as integer) END ),0) TRAN_AMOUNT 
, coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trand.' THEN  "RECPAY_INT_AMOUNT"   ELSE (-1) * "RECPAY_INT_AMOUNT" END ),0) RECPAY_INT_AMOUNT 
, coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trand.' THEN  "OTHER10_AMOUNT"   ELSE (-1) * "OTHER10_AMOUNT" END ),0) OTHER10_AMOUNT  FROM LOANTRAN 
WHERE cast("TRAN_DATE" as date) <= CAST('.$sdate.' AS DATE)
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) LOANTRAN,
          ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trand.' THEN  cast("TRAN_AMOUNT" as integer)  ELSE (-1) * cast("TRAN_AMOUNT" as integer) END),0) DAILY_AMOUNT 
                , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trand.' THEN  cast("RECPAY_INT_AMOUNT" as integer) ELSE (-1) * cast("RECPAY_INT_AMOUNT" as integer) END),0) DAILY_RECPAY_INT_AMOUNT  
                , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trand.' THEN  cast("OTHER10_AMOUNT" as integer)  ELSE (-1) * cast("OTHER10_AMOUNT" as integer)   END),0) DAILY_OTHER10_AMOUNT  
                FROM DAILYTRAN WHERE cast("TRAN_DATE" as date)<= CAST('.$sdate.' AS DATE)
                AND "TRAN_STATUS" =  '.$TRAN_STATUS.' 
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
          ) DAILYTRAN   Where LNMASTER."AC_ACNOTYPE"  = LOANTRAN."TRAN_ACNOTYPE"
           AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as integer)
           AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as integer)
           AND LNMASTER."AC_ACNOTYPE"  = DAILYTRAN."TRAN_ACNOTYPE"
           AND LNMASTER."AC_TYPE"  =  cast(DAILYTRAN."TRAN_ACTYPE" as integer)
           AND LNMASTER."AC_NO" =  cast(DAILYTRAN."TRAN_ACNO" as bigint)
           AND ((LNMASTER."AC_OPDATE" IS NULL) OR (cast(LNMASTER."AC_OPDATE" as date) <= CAST('.$sdate.' AS DATE)))
           AND ((LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date) > CAST('.$sdate.' AS DATE)))
)vwrecoveryclerkwiseloanlist, schemast, recoveryclearkmaster, lnmaster
LEFT OUTER JOIN guaranterdetails ON (LNMASTER."AC_ACNOTYPE" = GUARANTERDETAILS."AC_ACNOTYPE" AND cast(LNMASTER."AC_TYPE" as character varying) = GUARANTERDETAILS."AC_TYPE" AND cast(LNMASTER."AC_NO" as character varying) = GUARANTERDETAILS."AC_NO")
INNER JOIN idmaster i ON (lnmaster."idmasterID" = i.id)
INNER JOIN customeraddress ON (i.id = customeraddress."idmasterID")
WHERE SCHEMAST."S_ACNOTYPE" = LNMASTER."AC_ACNOTYPE" AND SCHEMAST."S_APPL" = LNMASTER."AC_TYPE" AND LNMASTER."AC_ACNOTYPE"=VWRECOVERYCLERKWISELOANLIST."AC_ACNOTYPE" 
AND LNMASTER."AC_TYPE"=VWRECOVERYCLERKWISELOANLIST."AC_TYPE" AND LNMASTER."AC_NO"=VWRECOVERYCLERKWISELOANLIST."AC_NO"   
and ("AC_EXPIRE_DATE" is null or cast("AC_EXPIRE_DATE" as date) > CAST('.$sdate.' AS DATE)) AND cast(LNMASTER."AC_RECOVERY_CLERK" as integer) = RECOVERYCLEARKMASTER."CODE"
AND (LNMASTER."AC_CLOSEDT" IS NULL OR cast(LNMASTER."AC_CLOSEDT" as date) > CAST('.$sdate.' AS DATE)) AND CLOSING_BALANCE > 0 
AND LNMASTER."AC_TYPE" ='.$AC_TYPE.' AND LNMASTER."AC_RECOVERY_CLERK" = '.$AC_RECOVERY_CLERK.'';
     
$sql =  pg_query($conn,$query);
$i = 0;
$grandtotal = 0;


while($row = pg_fetch_assoc($sql)){
    $grandtotal = $grandtotal + $row["closing_balance"];
    
    $tmp=[
    
         "actype" => $row['TRAN_ACTYPE'],
        "acno" => $row["AC_NO"],
        "Gname" => $row["gname"],
        "Acname" => $row["AC_NAME"],
        "sname" => $row['S_NAME'],
        "opdate" => $row['AC_OPDATE'],
        "edate" => $row['AC_EXPIRE_DATE'],
        "closingbalance" =>  $row["closing_balance"],
        "clerkname" => $row["clerk_name"],
        "total" => $grandtotal ,
        "branchname" => $branchname,
        "sdate" => $sdate,
        "AC_RECOVERY_CLERK" => $AC_RECOVERY_CLERK,
        "AC_TYPE" => $AC_TYPE,

    ];
    $data[$i]=$tmp;
    $i++;
  
}
ob_end_clean();
   
$config = ['driver'=>'array','data'=>$data];
//  print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
  
   
?>