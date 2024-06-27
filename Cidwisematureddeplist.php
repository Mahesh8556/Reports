<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

// $filename1 = __DIR__.'/CidwisematureddeplistFLAG1.jrxml';
$filename1 = __DIR__.'/CidwisematureddeplistFLAG1.jrxml';
$filename2 = __DIR__.'/Cidwisematureddeplist.jrxml';



$data = [];
$faker = Faker\Factory::create('en_US');

//$conn = pg_connect("host=127.0.0.1 dbname=bhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
 
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName = $_GET['BankName'];
$trandrcr="'D'";
$AC_OP_CD="'D'";
$tran_status="'1'";
$AC_CUSTID=$_GET['AC_CUSTID'];
$S_ACNOTYPE="'TD'";
$flag1 = $_GET['flag1'];
//$flag2 = $_GET['flag2'];
$sign = $_GET['sign'];
$query='';

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$AC_CUSTID1 = str_replace("'" , "" , $AC_CUSTID);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);

//$dateformate = "'DD/MM/YYYY'";
//$query = '';
/*
if($flag1==1)
{
    $query='SELECT DPMASTER."AC_ACNOTYPE", DPMASTER."AC_TYPE", DPMASTER."AC_NO",DPMASTER."BANKACNO","AC_CUSTID", 
    "AC_NAME", DPMASTER."AC_SCHMAMT", DPMASTER."AC_OPDATE", "AC_EXPDT", DPMASTER."AC_CLOSEDT", 
    (COALESCE(PAID_INTEREST,0) + COALESCE(cast("AC_PAYBLEINT_OP" As integer),0) + 
    COALESCE(cast("AC_PAID_INT_OP" As integer),0) + 
    CASE LEFT(cast(cast("AC_SCHMAMT" As integer) - cast("AC_OP_BAL" As integer) As character varying),1) WHEN '.$sign.' THEN cast("AC_OP_BAL" As integer) - cast("AC_SCHMAMT" As integer) ELSE 0 END ) PAID_INTEREST, 
    VWTMPDPBAL.CLOSING_BALANCE, COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,0) RECPAY_INT_AMOUNT
    From DPMASTER
LEFT OUTER JOIN
( 
SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
SUM(COALESCE(CASE "TRAN_DRCR"  WHEN '.$trandrcr.'  THEN  "RECPAY_INT_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE "TRAN_DRCR"  WHEN '.$trandrcr.'  THEN  cast("INTEREST_AMOUNT" As FLOAT) ELSE (-1) * cast("INTEREST_AMOUNT" As FLOAT) END,0)) PAID_INTEREST, 0 CURRENT_INT 
From DEPOTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date) 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
) DEPOTRAN 
ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE", "AC_TYPE" , "AC_NO", "BANKACNO" , "AC_OPDATE", "AC_CLOSEDT" , 
(COALESCE(CASE "AC_OP_CD"  WHEN '.$ac_op_cd.' THEN  cast("AC_OP_BAL" As FLOAT)  ELSE (-1) * cast("AC_OP_BAL" As FLOAT) END,0) + 
COALESCE(DEPOTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE , 
( COALESCE(CASE DPMASTER."AC_OP_CD"  WHEN '.$ac_op_cd.' THEN  cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  ELSE (-1) * cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  END,0) +  
COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0))  RECPAY_INT_AMOUNT 
FROM DPMASTER
LEFT OUTER JOIN
( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) TRAN_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DEPOTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DEPOTRAN
ON DPMASTER."BANKACNO" =  DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) DAILY_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DAILYTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
AND "TRAN_STATUS" = '.$tran_status.' 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DAILYTRAN
ON DPMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
WHERE ((DPMASTER."AC_OPDATE" IS NULL) OR (cast(DPMASTER."AC_OPDATE" As date) <= cast('.$edate.' As date)))
AND ((DPMASTER."AC_CLOSEDT" IS NULL) OR (cast(DPMASTER."AC_CLOSEDT" As date) > cast('.$edate.' As date)))
)VWTMPDPBAL ON  VWTMPDPBAL."BANKACNO"=DPMASTER."BANKACNO" 
INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE" 
AND SCHEMAST."id" = DPMASTER."AC_TYPE" 
AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.' 
AND cast(DPMASTER."AC_EXPDT" As date) BETWEEN cast('.$sdate.' As date) AND cast('.$edate.' As date) 
where "AC_CUSTID" = '.$AC_CUSTID.'';


 }
 else{
    $query='SELECT DPMASTER."AC_ACNOTYPE", DPMASTER."AC_TYPE", DPMASTER."AC_NO",DPMASTER."BANKACNO","AC_CUSTID", 
    "AC_NAME", "AC_EXPDT",  DEPOTRAN."TRAN_DATE" , DPMASTER."AC_REF_RECEIPTNO",
(COALESCE(PAID_INTEREST,0) + COALESCE(cast("AC_PAYBLEINT_OP" As integer),0) + 
COALESCE(cast("AC_PAID_INT_OP" As integer),0) + 
CASE LEFT(cast(cast("AC_SCHMAMT" As integer) - cast("AC_OP_BAL" As integer) As character varying),1) WHEN '.$sign.' THEN cast("AC_OP_BAL" As integer) - cast("AC_SCHMAMT" As integer) ELSE 0 END ) PAID_INTEREST, 
VWTMPDPBAL.CLOSING_BALANCE, COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,0) RECPAY_INT_AMOUNT, 
COALESCE(CURRENT_INT,0) CURRENT_INT
From DPMASTER
LEFT OUTER JOIN
( 
SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", "TRAN_DATE",
SUM(COALESCE(CASE "TRAN_DRCR"  WHEN '.$trandrcr.'  THEN  "RECPAY_INT_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE "TRAN_DRCR"  WHEN '.$trandrcr.'  THEN  cast("INTEREST_AMOUNT" As FLOAT) ELSE (-1) * cast("INTEREST_AMOUNT" As FLOAT) END,0)) PAID_INTEREST, 0 CURRENT_INT 
From DEPOTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date) 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO","TRAN_DATE" 
) DEPOTRAN 
ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE", "AC_TYPE" , "AC_NO", "BANKACNO" , "AC_OPDATE", "AC_CLOSEDT" , 
(COALESCE(CASE "AC_OP_CD"  WHEN '.$trandrcr.' THEN  cast("AC_OP_BAL" As FLOAT)  ELSE (-1) * cast("AC_OP_BAL" As FLOAT) END,0) + 
COALESCE(DEPOTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE , 
( COALESCE(CASE DPMASTER."AC_OP_CD"  WHEN '.$trandrcr.' THEN  cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  ELSE (-1) * cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  END,0) +  
COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0))  RECPAY_INT_AMOUNT 
FROM DPMASTER
LEFT OUTER JOIN
( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) TRAN_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DEPOTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DEPOTRAN
ON DPMASTER."BANKACNO" =  DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) DAILY_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DAILYTRAN 
WHERE cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
AND "TRAN_STATUS" = '.$tran_status.' 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DAILYTRAN
ON DPMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
WHERE ((DPMASTER."AC_OPDATE" IS NULL) OR (cast(DPMASTER."AC_OPDATE" As date) <= cast('.$edate.' As date)))
AND ((DPMASTER."AC_CLOSEDT" IS NULL) OR (cast(DPMASTER."AC_CLOSEDT" As date) > cast('.$edate.' As date)))
)VWTMPDPBAL ON  VWTMPDPBAL."BANKACNO"=DPMASTER."BANKACNO" 
INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE" 
AND SCHEMAST."id" = DPMASTER."AC_TYPE" 
AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.' 
AND cast(DPMASTER."AC_EXPDT" As date) BETWEEN cast('.$sdate.' As date) AND cast('.$edate.' As date) 
where "AC_CUSTID" = '.$AC_CUSTID.'';  


}
*/

