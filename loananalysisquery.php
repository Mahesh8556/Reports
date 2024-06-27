<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '.$transtatus.');
// ini_set('display_startup_errors', '.$transtatus.');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/loan analysis query.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');


$Branch  = $_GET['Branch'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$trandr = "'D'";
$transtatus = "'1'";
$trancr = "'C'";
$bankname = $_GET['bankname'];
$BRANCH_CODE  = $_GET['BRANCH_CODE'];

$sdate1 = str_replace("'", "", $sdate);
$edate1 = str_replace("'", "", $edate);
$Branch1 = str_replace("'", "", $Branch);
$bankname1 = str_replace("'", "", $bankname);


$query='
SELECT TMP."AC_ACNOTYPE",
	TMP."AC_TYPE",
	SCHEMAST."S_APPL",
	SCHEMAST."S_NAME",
	SUM(TMP.OPENING_ACCOUNTS) OPENING_ACCOUNTS,
	SUM(TMP.OPENING_LEDGER_BALANCE) OPENING_LEDGER_BALANCE,
	SUM(TMP.SANCTION_ACCOUNTS) SANCTION_ACCOUNTS,
	SUM(TMP.SANCTION_AMOUNT) SANCTION_AMOUNT,
	SUM(RECOVERD_ACCOUNTS) RECOVERD_ACCOUNTS,
	SUM(TMP.RECOVERD_AMOUNT) RECOVERD_AMOUNT,
	SUM(CLOSING_ACCOUNTS) CLOSING_ACCOUNTS,
	SUM(CLOSING_LEDGER_BALANCE) CLOSING_LEDGER_BALANCE
FROM
	(SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			SUM(CASE
											WHEN VWTMPZBALANCEFORM2.CLOSING_BALANCE = 0 THEN 0
											ELSE'.$BRANCH_CODE.'
							END) OPENING_ACCOUNTS,
			SUM(VWTMPZBALANCEFORM2.CLOSING_BALANCE) OPENING_LEDGER_BALANCE,
			0 SANCTION_ACCOUNTS,
			0 SANCTION_AMOUNT,
			0 RECOVERD_ACCOUNTS,
			0 RECOVERD_AMOUNT,
			0 CLOSING_ACCOUNTS,
			0 CLOSING_LEDGER_BALANCE
		FROM LNMASTER
		LEFT OUTER JOIN
			(SELECT LNMASTER."AC_ACNOTYPE",
					LNMASTER."AC_TYPE",
					LNMASTER."AC_NO",
					LNMASTER."AC_OPDATE",
					LNMASTER."AC_CLOSEDT",
					(COALESCE(CASE
																			WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN CAST(LNMASTER."AC_OP_BAL" AS float)
																			ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS float)
															END,

							0) + COALESCE(LOANTRAN.TRAN_AMOUNT,

													0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

																			0)) CLOSING_BALANCE,
					(COALESCE(CASE
																			WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN LNMASTER."AC_RECBLEINT_OP"
																			ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
															END,

							0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,

													0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,

																			0) + COALESCE(CASE
																																					WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float)
																																					ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float)
																																	END,

																									0) + COALESCE(LOANTRAN.OTHER10_AMOUNT,

																															0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,

																																					0)) RECPAY_INT_AMOUNT
				FROM LNMASTER
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
																				END),

								0) TRAN_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN "RECPAY_INT_AMOUNT"
																								ELSE (-1) * "RECPAY_INT_AMOUNT"
																				END),

								0) RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN "OTHER10_AMOUNT"
																								ELSE (-1) * "OTHER10_AMOUNT"
																				END),

								0) OTHER10_AMOUNT
						FROM LOANTRAN
						WHERE CAST("TRAN_DATE" AS date) <= CAST('.$sdate.' AS date)
							AND "BRANCH_CODE" ='.$BRANCH_CODE.'
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") LOANTRAN ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
																																	AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS float)
																																	AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.' AND LNMASTER."status"='.$transtatus.' and "SYSCHNG_LOGIN" IS NOT NULL 
																																	AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO")
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
																				END),

								0) DAILY_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("RECPAY_INT_AMOUNT" AS float)
																								ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS float)
																				END),

								0) DAILY_RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("OTHER10_AMOUNT" AS float)
																								ELSE (-1) * CAST("OTHER10_AMOUNT" AS float)
																				END),

								0) DAILY_OTHER10_AMOUNT
						FROM DAILYTRAN
						WHERE CAST("TRAN_DATE" AS date) <= CAST('.$sdate.' AS date)
							AND "TRAN_STATUS" = '.$transtatus.'
							AND "BRANCH_CODE" ='.$BRANCH_CODE.'
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") DAILYTRAN ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
																																		AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS float)
																																		AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.' AND LNMASTER."status"= '.$transtatus.' and "SYSCHNG_LOGIN" IS NOT NULL
																																		AND LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO" )
				WHERE ((LNMASTER."AC_OPDATE" IS NULL)
											OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
					AND ((LNMASTER."AC_CLOSEDT" IS NULL)
										OR (CAST(LNMASTER."AC_CLOSEDT" AS date) > CAST('.$sdate.' AS date))) )VWTMPZBALANCEFORM2 ON (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2."AC_ACNOTYPE"
																																																																																																									AND LNMASTER."AC_TYPE" = VWTMPZBALANCEFORM2."AC_TYPE"
																																																																																																									AND LNMASTER."AC_NO" = VWTMPZBALANCEFORM2."AC_NO")
		WHERE LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2."AC_ACNOTYPE"
			AND VWTMPZBALANCEFORM2.CLOSING_BALANCE <> 0
			AND ((LNMASTER."AC_OPDATE" IS NULL)
								OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$sdate.' AS date)))
			AND ((LNMASTER."AC_CLOSEDT" IS NULL)
								OR (CAST(LNMASTER."AC_CLOSEDT" AS date) >= CAST('.$sdate.' AS date)))
			AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'
		GROUP BY LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE"
		UNION ALL SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			0 OPENING_ACCOUNTS,
			0 OPENING_LEDGER_BALANCE,
			SUM(CASE
											WHEN LNMASTER.AC_SANCTION_AMOUNT = 0 THEN 0
											ELSE'.$BRANCH_CODE.'
							END) SANCTION_ACCOUNTS,
			COALESCE(SUM(LNMASTER.AC_SANCTION_AMOUNT),
				0) SANCTION_AMOUNT,
			0 RECOVERD_ACCOUNTS,
			0 RECOVERD_AMOUNT,
			0 CLOSING_ACCOUNTS,
			0 CLOSING_LEDGER_BALANCE
		FROM
			(SELECT LNMASTER."AC_ACNOTYPE",
					LNMASTER."AC_TYPE",
					LNMASTER."AC_NO",
					(COALESCE(LOANTRAN.TRAN_AMOUNT,

							0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

													0))AC_SANCTION_AMOUNT
				FROM LNMASTER
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE 0
																				END),

								0) TRAN_AMOUNT
						FROM LOANTRAN
						WHERE CAST("TRAN_DATE" AS date) >= CAST('.$sdate.' AS date) AND "BRANCH_CODE"=1
							AND CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") LOANTRAN ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
																																	AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS float)
																																	AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" AND LNMASTER."status"='.$transtatus.' and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL )
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE 0
																				END),

								0) DAILY_AMOUNT
						FROM DAILYTRAN
						WHERE CAST("TRAN_DATE" AS date) >= CAST('.$sdate.' AS date)
							AND CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
							AND "TRAN_STATUS" = '.$transtatus.'
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") DAILYTRAN ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
																																		AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS float)
																																		AND LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO" )
				WHERE CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$edate.' AS date)
			 AND LNMASTER."status"='.$transtatus.' and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
					AND (LNMASTER."AC_CLOSEDT" IS NULL
										OR CAST(LNMASTER."AC_CLOSEDT" AS date) >= CAST('.$sdate.' AS date)) ) LNMASTER
		WHERE LNMASTER.AC_SANCTION_AMOUNT <> 0  
		GROUP BY LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE" 
	 UNION ALL SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			0 OPENING_ACCOUNTS,
			0 OPENING_LEDGER_BALANCE,
			0 SANCTION_ACCOUNTS,
			0 SANCTION_AMOUNT,
			SUM(CASE
											WHEN LNMASTER.RECOVERD_AMOUNT = 0 THEN 0
											ELSE'.$BRANCH_CODE.'
							END) RECOVERD_ACCOUNTS,
			COALESCE(SUM(LNMASTER.RECOVERD_AMOUNT),
				0) RECOVERD_AMOUNT,
			0 CLOSING_ACCOUNTS,
			0 CLOSING_LEDGER_BALANCE
		FROM
			(SELECT LNMASTER."AC_ACNOTYPE",
					LNMASTER."AC_TYPE",
					LNMASTER."AC_NO",
					(COALESCE(LOANTRAN.TRAN_AMOUNT,

							0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

													0))RECOVERD_AMOUNT
				FROM LNMASTER
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trancr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE 0
																				END),

								0) TRAN_AMOUNT
						FROM LOANTRAN
						WHERE CAST("TRAN_DATE" AS date) >= CAST('.$sdate.' AS date)
							AND CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date) AND "BRANCH_CODE"=1
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") LOANTRAN ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
																																	AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS float)
																																	AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO")
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trancr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE 0
																				END),

								0) DAILY_AMOUNT
						FROM DAILYTRAN
						WHERE CAST("TRAN_DATE" AS date) >= CAST('.$sdate.' AS date)
							AND CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
							AND "TRAN_STATUS" = '.$transtatus.'
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") DAILYTRAN ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
																																		AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS float)
																																		AND LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO" )
				WHERE CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$edate.' AS date) AND  LNMASTER."status"='.$transtatus.' and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
					AND (LNMASTER."AC_CLOSEDT" IS NULL
										OR CAST(LNMASTER."AC_CLOSEDT" AS date) >= CAST('.$sdate.' AS date)) )LNMASTER
		WHERE LNMASTER.RECOVERD_AMOUNT <> 0
		GROUP BY LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE"
		UNION ALL SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			0 OPENING_ACCOUNTS,
			0 OPENING_LEDGER_BALANCE,
			0 SANCTION_ACCOUNTS,
			0 SANCTION_AMOUNT,
			0 RECOVERD_ACCOUNTS,
			0 RECOVERD_AMOUNT,
			SUM(CASE
											WHEN VWTMPZBALANCEFORM2B.CLOSING_BALANCE = 0 THEN 0
											ELSE'.$BRANCH_CODE.'
							END) CLOSING_ACCOUNTS,
			SUM(VWTMPZBALANCEFORM2B.CLOSING_BALANCE) CLOSING_LEDGER_BALANCE
		FROM LNMASTER
		LEFT OUTER JOIN
			(SELECT LNMASTER."AC_ACNOTYPE",
					LNMASTER."AC_TYPE",
					LNMASTER."AC_NO",
					LNMASTER."AC_OPDATE",
					LNMASTER."AC_CLOSEDT",
					(COALESCE(CASE
																			WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN CAST(LNMASTER."AC_OP_BAL" AS float)
																			ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS float)
															END,

							0) + COALESCE(LOANTRAN.TRAN_AMOUNT,

													0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

																			0)) CLOSING_BALANCE,
					(COALESCE(CASE
																			WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN LNMASTER."AC_RECBLEINT_OP"
																			ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
															END,

							0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,

													0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,

																			0) + COALESCE(CASE
																																					WHEN LNMASTER."AC_OP_CD" = '.$trandr.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float)
																																					ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float)
																																	END,

																									0) + COALESCE(LOANTRAN.OTHER10_AMOUNT,

																															0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,

																																					0)) RECPAY_INT_AMOUNT
				FROM LNMASTER
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
																				END),

								0) TRAN_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN "RECPAY_INT_AMOUNT"
																								ELSE (-1) * "RECPAY_INT_AMOUNT"
																				END),

								0) RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN "OTHER10_AMOUNT"
																								ELSE (-1) * "OTHER10_AMOUNT"
																				END),

								0) OTHER10_AMOUNT
						FROM LOANTRAN
						WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date) AND "BRANCH_CODE"=1
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") LOANTRAN ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
																																	AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS float)
																																	AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" )
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("TRAN_AMOUNT" AS float)
																								ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
																				END),

								0) DAILY_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("RECPAY_INT_AMOUNT" AS float)
																								ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS float)
																				END),

								0) DAILY_RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE
																								WHEN "TRAN_DRCR" = '.$trandr.' THEN CAST("OTHER10_AMOUNT" AS float)
																								ELSE (-1) * CAST("OTHER10_AMOUNT" AS float)
																				END),

								0) DAILY_OTHER10_AMOUNT
						FROM DAILYTRAN
						WHERE CAST("TRAN_DATE" AS date) <= CAST('.$edate.' AS date)
							AND "TRAN_STATUS" = '.$transtatus.'
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO") DAILYTRAN ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
																																		AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS float)
																																		AND LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO")
				WHERE ((LNMASTER."AC_OPDATE" IS NULL)
											OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$edate.' AS date)))
			 AND LNMASTER."status"='.$transtatus.' and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
					AND ((LNMASTER."AC_CLOSEDT" IS NULL)
										OR (CAST(LNMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date))) )VWTMPZBALANCEFORM2B ON (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2B."AC_ACNOTYPE"
																																																																																																										AND LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2B."AC_ACNOTYPE"
																																																																																																										AND LNMASTER."AC_TYPE" = VWTMPZBALANCEFORM2B."AC_TYPE"
																																																																																																										AND LNMASTER."AC_NO" = VWTMPZBALANCEFORM2B."AC_NO")
		WHERE ((LNMASTER."AC_CLOSEDT" IS NULL)
									OR (CAST(LNMASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS date)))
		GROUP BY LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE") TMP left join schemast on schemast.id=TMP."AC_TYPE"
