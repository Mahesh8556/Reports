<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '.$transtatus.');
// ini_set('display_startup_errors', '.$transtatus.');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/cash_credit_less_report.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
$dd="'DD/MM/YYYY'";
$dateformate = "'DD/MM/YYYY'";
$bankName  = $_GET['bankName'];
$Branch  = $_GET['Branch'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$trandrcr = "'D'";
$transtatus = "'1'";
$BRANCH_CODE  = $_GET['BRANCH_CODE'];
$status = '1';


$Branch1 = str_replace("'", "", $Branch);
$bankName1 = str_replace("'", "", $bankName);
$sdate1 = str_replace("'", "", $sdate);
$edate1 = str_replace("'", "", $edate);




       
$query =
//  '
// SELECT ledgerbalance(cast (schemast."S_APPL" as character varying), LNMASTER."BANKACNO",'.$edate.',0,1)as
// ledgerbalance,LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."AC_EXPIRE_DATE" , 
// LNMASTER."AC_NAME", LNMASTER."AC_SANCTION_AMOUNT", LNMASTER."AC_OPDATE", LNMASTER."AC_ODAMT" , 
// LNMASTER."AC_SODAMT", LNMASTER."AC_ODDATE", VWTMPZBALANCEOVERDUE.CLOSING_BALANCE , SCHEMAST."S_NAME",SCHEMAST."S_APPL"
// FROM lnmaster
// LEFT OUTER JOIN
// (SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT" , 
//  (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandrcr.' THEN cast(LNMASTER."AC_OP_BAL" as float)
// 		   ELSE (-1) * cast(LNMASTER."AC_OP_BAL" as float) END ,0) + coalesce(LOANTRAN.TRAN_AMOUNT,0) +
//   coalesce(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE ,
//  (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandrcr.' THEN LNMASTER."AC_RECBLEINT_OP"
// 		   ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END ,0) + coalesce(LOANTRAN.RECPAY_INT_AMOUNT,0) + 
//   coalesce(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) + 
//   coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandrcr.' THEN cast(LNMASTER."AC_RECBLEODUEINT_OP" as integer)
// 		   ELSE (-1) * cast(LNMASTER."AC_RECBLEODUEINT_OP" as integer)END ,0) + 
//   coalesce(LOANTRAN.OTHER10_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT
//  FROM lnmaster
//  LEFT OUTER JOIN 
//  ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",
//   coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN cast("TRAN_AMOUNT" as FLOAT)
// 			   ELSE (-1) * cast("TRAN_AMOUNT" as FLOAT) END ),0) TRAN_AMOUNT ,
//   coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN "RECPAY_INT_AMOUNT" ELSE (-1) *  "RECPAY_INT_AMOUNT" END ),0)
//   RECPAY_INT_AMOUNT , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN "OTHER10_AMOUNT" ELSE (-1) * "OTHER10_AMOUNT" END ),0)
//   OTHER10_AMOUNT FROM LOANTRAN WHERE cast("TRAN_DATE" as date) <= cast('.$edate.' As date) GROUP BY 
//   "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) loantran
//  ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as integer)
// 	 AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as Bigint) AND  LNMASTER."status"='.$transtatus.'  and lnmaster."SYSCHNG_LOGIN" is not null ) LEFT OUTER JOIN
//  ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",
//   coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN cast("TRAN_AMOUNT" as float) ELSE (-1) * cast("TRAN_AMOUNT" as float) 
// 			   END ),0) DAILY_AMOUNT , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN cast("RECPAY_INT_AMOUNT" as FLOAT) 
// 													ELSE (-1) * cast("RECPAY_INT_AMOUNT" as FLOAT) END ),0) 
//   DAILY_RECPAY_INT_AMOUNT , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandrcr.' THEN cast("OTHER10_AMOUNT" as FLOAT) 
// 										 ELSE (-1) * cast("OTHER10_AMOUNT" as FLOAT) END ),0) DAILY_OTHER10_AMOUNT
//   FROM DAILYTRAN WHERE cast("TRAN_DATE" as date) <= cast('.$edate.' As date) AND "TRAN_STATUS" = '.$transtatus.'  AND "BRANCH_CODE"='.$BRANCH_CODE.'
//   GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) dailytran ON
//  (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(DAILYTRAN."TRAN_ACTYPE" as bigint)
//   AND LNMASTER."AC_NO" = cast( DAILYTRAN."TRAN_ACNO" as bigint)) WHERE 
//  ((LNMASTER."AC_OPDATE" IS NULL) OR (cast(LNMASTER."AC_OPDATE" as date) <= cast('.$sdate.' As date))) AND
//  ((LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date) > cast('.$edate.' As date))) 
//    )
//  vwtmpzbalanceoverdue ON 
//  (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEOVERDUE."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = VWTMPZBALANCEOVERDUE."AC_TYPE" 
//   AND LNMASTER."AC_NO" = VWTMPZBALANCEOVERDUE."AC_NO") inner JOIN schemast ON ( LNMASTER."AC_TYPE" = SCHEMAST."id") 
//   WHERE LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.' AND LNMASTER."AC_TYPE" ='.$AC_TYPE.' AND
//   cast(LNMASTER."AC_OPDATE" as date) <= cast('.$sdate.' As date) AND 
//   LNMASTER."status"='.$transtatus.' and lnmaster."SYSCHNG_LOGIN" is not null AND LNMASTER."BRANCH_CODE"='.$BRANCH_CODE.' AND
//   (LNMASTER."AC_CLOSEDT" IS NULL OR cast(LNMASTER."AC_CLOSEDT" as date) > cast('.$edate.' As date))
//   AND coalesce(VWTMPZBALANCEOVERDUE.CLOSING_BALANCE,0) < (coalesce(cast(LNMASTER."AC_SANCTION_AMOUNT" as FLOAT),0)
// 														  + coalesce(cast(LNMASTER."AC_SODAMT" as FLOAT),0))';
$query ='
SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying),
LNMASTER."BANKACNO",'.$edate.',0,1)AS LEDGERBALANCE,
LNMASTER."AC_ACNOTYPE",
LNMASTER."AC_TYPE",
LNMASTER."AC_NO",
LNMASTER."AC_EXPIRE_DATE",
LNMASTER."AC_NAME",
LNMASTER."AC_SANCTION_AMOUNT",
LNMASTER."AC_OPDATE",
LNMASTER."AC_ODAMT",
LNMASTER."AC_SODAMT",
LNMASTER."AC_ODDATE",
VWTMPZBALANCEOVERDUE.CLOSING_BALANCE,
SCHEMAST."S_NAME",
SCHEMAST."S_APPL"
FROM LNMASTER
LEFT OUTER JOIN
(SELECT LNMASTER."AC_ACNOTYPE",
LNMASTER."AC_TYPE",
LNMASTER."AC_NO",
LNMASTER."AC_OPDATE",
LNMASTER."AC_CLOSEDT",
(COALESCE(CASE
WHEN LNMASTER."AC_OP_CD" = '.$trandrcr.' THEN CAST(LNMASTER."AC_OP_BAL" AS float)
ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS float)
END,
0) + COALESCE(CAST(LOANTRAN.TRAN_AMOUNT AS FLOAT),
0) + COALESCE(CAST(DAILYTRAN.DAILY_AMOUNT AS FLOAT),
0)) CLOSING_BALANCE,
(COALESCE(CASE
WHEN LNMASTER."AC_OP_CD" = '.$trandrcr.' THEN LNMASTER."AC_RECBLEINT_OP"
ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
END,
0) + COALESCE(CAST(LOANTRAN.RECPAY_INT_AMOUNT AS FLOAT),
0) + COALESCE(CAST(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT AS FLOAT),
0) + COALESCE(CASE
WHEN LNMASTER."AC_OP_CD" = '.$trandrcr.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
END,
0) + COALESCE(CAST(LOANTRAN.OTHER10_AMOUNT AS FLOAT),
0) + COALESCE(CAST(DAILYTRAN.DAILY_OTHER10_AMOUNT AS FLOAT),
0)) RECPAY_INT_AMOUNT
FROM LNMASTER
LEFT OUTER JOIN
(SELECT "TRAN_ACNOTYPE",
"TRAN_ACTYPE",
"TRAN_ACNO",
COALESCE(SUM(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
END),
0) TRAN_AMOUNT,
COALESCE(SUM(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN "RECPAY_INT_AMOUNT"
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
END),
0) RECPAY_INT_AMOUNT,
COALESCE(SUM(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN "OTHER10_AMOUNT"
ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
END),
0) OTHER10_AMOUNT
FROM LOANTRAN
WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$edate.','.$dd.')
GROUP BY "TRAN_ACNOTYPE",
"TRAN_ACTYPE",
"TRAN_ACNO") LOANTRAN ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER)
AND LNMASTER."AC_NO" = CAST(LOANTRAN."TRAN_ACNO" AS BIGINT)
AND LNMASTER."status" = '.$status.'
AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL)
LEFT OUTER JOIN
(SELECT "TRAN_ACNOTYPE",
"TRAN_ACTYPE",
"TRAN_ACNO",
COALESCE(SUM(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
END),
0) DAILY_AMOUNT,
COALESCE(SUM(CASE WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)END),0) DAILY_RECPAY_INT_AMOUNT,
COALESCE(SUM(CASE WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)END),0) DAILY_OTHER10_AMOUNT
FROM DAILYTRAN
WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$edate.','.$dd.')
AND "TRAN_STATUS" = '.$transtatus.'
AND "BRANCH_CODE" = '.$BRANCH_CODE.'
GROUP BY "TRAN_ACNOTYPE",
"TRAN_ACTYPE",
"TRAN_ACNO") DAILYTRAN ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS INTEGER)
AND LNMASTER."AC_NO" = CAST(DAILYTRAN."TRAN_ACNO" AS BIGINT))
WHERE ((LNMASTER."AC_OPDATE" IS NULL)
OR (CAST(LNMASTER."AC_OPDATE" AS date) <= TO_DATE('.$sdate.','.$dd.')))
AND ((LNMASTER."AC_CLOSEDT" IS NULL)
OR (CAST(LNMASTER."AC_CLOSEDT" AS date) > TO_DATE('.$edate.','.$dd.')))
) VWTMPZBALANCEOVERDUE
ON (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEOVERDUE."AC_ACNOTYPE"
AND LNMASTER."AC_TYPE" = VWTMPZBALANCEOVERDUE."AC_TYPE"
AND LNMASTER."AC_NO" = VWTMPZBALANCEOVERDUE."AC_NO")
INNER JOIN SCHEMAST ON (LNMASTER."AC_TYPE" = SCHEMAST."id")
WHERE LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND LNMASTER."AC_TYPE" = '.$AC_TYPE.'
AND CAST(LNMASTER."AC_OPDATE" AS date) <= TO_DATE('.$sdate.','.$dd.')
AND LNMASTER."status" = '.$status.'
AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND LNMASTER."BRANCH_CODE" = '.$BRANCH_CODE.'
AND (LNMASTER."AC_CLOSEDT" IS NULL
OR CAST(LNMASTER."AC_CLOSEDT" AS date) > TO_DATE('.$edate.','.$dd.'))
AND cast(LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying),
LNMASTER."BANKACNO",'.$edate.',0,1) as float) < (COALESCE(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT),0)
 + COALESCE(CAST(LNMASTER."AC_SODAMT" AS FLOAT),
0)) ORDER BY LNMASTER."AC_NO"';