// if($flag1==1)
// {
//     $query='SELECT 
//     SCHEMAST."S_APPL",
// 	SCHEMAST."S_NAME",
//     DPMASTER."AC_ACNOTYPE",
// 	DPMASTER."AC_TYPE",
// 	DPMASTER."AC_NO",
// 	DPMASTER."BANKACNO",
// 	"AC_CUSTID",
// 	"AC_NAME",
// 	DPMASTER."AC_SCHMAMT",
// 	DPMASTER."AC_OPDATE",
// 	"AC_EXPDT",
// 	DPMASTER."AC_CLOSEDT",
// 	(COALESCE(PAID_INTEREST,
// 			0) + COALESCE(CAST("AC_PAYBLEINT_OP" AS integer),
// 0) + COALESCE(CAST("AC_PAID_INT_OP" AS integer),
// 0) + CASE LEFT(CAST(CAST("AC_SCHMAMT" AS integer) - CAST("AC_OP_BAL" AS integer) AS CHARACTER varying),1)
// WHEN '.$sign.' THEN CAST("AC_OP_BAL" AS integer) - CAST("AC_SCHMAMT" AS integer)
// ELSE 0
// END) PAID_INTEREST,
// 	VWTMPDPBAL.CLOSING_BALANCE,
// 	COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,
// 		0) RECPAY_INT_AMOUNT
// FROM DPMASTER
// LEFT OUTER JOIN
// 	(SELECT "TRAN_ACNOTYPE",
// 			"TRAN_ACTYPE",
// 			"TRAN_ACNO",
// 			SUM(COALESCE(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN "RECPAY_INT_AMOUNT"
// ELSE 0 END,0) + COALESCE(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,0)) PAID_INTEREST,
// 			0 CURRENT_INT
// 		FROM DEPOTRAN
// 		WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
// 	 AND "BRANCH_CODE"='.$branch_code.'
// 		GROUP BY "TRAN_ACNOTYPE",
// 			"TRAN_ACTYPE",
// 			"TRAN_ACNO") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
// LEFT OUTER JOIN
// 	(SELECT "AC_ACNOTYPE",
// 			"AC_TYPE",
// 			"AC_NO",
// 			"BANKACNO",
// 			"AC_OPDATE",
// 			"AC_CLOSEDT",
// 			(COALESCE(CASE "AC_OP_CD"
// WHEN '.$AC_OP_CD.' THEN CAST("AC_OP_BAL" AS FLOAT)
// ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT) END,
// 0) + COALESCE(DEPOTRAN.TRAN_AMOUNT,
// 0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,
// 0)) CLOSING_BALANCE,
// 			(COALESCE(CASE DPMASTER."AC_OP_CD"
// WHEN '.$AC_OP_CD.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
// ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
// END,0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,
// 0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0)) RECPAY_INT_AMOUNT
// 		FROM DPMASTER
// 		LEFT OUTER JOIN
// 			(SELECT "TRAN_ACNOTYPE",
// 					"TRAN_ACTYPE",
// 					"TRAN_ACNO",
// 					COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
// END),0) TRAN_AMOUNT,
// SUM(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// END) RECPAY_INT_AMOUNT
// 				FROM DEPOTRAN
// 				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
// 			 AND "BRANCH_CODE"='.$branch_code.'
// 				GROUP BY "TRAN_ACNOTYPE",
// 					"TRAN_ACTYPE",
// 					"TRAN_ACNO") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
// 		LEFT OUTER JOIN
// 			(SELECT "TRAN_ACNOTYPE",
// 					"TRAN_ACTYPE",
// 					"TRAN_ACNO",
// 					COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),
// 						0) DAILY_AMOUNT,
// 					SUM(CASE "TRAN_DRCR"
// WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END) RECPAY_INT_AMOUNT
// 				FROM DAILYTRAN
// 				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
// 					AND "TRAN_STATUS" = '.$tran_status.'
// 			 AND "BRANCH_CODE"='.$branch_code.'
// 				GROUP BY "TRAN_ACNOTYPE",
// 					"TRAN_ACTYPE",
// 					"TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
// 		WHERE ((DPMASTER."AC_OPDATE" IS NULL)
// 									OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
// 			AND ((DPMASTER."AC_CLOSEDT" IS NULL)
// 								OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date)))
// 	)VWTMPDPBAL ON VWTMPDPBAL."BANKACNO" = DPMASTER."BANKACNO"
// INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"
// AND SCHEMAST."id" = DPMASTER."AC_TYPE"
// AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.'
// AND DPMASTER."BRANCH_CODE"='.$branch_code.' AND DPMASTER."status"=1 AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
// AND CAST(DPMASTER."AC_EXPDT" AS date) BETWEEN CAST('.$sdate.' AS date) AND CAST('.$edate.' AS date)
// WHERE "AC_CUSTID" = '.$AC_CUSTID.'';


