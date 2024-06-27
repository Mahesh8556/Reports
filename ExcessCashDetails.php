<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ExcessCashDetails.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


 //variables

$START_DATE = $_GET['START_DATE'];
$END_DATE = $_GET['END_DATE'];
$BRANCH =$_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];


$START_DATE1 = str_replace("'", "" , $START_DATE);
$END_DATE2 = str_replace("'", "" , $END_DATE);
$BRANCH1 = str_replace("'", "" , $BRANCH);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);

$TRAN = "'D'";
$TRAN1 = "'GL'";
$AC_TYPE ="'4'";
$TRAN_ACNOTYPE = "'10'";
$TYPE = "'CS'";
$STATUS ="'PS'";

$dateformate = "'DD/MM/YYYY'";



//  $query =   'SELECT (COALESCE(CASE "AC_OP_CD" WHEN '.$TRAN.' THEN CAST("AC_OP_BAL" AS FLOAT) ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)
//              END,0) + COALESCE(CAST(ACCOTRAN.TRAN_AMOUNT AS FLOAT),0) + COALESCE(CASHAMT.CASH_AMOUNT,0))CLOSING_BALANCE 
//  FROM ACMASTER, 
//  (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$TRAN.'
//  THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) TRAN_AMOUNT 
//   FROM ACCOTRAN
//   WHERE "TRAN_ACNOTYPE" = '.$TRAN1.' AND "TRAN_ACTYPE" ='.$AC_TYPE.' AND "TRAN_ACNO" = 1 AND CAST("TRAN_DATE" AS DATE) <= cast('.$START_DATE.' as date) 
//   AND NOT  cast("TRAN_DATE" as date) = cast('.$START_DATE.' as date) AND COALESCE(CAST("CLOSING_ENTRY" AS INTEGER),CAST(0 AS INTEGER)) <> 0 ) 
//   GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") ACCOTRAN , 
//   (SELECT '.$TRAN1.' "TRAN_ACNOTYPE", '.$AC_TYPE.' "TRAN_ACTYPE", 1 "TRAN_ACNO" ,(COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$TRAN.' 
//  THEN (-1) * CAST("TRAN_AMOUNT" AS FLOAT) ELSE CAST("TRAN_AMOUNT" AS FLOAT) END),0)) CASH_AMOUNT 
//    FROM VWDETAILDAILYTRAN 
//    WHERE "TRAN_TYPE" = '.$TYPE.' AND CAST("TRAN_DATE" AS DATE) <= cast('.$START_DATE.' as date)  AND "TRAN_STATUS" = '.$STATUS.' ) CASHAMT 
//    Where ACMASTER."AC_ACNOTYPE" = ACCOTRAN."TRAN_ACNOTYPE" AND ACMASTER."AC_TYPE" = CAST(ACCOTRAN."TRAN_ACTYPE" AS INTEGER) 
//    AND ACMASTER."AC_NO" = ACCOTRAN."TRAN_ACNO" AND ACMASTER."AC_ACNOTYPE" = CASHAMT."TRAN_ACNOTYPE"
//    AND ACMASTER."AC_TYPE" = CAST(CASHAMT."TRAN_ACTYPE" AS INTEGER) AND ACMASTER."AC_NO" = CASHAMT."TRAN_ACNO"
//    AND ACMASTER."AC_ACNOTYPE" = '.$TRAN1.' AND ACMASTER."AC_TYPE" = '.$AC_TYPE.' AND ACMASTER."AC_NO" = 1';




$query='SELECT distinct
(COALESCE(
    CASE "AC_OP_CD"
        WHEN '.$TRAN.' THEN CAST("AC_OP_BAL" AS FLOAT)
        ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)
    END, 0) +
COALESCE(CAST(ACCOTRAN.TRAN_AMOUNT AS FLOAT), 0)) AS "TRAN_AMOUNT",
ACCOTRAN."TRAN_DATE",
EXCESSCASH."CASH_LIMIT",
EXCESSCASH."EXCESS_CASH" AS "SANCTIONED_CASH_LIMIT",
EXCESSCASH."REASON"
FROM
ACMASTER
JOIN
(SELECT
    "TRAN_ACNOTYPE",
    "TRAN_ACTYPE",
    "TRAN_ACNO",
    "TRAN_DATE",
    COALESCE(
        SUM(CASE "TRAN_DRCR"
            WHEN '.$TRAN.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
            ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
        END), 0) AS TRAN_AMOUNT
FROM
    ACCOTRAN
WHERE
    "TRAN_ACNOTYPE" = '.$TRAN1.'
    AND "TRAN_ACTYPE" = '.$AC_TYPE.'
    AND "TRAN_ACNO" = 1
    AND "BRANCH_CODE" = 2
    AND CAST("TRAN_DATE" AS DATE) BETWEEN TO_DATE('.$START_DATE.', '.$dateformate.')
    AND TO_DATE('.$END_DATE.', '.$dateformate.')
    AND COALESCE("CLOSING_ENTRY", 0) = 0
GROUP BY
    "TRAN_DATE",
    "TRAN_ACNOTYPE",
    "TRAN_ACTYPE",
    "TRAN_ACNO") ACCOTRAN ON ACMASTER."AC_ACNOTYPE" = ACCOTRAN."TRAN_ACNOTYPE"
AND ACMASTER."AC_TYPE" = CAST(ACCOTRAN."TRAN_ACTYPE" AS INTEGER)
AND ACMASTER."AC_NO" = ACCOTRAN."TRAN_ACNO"
JOIN
EXCESSCASH ON ACCOTRAN."TRAN_DATE" = EXCESSCASH."TRAN_DATE"
WHERE
ACMASTER."AC_ACNOTYPE" = '.$TRAN1.'
AND ACMASTER."AC_TYPE" = '.$AC_TYPE.'
AND ACMASTER."AC_NO" = 1;
';


        //  echo $query;

$query1 = ' SELECT "SANCTIONED_CASH_LIMIT" FROM SYSPARA '; 


          
$sql =  pg_query($conn,$query);
$sql1 =  pg_query($conn,$query1);
$SECTION_CASH_LIMIT = 0;
$TRAN_DATE = 0;
$TRAN_AMOUNT = 0; 
$cal=0;


while($row = pg_fetch_assoc($sql1))
{
{
    // $SECTION_CASH_LIMIT = $row['SANCTIONED_CASH_LIMIT'];  
    

}
   

$i = 0;
while($row = pg_fetch_assoc($sql))
{  

     $tmp=[
        'SANCTIONED_CASH_LIMIT' => $row['SANCTIONED_CASH_LIMIT'],
        'TRAN_DATE' => $row['TRAN_DATE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],       
        // => $row['closing_balance'],
        'START_DATE'=> $START_DATE1,
        'END_DATE' => $END_DATE2,
        'BRANCH' => $BRANCH1,  
        'BANK_NAME' => $BANK_NAME,
        'reason' => $row['REASON'],
        'CASH_LIMIT' => $row['CASH_LIMIT'],
       
     ];
    
    $data[$i]=$tmp;
    $i++;
    
}

// $tmp =[
    
//     'SANCTIONED_CASH_LIMIT' => $SECTION_CASH_LIMIT,
//     'TRAN_DATE' => $TRAN_DATE,
//     'TRAN_AMOUNT' => $TRAN_AMOUNT,
// ];
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
 //print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?> 

