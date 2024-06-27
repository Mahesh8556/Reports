<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Citywise_Loan_Balance_List_report.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$AC_ACNOTYPE=$_GET['AC_ACNOTYPE'];
$AC_TYPE=$_GET['AC_TYPE'];
$etdate = $_GET['etdate'];
$stdate =$_GET['stdate'];
$branchName =$_GET['branchName'];
$AC_CTCODE =$_GET['AC_CTCODE'];
$bankName =$_GET['bankName'];
$branch_code =$_GET['branch_code'];

$D = "'D'";
// $AC_CTCODE="'1'";
$TRAN_STATUS="'1'";

// $branch = $_GET['branch'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);


$query='SELECT SCHEMAST."S_NAME",
	SCHEMAST."S_APPL",
	GUARANTERDETAILS."AC_NAME",
	CUST."CITY_NAME",
	LNMASTER."AC_OPDATE",
	LNMASTER."idmasterID",
	CUSTOMERADDRESS."AC_CTCODE",
	LNMASTER."AC_EXPIRE_DATE",
	VWCITYWISELOANLIST."AC_ACNOTYPE",
	VWCITYWISELOANLIST."AC_TYPE",
	VWCITYWISELOANLIST."BANKACNO",
	CLOSING_BALANCE,
	LNMASTER."AC_NAME" AS NAME,
	CUSTOMERADDRESS."AC_CTCODE"
FROM LNMASTER
INNER JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE"
INNER JOIN
	(SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			LNMASTER."BANKACNO",
			LNMASTER."AC_OPDATE",
			LNMASTER."AC_CLOSEDT",
			(COALESCE(CASE LNMASTER."AC_OP_CD"
			WHEN '.$D.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
			ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
			END, 0) + COALESCE(CAST(LOANTRAN.TRAN_AMOUNT AS FLOAT), 0) + 
			 COALESCE(CAST(DAILYTRAN.DAILY_AMOUNT AS FLOAT), 0)) CLOSING_BALANCE,
			(COALESCE(CASE LNMASTER."AC_OP_CD"
			WHEN '.$D.' THEN CAST(LNMASTER."AC_RECBLEINT_OP" AS FLOAT)
			ELSE (-1) * CAST(LNMASTER."AC_RECBLEINT_OP" AS FLOAT)
		    END,	0) + COALESCE(CAST(LOANTRAN.RECPAY_INT_AMOUNT AS FLOAT),
			0) + COALESCE(CAST(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT AS FLOAT),
			0) + COALESCE(CASE LNMASTER."AC_OP_CD"
			WHEN '.$D.' THEN CAST("AC_RECBLEODUEINT_OP" AS FLOAT)
			ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT) END,
			0) + COALESCE(CAST(LOANTRAN.OTHER10_AMOUNT AS FLOAT),
			0) + COALESCE(CAST(DAILYTRAN.DAILY_OTHER10_AMOUNT AS FLOAT),
			0)) RECPAY_INT_AMOUNT	FROM LNMASTER
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(CASE "TRAN_DRCR"
					WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS float)
					ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
					END),

						0) TRAN_AMOUNT,
					COALESCE(SUM(CASE "TRAN_DRCR"
					WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS float)
						ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS float)
								END),

						0) RECPAY_INT_AMOUNT,
					COALESCE(SUM(CASE "TRAN_DRCR"
					WHEN '.$D.' THEN CAST("OTHER10_AMOUNT" AS float)
							ELSE (-1) * CAST("OTHER10_AMOUNT" AS float)
						END),	0) OTHER10_AMOUNT
				FROM LOANTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$stdate.'AS date)
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(CASE "TRAN_DRCR"
					WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS float)
				ELSE (-1) * CAST("TRAN_AMOUNT" AS float)
					END),		0) DAILY_AMOUNT,
			COALESCE(SUM(CASE "TRAN_DRCR"
			WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS float)
			ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS float)
			END),

						0) DAILY_RECPAY_INT_AMOUNT,
					COALESCE(SUM(CASE "TRAN_DRCR"
			WHEN '.$D.' THEN CAST("OTHER10_AMOUNT" AS float)
			ELSE (-1) * CAST("OTHER10_AMOUNT" AS float)	END),
			0) DAILY_OTHER10_AMOUNT
				FROM DAILYTRAN
				WHERE CAST("TRAN_DATE" AS date) <= CAST('.$etdate.'AS date)
					AND "TRAN_STATUS" = '.$TRAN_STATUS.'
					AND "BRANCH_CODE" = '.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DAILYTRAN ON LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
		WHERE ((LNMASTER."AC_OPDATE" IS NULL)
									OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.'AS DATE)))
			AND LNMASTER."status" = 1
			AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
			AND ((LNMASTER."AC_CLOSEDT" IS NULL)
								OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$etdate.'AS DATE))) ) VWCITYWISELOANLIST ON LNMASTER."BANKACNO" = VWCITYWISELOANLIST."BANKACNO"
LEFT OUTER JOIN GUARANTERDETAILS ON LNMASTER."BANKACNO" = GUARANTERDETAILS."AC_NO"
LEFT OUTER JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID"
LEFT OUTER JOIN
	(SELECT CITYMASTER."CITY_NAME",
			CITYMASTER."CITY_CODE",
			CUSTOMERADDRESS."idmasterID"
		FROM CITYMASTER
		INNER JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID) CUST ON CUST."idmasterID" = LNMASTER."idmasterID"
WHERE ("EXP_DATE" IS NULL
							OR CAST("EXP_DATE" AS DATE) > CAST('.$etdate.' AS DATE))
	AND (LNMASTER."AC_CLOSEDT" IS NULL
						OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$etdate.' AS DATE))
	AND CLOSING_BALANCE > 0
	AND CUSTOMERADDRESS."AC_CTCODE" = '.$AC_CTCODE.'
	AND VWCITYWISELOANLIST."AC_TYPE" = '.$AC_TYPE.'
		AND VWCITYWISELOANLIST."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'';
// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$total = 0;
$citywise= 0;



// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
           $total=$total+$row['closing_balance'],

            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'S_APPL' => $row['S_APPL'],
            'AC_EXPIRE_DATE' => $row['AC_EXPIRE_DATE'],
            'BANKACNO' => $row['BANKACNO'],
            'name' => $row['name'],
            'S_NAME' => $row['S_NAME'],
            'AC_NAME' => $row['AC_NAME'],
            'CITY_NAME' => $row['CITY_NAME'],
            'closing_balance' =>sprintf("%.2f", ($row['closing_balance'] + 0.0)),

            
           'stdate' => $stdate_,
           'etdate' => $etdate_,
            'branchName' => $branchName,
            'branch_code' => $branch_code,
            '.$D.' => $D,
            'AC_CTCODE'=>$AC_CTCODE,
            'TRAN_STATUS' => $TRAN_STATUS,
            // 'city'=>$city,
            'total'=>sprintf("%.2f", ($total+ 0.0)),
            'citywise'=>$citywise,

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
    
    
// }
?>