//  }
//  else{
//     $query='SELECT 
//     SCHEMAST."S_APPL",
//         SCHEMAST."S_NAME",
//         DPMASTER."AC_ACNOTYPE",
//         DPMASTER."AC_TYPE",
//         DPMASTER."AC_NO",
//         DPMASTER."BANKACNO",
//         "AC_CUSTID",
//         "AC_NAME",
//         "AC_EXPDT",
//         DEPOTRAN."TRAN_DATE",
//         DPMASTER."AC_REF_RECEIPTNO",
//         (COALESCE(PAID_INTEREST,
//                 0) + COALESCE(CAST("AC_PAYBLEINT_OP" AS integer),0) + COALESCE(CAST("AC_PAID_INT_OP" AS integer),
//     0) + CASE LEFT(CAST(CAST("AC_SCHMAMT" AS integer) - CAST("AC_OP_BAL" AS integer) AS CHARACTER varying),	1)
//     WHEN '.$sign.' THEN CAST("AC_OP_BAL" AS integer) - CAST("AC_SCHMAMT" AS integer) ELSE 0 END) PAID_INTEREST,
//         VWTMPDPBAL.CLOSING_BALANCE,
//         COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,
//             0) RECPAY_INT_AMOUNT,
//         COALESCE(CURRENT_INT,
//             0) CURRENT_INT
//     FROM DPMASTER
//     LEFT OUTER JOIN
//         (SELECT "TRAN_ACNOTYPE",
//                 "TRAN_ACTYPE",
//                 "TRAN_ACNO",
//                 "TRAN_DATE",
//                 SUM(COALESCE(CASE "TRAN_DRCR"
//     WHEN '.$trandrcr.' THEN "RECPAY_INT_AMOUNT"	ELSE 0	END,0) + COALESCE(CASE "TRAN_DRCR"	WHEN '.$trandrcr.' THEN
//     CAST("INTEREST_AMOUNT" AS FLOAT) ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,	0)) PAID_INTEREST,
//                 0 CURRENT_INT
//             FROM DEPOTRAN
//             WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
//             AND "BRANCH_CODE"='.$branch_code.'
//             GROUP BY "TRAN_ACNOTYPE",
//                 "TRAN_ACTYPE",
//                 "TRAN_ACNO",
//                 "TRAN_DATE") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
//     LEFT OUTER JOIN
//         (SELECT "AC_ACNOTYPE",
//                 "AC_TYPE",
//                 "AC_NO",
//                 "BANKACNO",
//                 "AC_OPDATE",
//                 "AC_CLOSEDT",
//                 (COALESCE(CASE "AC_OP_CD"
//         WHEN '.$AC_OP_CD.' THEN CAST("AC_OP_BAL" AS FLOAT)
//     ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)	END,0) + COALESCE(DEPOTRAN.TRAN_AMOUNT,	0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,
//     0)) CLOSING_BALANCE,(COALESCE(CASE DPMASTER."AC_OP_CD"
//         WHEN '.$AC_OP_CD.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
//     ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)	END,0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,
//     0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0)) RECPAY_INT_AMOUNT
//             FROM DPMASTER
//             LEFT OUTER JOIN
//                 (SELECT "TRAN_ACNOTYPE",
//                         "TRAN_ACTYPE",
//                         "TRAN_ACNO",
//                         COALESCE(SUM(CASE "TRAN_DRCR"
//     WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
//     ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)	END),0) TRAN_AMOUNT,
//     SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
//     ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END) RECPAY_INT_AMOUNT
//                     FROM DEPOTRAN
//                     WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
//                     AND "BRANCH_CODE"='.$branch_code.'
//                     GROUP BY "TRAN_ACNOTYPE",
//                         "TRAN_ACTYPE",
//                         "TRAN_ACNO") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
//             LEFT OUTER JOIN
//                 (SELECT "TRAN_ACNOTYPE",
//                         "TRAN_ACTYPE",
//                         "TRAN_ACNO",
//                         COALESCE(SUM(CASE "TRAN_DRCR"
//     WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
//     ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)	END),0) DAILY_AMOUNT,
//                         SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
//     ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END) RECPAY_INT_AMOUNT
//                     FROM DAILYTRAN
//                     WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
//                         AND "TRAN_STATUS" = '.$tran_status.'
//                  AND "BRANCH_CODE"='.$branch_code.'
//                     GROUP BY "TRAN_ACNOTYPE",
//                         "TRAN_ACTYPE",
//                         "TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
//             WHERE ((DPMASTER."AC_OPDATE" IS NULL)
//                                         OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
//                 AND ((DPMASTER."AC_CLOSEDT" IS NULL)
//                                     OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date))) 
//         )VWTMPDPBAL ON VWTMPDPBAL."BANKACNO" = DPMASTER."BANKACNO"
//     INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"
//     AND SCHEMAST."id" = DPMASTER."AC_TYPE"
//     AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.'
//     AND DPMASTER."BRANCH_CODE"='.$branch_code.' AND DPMASTER."status"=1 AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
//     AND CAST(DPMASTER."AC_EXPDT" AS date) BETWEEN CAST('.$sdate.' AS date) AND CAST('.$edate.' AS date)
//     WHERE "AC_CUSTID" = '.$AC_CUSTID.'';  


