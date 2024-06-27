<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ShareIssueRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


 //variables
$dd="'DD/MM/YYYY'";
$START_DATE = $_GET['START_DATE'];
$END_DATE = $_GET['END_DATE'];
$AC_TYPE = $_GET['AC_TYPE'];    
$BRANCH = $_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];
$dateformate = "'DD/MM/YYYY'";
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$status = '1';
$shi = "'SHI'";

$BRANCH = str_replace("'", "" , $BRANCH);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);
$END_DATE2 = str_replace("'", "" , $END_DATE);
$START_DATE1 = str_replace("'", "" , $START_DATE);


// sql query

//  $query = ' SELECT SHARETRAN."TRAN_DATE", SHMASTER."AC_NO", SHARETRAN."TRAN_AMOUNT", SHARETRAN."NO_OF_SHARES",
//  SHARETRAN."CERTIFICATE_NO", SHARETRAN."SHARES_FROM_NO", SHARETRAN."SHARES_TO_NO", 
//  SHARETRAN."SHARES_TRANSFER_DATE", SHARETRAN."SHARES_RETURN_DATE", SHARETRAN."RESULATION_DATE",
//  SHARETRAN."RESULATION_NO", SHARETRAN."TRAN_TYPE" , SHMASTER."AC_TYPE", SHMASTER."AC_NAME", SCHEMAST."S_NAME", SCHEMAST."S_APPL",
//  CITYMASTER."CITY_NAME" 
//  FROM SHARETRAN 
//  LEFT OUTER JOIN SHMASTER ON SHARETRAN."TRAN_ACNO" = SHMASTER."BANKACNO" 
//  LEFT OUTER JOIN SCHEMAST ON CAST(SHARETRAN."TRAN_ACTYPE" AS INTEGER)= SCHEMAST.ID 
//  LEFT OUTER JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = SHMASTER."idmasterID"
//  LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID 
//  WHERE SHMASTER."AC_TYPE" ='.$AC_TYPE.' 
//  AND SHMASTER."status"='.$status.' and sharetran."BRANCH_CODE"='.$BRANCH_CODE.' AND SHMASTER."SYSCHNG_LOGIN" IS NOT NULL 
//  AND CAST(SHARETRAN."TRAN_DATE" AS DATE) >= ('.$START_DATE.') AND CAST(SHARETRAN."TRAN_DATE" AS DATE) <= ('.$END_DATE.')
//  Order By SHARETRAN."CERTIFICATE_NO" ,CAST(SHARETRAN."TRAN_DATE" AS DATE)';
$query = 'SELECT SHARETRAN."TRAN_DATE",
SHMASTER."AC_NO",
SHARETRAN."TRAN_AMOUNT",
SHARETRAN."NO_OF_SHARES",
SHARETRAN."CERTIFICATE_NO",
SHARETRAN."SHARES_FROM_NO",
SHARETRAN."SHARES_TO_NO",
SHARETRAN."SHARES_TRANSFER_DATE",
SHARETRAN."SHARES_RETURN_DATE",
SHARETRAN."RESULATION_DATE",
SHARETRAN."RESULATION_NO",
SHARETRAN."TRAN_TYPE",
SHMASTER."AC_TYPE",
SHMASTER."AC_NAME",
SCHEMAST."S_NAME",
SCHEMAST."S_APPL",
CITYMASTER."CITY_NAME"
FROM SHARETRAN
LEFT OUTER JOIN SHMASTER ON SHARETRAN."TRAN_ACNO" = SHMASTER."BANKACNO"
LEFT OUTER JOIN SCHEMAST ON CAST(SHARETRAN."TRAN_ACTYPE" AS INTEGER) = SCHEMAST.ID
LEFT OUTER JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = SHMASTER."idmasterID"
LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID
WHERE SHMASTER."AC_TYPE" = '.$AC_TYPE.'
AND SHMASTER."status" = '.$status.'
AND SHARETRAN."TRAN_ENTRY_TYPE"= '.$shi.'
AND SHARETRAN."BRANCH_CODE" = '.$BRANCH_CODE.'
AND SHMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND CAST(SHARETRAN."TRAN_DATE" AS DATE) >= TO_DATE('.$START_DATE.','.$dd.')
AND CAST(SHARETRAN."TRAN_DATE" AS DATE) <= TO_DATE ('.$END_DATE.','.$dd.')
ORDER BY SHARETRAN."CERTIFICATE_NO",
CAST(SHARETRAN."TRAN_DATE" AS DATE)';
            //  echo $query; 
          
$query =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($query))
{ 

    $CERNOT=$CERNOT+ $row['CERTIFICATE_NO'];
   $tshares=$tshares+ $row['NO_OF_SHARES'];
   $tshramt=$tshramt+ $row['TRAN_AMOUNT'];

     $tmp=[
        'AC_NAME' => $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'S_NAME' => $row['S_APPL']. ' '.$row['S_NAME'],
        'CITY_NAME' => $row['CITY_NAME'],
        'TRAN_AMOUNT' => sprintf("%.2f", ($row['TRAN_AMOUNT'] + 0.0)),
        'NO_OF_SHARES' => $row['NO_OF_SHARES'],
        'RESULATION_NO' => $row['RESULATION_NO'],
        'RESULATION_DATE' => $row['RESULATION_DATE'],
        'CERTIFICATE_NO' => $row['CERTIFICATE_NO'],
        'SHARES_FROM_NO' => $row['SHARES_FROM_NO'],
        'SHARES_TO_NO' => $row['SHARES_TO_NO'],
        'SHARES_TRANSFER_DATE' => $row['SHARES_TRANSFER_DATE'],
        'START_DATE' => $START_DATE1,
        'END_DATE' => $END_DATE2,
        'BRANCH'  => $BRANCH,
        'BANK_NAME' => $BANK_NAME,
        'AC_TYPE' => $AC_TYPE, 
        'CERNOT' => $CERNOT, 
        'tshares' => $tshares, 
        'tshramt'=> sprintf("%.2f", ($tshramt + 0.0)),

        // 'tshramt' => $tshramt, 


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
//     ->export('Pdf');

?> 