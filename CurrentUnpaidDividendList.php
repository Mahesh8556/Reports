<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CurrentUnpaidDividendList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=bank user=postgres password=tushar");


 //variables
// $BANK_CODE = $_GET['BANK_CODE'];
 $PRINT_DATE = $_GET['PRINT_DATE'];
 $SCHEME_CODE = $_GET['SCHEME_CODE'];    
$BRANCH = $_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];

$PRINT_DATE = str_replace("'", "",$PRINT_DATE);
$SCHEME_CODE = str_replace("'", "", $SCHEME_CODE);
$BRANCH = str_replace("'", "", $BRANCH);
$BANK_NAME = str_replace("'", "", $BANK_NAME);

$dateformate = "'DD/MM/YYYY'";



 $query = ' SELECT DIVIDEND."ACNOTYPE", DIVIDEND."ACTYPE", DIVIDEND."AC_NO", DIVIDEND."TOTAL_SHARES_AMOUNT", DIVIDEND."DIVIDEND_AMOUNT", 
            DIVIDEND."BONUS_AMOUNT"  , DIVIDEND."MEMBER_CLOSE_DATE", SHMASTER."AC_NAME", SHMASTER."AC_SBNO" , SHMASTER."BANKACNO" , 
            DIVIDEND."DIV_TRANSFER_BRANCH"  From SHMASTER ,   
            ( SELECT "ACNOTYPE", "ACTYPE", "AC_NO", "TOTAL_SHARES_AMOUNT", "DIVIDEND_AMOUNT", "BONUS_AMOUNT" , "MEMBER_CLOSE_DATE" ,"DIV_TRANSFER_BRANCH"           
	        FROM  DIVIDEND             
	        WHERE COALESCE("AC_NO",0) <> 0
	        AND "ACTYPE" = '.$SCHEME_CODE.' 
	        AND CAST("WARRENT_DATE" AS DATE) = ('.$PRINT_DATE.')
	        AND ( "DIV_PAID_DATE" IS NULL OR "DIV_PAID_DATE" > ('.$PRINT_DATE.'))   
	        UNION ALL 
            SELECT "ACNOTYPE", "ACTYPE", "AC_NO", CAST("TOTAL_SHARES_AMOUNT" AS FLOAT),"DIVIDEND_AMOUNT","BONUS_AMOUNT",
            "MEMBER_CLOSE_DATE",CAST("DIV_TRANSFER_BRANCH" AS INTEGER)           
	        FROM  HISTORYDIVIDEND 
	        WHERE COALESCE("AC_NO",0) <> 0 
	        AND "ACTYPE" = '.$SCHEME_CODE.'
            AND CAST("WARRENT_DATE" AS DATE) = ('.$PRINT_DATE.') 
            AND ( "DIV_PAID_DATE" IS NULL OR "DIV_PAID_DATE" > ('.$PRINT_DATE.'))) DIVIDEND
            Where DIVIDEND."ACNOTYPE" = SHMASTER."AC_ACNOTYPE"    
            AND CAST(DIVIDEND."ACTYPE" AS INTEGER) = SHMASTER."AC_TYPE"    
            AND DIVIDEND."AC_NO" = SHMASTER."AC_NO"  
            Order By DIVIDEND."ACNOTYPE" , DIVIDEND."ACTYPE" ASC,DIVIDEND."AC_NO" ';

           // echo $query; 

          
$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql))
{ 

     $tmp=[
        'ACNOTYPE' => $row['ACNOTYPE'],
        'AC_NO' => $row['AC_NO'],
        'DIVIDEND_AMOUNT' => $row['DIVIDEND_AMOUNT'],
        'TOTAL_SHARES_AMOUNT' => $row['TOTAL_SHARES_AMOUNT'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'NO_OF_SHARES' => $row['NO_OF_SHARES'],
        'BONUS_AMOUNT' => $row['BONUS_AMOUNT'],
        'MEMBER_CLOSE_DATE' => $row['MEMBER_CLOSE_DATE'],
        'DIV_TRANSFER_BRANCH' => $row['DIV_TRANSFER_BRANCH'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_SBNO' => $row['AC_SBNO'],
        'BANKACNO' => $row['BANKACNO'],
        'SHARES_RETURN__DATE' => $row['SHARES_RETURN__DATE'],
        // 'NOMINEE_RELATION' => $row['NOMINEE_RELATION'],
        // 'ARTICLE_NAME' => $row['ARTICLE_NAME'],
        // 'TOTAL_WEIGHT_GMS' => $row['TOTAL_WEIGHT_GMS'],
        // 'CLEAR_WEIGHT_GMS' => $row['CLEAR_WEIGHT_GMS'],
        // 'RATE' => $row['RATE'],
        'PRINT_DATE' => $PRINT_DATE,
        'BRANCH'  => $BRANCH,
        'SCHEME_CODE' => $SCHEMECODE,
        'BANK_NAME' => $BANK_NAME,
        // 'AC_ACNOTYPE' => $AC_ACNOTYPE,
        
       
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

?> 