<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Short Balance General Ledger Accounts List.jrxml';

$data = [];
// $faker = Faker\Factory::create('en_US');



$bankname = $_GET['bankname'];
$Branch_name = $_GET['Branch_name'];
$date = $_GET['date'];

$d="'D'";
$gl="'GL'";
$cs="'CS'";
$dd="'DD/MM/YYYY'";
$transtatus="'1'";
$sappl="'980'";
$ac_type="'10'";


$Branch_name1 = str_replace("'", "", $Branch_name);
$bankname1 = str_replace("'", "", $bankname);
$date1 = str_replace("'", "", $date);
// $edate1 = str_replace("'", "", $edate);

$query='SELECT "S_APPL", "AC_NO", "CLOSING_BALANCE", SCHEMAST."S_NAME" FROM 
(SELECT "AC_ACNOTYPE",  CAST("AC_TYPE" AS INT) , "AC_NO", "AC_OPDATE", "AC_CLOSEDT",							
	      (COALESCE(CASE "AC_OP_CD"  WHEN '.$d.' THEN  CAST("AC_OP_BAL" AS FLOAT)  ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT) END,0) + COALESCE(CAST(ACCOTRAN."TRAN_AMOUNT" AS FLOAT),0) + 
			COALESCE(CAST(DAILYTRAN."DAILY_AMOUNT" AS FLOAT),0) + COALESCE(CAST(CASHAMT."CASH_AMOUNT" AS FLOAT),0) ) "CLOSING_BALANCE" , 0 "RECPAY_INT_AMOUNT"							
	     FROM ACMASTER 
 LEFT OUTER JOIN							
	           (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE","TRAN_ACNO",
			COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN   CAST("TRAN_AMOUNT" AS FLOAT)  
			 ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) "TRAN_AMOUNT" 							
       FROM ACCOTRAN WHERE "TRAN_ACNOTYPE" = '.$gl.'							
       AND CAST("TRAN_DATE" AS DATE)<= TO_DATE('.$date.', '.$dd.') 
	   AND NOT ( CAST("TRAN_DATE" AS DATE)= TO_DATE('.$date.', '.$dd.') 
  	  AND COALESCE(CAST("CLOSING_ENTRY" AS INT),0) <> 0 ) 							
	                       GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"	
	           ) ACCOTRAN ON ACMASTER."AC_NO" =  ACCOTRAN."TRAN_ACNO" 
	LEFT OUTER JOIN							
	           (SELECT '.$gl.' "TRAN_ACNOTYPE", "tran_glactype" "TRAN_ACTYPE" , "TRAN_GLACNO" "TRAN_ACNO", 
   					COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  CAST("TRAN_AMOUNT" AS FLOAT) 
				ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)  END),0) "DAILY_AMOUNT" 							
	                 FROM VWDETAILDAILYTRAN WHERE "tran_glactype" = '.$sappl.'							
	                       AND CAST("TRAN_DATE" AS DATE)<= TO_DATE('.$date.', '.$dd.')							
	                       AND "TRAN_STATUS" = '.$transtatus.'							
	                       GROUP BY "tran_glactype","TRAN_GLACNO"	
			   ) DAILYTRAN ON ACMASTER."AC_NO" = CAST(DAILYTRAN."TRAN_ACNO" AS INT)
  LEFT OUTER JOIN							
	         ( SELECT '.$gl.' "TRAN_ACNOTYPE", '.$sappl.' "TRAN_ACTYPE", 1 "TRAN_ACNO", 
 			(COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  (-1) * CAST("TRAN_AMOUNT" AS FLOAT)  ELSE CAST("TRAN_AMOUNT" AS FLOAT) END),0)) "CASH_AMOUNT" 							
	                 FROM VWDETAILDAILYTRAN 
					 WHERE "TRAN_TYPE" = '.$cs.'   AND 
					 CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$date.', '.$dd.')							
	                  AND "TRAN_STATUS" = '.$transtatus.'	) CASHAMT ON ACMASTER."AC_NO" =  CASHAMT."TRAN_ACNO" 
                    Where  ((ACMASTER."AC_OPDATE" IS NULL) OR (CAST(ACMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$date.','.$dd.')))							
	            AND ((ACMASTER."AC_CLOSEDT" IS NULL) OR (CAST(ACMASTER."AC_CLOSEDT" AS DATE)> To_DATE('.$date.','.$dd.')))							
	            AND ACMASTER."AC_ACNOTYPE"  = '.$gl.'							
	            AND ACMASTER."AC_TYPE" = '.$ac_type.'
) VWTMPZBALANCE,SCHEMAST WHERE  "CLOSING_BALANCE" <> 0 
	AND "S_APPL" = '.$sappl.' 
	AND  VWTMPZBALANCE."AC_TYPE"=SCHEMAST."id"   ';
// echo $query;
$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql)){
      
    $tmp=[
        'scheme' => $row['S_NAME'],
        'Ac_no' => $row['AC_NO'],
        'closing_balance' => $row['CLOSING_BALANCE'],
      
        
        'Branch_name' => $Branch_name1,
        'date' => $date1,
        'bankname' => $bankname1,
        // "total" => sprintf("%.2f", (abs($GRAND_TOTAL1+0.0))),
        // 'scheme' => $row["S_APPL"].' '. $row['S_NAME'],
        
       
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