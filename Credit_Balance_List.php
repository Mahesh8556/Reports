<?php
ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Credit_Balance_List.jrxml';
//$filename = __DIR__.'/credit_blc.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$Branch  = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName  = $_GET['BankName'];
//$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$trandrcr="'D'";
$AC_OP_CD="'D'";
$transtatus="'1'";
$QUOTES = $_GET['QUOTES'];
$AC_ACNOTYPE1="'LN'";
// $AC_ACNOTYPE="'DS'";
// $AC_ACNOTYPE="'CS'";
$AC_ACNOTYPE2="'DS'";
$AC_ACNOTYPE3="'CC'";


$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
//$AC_TYPE1 = str_replace("'" , "" , $AC_TYPE);
//$AC_ACNOTYPE2 = str_replace("'" , "" , $AC_ACNOTYPE);


$query='SELECT *
FROM
	(SELECT
	 SCHEMAST."S_APPL", 
	  SCHEMAST."S_NAME", 
	 MASTER."AC_ACNOTYPE",
			MASTER."AC_TYPE",
			MASTER."AC_NO",
			MASTER."AC_MEMBTYPE" MEMBERTYPE,
			MASTER."AC_MEMBNO" MEMBERNO,
			CAST(MASTER."AC_INTRATE" AS double precision)INTRATE,
			'.$QUOTES.' REF_ACNO,
			MASTER."AC_NAME",
			MASTER."AC_OPDATE",
			'.$QUOTES.' AC_PARTICULAR,
			'.$QUOTES.' AC_TDRECEIPTNO,
			NULL AC_EXPDT,	 
			((COALESCE(CASE	WHEN MASTER."AC_OP_CD" = '.$AC_OP_CD.' THEN CAST(MASTER."AC_OP_BAL" AS float)
ELSE (-1) * CAST(MASTER."AC_OP_BAL" AS float)	END,0) + COALESCE(TRAN_TABLE.TRAN_AMOUNT,
0) + COALESCE(DAILY_TABLE.DAILY_AMOUNT,	0)) * -1) CLOSING_BALANCE
		FROM LNMASTER MASTER
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					SUM(COALESCE(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float)END,0)) TRAN_AMOUNT
				FROM LOANTRAN
				WHERE COALESCE(CAST("TRAN_ACNO" AS BIGINT),	CAST(0 AS BIGINT)) <> 0
					AND CAST("TRAN_DATE" AS date) <= DATE('.$edate.')
			 AND "BRANCH_CODE"='.$branch_code.'
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") TRAN_TABLE ON MASTER."BANKACNO" = TRAN_TABLE."TRAN_ACNO"
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					SUM(COALESCE(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float)END,0)) DAILY_AMOUNT
				FROM DAILYTRAN
				WHERE COALESCE(CAST("TRAN_ACNO" AS BIGINT),
											CAST(0 AS BIGINT)) <> 0
					AND "TRAN_STATUS" = '.$transtatus.'
			 AND "BRANCH_CODE"='.$branch_code.'
					AND CAST("TRAN_DATE" AS date) <= DATE('.$edate.')
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") DAILY_TABLE ON
	 (CAST(MASTER."BANKACNO" AS CHARACTER varying) = DAILY_TABLE."TRAN_ACNO")
		LEFT OUTER JOIN
			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					SUM(COALESCE(CASE
WHEN "TRAN_DRCR" = '.$trandrcr.' THEN CAST("TRAN_AMOUNT" AS float)
ELSE (-1) * CAST("TRAN_AMOUNT" AS float)END,0)) INT_TRAN_AMOUNT
				FROM INTERESTTRAN
				WHERE COALESCE(CAST("TRAN_ACNO" AS BIGINT),	CAST(0 AS BIGINT)) <> 0
					AND "TRAN_STATUS" = '.$transtatus.'
			 AND "BRANCH_CODE"='.$branch_code.'
					AND CAST("TRAN_DATE" AS date) <= DATE('.$edate.')
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") INT_TABLE ON MASTER."BANKACNO" = INT_TABLE."TRAN_ACNO"
	 LEFT JOIN SCHEMAST ON SCHEMAST.ID= MASTER."AC_TYPE"
		WHERE (MASTER."AC_OPDATE" IS NULL
									OR CAST(MASTER."AC_OPDATE" AS date) <= CAST('.$edate.' AS DATE))
			AND (MASTER."AC_CLOSEDT" IS NULL
								OR CAST(MASTER."AC_CLOSEDT" AS date) > CAST('.$edate.' AS DATE))
			AND MASTER."AC_ACNOTYPE" IN ('.$AC_ACNOTYPE1.','.$AC_ACNOTYPE2.','.$AC_ACNOTYPE3.')
	AND MASTER."BRANCH_CODE"='.$branch_code.' AND MASTER."status"=1  AND MASTER."SYSCHNG_LOGIN" IS NOT NULL
	 ORDER BY SCHEMAST."S_APPL", MASTER."AC_NO") S
WHERE CLOSING_BALANCE > 0';

// echo $query;
        
$sql =  pg_query($conn,$query);

$i = 0;




// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row["closing_balance"];
	$GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["SCHME_WISE_TOTAL"];


	if(isset($varsd)){
	if($varsd == $row['S_APPL']){
		$sc[] = $row['closing_balance']; 
		$sumVar += $row['closing_balance'];
	   // echo "if part";
	}
	else{
		$sumVar=0;
		$sc = array_diff($sc, $sc);
		$varsd = $row['S_APPL'];
		$sc[] = $row['closing_balance'];
		$sumVar += $row['closing_balance'];
	 //   echo "else1 part";
	}
}else{
	$sumVar=0;
	$varsd = $row['S_APPL'];
	$sc[] = $row['closing_balance'];
	$sumVar += $row['closing_balance'];
   // echo "2nd else part";
}
$result[$varsd] = $sc;
$sumArray[$varsd] = $sumVar;


    $tmp=[
        
        "SR_NO" => $row["SR_NO"],
        "AC_NO"=> $row["AC_NO"],
        "AC_NAME"=> $row["AC_NAME"],
        "closing_balance" => sprintf("%.2f", (abs($row['closing_balance']))),
        "S_APPL" => $row["S_APPL"],
		"S_NAME" => $row["S_NAME"],
   
       
        "Branch" => $Branch ,
		'branch_code' => $branch_code ,
        //"sdate" => $sdate,
        "edate" => $edate2,
        "BankName" => $BankName1 ,
        //"trandrcr" => $trandrcr,
        //"transtatus" => $transtatus,
		"AC_ACNOTYPE1"=>$AC_ACNOTYPE1 ,
	    "AC_ACNOTYPE2"=>$AC_ACNOTYPE2 ,
	    "AC_ACNOTYPE3"=>$AC_ACNOTYPE3 ,
        "QUOTES" => $QUOTES,
        "TOTAL_BALANCE" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
		'stotal'=>sprintf("%.2f", ($sumArray[$varsd])).' '.$netType,
		
		// "SCHME_WISE_TOTAL"=> sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) 
        
        

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
    
//}   
?>
