<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/TermDepoStatement.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
// echo 'report';
// $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

$format = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$int = "'0'";

$bankName    = $_GET['bankName'];
$branchName      = $_GET['branchName'];
$stdate      = $_GET['startDate'];
$END_DATE    = $_GET['endDate'];
$sdate       = $_GET['sdate'];
// $scheme = $_GET['scheme'];
$Scheme_code = $_GET['schemeCode'];
$Starting_Account = $_GET['startingcode'];
// $Ending_Account = $_GET['endingcode'];
$print1         = $_GET['PrintAddedPenalInterest'];
$print2         = $_GET['PrintConciseReporteme'];
$TRAN_ACTYPE =$_GET['schemeCode'];
$TRAN_STATUS = "'1'";



$td  = $_GET['scheme']; //change dynamic

// echo $sdate;

$bankName = str_replace("'", "", $bankName);
$branchName = str_replace("'", "", $branchName);
$Scheme_code1 = str_replace("'", "", $Scheme_code);
$stdate_ = str_replace("'", "", $stdate);
$END_DATE_ = str_replace("'", "", $END_DATE);
$Starting_Account1 = str_replace("'", "", $Starting_Account);
// $Ending_Account1 = str_replace("'", "", $Ending_Account);



// $query='SELECT SCHEMAST."S_NAME",
// SCHEMAST."IS_ZERO_BAL_REQUIRED",
// DPMASTER."AC_ACNOTYPE",
// DPMASTER."AC_TYPE",
// DPMASTER."AC_NO",
// DPMASTER."AC_NAME",
// DPMASTER."AC_OP_CD",
// (COALESCE(CASE DPMASTER ."AC_OP_CD"
// WHEN '.$d.' THEN CAST(DPMASTER."AC_OP_BAL" AS FLOAT)
// ELSE (-1) * CAST(DPMASTER."AC_OP_BAL" AS FLOAT)
// END,
// 0) + COALESCE(OPENING_TRANTABLE.OP_TRAN_AMT,
// 0) + COALESCE(OPENING_DAILYTABLE.OP_DAILY_AMT,
// 0)) OP_BALANCE,
// TRANTABLE."TRAN_NO",
// TRANTABLE."TRAN_DATE",
// TRANTABLE."TRAN_TYPE",
// REPLACE(REPLACE(TRANTABLE."NARRATION", CHR(10),NULL), CHR(13),NULL) NARRATION,
// TRANTABLE."CHEQUE_NO",
// TRANTABLE.DR_AMOUNT,
// TRANTABLE.CR_AMOUNT,
// TRANTABLE.INTEREST_AMOUNT,
// TRANTABLE.PAYABLEINT_AMOUNT,
// NOMINEELINK."AC_NNAME"
// FROM DPMASTER
// INNER JOIN SCHEMAST ON SCHEMAST.ID = DPMASTER."AC_TYPE"
// LEFT OUTER JOIN NOMINEELINK ON NOMINEELINK."DPMasterID" = DPMASTER.ID
// LEFT OUTER JOIN
// (SELECT "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO",
// COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
// END),0) OP_TRAN_AMT,
// COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// END),
// 0) RECPAY_INT_AMOUNT
//     FROM DEPOTRAN
//     WHERE CAST("TRAN_DATE" AS DATE) < CAST('.$stdate.' AS DATE)
//         AND "TRAN_ACNOTYPE" = '.$td .'
//         AND CAST("TRAN_ACTYPE" AS integer) = 4
//     GROUP BY "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO") OPENING_TRANTABLE ON OPENING_TRANTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
// LEFT OUTER JOIN
// (SELECT "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO",
//         COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
// END),
//             0) OP_DAILY_AMT,
//         COALESCE(SUM(CASE "TRAN_DRCR"
// WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
// END),
//             0) DAILY_RECPAY_INT_AMOUNT
//     FROM DAILYTRAN
//     WHERE CAST("TRAN_DATE" AS DATE) < CAST('.$stdate.' AS DATE)
//         AND CAST("TRAN_STATUS" AS integer) = 1
//         AND "TRAN_ACNOTYPE" = '.$td .'
//         AND CAST("TRAN_ACTYPE" AS integer) = 4
//     GROUP BY "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO") OPENING_DAILYTABLE ON OPENING_DAILYTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
// LEFT OUTER JOIN
// (SELECT "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO",
//         "TRAN_NO",
//         "TRAN_DATE",
//         "NARRATION",
//         "CHEQUE_NO",
//         "TRAN_TYPE",
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)ELSE 0 END,
//             0) DR_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$c.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0 END,
//             0) CR_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("INTEREST_AMOUNT" AS FLOAT) ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,
//             0) INTEREST_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END,
//             0) PAYABLEINT_AMOUNT
//     FROM DEPOTRAN
//     WHERE CAST("TRAN_DATE" AS DATE) >= CAST('.$stdate.' AS DATE)
//         AND CAST("TRAN_DATE" AS DATE) <= CAST('.$END_DATE.' AS DATE)
//         AND "TRAN_ACNOTYPE" = '.$td .'
//         AND CAST("TRAN_ACTYPE" AS integer) = 3
//     UNION ALL SELECT "TRAN_ACNOTYPE",
//         "TRAN_ACTYPE",
//         "TRAN_ACNO",
//         "TRAN_NO",
//         "TRAN_DATE",
//         "NARRATION",
//         "CHEQUE_NO",
//         "TRAN_TYPE",
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0
//         END,
//             0) DR_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$c.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0 END,
//             0) CR_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("INTEREST_AMOUNT" AS FLOAT) ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,
//             0) INTEREST_AMOUNT,
//         COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END,
//             0) PAYABLEINT_AMOUNT
//     FROM DAILYTRAN
//     WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$END_DATE.' AS DATE)
//         AND CAST("TRAN_STATUS" AS integer) = 1
//         AND "TRAN_ACNOTYPE" = '.$td .'
//         AND CAST("TRAN_ACTYPE" AS integer) = 4 ) TRANTABLE ON TRANTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
// WHERE (DPMASTER."AC_OPDATE" IS NULL
//                         OR CAST(DPMASTER."AC_OPDATE" AS DATE) <= CAST('.$END_DATE.' AS DATE))
// AND (DPMASTER."AC_CLOSEDT" IS NULL
//                     OR CAST(DPMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE))
// AND DPMASTER."AC_ACNOTYPE" = '.$td .'
// AND CAST(DPMASTER."BANKACNO" AS bigint) BETWEEN 101101201100001 AND 101101201100200
// AND CAST(DPMASTER."AC_TYPE" AS integer) = 10';

