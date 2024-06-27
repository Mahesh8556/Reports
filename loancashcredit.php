<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Loan_Cash_Credit_Expiry_List.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";


$LIST_DATE = $_GET['LIST_DATE'];
$NAME  = $_GET['NAME'];
$TRANDRCR = $_GET['TRANDRCR'];
$SDATE = $_GET['SDATE'];
$ACACNOTYPE = $_GET['ACACNOTYPE'];
$ACTYPE = $_GET['ACTYPE'];
$TRANSTATUS = $_GET['TRANSTATUS'];


$query = 'SELECT  LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."BANKACNO", LNMASTER."AC_NAME", LNMASTER."AC_SANCTION_AMOUNT" 												
, LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT", LNMASTER."AC_EXPIRE_DATE"  												
, SCHEMAST."S_NAME" SCHEME_NAME , VWTMPZBALANCEXPIRY.CLOSING_BALANCE,IDMASTER."AC_MOBILENO",IDMASTER."AC_PHONE_OFFICE",IDMASTER."AC_PHONE_RES" 												
FROM LNMASTER
LEFT OUTER JOIN(
SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT" 	
              , (COALESCE(CASE LNMASTER."AC_OP_CD"  WHEN '.$TRANDRCR.' THEN  CAST(LNMASTER."AC_OP_BAL" AS FLOAT)  ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END,0) + COALESCE(LOANTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE	
              ,  (COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$TRANDRCR.' THEN CAST(LNMASTER."AC_PAYBLEINT_OP" AS FLOAT) ELSE (-1) * CAST(LNMASTER."AC_PAYBLEINT_OP" AS FLOAT) END,0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 	
                    + COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$TRANDRCR.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT) ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT) END,0) + COALESCE(LOANTRAN.OTHER10_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT 	
  FROM LNMASTER
       LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("TRAN_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) TRAN_AMOUNT 	
             , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END),0) RECPAY_INT_AMOUNT 	
             , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("OTHER10_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)  END),0) OTHER10_AMOUNT  FROM LOANTRAN 	
              WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$LIST_DATE.' AS DATE)	
              GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) LOANTRAN ON LOANTRAN."TRAN_ACNO" = LNMASTER."BANKACNO"
        LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
     COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("TRAN_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) DAILY_AMOUNT 	
              , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END),0) DAILY_RECPAY_INT_AMOUNT  	
              , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$TRANDRCR.' THEN  CAST("OTHER10_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)  END),0) DAILY_OTHER10_AMOUNT  	
              FROM DAILYTRAN WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$LIST_DATE.' AS DATE)	
            AND "TRAN_STATUS" = '.$TRANSTATUS.' 	
              GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"	
        ) DAILYTRAN ON DAILYTRAN."TRAN_ACNO" = LNMASTER."BANKACNO"
    WHERE 
  ((LNMASTER."AC_OPDATE" IS NULL) OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= CAST('.$LIST_DATE.' AS DATE)))	
         AND (LNMASTER."AC_CLOSEDT" IS NULL) OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$LIST_DATE.' AS DATE))

)VWTMPZBALANCEXPIRY ON VWTMPZBALANCEXPIRY."BANKACNO" = LNMASTER."BANKACNO"
INNER JOIN SCHEMAST ON SCHEMAST.id = LNMASTER."AC_TYPE" 
INNER JOIN IDMASTER ON IDMASTER.id = LNMASTER."idmasterID" 												
WHERE VWTMPZBALANCEXPIRY.CLOSING_BALANCE <> 0  												
AND LNMASTER."AC_ACNOTYPE" ='.$ACACNOTYPE.' 
AND LNMASTER."AC_TYPE" ='.$ACTYPE.' 
AND CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) >= CAST('.$SDATE.' AS DATE)  
AND CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) <= CAST('.$LIST_DATE.' AS DATE)

';

//echo $query;



          
$sql =  pg_query($conn,$query);

$i = 0;

$SCHEME_TOTAL= 0;
$CITY_TOTAL = 0;
$SLBAL_TOTAL = 0;
$CLBAL_TOTAL = 0;
$SR_NO = 1;

if ($row['balance'] < 0) {
  $netType = 'Cr';
} else {
  $netType = 'Dr';
}
if ($row['SLBAL_TOTAL'] < 0) {
  $netType = 'Cr';
} else {
  $netType = 'Dr';
}

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $SCHEME_TOTAL = $SCHEME_TOTAL + $row["AC_SANCTION_AMOUNT"];
    $CITY_TOTAL=$CITY_TOTAL + $row["AC_SANCTION_AMOUNT"];
    $SLBAL_TOTAL=$SLBAL_TOTAL + $row["closing_balance"];
    $CLBAL_TOTAL = $CLBAL_TOTAL + $row["closing_balance"];

    $tmp=[
        "SR_NO" => $SR_NO++,
        "AC_NO" => $row["AC_NO"],
        "Scheme" => $row["scheme_name"],
        "AC_NAME" => $row["AC_NAME"],
        "AC_OPDATE" => $row["AC_OPDATE"],
        "AC_EXPIRE_DATE" => $row["AC_EXPIRE_DATE"],
        "AC_SANCTION_AMOUNT" => sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))),
        "SCHEME_TOTAL" => sprintf("%.2f",($SCHEME_TOTAL) + 0.0 ),
        "CITY_TOTAL" => sprintf("%.2f",($CITY_TOTAL) + 0.0 ),
        // "SCHEME_TOTAL" =>  $SCHEME_TOTAL ,
        // "CITY_TOTAL" => $CITY_TOTAL,
        "closing_balance"=>sprintf("%.2f", (abs($row['closing_balance']))).' '.$netType,
        "SLBAL_TOTAL" => sprintf("%.2f",($SLBAL_TOTAL) + 0.0 ).' '.$netType,
        "CLBAL_TOTAL" => sprintf("%.2f",($CLBAL_TOTAL) + 0.0 ).' '.$netType,
        "LIST_DATE" => $LIST_DATE,
        "TRANDRCR" => $TRANDRCR,
        "SDATE" => $SDATE,
        "ACACNOTYPE" => $ACACNOTYPE,
        "ACTYPE" => $ACTYPE,
        "TRANSTATUS"=>$TRANSTATUS,
        "NAME" => $NAME,
        "City" => 'kolhpaur'
        
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
      ->setDataSource($config)
      ->export('Pdf');
    
// }   
?>
