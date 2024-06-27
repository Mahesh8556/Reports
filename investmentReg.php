<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/investmentReg.jrxml';



$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $startDate = "'18/03/2018'";
// $enddate = "'20/03/2018'";

//$c = "'C'";
$TRAN_ACTYPE="'14'";
$IS_INTEREST_ENTRY="'0'";
$AC_TYPE1="'13'";
$AC_TYPE = $_GET['AC_TYPE'];  
$startDate_ = $_GET['startDate_'];
$enddate_ = $_GET['enddate_'];
$ACNOTYPE=$_GET['AC_ACNOTYPE'];
$NAME = $_GET['NAME'];
$bankName =  $_GET['bankName'];
$trandrcr=$_GET['trandrcr'];
$ac_op_cd=$_GET['ac_op_cd'];
$tran_status=$_GET['tran_status'];
// $AC_DATA = $_GET['AC_DATA'];
$branch  = $_GET['branchcode'];
$acclose=$_GET['acclose'];
$dateformate = "'DD/MM/YYYY'";
$startDate = str_replace("'", "", $startDate_);
$endDate = str_replace("'", "", $enddate_);
$NAME = str_replace("'", "", $NAME);
$bankName = str_replace("'", "", $bankName);

$ACNOTYPE1 = str_replace("'", "", $ACNOTYPE);

if($acclose==1)
{
    $opcd = "Opening";
	$opcddt = "Opening Date";

}else{
    $opcd = "Closing"; 
	// $opcddt = "Closing Date";

}

// echo $AC_DATA;

$query = 'SELECT DPMASTER."AC_ACNOTYPE",
	DPMASTER."AC_TYPE",
	DPMASTER."AC_NO",
	DPMASTER."BANKACNO",
	DPMASTER."AC_NAME",
	"INVEST_BANK",
	"INVEST_BRANCH",
	DPMASTER."AC_CLOSEDT",
	DPMASTER."AC_OPDATE",
	DPMASTER."AC_REF_RECEIPTNO",
	DPMASTER."AC_SCHMAMT",
	DPMASTER."AC_INTRATE",
	DPMASTER."AC_MATUAMT",
	DPMASTER."AC_EXPDT",
	VWTMPZBALANCEIV.CLOSING_BALANCE,
	VWTMPZBALANCEIV.RECPAY_INT_AMOUNT,
	DEPOTRAN.INT_AMOUNT,
	0 CURRENT_INT
FROM DPMASTER
LEFT OUTER JOIN
	(SELECT "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO",
			SUM(INT_AMOUNT) INT_AMOUNT
		FROM DPMASTER,

			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					SUM(CAST("TRAN_AMOUNT" AS FLOAT)) + SUM(CAST("RECPAY_INT_AMOUNT" AS FLOAT)) INT_AMOUNT
				FROM DEPOTRAN
				WHERE "TRAN_DRCR" = '.$trandrcr.'
					AND "IS_INTEREST_ENTRY" = -1
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO"
				UNION ALL SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					SUM(CAST("INTEREST_AMOUNT" AS integer)) INT_AMOUNT
				FROM DEPOTRAN
				WHERE "TRAN_DRCR" = '.$trandrcr.'
					AND CAST("INTEREST_AMOUNT" AS integer) <> 0
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") S
		GROUP BY "TRAN_ACNOTYPE",
			"TRAN_ACTYPE",
			"TRAN_ACNO") DEPOTRAN ON DEPOTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
