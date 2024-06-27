<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/insurance_due.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$S_APPL = $_GET['S_APPL'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];


$tran_status="'1'";
$AC_TYPE="'1'";
$var="'D'";


// $bankName = str_replace(''', '', $bankName);
// $stdate_ = str_replace(''', '', $stdate);
// $etdate_ = str_replace(''', '', $etdate);
//$branchName = str_replace(''', '', $branchName);


$query='SELECT SHMASTER."AC_ACNOTYPE", 
 SHMASTER."AC_TYPE", SHMASTER."BANKACNO", 
 VWTMPSHMASTERBAL.CLOSING_BALANCE TOTAL_SHARES_AMOUNT, VWTMPSHMASTERBAL.ac_membno, VWTMPSHMASTERBAL.ac_membtype,
 HISTORYDIVIDEND.DIVIDEND_AMOUNT, HISTORYDIVIDEND.BONUS_AMOUNT, 
 SHMASTER."AC_NAME" FROM SHMASTER
 
 LEFT OUTER JOIN(SELECT "ACNOTYPE", "ACTYPE", "AC_NO", 
 SUM(CAST("DIVIDEND_AMOUNT" AS FLOAT)) DIVIDEND_AMOUNT, 
 SUM("BONUS_AMOUNT") BONUS_AMOUNT 
  FROM 
 HISTORYDIVIDEND 
  Where
  (CAST("DIVIDEND_AMOUNT" AS FLOAT) + CAST("BONUS_AMOUNT" AS FLOAT) <> 0) 
 AND CAST(HISTORYDIVIDEND."WARRENT_DATE" AS DATE) <= CAST('.$stdate.' AS DATE)
 AND ( HISTORYDIVIDEND."DIV_PAID_DATE" IS NULL 
	  OR  CAST(HISTORYDIVIDEND."DIV_PAID_DATE"  AS DATE)> CAST('.$etdate.'AS DATE)) 
 GROUP BY "ACNOTYPE", "ACTYPE", "AC_NO" 
 ) HISTORYDIVIDEND
 ON CAST(SHMASTER."BANKACNO" AS FLOAT)=CAST(HISTORYDIVIDEND."AC_NO" AS FLOAT)
 
LEFT OUTER JOIN(SELECT "AC_ACNOTYPE", "AC_TYPE", "BANKACNO", "AC_OPDATE", "AC_CLOSEDT" 
                 , (COALESCE(CASE "AC_OP_CD" 
							 WHEN '.$var.' 
							 THEN  CAST("AC_OP_BAL"  AS FLOAT)
							 ELSE (-1) * CAST("AC_OP_BAL"  AS FLOAT)END,0) 
					+ COALESCE(SHARETRAN.TRAN_AMOUNT,0) 
					+ COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE, 0 RECPAY_INT_AMOUNT
,SHMASTER."AC_NO" AC_MEMBNO,SHMASTER."AC_TYPE" AC_MEMBTYPE 
FROM SHMASTER
	
LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
			COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$var.' THEN  CAST("TRAN_AMOUNT"  AS FLOAT)
						 ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) TRAN_AMOUNT 
                 FROM SHARETRAN 
			WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$stdate.' AS DATE)
                 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
           ) SHARETRAN
	ON   SHMASTER."BANKACNO" =  SHARETRAN."TRAN_ACNO"
	
	
LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",
			COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT) 
						 ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END),0) DAILY_AMOUNT 
                 From VWDETAILDAILYTRAN 
			WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$stdate.' AS DATE)
                 AND "TRAN_STATUS" = '.$tran_status.'
			     AND ref_field = '.$TRAN_AMOUNT.' 
                 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
           ) DAILYTRAN
	ON SHMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"

 
WHERE ((SHMASTER."AC_OPDATE" IS NULL)
	   OR (CAST(SHMASTER."AC_OPDATE" AS DATE) <= CAST('.$stdate.' AS DATE)))
            AND ((SHMASTER."AC_CLOSEDT" IS NULL) 
				 OR (CAST(SHMASTER."AC_CLOSEDT" AS DATE) > CAST('.$etdate.'AS DATE)))
) VWTMPSHMASTERBAL
ON SHMASTER."BANKACNO" = VWTMPSHMASTERBAL."BANKACNO" 

WHERE
(COALESCE(CLOSING_BALANCE,0) + COALESCE(DIVIDEND_AMOUNT,0) 
 + COALESCE(BONUS_AMOUNT,0)) <> 0 
 AND SHMASTER."AC_TYPE" = '.$AC_TYPE.' 
 ORDER BY SHMASTER."AC_ACNOTYPE", 
 SHMASTER."AC_TYPE" , SHMASTER."BANKACNO" ';


//echo $query;
$sql =pg_query($conn,$query);
$i = 0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
            
            'ac_membno' => $row['ac_membno'],
            'AC_NAME' => $row['AC_NAME'],
            'total_shares_amount' => $row['total_shares_amount'],
            'dividend_amount' => $row['dividend_amount'],
            'bonus_amount' => $row['bonus_amount'],
        

           
            'stdate' => $stdate,
            'etdate' => $etdate,
            'branchName' => $branchName,
            'S_APPL' => $S_APPL,
            'AC_ACNOTYPE' =>$AC_ACNOTYPE,
            

            // 'revoke' => $revoke,
            // 'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
    
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();

//print_r($data);
 //echo $query;
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
// //}
?>
