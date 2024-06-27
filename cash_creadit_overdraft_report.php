<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/cash_creadit_overdraft_report.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$dd="'DD/MM/YYYY'";
$BRANCH  = $_GET['BRANCH'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$BANK_NAME=$_GET['BANK_NAME'];
$START_DATE = $_GET['START_DATE'];
 $END_DATE = $_GET['END_DATE'];
$AC_TYPE = $_GET['AC_TYPE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$trandrcr="'D'";
$AC_OP_CD="'D'";
$transtatus="'1'";
$STATUS="'1'";

$START_DATE1 = str_replace("'" , "" , $START_DATE);
$END_DATE2 = str_replace("'" , "" , $END_DATE);
$BRANCH = str_replace("'" , "" , $BRANCH);
$BANK_NAME1 = str_replace("'" , "" , $BANK_NAME);
$AC_TYPE1 = str_replace("'" , "" , $AC_TYPE);
$AC_ACNOTYPE2 = str_replace("'" , "" , $AC_ACNOTYPE);



$query='SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), LNMASTER."BANKACNO",'.$END_DATE.', 0, 1)AS LEDGERBALANCE,
LNMASTER."AC_ACNOTYPE",
LNMASTER."AC_TYPE",
LNMASTER."AC_NO",
LNMASTER."BANKACNO",
LNMASTER."AC_EXPIRE_DATE",
LNMASTER."AC_NAME",
LNMASTER."AC_SANCTION_AMOUNT",
LNMASTER."AC_OPDATE",
LNMASTER."AC_ODAMT",
LNMASTER."AC_SODAMT",
LNMASTER."AC_ODDATE",
VWTMPZBALANCEOVERDUE.CLOSING_BALANCE,
SCHEMAST."S_APPL",
SCHEMAST."S_NAME"
FROM LNMASTER
LEFT OUTER JOIN
(SELECT LNMASTER."AC_ACNOTYPE",
        LNMASTER."AC_TYPE",
        LNMASTER."AC_NO",
        LNMASTER."BANKACNO",
        LNMASTER."AC_OPDATE",
        LNMASTER."AC_CLOSEDT",
        (COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$AC_OP_CD.'THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
        ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)END, 0) 
         + COALESCE(LOANTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE,
        (COALESCE(CASE LNMASTER."AC_OP_CD"	WHEN '.$AC_OP_CD.' THEN CAST(LNMASTER."AC_PAYBLEINT_OP" AS integer)
        ELSE (-1) * CAST(LNMASTER."AC_PAYBLEINT_OP" AS integer)	END, 0) 
         + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT, 0) 
         + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 
         + COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$AC_OP_CD.' THEN 
         CAST("AC_RECBLEODUEINT_OP" AS integer) ELSE (-1) 
         * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS integer) END,	0) 
         + COALESCE(LOANTRAN.OTHER10_AMOUNT,0) 
         + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,	0)) RECPAY_INT_AMOUNT
    FROM LNMASTER
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN 
                        CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),	0) TRAN_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN 
                        CAST("RECPAY_INT_AMOUNT" AS FLOAT)	ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END),0) RECPAY_INT_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("OTHER10_AMOUNT" AS FLOAT) ELSE (-1) 
                             * CAST("OTHER10_AMOUNT" AS FLOAT) END), 0) OTHER10_AMOUNT
            FROM LOANTRAN
            WHERE CAST("TRAN_DATE" AS date) <= CAST('.$END_DATE.' AS date)
                AND "BRANCH_CODE" = '.$BRANCH_CODE.'
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT) 
                             ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),	0) DAILY_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                              ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END),0) DAILY_RECPAY_INT_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                             ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT) END), 0) DAILY_OTHER10_AMOUNT
            FROM DAILYTRAN
            WHERE CAST("TRAN_DATE" AS date) <= CAST('.$END_DATE.' AS date)
                AND "BRANCH_CODE" = '.$BRANCH_CODE.'
                AND "TRAN_STATUS" = '.$transtatus.'
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") DAILYTRAN ON LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
    WHERE ((LNMASTER."AC_OPDATE" IS NULL)
                                OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$START_DATE.' AS date)))
        AND ((LNMASTER."AC_CLOSEDT" IS NULL)
                            OR (CAST(LNMASTER."AC_CLOSEDT" AS date) > CAST('.$END_DATE.' AS date)))
        AND LNMASTER."BRANCH_CODE" = '.$BRANCH_CODE.'
        AND LNMASTER."status" = '.$STATUS.' 
        AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL )VWTMPZBALANCEOVERDUE ON VWTMPZBALANCEOVERDUE."BANKACNO" = LNMASTER."BANKACNO"
INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST."id"
WHERE LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND LNMASTER."AC_TYPE" = '.$AC_TYPE.'
AND CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$START_DATE.'AS date)
AND (LNMASTER."AC_CLOSEDT" IS NULL
                    OR CAST(LNMASTER."AC_CLOSEDT" AS date) > TO_DATE('.$END_DATE.','.$dd.'))
AND COALESCE(VWTMPZBALANCEOVERDUE.CLOSING_BALANCE,0) > (COALESCE(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT), 0) 
                                                        + COALESCE(CAST(LNMASTER."AC_SODAMT" AS FLOAT),0))
ORDER BY LNMASTER."AC_NO"';
     

// echo $query;

$query =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
$GRAND_TOTAL3 = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($query)){

    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["AC_SANCTION_AMOUNT"];
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row["ledgerbalance"];
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + $row["OVERDRAFT_AMT"];
    $GRAND_TOTAL4 = $GRAND_TOTAL4 + ($row["AC_ODAMT"] + $row["AC_SODAMT"]);
    $GRAND_TOTAL5 = $GRAND_TOTAL5 + ($row["ledgerbalance"] - $row["AC_SANCTION_AMOUNT"]);

    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "Total_Accounts" => $row["Total_Accounts"],
        "AC_NO" => $row["AC_NO"],
        "AC_NAME" => $row["AC_NAME"],
        "AC_OPDATE" => $row["AC_OPDATE"],
        "AC_EXPIRE_DATE" => $row["AC_EXPIRE_DATE"],
        "AC_SANCTION_AMOUNT" => sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))),
        "SANCTION_OD" => $GRAND_TOTAL4,
        "ledgerbalance" => sprintf("%.2f", (abs($row['ledgerbalance']))),
        "OVERDRAFT_AMT" => sprintf("%.2f", (abs($row['ledgerbalance'])) - abs($row['AC_SANCTION_AMOUNT'])) ,
        
        "SCHEME_SANCTION_AMT" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ),
        "SCHEME_LEDGER_BLC" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        "SCHEME_OVERDRAFT_AMT" => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,
        "TOTAL_SANCTION_AMT" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ),
        "TOTAL_LEDGER_BLC" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        "TOTAL_OVERDRAFT_AMT" => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ),
		
        "START_DATE" => $START_DATE1,
        "END_DATE" => $END_DATE2,
        "BRANCH" => $BRANCH,
		'BRANCH_CODE' => $BRANCH_CODE,
        "BANK_NAME" => $BANK_NAME1,
        "AC_TYPE" => $AC_TYPE1,
        "AC_ACNOTYPE" => $row['S_APPL'].'  '.$row['S_NAME'],
       
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
    
//}   
?>