// echo $query;
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
$GRAND_TOTAL3 = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["AC_SANCTION_AMOUNT"];
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row["ledgerbalance"];
    
    $GRAND_TOTAL4 = $GRAND_TOTAL4 + ($row["AC_ODAMT"] + $row["AC_SODAMT"]);
    $GRAND_TOTAL5 = $GRAND_TOTAL5 + $row["AC_SANCTION_AMOUNT"] - $row["ledgerbalance"];
    $total_voucher = $total_voucher + 1;
    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_NO" => $row["AC_NO"],
        "AC_NAME" => $row["AC_NAME"],
        "AC_OPDATE" => $row["AC_OPDATE"],
        "AC_EXPIRE_DATE" => $row["AC_EXPIRE_DATE"],
        "AC_SANCTION_AMOUNT" => sprintf("%.2f", ($row["AC_SANCTION_AMOUNT"] + 0.0)),
        "SANCTION_OD" => $GRAND_TOTAL4,
        "ledgerbalance" => $row["ledgerbalance"],
        "LESSDRAFT_AMT" => sprintf("%.2f", (abs(($row["ledgerbalance"] - $row["AC_SANCTION_AMOUNT"])+0.0))),
        "SCHEME_SANCTION_AMT" => sprintf("%.2f", (abs($GRAND_TOTAL1+0.0))),
        "SCHEME_LEDGER_BLC" => sprintf("%.2f", ($GRAND_TOTAL2 + 0.0)),
        "total" =>sprintf("%.2f", $GRAND_TOTAL3+0.0),
        "TOTAL_SANCTION_AMT" => sprintf("%.2f", (abs($GRAND_TOTAL1+0.0))),
        "TOTAL_LEDGER_BLC" => sprintf("%.2f", ($GRAND_TOTAL2 + 0.0)),
        "BRANCH_CODE" => $BRANCH_CODE ,
        "sdate" => $sdate1,
        "edate" => $edate1,
        "Branch" => $Branch1,
        'total_voucher'=> $total_voucher,
        "AC_ACNOTYPE" => $AC_ACNOTYPE1,
        "AC_TYPE" => $row["S_APPL"]. ' ' . $row['S_NAME'],
        "bankName" => $bankName1,
        
    ];
    $data[$i]=$tmp;
    $i++;
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + $tmp["LESSDRAFT_AMT"]; 
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
    
 }   
?>