// }
//*/

// echo $flag1;

if($flag1==1)
{
    $query = 'SELECT SCHEMAST."S_APPL",
	SCHEMAST."S_NAME",
	DPMASTER."AC_ACNOTYPE",
	DPMASTER."AC_TYPE",
	DPMASTER."AC_NO",
	DPMASTER."BANKACNO",
	DPMASTER."AC_PAYBLEINT_OP",
	DPMASTER."AC_CLOSED",
	"AC_CUSTID",
	"AC_NAME",
	"AC_EXPDT",
	DPMASTER."AC_REF_RECEIPTNO",
	(COALESCE(PAID_INTEREST, 0) + COALESCE(CAST("AC_PAYBLEINT_OP" AS integer),0) 
	 + COALESCE(CAST("AC_PAID_INT_OP" AS integer),0) 
	 + CASE LEFT(CAST(CAST("AC_SCHMAMT" AS integer) 
	 - CAST("AC_OP_BAL" AS integer) AS CHARACTER varying),	1)
		WHEN '.$sign.' THEN CAST("AC_OP_BAL" AS integer) - CAST("AC_SCHMAMT" AS integer)
		ELSE 0 	END) PAID_INTEREST,	
	VWTMPDPBAL.CLOSING_BALANCE,
	COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,0) RECPAY_INT_AMOUNT,
	COALESCE(CURRENT_INT,0) CURRENT_INT