WHERE ((OPENING_LEDGER_BALANCE + SANCTION_AMOUNT + CLOSING_LEDGER_BALANCE) - RECOVERD_AMOUNT) <> 0
GROUP BY "AC_ACNOTYPE",
	"AC_TYPE",SCHEMAST."S_APPL",SCHEMAST."S_NAME"';




















$sql =  pg_query($conn,$query);
$g1=0;
$g2=0;
$g3=0;
$g4=0;
$g5=0;
$g6=0;
$g7=0;
$g8=0;



$i = 0;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql)){
    $g1 = $g1 + $row["opening_accounts"];
	$g2 = $g2 + $row["opening_ledger_balance"];
	$g3 = $g3 + $row["sanction_accounts"];
	$g4 = $g4 + $row["sanction_amount"];
	$g5 = $g5 + $row["recoverd_accounts"];
	$g6 = $g6 + $row["recoverd_amount"];
	$g7 = $g7 + $row["closing_accounts"];
	$g8 = $g8 + $row["closing_ledger_balance"];
	$tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_ACNOTYPE" => $row["S_APPL"]. ' ' . $row['S_NAME'],
        "AC_TYPE" => $row["AC_TYPE"],
        "OPENING_ACCOUNTS" => $row['opening_accounts'],
        "OPENING_LEDGER_BALANCE" => sprintf("%.2f", ($row['opening_ledger_balance']+ 0.0)),
        "SANCTION_ACCOUNTS" => $row['sanction_accounts'],
        "SANCTION_AMOUNT" => sprintf("%.2f", ($row['sanction_amount']+ 0.0)),
        "RECOVERD_ACCOUNTS" => $row["recoverd_accounts"],
        "RECOVERD_AMOUNT" => sprintf("%.2f", ($row['recoverd_amount']+ 0.0)),
        "CLOSING_ACCOUNTS" => $row['closing_accounts'],
        "CLOSING_LEDGER_BALANCE" => sprintf("%.2f", ($row['closing_ledger_balance']+ 0.0)),
        "sdate" => $sdate1,
        "edate" => $edate1,
        "Branch" => $Branch1,
        "bankname" => $bankname1,
        "transtatus" => $transtatus,
        "BRANCH_CODE" => $BRANCH_CODE ,
		"g1" => $g1 ,
		"g2" => sprintf("%.2f", ($g2+ 0.0)),
		"g3" => $g3 ,
		"g4" => sprintf("%.2f", ($g4+ 0.0)),
		"g5" => $g5 ,
		"g6" => sprintf("%.2f", ($g6+ 0.0)),
		"g7" => $g7 ,
		"g8" => sprintf("%.2f", ($g8+ 0.0)),

    ];
    $data[$i]=$tmp;
    $i++;
  
}
ob_end_clean();
   
$config = ['driver'=>'array','data'=>$data];
//  print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
    
}   
?>

