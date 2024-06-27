<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Debit_Balance_Report.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
// $ac_acnotype="'TD'";
// $ac_type="'5'";
$AC_ACNOTYPE=$_GET['AC_ACNOTYPE'];
$AC_TYPE=$_GET['AC_TYPE'];
$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$branch_code = $_GET['branch_code'];
$TRAN_STATUS="'1'";
$status="'1'";


// $stdate ="'01/04/2016'";
$etdate =$_GET['etdate'];
$stdate =$_GET['stdate'];

$var="'D'";
$var1="' '";
$TD="'TD'";
$SB="'SB'"; 
$CA="'CA'";
$AG="'AG'";
$LK="'LK'";
$PG="'PG'";
// $branch = $_GET['branch'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);


$query=' (SELECT *
FROM
        (SELECT
          SCHEMAST."S_APPL",
         SCHEMAST."S_NAME",
         MASTER."AC_ACNOTYPE",
                        MASTER."AC_TYPE",
                        MASTER."BANKACNO",
                        MASTER."AC_MEMBTYPE" MEMBERTYPE,
                        MASTER."AC_MEMBNO" MEMBERNO,
                        MASTER."AC_INTRATE" INTRATE,
                        '.$var1.' REF_ACNO,
                        MASTER."AC_NAME",
                        MASTER."AC_OPDATE",
                        '.$var1.' AC_PARTICULAR,
                        '.$var1.' AC_TDRECEIPTNO,
                        NULL AC_EXPDT,			
                        (COALESCE(CASE MASTER."AC_OP_CD"
WHEN '.$var.' THEN CAST(MASTER."AC_OP_BAL" AS FLOAT)
ELSE (-1) * CAST(MASTER."AC_OP_BAL" AS FLOAT)END,0) + COALESCE(TRAN_TABLE.TRAN_AMOUNT,
0) + COALESCE(DAILY_TABLE.DAILY_AMOUNT,0)) CLOSING_BALANCE
                FROM DPMASTER MASTER
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float) END,0)) TRAN_AMOUNT
                                FROM DEPOTRAN
                                WHERE COALESCE(CAST("TRAN_ACNO" AS float),	0) <> 0
                                        AND CAST("TRAN_DATE" AS date) <= CAST('.$etdate.' AS date)
                         AND "BRANCH_CODE"='.$branch_code.'
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") TRAN_TABLE ON MASTER."BANKACNO" = TRAN_TABLE."TRAN_ACNO"
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
END,0)) DAILY_AMOUNT
FROM DAILYTRAN WHERE COALESCE(CAST("TRAN_ACNO" AS float),0) <> 0
                                        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
                         AND "BRANCH_CODE"='.$branch_code.'
                                        AND CAST("TRAN_DATE" AS date) <= CAST('.$etdate.' AS date)
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") DAILY_TABLE ON MASTER."BANKACNO" = DAILY_TABLE."TRAN_ACNO"
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1)  * CAST("TRAN_AMOUNT" AS float)END,	0)) INT_TRAN_AMOUNT
                                FROM INTERESTTRAN
                                WHERE COALESCE(CAST("TRAN_ACNO" AS float),0) <> 0
                                        AND "TRAN_STATUS" ='.$TRAN_STATUS.'
                         AND "BRANCH_CODE"='.$branch_code.'
                                        AND CAST("TRAN_DATE" AS date) <= CAST('.$etdate.' AS date)
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") INT_TABLE ON MASTER."BANKACNO" = INT_TABLE."TRAN_ACNO"
         LEFT JOIN SCHEMAST ON SCHEMAST.ID=MASTER."AC_TYPE"
                WHERE (MASTER."AC_OPDATE" IS NULL
                                                                        OR CAST(MASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.' AS DATE))
                        AND (MASTER."AC_CLOSEDT" IS NULL
                                                                OR CAST(MASTER."AC_CLOSEDT" AS DATE) > CAST('.$stdate.' AS DATE))
                        AND MASTER."AC_ACNOTYPE" IN ('.$TD.','.$SB.','.$CA.','.$AG.','.$LK.','.$PG.')
         AND MASTER.STATUS='.$status.' AND MASTER."BRANCH_CODE"='.$branch_code.' AND MASTER."SYSCHNG_LOGIN" IS NOT NULL
                UNION ALL SELECT
          SCHEMAST."S_APPL",
         SCHEMAST."S_NAME",
         MASTER."AC_ACNOTYPE",
                        MASTER."AC_TYPE",
                        CAST(MASTER."BANKACNO" AS CHARACTER VARYING),
                        MASTER."AC_MEMBTYPE" MEMBERTYPE,
                        MASTER."AC_MEMBNO" MEMBERNO,
                        MASTER."AC_INTCATA" INTCATA,
                        '.$var1.' REF_ACNO,
                        MASTER."AC_NAME",
                        MASTER."AC_OPDATE",
                        '.$var1.' AC_PARTICULAR,
                        '.$var1.' AC_TDRECEIPTNO,
                        NULL AC_EXPDT,
                        (COALESCE(CASE MASTER."AC_OP_CD"
WHEN '.$var.' THEN CAST(MASTER."AC_OP_BAL" AS FLOAT)
ELSE (-1) * CAST(MASTER."AC_OP_BAL" AS FLOAT) END,	0) + COALESCE(TRAN_TABLE.TRAN_AMOUNT,
0) + COALESCE(DAILY_TABLE.DAILY_AMOUNT,	0)) CLOSING_BALANCE
                FROM PGMASTER MASTER
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END,0)) TRAN_AMOUNT
                                FROM PIGMYTRAN
                                WHERE COALESCE(CAST("TRAN_ACNO" AS FLOAT),	0) <> 0
                                        AND CAST("TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS DATE)
                         AND "BRANCH_CODE"='.$branch_code.'
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") TRAN_TABLE ON MASTER."BANKACNO" = TRAN_TABLE."TRAN_ACNO"
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)	END,0)) DAILY_AMOUNT
                                FROM DAILYTRAN
                                WHERE COALESCE(CAST("TRAN_ACNO" AS FLOAT),0) <> 0
                                        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
                         AND "BRANCH_CODE"='.$branch_code.'
                                        AND CAST("TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS date)
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") DAILY_TABLE ON MASTER."BANKACNO" = DAILY_TABLE."TRAN_ACNO"
                LEFT OUTER JOIN
                        (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        SUM(COALESCE(CASE "TRAN_DRCR"
WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END,0)) INT_TRAN_AMOUNT
                                FROM INTERESTTRAN
                                WHERE COALESCE(CAST("TRAN_ACNO" AS FLOAT),	0) <> 0
                                        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
                         AND "BRANCH_CODE"='.$branch_code.'
                                        AND CAST("TRAN_DATE" AS date) <= CAST('.$etdate.' AS date)
                                GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") INT_TABLE ON MASTER."BANKACNO" = INT_TABLE."TRAN_ACNO"
         LEFT JOIN SCHEMAST ON SCHEMAST.ID=MASTER."AC_TYPE"
                WHERE (MASTER."AC_OPDATE" IS NULL
                                                                        OR CAST(MASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.'AS DATE))
                        AND (MASTER."AC_CLOSEDT" IS NULL
                                                                OR CAST(MASTER."AC_CLOSEDT" AS DATE) > CAST('.$stdate.' AS DATE))
         AND MASTER.STATUS=1 AND MASTER."SYSCHNG_LOGIN" IS NOT NULL AND MASTER."BRANCH_CODE"='.$branch_code.' AND
                         MASTER."AC_ACNOTYPE" IN ('.$TD.','.$SB.','.$CA.','.$AG.','.$LK.','.$PG.'))XYZ
WHERE CLOSING_BALANCE > 0)
';


//echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$total = 0;
$schemwise_total = 0;
// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else



    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
            $total=$total+$row['closing_balance'],
            $schemwise_total=$schemwise_total+$row['closing_balance'],
            
            'BANKACNO' => $row['BANKACNO'],
            'AC_NAME' => $row['AC_NAME'],
            'S_APPL' => $row['S_APPL'],
            'S_NAME' => $row['S_NAME'],
            'closing_balance' => sprintf("%.2f", ($row['closing_balance'] + 0.0)),

            'branch' => $branch,
            'stdate' => $stdate_,
            'etdate' => $etdate_,
            'branchName' => $branchName,
            'total'=>sprintf("%.2f", ($total + 0.0)),
            'schemwise_total'=>$schemwise_total,
            'AC_TYPE'=>$AC_TYPE,
            'AC_ACNOTYPE'=>$AC_ACNOTYPE,
            //'feild1'=>$feild1,
            // 'revoke' => $revoke,
             'bankName' => $bankName,

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