LEFT OUTER JOIN
	(SELECT "AC_ACNOTYPE",
			"AC_TYPE",
			"AC_NO",
			"AC_OPDATE",
			"AC_CLOSEDT",
			DPMASTER."BANKACNO",
			(COALESCE(CASE "AC_OP_CD"
																	WHEN '.$ac_op_cd.' THEN CAST("AC_OP_BAL" AS integer)
																	ELSE (-1) * CAST("AC_OP_BAL" AS integer)
													END,

					0) + COALESCE(DEPOTRAN.TRAN_AMOUNT,

											0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

																	0)) CLOSING_BALANCE,
			(COALESCE(CASE DPMASTER."AC_OP_CD"
																	WHEN '.$ac_op_cd.' THEN CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
																	ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS integer)
													END,

					0) + COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,

											0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,

																	0)) RECPAY_INT_AMOUNT
		FROM DPMASTER
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(CASE "TRAN_DRCR"
																						WHEN '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
																						ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
																		END),

						0) TRAN_AMOUNT,
					SUM(CASE "TRAN_DRCR"
													WHEN '.$trandrcr.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
													ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
									END) RECPAY_INT_AMOUNT
				FROM DEPOTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$enddate_.' AS date)
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DEPOTRAN ON DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
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
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$enddate_.' AS date)
					AND "TRAN_STATUS" = '.$tran_status.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DAILYTRAN ON DPMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
		WHERE ((DPMASTER."AC_OPDATE" IS NULL)
									OR (CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$startDate_.' AS date)))
			AND ((DPMASTER."AC_CLOSEDT" IS NULL)
								OR (CAST(DPMASTER."AC_CLOSEDT" AS date) > CAST('.$enddate_.' AS date))))VWTMPZBALANCEIV ON VWTMPZBALANCEIV."BANKACNO" = DPMASTER."BANKACNO"
WHERE DPMASTER."AC_ACNOTYPE" = '.$ACNOTYPE.'
	AND CAST(DPMASTER."AC_OPDATE" AS date) >= CAST('.$startDate_.' AS date)
	AND CAST(DPMASTER."AC_OPDATE" AS date) <= CAST('.$enddate_.' AS date)
';
	if($acclose =='1'){
		$query .= '	AND DPMASTER."AC_CLOSEDT" IS  NULL';
	}else{
		$query .= 'AND dpmaster."AC_CLOSEDT" IS NOT NULL';
	} 


			
				
$cr_schemast = pg_query($conn,'select * from schemast where id = '.$AC_TYPE);
$crAcType = '000';
while($row1 = pg_fetch_assoc($cr_schemast)){
    $crAcType = $row1['S_APPL'];
}
// echo $query;

          

$sql =  pg_query($conn,$query);

$i = 0;
$srno = 1;

$GRAND_TOTAL = 0;
$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
$GRAND_TOTAL3 = 0;



if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    // print_r($row);
    if ($row['closing_balance'] < 0) {
        $netType = 'Cr';
    } else {
        $netType = 'Dr';
    }
    if((int)$row['AC_SCHMAMT'] != 0){
    $GRAND_TOTAL = $GRAND_TOTAL + (int)$row['AC_SCHMAMT'];
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['closing_balance'] ;
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row['int_amount'] ;
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + $row['recpay_int_amount'] ;

    

    $tmp=[
        'SR_NO' => $srno,
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'INVEST_BRANCH' => $row['INVEST_BRANCH'],
        'AC_SCHMAMT' => sprintf("%.2f",($row['AC_SCHMAMT']) + 0.0 ),
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_CLOSEDT'=> $row['AC_CLOSEDT'],
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        'closing_balance' => sprintf("%.2f", (abs($row['closing_balance']==''?0:$row['closing_balance']))).' '.$netType,
        'INTEREST_AMOUNT' => $row['int_amount'],
        'RECPAY_INT_AMOUNT' => $row['recpay_int_amount'],
        'SCHEME_AMT' => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ),
        'TOT_AMT' => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ),
        "SCHEME_BAL" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        "TOT_BAL" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        'SCHEME_REC_INT'=> $GRAND_TOTAL2,
        'TOT_REC_INT'=> $GRAND_TOTAL2,
        'SCHEME_RECEIVABLE_INT'=> $GRAND_TOTAL3,
        'TOT_RECEIVABLE_INT'=> $GRAND_TOTAL3,
        'opcd'=> $opcd,
        // 'opcddt'=> $opcddt,

        'NAME'=>$NAME,
        'bankName' => $bankName,
        'ACNOTYPE'=> $ACNOTYPE1,
        // 'AC_TYPE'=>$crAcType,
        'startDate_' => $startDate,
        'enddate_' => $endDate,
        'trandrcr'=>$trandrcr,
        'ac_op_cd'=>$ac_op_cd,
        'tran_status'=>$tran_status,
		'closeDate' => $row['AC_CLOSEDT'],
    ];
    $data[$i]=$tmp;
    $i++;
    $srno++;
}
    
}
ob_end_clean();
if(count($data) == 0){
    include "errormsg.html";
}else{
$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
}

}
?>  