FROM DPMASTER

LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			SUM(COALESCE(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN "RECPAY_INT_AMOUNT"
		ELSE 0
		END, 0) + COALESCE(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
	 ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT)
	END, 0)) PAID_INTEREST,
			0 CURRENT_INT
		FROM DEPOTRAN
		WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
			AND "BRANCH_CODE" = '.$branch_code.'
		GROUP BY "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO"
	) DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
	(SELECT "AC_ACNOTYPE",
			"AC_TYPE",
			"AC_NO",
			"BANKACNO",
			"AC_OPDATE",
			"AC_CLOSEDT",
	(COALESCE(CASE "AC_OP_CD" WHEN '.$AC_OP_CD.' THEN CAST("AC_OP_BAL" AS FLOAT)
	ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)
	END,	0) + COALESCE(DEPOTRAN.TRAN_AMOUNT, 0) + 
	 COALESCE(DAILYTRAN.DAILY_AMOUNT, 0)) CLOSING_BALANCE,
			(COALESCE(CASE DPMASTER."AC_OP_CD"WHEN '.$AC_OP_CD.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
			ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
			END, 0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT, 0) + 
			 COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT, 0)) RECPAY_INT_AMOUNT
		FROM DPMASTER
	 
		LEFT OUTER JOIN
			(SELECT DISTINCT "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO",
			COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)				
						ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
						END),
						0) TRAN_AMOUNT,
					SUM(CASE "TRAN_DRCR"
					WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
					ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
									END) RECPAY_INT_AMOUNT
				FROM DEPOTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO")
	 DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
		LEFT OUTER JOIN
	 
			(SELECT DISTINCT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
					END),

						0) DAILY_AMOUNT,
					SUM(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
									END) RECPAY_INT_AMOUNT
				FROM DAILYTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
					AND "TRAN_STATUS" = '.$tran_status.'
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
		WHERE ((DPMASTER."AC_OPDATE" IS NULL)
	OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
			AND ((DPMASTER."AC_CLOSEDT" IS NULL)
	OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date))) )VWTMPDPBAL 
	ON VWTMPDPBAL."BANKACNO" = DPMASTER."BANKACNO"
INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"
AND SCHEMAST."id" = DPMASTER."AC_TYPE"
AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.'
AND DPMASTER."BRANCH_CODE" = '.$branch_code.'
AND DPMASTER."status" = 1
AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND CAST(DPMASTER."AC_EXPDT" AS date) BETWEEN CAST('.$sdate.' AS date) 
AND CAST('.$edate.' AS date)
WHERE "AC_CUSTID" = '.$AC_CUSTID.' and DPMASTER."AC_CLOSEDT" IS NOT NULL';
// echo $query;
}
else
{
    $query = 'SELECT SCHEMAST."S_APPL",
	SCHEMAST."S_NAME",
	DPMASTER."AC_ACNOTYPE",
	DPMASTER."AC_TYPE",
	DPMASTER."AC_NO",
	DPMASTER."BANKACNO",
	DPMASTER."AC_PAYBLEINT_OP",
	DPMASTER."AC_CLOSED",
	"AC_CUSTID",
	"AC_NAME",
	"AC_EXPDT",
	DPMASTER."AC_REF_RECEIPTNO",
	(COALESCE(PAID_INTEREST, 0) + COALESCE(CAST("AC_PAYBLEINT_OP" AS integer),0) 
	 + COALESCE(CAST("AC_PAID_INT_OP" AS integer),0) 
	 + CASE LEFT(CAST(CAST("AC_SCHMAMT" AS integer) 
	 - CAST("AC_OP_BAL" AS integer) AS CHARACTER varying),	1)
		WHEN '.$sign.' THEN CAST("AC_OP_BAL" AS integer) - CAST("AC_SCHMAMT" AS integer)
		ELSE 0 	END) PAID_INTEREST,	
	VWTMPDPBAL.CLOSING_BALANCE,
	COALESCE(VWTMPDPBAL.RECPAY_INT_AMOUNT,0) RECPAY_INT_AMOUNT,
	COALESCE(CURRENT_INT,0) CURRENT_INT
FROM DPMASTER

LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			SUM(COALESCE(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN "RECPAY_INT_AMOUNT"
		ELSE 0
		END, 0) + COALESCE(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
	 ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT)
	END, 0)) PAID_INTEREST,
			0 CURRENT_INT
		FROM DEPOTRAN
		WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
			AND "BRANCH_CODE" = '.$branch_code.'
		GROUP BY "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO"
	) DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
	(SELECT "AC_ACNOTYPE",
			"AC_TYPE",
			"AC_NO",
			"BANKACNO",
			"AC_OPDATE",
			"AC_CLOSEDT",
	(COALESCE(CASE "AC_OP_CD" WHEN '.$AC_OP_CD.' THEN CAST("AC_OP_BAL" AS FLOAT)
	ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)
	END,	0) + COALESCE(DEPOTRAN.TRAN_AMOUNT, 0) + 
	 COALESCE(DAILYTRAN.DAILY_AMOUNT, 0)) CLOSING_BALANCE,
			(COALESCE(CASE DPMASTER."AC_OP_CD"WHEN '.$AC_OP_CD.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
			ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
			END, 0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT, 0) + 
			 COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT, 0)) RECPAY_INT_AMOUNT
		FROM DPMASTER
	 
		LEFT OUTER JOIN
			(SELECT DISTINCT "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO",
			COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)				
						ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
						END),
						0) TRAN_AMOUNT,
					SUM(CASE "TRAN_DRCR"
					WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
					ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
									END) RECPAY_INT_AMOUNT
				FROM DEPOTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO")
	 DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
		LEFT OUTER JOIN
	 
			(SELECT DISTINCT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
					END),

						0) DAILY_AMOUNT,
					SUM(CASE "TRAN_DRCR"
		WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
									END) RECPAY_INT_AMOUNT
				FROM DAILYTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
					AND "TRAN_STATUS" = '.$tran_status.'
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
		WHERE ((DPMASTER."AC_OPDATE" IS NULL)
	OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
			AND ((DPMASTER."AC_CLOSEDT" IS NULL)
	OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date))) )VWTMPDPBAL 
	ON VWTMPDPBAL."BANKACNO" = DPMASTER."BANKACNO"