$query='SELECT SCHEMAST."S_NAME",
schemast."IS_RECURRING_TYPE",
	SCHEMAST."IS_ZERO_BAL_REQUIRED",
	DPMASTER."AC_ACNOTYPE",
	DPMASTER."AC_TYPE",
	DPMASTER."AC_NO",
	DPMASTER."AC_NAME",
	DPMASTER."AC_OP_CD",
    DPMASTER."AC_ASON_DATE",
	DPMASTER."AC_EXPDT",
	DPMASTER."AC_OPDATE",
    DPMASTER."AC_SCHMAMT",
    DPMASTER."AC_MATUAMT",
	 DPMASTER."BRANCH_CODE",
	 DPMASTER."AC_REF_RECEIPTNO", 
	(COALESCE(CASE DPMASTER ."AC_OP_CD"
	WHEN '.$d.' THEN CAST(DPMASTER."AC_OP_BAL" AS FLOAT)
	ELSE (-1) * CAST(DPMASTER."AC_OP_BAL" AS FLOAT)
	END,
	0) + COALESCE(OPENING_TRANTABLE.OP_TRAN_AMT,
	0) + COALESCE(OPENING_DAILYTABLE.OP_DAILY_AMT,
	0)) OP_BALANCE,
	TRANTABLE."TRAN_NO",
	TRANTABLE."TRAN_DATE",
	TRANTABLE."TRAN_TYPE",
	REPLACE(REPLACE(TRANTABLE."NARRATION", CHR(10),NULL), CHR(13),NULL) NARRATION,
	TRANTABLE."CHEQUE_NO",
	TRANTABLE.DR_AMOUNT,
	TRANTABLE.CR_AMOUNT,
	ABS(TRANTABLE.INTEREST_AMOUNT)AS INTEREST_AMOUNT,
	ABS(TRANTABLE.PAYABLEINT_AMOUNT) AS PAYABLEINT_AMOUNT,
	NOMINEELINK."AC_NNAME",
    fn_get_cust_address(DPMASTER."AC_CUSTID") "AC_ADDR",
	DPMASTER."BANKACNO",
	DPMASTER."AC_CUSTID"
