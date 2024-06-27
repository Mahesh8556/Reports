<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ABtypemdepositelist.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$Branch  = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName=$_GET['BankName'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE=$_GET['AC_ACNOTYPE'];
$AC_TYPE=$_GET['AC_TYPE'];
$TRAN_DRCR="'D'";
$AC_OP_CD="'D'";
$TRAN_STATUS="'1'";

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName = str_replace("'" , "" , $BankName);



$query='SELECT DPMASTER."AC_MEMBTYPE",
DPMASTER."AC_ACNOTYPE",
DPMASTER."AC_TYPE",
DPMASTER."AC_NO",
DPMASTER."BANKACNO",
DPMASTER."AC_NAME",
DPMASTER."AC_SCHMAMT",
VWTMPBALANCE.CLOSING_BALANCE BALANCE,
SCHEMAST."S_APPL",
SCHEMAST."S_NAME",
SCHEMAST."MEMBER_TYPE",
SCHEME."S_APPL",
SCHEME."S_NAME"
FROM DPMASTER
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO",
        "AC_OPDATE",
        "AC_CLOSEDT",
        "BANKACNO",
        (COALESCE(CASE "AC_OP_CD"
WHEN '.$AC_OP_CD.' THEN CAST("AC_OP_BAL" AS FLOAT)
ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT)	END,
0) + COALESCE(DEPOTRAN.TRAN_AMOUNT,	0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,
0)) CLOSING_BALANCE,(COALESCE(CASE DPMASTER."AC_OP_CD"
WHEN '.$AC_OP_CD.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
END,0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,
0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,
0)) RECPAY_INT_AMOUNT
    FROM DPMASTER
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR"	WHEN '.$TRAN_DRCR.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1)  * CAST("TRAN_AMOUNT" AS FLOAT)	END),						0) TRAN_AMOUNT,
                SUM(CASE "TRAN_DRCR"		WHEN '.$TRAN_DRCR.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)	END) RECPAY_INT_AMOUNT
            FROM DEPOTRAN
            WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
         AND "BRANCH_CODE"='.$branch_code.' 
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
        (SELECT "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO",
                COALESCE(SUM(CASE "TRAN_DRCR"	WHEN '.$TRAN_DRCR.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)	END),0) DAILY_AMOUNT,
                SUM(CASE "TRAN_DRCR"	WHEN '.$TRAN_DRCR.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                END) RECPAY_INT_AMOUNT
            FROM DAILYTRAN
            WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
                AND "TRAN_STATUS" = '.$TRAN_STATUS.'
         AND "BRANCH_CODE"='.$branch_code.' 
            GROUP BY "TRAN_ACNOTYPE",
                "TRAN_ACTYPE",
                "TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
    WHERE ((DPMASTER."AC_OPDATE" IS NULL)
                                OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
        AND ((DPMASTER."AC_CLOSEDT" IS NULL)
                            OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date)))
) VWTMPBALANCE ON VWTMPBALANCE."BANKACNO" = DPMASTER."BANKACNO"
INNER JOIN SCHEMAST ON CAST(DPMASTER."AC_MEMBTYPE" AS integer) = SCHEMAST."id"
LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID=DPMASTER."AC_TYPE"
WHERE DPMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND DPMASTER."AC_TYPE" = '.$AC_TYPE.'
AND CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)
AND CLOSING_BALANCE <> 0
AND CAST(DPMASTER."AC_SCHMAMT" AS integer) <> 0
AND (DPMASTER."AC_CLOSEDT" IS NULL
                    OR CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date))
                    AND DPMASTER."BRANCH_CODE"='.$branch_code.' AND DPMASTER."status"=1 and DPMASTER."SYSCHNG_LOGIN" IS NOT NULL ORDER BY DPMASTER."AC_NO"';
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

    // if ($row['balance'] < 0) {
    //     $netType = 'Cr';
    // } else {
    //     $netType = 'Dr';
    // }

while($row = pg_fetch_assoc($sql)){

    $DEPOSITE_TOTAL = $DEPOSITE_TOTAL + $row["AC_SCHMAMT"];
    $TOT_BAL = $TOT_BAL + sprintf("%.2f", (abs($row['balance'])));
   

    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_NO"=>$row["AC_NO"],
        "AC_NAME"=>$row["AC_NAME"],
        "AC_MEMBTYPE"=>$row["AC_MEMBTYPE"],
        "DEPOSITE_AMT"=> sprintf("%.2f", (abs($row['AC_SCHMAMT']))),
        "balance" =>sprintf("%.2f", (abs($row['balance']))),
        "TOTAL_DEPOSITE_AMT" => sprintf("%.2f",($DEPOSITE_TOTAL) + 0.0 ) ,
        "TOTAL_BALANCE" => sprintf("%.2f",($TOT_BAL) + 0.0 ) ,
        "sdate" => $sdate1,
        "edate" => $edate2,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "BankName" => $BankName,
        "AC_ACNOTYPE"=>$row['S_APPL'].'  '.$row['S_NAME'],
        "AC_TYPE"=>$AC_TYPE
        //"TRAN_DRCR"=>$TRAN_DRCR,
        //"AC_OP_CD"=>$AC_OP_CD,
        //"TRAN_STATUS"=>$TRAN_STATUS,
       
    ];
    if($row["AC_MEMBTYPE"]==1){
        $tmp['AC_MEMBTYPE']='A-Type Member Deposit List';
    }
    else if($row["AC_MEMBTYPE"]==NULL){
        $tmp['AC_MEMBTYPE']='B-Type Member Deposit List';
    }
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
    
//}   
?>