INNER JOIN SCHEMAST ON SCHEMAST."S_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"
AND SCHEMAST."id" = DPMASTER."AC_TYPE"
AND SCHEMAST."S_ACNOTYPE" = '.$S_ACNOTYPE.'
AND DPMASTER."BRANCH_CODE" = '.$branch_code.'
AND DPMASTER."status" = 1
AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND CAST(DPMASTER."AC_EXPDT" AS date) BETWEEN CAST('.$sdate.' AS date) 
AND CAST('.$edate.' AS date)
WHERE "AC_CUSTID" = '.$AC_CUSTID.' and DPMASTER."AC_CLOSEDT" IS NULL ';
}

echo $query;




$sql =  pg_query($conn,$query);

$i = 0;


   

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['AC_SCHMAMT'];
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row['paid_interest'] ;
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + $row['total'] ;
    $GRAND_TOTAL4 = $GRAND_TOTAL4 + $row['RECPAY_INT_AMOUNT'] ;
	$GRAND_TOTAL5 = $GRAND_TOTAL5 + $row['AC_PAYBLEINT_OP'] ;
    $GRAND_TOTAL6 = $GRAND_TOTAL6 + $row['current_int'] ;


    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row["AC_MATUAMT"];
    
    
    $GRAND_TOTAL4 = $GRAND_TOTAL4+($row['AC_SCHMAMT'] + $row['paid_interest']);
    

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_OPDATE'=> $row['AC_OPDATE'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_CLOSEDT'=> $row['AC_CLOSEDT'],
        'AC_SCHMAMT'=>sprintf("%.2f", (abs($row['AC_SCHMAMT']))),
        'paid_interest' => sprintf("%.2f",($row['paid_interest']) + 0.0 ),
        'recpay_int_amount' => sprintf("%.2f", (abs($row['RECPAY_INT_AMOUNT']))),
		'AC_PAYBLEINT_OP' => sprintf("%.2f", (abs($row['AC_PAYBLEINT_OP']))),
        'total' => sprintf("%.2f", (abs(($row['AC_SCHMAMT']) -($row['paid_interest'])))) ,
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        'TRAN_DATE' => $row['TRAN_DATE'],
        'recpay_int_amount' => $row['recpay_int_amount'],
        'current_int'=> sprintf("%.2f",($row['current_int']) + 0.0 ),
		'recpayIntAmout' =>sprintf("%.2f", (abs($row['recpay_int_amount']))),
        
        

        'TOTALINT_AC_SCHMAMT' =>  sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        'TOTALINT_PAID_INTEREST' =>sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ),
        'TOTALINT_AMT' => sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        'TOTAL_AC_SCHMAMT'=> sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        'TOTAL_PAID_INTEREST'=> sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        'TOTAL_AMT'=> sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        'TOTALINT_RECPAY_AMT' => sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        'TOTAL_RECPAY_AMT'=> sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        'CUSTWISE_TOTAL'=> sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,

        'Branch'=>$Branch,
		'branch_code' => $branch_code ,
        'BankName'=>$BankName1,
        'sdate' => $sdate1,
        'edate' => $edate2,
        'trandrcr'=>$trandrcr,
        //'ac_op_cd'=>$ac_op_cd,
        'tran_status'=>$tran_status,
        'S_ACNOTYPE'=>$S_ACNOTYPE,
        'AC_CUSTID' => $AC_CUSTID1,
        'sign'=> $sign,
        'flag1'=> $flag1,
        'flag2'=> $flag2,
		'S_APPL' => $row['S_APPL'],
		'total_AC_PAYBLEINT_OP' => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,
		'total_current_int' => sprintf("%.2f",($GRAND_TOTAL6) + 0.0 ),
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// print_r($data);
// echo $flag1;
$report = new PHPJasperXML();
if($flag1=='0')
{
    $report->load_xml_file($filename1)    
     ->setDataSource($config)
     ->export('Pdf');
}
else
{
    $report->load_xml_file($filename2)    
     ->setDataSource($config)
     ->export('Pdf');

}
?>