FROM DPMASTER
INNER JOIN SCHEMAST ON SCHEMAST.ID = DPMASTER."AC_TYPE"
LEFT OUTER JOIN NOMINEELINK ON NOMINEELINK."DPMasterID" = DPMASTER.ID
LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
COALESCE(SUM(CASE "TRAN_DRCR"
WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
END),0) OP_TRAN_AMT,
COALESCE(SUM(CASE "TRAN_DRCR"
	WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
	ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
	END),
	0) RECPAY_INT_AMOUNT
		FROM DEPOTRAN
		WHERE CAST("TRAN_DATE" AS DATE) < CAST('.$stdate.' AS DATE)
			AND "TRAN_ACNOTYPE" = '.$td.'
			AND CAST("TRAN_ACTYPE" AS integer) = '.$TRAN_ACTYPE.'
		GROUP BY "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO") OPENING_TRANTABLE ON OPENING_TRANTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			COALESCE(SUM(CASE "TRAN_DRCR"
	WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
	ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
	END),
				0) OP_DAILY_AMT,
			COALESCE(SUM(CASE "TRAN_DRCR"
	WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
	ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
	END),
				0) DAILY_RECPAY_INT_AMOUNT
		FROM DAILYTRAN 
		WHERE  CAST("TRAN_DATE" AS DATE) < CAST('.$stdate.' AS DATE)
			AND CAST("TRAN_STATUS" AS integer) = '.$TRAN_STATUS.'
			AND "TRAN_ACNOTYPE" = '.$td.'
			AND CAST("TRAN_ACTYPE" AS integer) = '.$TRAN_ACTYPE.'
		GROUP BY "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO") OPENING_DAILYTABLE ON OPENING_DAILYTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			"TRAN_NO",
			"TRAN_DATE",
			"NARRATION",
			"CHEQUE_NO",
			"TRAN_TYPE",
			 "BRANCH_CODE",
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)ELSE 0 END,
				0) DR_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$c.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0 END,
				0) CR_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("INTEREST_AMOUNT" AS FLOAT) ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,
				0) INTEREST_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END,
				0) PAYABLEINT_AMOUNT
		FROM DEPOTRAN
		WHERE CAST("TRAN_DATE" AS DATE) >= CAST('.$stdate.' AS DATE) 
			AND CAST("TRAN_DATE" AS DATE) <= CAST('.$END_DATE.' AS DATE)
			AND "TRAN_ACNOTYPE" = '.$td.'
			AND CAST("TRAN_ACTYPE" AS integer) = '.$TRAN_ACTYPE.'
		UNION ALL SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			"TRAN_NO",
			"TRAN_DATE",
			"NARRATION",
			"CHEQUE_NO",
			"TRAN_TYPE",
	 		"BRANCH_CODE",
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0
			END,
				0) DR_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$c.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0 END,
				0) CR_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("INTEREST_AMOUNT" AS FLOAT) ELSE (-1) * CAST("INTEREST_AMOUNT" AS FLOAT) END,
				0) INTEREST_AMOUNT,
			COALESCE(CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END,
				0) PAYABLEINT_AMOUNT
		FROM DAILYTRAN
		WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$END_DATE.' AS DATE) 
			AND CAST("TRAN_STATUS" AS integer) = '.$TRAN_STATUS.'
			AND "TRAN_ACNOTYPE" = '.$td.'
			AND CAST("TRAN_ACTYPE" AS integer) = '.$TRAN_ACTYPE.' ) TRANTABLE ON TRANTABLE."TRAN_ACNO" = DPMASTER."BANKACNO"
