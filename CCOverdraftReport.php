<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CCOverdraftReport.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$Branch  = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName  = $_GET['BankName'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$trandrcr="'D'";
$AC_OP_CD="'D'";
$transtatus="'1'";

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
$AC_ACNOTYPE1 = str_replace("'" , "" , $AC_ACNOTYPE);


$query='SELECT LNMASTER."AC_ACNOTYPE",
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
        (COALESCE(CASE LNMASTER."AC_OP_CD"
WHEN '.$AC_OP_CD.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)	END,
0) + COALESCE(LOANTRAN.TRAN_AMOUNT,	0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE,
(COALESCE(CASE LNMASTER."AC_OP_CD"	WHEN '.$AC_OP_CD.' THEN CAST(LNMASTER."AC_PAYBLEINT_OP" AS INTEGER)
ELSE (-1)  * CAST(LNMASTER."AC_PAYBLEINT_OP" AS INTEGER)END,0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,
0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) + COALESCE(CASE LNMASTER."AC_OP_CD"
WHEN '.$AC_OP_CD.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS INTEGER)
ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS INTEGER)	END,
0) + COALESCE(LOANTRAN.OTHER10_AMOUNT,	0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT
    FROM LNMASTER
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR"
WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END),0) TRAN_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR"
WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END),
0) RECPAY_INT_AMOUNT,COALESCE(SUM(CASE "TRAN_DRCR"	WHEN '.$trandrcr.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)	END),0) OTHER10_AMOUNT
            FROM LOANTRAN
            WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
         AND "BRANCH_CODE"=1
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR"
WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END),0) DAILY_AMOUNT,
                COALESCE(SUM(CASE "TRAN_DRCR"	WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END),	0) DAILY_RECPAY_INT_AMOUNT,
COALESCE(SUM(CASE "TRAN_DRCR"	WHEN '.$trandrcr.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)	END),0) DAILY_OTHER10_AMOUNT
            FROM DAILYTRAN
            WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
                AND "TRAN_STATUS" = '.$transtatus.'
         AND "BRANCH_CODE"=1
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") DAILYTRAN ON LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
    WHERE ((LNMASTER."AC_OPDATE" IS NULL)
                                OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= CAST('.$sdate.' AS DATE)))
        AND ((LNMASTER."AC_CLOSEDT" IS NULL)
                            OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$edate.' AS DATE))) 
)VWTMPZBALANCEOVERDUE ON VWTMPZBALANCEOVERDUE."BANKACNO" = LNMASTER."BANKACNO"
INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST."id"
WHERE LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND CAST(LNMASTER."AC_OPDATE" AS DATE) <= CAST('.$sdate.' AS DATE)
AND (LNMASTER."AC_CLOSEDT" IS NULL
                    OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$edate.' AS DATE))
                    AND LNMASTER."BRANCH_CODE"='.$branch_code.' AND LNMASTER."status"=1 AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND COALESCE(VWTMPZBALANCEOVERDUE.CLOSING_BALANCE,	0) > (COALESCE(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS INTEGER),
0) + COALESCE(CAST(LNMASTER."AC_SODAMT" AS INTEGER),0))';

// echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL1 = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + ($row["closing_balance"]-($row["AC_SODAMT"]+$row["AC_SANCTION_AMOUNT"]));
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + ($row["AC_SANCTION_AMOUNT"]);

    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "NO_OF_ACS" => $row["NO_OF_ACS"],
        "AC_NO"=> $row["AC_NO"],
        "AC_NAME"=> $row["AC_NAME"],
        "AC_OPDATE" => $row["AC_OPDATE"],
        "AC_SANCTION_AMOUNT"=>sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))),
        "SACTION_OD" => $row["AC_ODAMT"]+$row["AC_SODAMT"],
        "closing_balance" => sprintf("%.2f", (abs($row['closing_balance']))),
        "OVERDRAFT_AMT"=>sprintf("%.2f",abs($row["closing_balance"])-(abs($row["AC_SODAMT"])+abs($row["AC_SANCTION_AMOUNT"]))),

        "Branch" => $Branch ,
        'branch_code' => $branch_code ,
        "BankName" => $BankName1 ,
        "sdate" => $sdate1,
        "edate" => $edate2,
        //"trandrcr" => $trandrcr,
        //"transtatus" => $transtatus,
        "AC_ACNOTYPE" => $row['S_APPL'].'  '.$row['S_NAME'],
        "SUBTOTAL_OVERDRAFT_AMT" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ),
        "TOTAL_OVERDRAFT_AMT" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ),
        "sanction_total"=>sprintf("%.2f",($GRAND_TOTAL2) + 0.0 )
        

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
    
// //}   
?>