WHERE (DPMASTER."AC_OPDATE" IS NULL
							OR CAST(DPMASTER."AC_OPDATE" AS DATE) <= CAST('.$END_DATE.' AS DATE))
	AND (DPMASTER."AC_CLOSEDT" IS NULL
						OR CAST(DPMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE))
	AND DPMASTER."AC_ACNOTYPE" = '.$td.'
		
	AND CAST(DPMASTER."BANKACNO" AS bigint) = '.$Starting_Account.'
	AND CAST(DPMASTER."AC_TYPE" AS integer) = '.$TRAN_ACTYPE.' order by cast(TRANTABLE."TRAN_DATE" as date)';

          // echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_BALTOT = 0;
$BALANCE = 0 ;
$LEDGER_BAL = 0;
$GRAND_AMTTOT = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else
//  {
$balAmount=0;

$credit_total=0;
$debit_total=0;
$crpayint_total=0;
$drpayint_total=0;

while($row = pg_fetch_assoc($sql))
{

	$credit_total = $credit_total + $row['cr_amount'];
	$debit_total = $debit_total + $row['dr_amount'];
	$crpayint_total = $crpayint_total + $row['payableint_amount'];
	$drpayint_total = $drpayint_total + $row['drpayint'];


    // $credit_total = $credit_total + $row['dramt'];
	// $credit_total=$credit_total+ $row['cramt'] ;



	if($i==0){
        $balAmount= (abs($row['op_balance']));
    }



    $GRAND_AMTTOT = $GRAND_AMTTOT + $row['interest_amount'];
    $GRAND_BALTOT = $GRAND_BALTOT + $BALANCE ;
    $BALANCE = $row['cramt'] + $row['dramt'] ;
    $LEDGER_BAL = $LEDGER_BAL + $row['ledger_balance'];
$balAmount= $balAmount+$row['cr_amount'] - $row['dr_amount'];
    $tmp=[
        'AC_NAME' => $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_ASON_DATE'=> $row['AC_ASON_DATE'],
        'AC_MATUAMT'=> $row['AC_MATUAMT'],
        'AC_REF_RECEIPTNO'=> $row['AC_REF_RECEIPTNO'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'NAME'=> $row['NAME'],
        'S_NAME'=> $row['S_NAME'],
        'cramt'=> sprintf("%.2f",($row['cr_amount'] + 0.0)),
        'dramt'=> sprintf("%.2f",($row['dr_amount']+ 0.0)),
        'crpayint'=> sprintf("%.2f",($row['payableint_amount']+ 0.0)),
        'drpayint'=> $row['drpayint'],
        'TRAN_DATE'=> $row['TRAN_DATE'],
        'CHEQUE_NO'=> $row['CHEQUE_NO'],
        'NARRATION'=> $row['narration'],
        // 'AC_ADDR'=> $row['AC_ADDR'],
        'INTEREST_AMOUNT'=> sprintf("%.2f",($row['interest_amount']+ 0.0)),
        'ledger_balance' => $row['ledger_balance'],
        'balance' => sprintf("%.2f",($balAmount+ 0.0)),
        'baltotal' =>  sprintf("%.2f",($balAmount+ 0.0)),
        'intamttot' => sprintf("%.2f",($GRAND_AMTTOT+ 0.0)),
        'ledgerbalance' => $LEDGER_BAL,
        'branchName' => $branchName,
        'bankName' => $bankName,
        'stdate' => $stdate_,
        'END_DATE' => $END_DATE_,
        'sdate' => $sdate,
        'Scheme_code' => $Scheme_code,
        'Starting_Account' => $Starting_Account,
        'Ending_Account' => $Ending_Account,
        'print1' => $print1,
        'print2' => $print2,
        'Address' => $row['AC_ADDR'],
		'Opening Balance' => abs($row['op_balance']),
		'crtotal' => sprintf("%.2f",($credit_total+ 0.0)),
		'drtotal' => sprintf("%.2f",($debit_total+ 0.0)),
		'crpayinttotal' => sprintf("%.2f",($crpayint_total+ 0.0)),
		'drpayinttotal' => sprintf("%.2f",($drpayint_total+ 0.0)),


    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
print_r($data)


?>

