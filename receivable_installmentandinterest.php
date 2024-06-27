<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/receivable_installmentandinterest.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$dd="'DD/MM/YYYY'";

$date = $_GET['date'];
$branchName = $_GET['branchName'];
$branch = $_GET['branch'];
$scheme_name = $_GET['scheme_name'];
$scheme  = $_GET['scheme'];
$FLAG = $_GET['FLAG'];
$bankName = $_GET['bankName'];
$date1=$_GET['date'];



$arr= array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16);
$arr = $scheme_name;

// $S = explode($scheme_name);
$var="'D'";
$var1="'-'";
$var2="'O'";
$M="'M'";$Q="'Q'";$H="'H'";$Y="'Y'";
// $var3="'DP'";
// $var4="'NULL'";


$bankName = str_replace("'", "", $bankName);
// $stdate_ = str_replace("'", "", $stdate);
// $etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);
$date1_ = str_replace("'", "", $date1);
$date_ = str_replace("'", "", $date);

$query = '';
if($FLAG == 1)
{$query.='SELECT "AC_ACNOTYPE",
	"AC_TYPE",
	"BANKACNO",
	"AC_NAME",
	"AC_SANCTION_AMOUNT",
	"AC_OPDATE",
	"AC_EXPIRE_DATE",
	"AC_REPAYMODE",
	"AC_INSTALLMENT",
	SCHEME_NAME,
	CLOSING_BALANCE,
	RECPAY_INT_AMOUNT,
	CAST(TOTINST AS float),
	CASE LEFT(CAST(DUE_PRM AS CHARACTER VARYING),1)	WHEN '.$var1.' THEN 0 ELSE DUE_PRM END DUE_PRM, CURRENT_INT
FROM
	(SELECT LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			LNMASTER."BANKACNO",
			LNMASTER."AC_NAME",
			LNMASTER."AC_SANCTION_AMOUNT",
			LNMASTER."AC_OPDATE",
			LNMASTER."AC_EXPIRE_DATE",
			LNMASTER."AC_REPAYMODE",
			LNMASTER."AC_INSTALLMENT",
			SCHEMAST."S_NAME" SCHEME_NAME,
			COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,
				0) CLOSING_BALANCE,
			COALESCE(VWTMPZBALANCEINSTALL.RECPAY_INT_AMOUNT,
				0) RECPAY_INT_AMOUNT,
			CASE "AC_REPAYMODE"
							WHEN '.$var2.' THEN CASE LEFT(CAST(((EXTRACT(YEAR FROM CAST("AC_EXPIRE_DATE" AS date)) - EXTRACT(YEAR FROM CAST('.$date.' AS date))) * 12) 
			 + EXTRACT(MONTH FROM CAST("AC_EXPIRE_DATE" AS date)) 
			- EXTRACT(MONTH FROM CAST('.$date.' AS date)) AS CHARACTER VARYING), 1)	WHEN '.$var1.' THEN 1 ELSE 0 END
			 ELSE ((EXTRACT(YEAR FROM CAST((CASE (LEFT(CAST(((EXTRACT(YEAR FROM CAST("AC_EXPIRE_DATE" AS date)) - EXTRACT(YEAR FROM CAST('.$date.' AS date))) * 12) 
			+ EXTRACT(MONTH FROM CAST("AC_EXPIRE_DATE" AS date)) 
	- EXTRACT(MONTH FROM CAST('.$date.' AS date)) AS CHARACTER VARYING),1))
	WHEN '.$var1.' THEN CAST("AC_EXPIRE_DATE" AS date)
			ELSE CAST('.$date.' AS DATE)	END) AS DATE)) 
				- EXTRACT(YEAR FROM CAST(LNMASTER."AC_OPDATE" AS date)) * 12) + EXTRACT(MONTH FROM CAST((CASE (LEFT(CAST(((EXTRACT(YEAR FROM CAST("AC_EXPIRE_DATE" AS date)) 
			- EXTRACT(YEAR FROM CAST('.$date.' AS date))) * 12) + EXTRACT(MONTH FROM CAST("AC_EXPIRE_DATE" AS date)) - EXTRACT(MONTH FROM CAST('.$date.' AS date)) AS CHARACTER VARYING),1)) WHEN '.$var1.' THEN CAST("AC_EXPIRE_DATE" AS date) ELSE CAST('.$date.' AS DATE)
				END) AS DATE)) - EXTRACT(MONTH
							FROM CAST('.$date.' AS date)))
			END / (CASE "AC_REPAYMODE"
		WHEN '.$M.' THEN 1
		WHEN '.$Q.' THEN 3
		WHEN '.$H.' THEN 6
		WHEN '.$Y.' THEN 12
		WHEN '.$var2.' THEN 1
										END)TOTINST,
			CASE(LEFT(CAST(((EXTRACT(YEAR
						FROM CAST("AC_EXPIRE_DATE" AS date)) - EXTRACT(YEAR
FROM CAST('.$date.' AS date))) * 12) + EXTRACT(MONTH
FROM CAST("AC_EXPIRE_DATE" AS date)) - EXTRACT(MONTH
FROM CAST('.$date.' AS date)) AS CHARACTER VARYING),

									1))
							WHEN '.$var1.' THEN (COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,

	0))
							ELSE (COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,

		0) - (CAST("AC_SANCTION_AMOUNT" AS FLOAT) - (CAST("AC_INSTALLMENT" AS INTEGER) * ((EXTRACT(YEAR
	FROM CAST(LNMASTER."AC_OPDATE" AS date)) - EXTRACT(YEAR
				FROM CAST('.$date.' AS date)) * 12) + EXTRACT(MONTH
									FROM CAST(LNMASTER."AC_OPDATE" AS date)) - EXTRACT(MONTH
							FROM CAST('.$date.' AS date))) / CASE "AC_REPAYMODE"
						WHEN '.$M.' THEN 1
		WHEN '.$Q.' THEN 3
		WHEN '.$H.' THEN 6
WHEN '.$Y.' THEN 12
		END)))
			END DUE_PRM,
			0 CURRENT_INT
		FROM LNMASTER
		LEFT OUTER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.ID
		LEFT OUTER JOIN
			(SELECT LNMASTER."AC_ACNOTYPE",
					LNMASTER."AC_TYPE",
					LNMASTER."BANKACNO",
					LNMASTER."AC_OPDATE",
					LNMASTER."AC_CLOSEDT",
					(COALESCE(CASE LNMASTER."AC_OP_CD"
							WHEN '.$var.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
							ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
			END,

							0) + COALESCE(CAST(LOANTRAN.TRAN_AMOUNT AS FLOAT),

	0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,

							0)) CLOSING_BALANCE,
					(COALESCE(CASE LNMASTER."AC_OP_CD"
							WHEN '.$var.' THEN LNMASTER."AC_RECBLEINT_OP"
							ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
			END,

							0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,

	0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,

							0) + COALESCE(CASE LNMASTER."AC_OP_CD"
			WHEN '.$var.' THEN CAST("AC_RECBLEODUEINT_OP" AS FLOAT)
			ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
											END,

			0) + COALESCE(LOANTRAN.OTHER10_AMOUNT,

									0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,

			0)) RECPAY_INT_AMOUNT
				FROM LNMASTER
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
								END),

								0)TRAN_AMOUNT,
							COALESCE(SUM(CASE"TRAN_DRCR"
		WHEN '.$var.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
								END),

								0) RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE"TRAN_DRCR"
		WHEN '.$var.' THEN "OTHER10_AMOUNT"
		ELSE (-1) * "OTHER10_AMOUNT"
								END),

								0) OTHER10_AMOUNT
						FROM LOANTRAN
						WHERE CAST("TRAN_DATE" AS Date) <= CAST('.$date.' AS date)
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_AMOUNT",
							"TRAN_ACNO") LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
				LEFT OUTER JOIN
					(SELECT "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$var.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
								END),

								0) DAILY_AMOUNT,
							COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$var.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
								END),

								0) DAILY_RECPAY_INT_AMOUNT,
							COALESCE(SUM(CASE "TRAN_DRCR"
		WHEN '.$var.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
								END),

								0) DAILY_OTHER10_AMOUNT
						FROM DAILYTRAN
						WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$date.' AS DATE)
							AND CAST("TRAN_STATUS" AS INTEGER) = 1
						GROUP BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO",
							"TRAN_AMOUNT") DAILYTRAN ON LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
				WHERE LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
					AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER)
					AND LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE"
					AND LNMASTER."AC_TYPE" = CAST(DAILYTRAN."TRAN_ACTYPE" AS INTEGER) AND LNMASTER."AC_TYPE"= '.$scheme.'
					AND ((LNMASTER."AC_OPDATE" IS NULL)
										OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= CAST('.$date.'AS DATE)))
					AND ((LNMASTER."AC_CLOSEDT" IS NULL)
										OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > CAST('.$date.'AS DATE)))
			)VWTMPZBALANCEINSTALL ON LNMASTER."BANKACNO" = VWTMPZBALANCEINSTALL."BANKACNO"
					WHERE VWTMPZBALANCEINSTALL.CLOSING_BALANCE <> 0 ) TMP
WHERE DUE_PRM > 0



';}

else
{$query.='SELECT "AC_ACNOTYPE", "AC_TYPE", "BANKACNO", "AC_NAME", 
    "AC_SANCTION_AMOUNT", "AC_OPDATE", "AC_EXPIRE_DATE", "AC_REPAYMODE", "AC_INSTALLMENT",
	SCHEME_NAME, 
    CLOSING_BALANCE, RECPAY_INT_AMOUNT, cast(TOTINST as float), 
	CASE LEFT(CAST(DUE_PRM AS CHARACTER VARYING),1) WHEN '.$var1.' THEN 0  ELSE DUE_PRM END DUE_PRM, CURRENT_INT 
	
	FROM (
    SELECT 
    LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NAME", 
    LNMASTER."AC_SANCTION_AMOUNT" , LNMASTER."AC_OPDATE", LNMASTER."AC_EXPIRE_DATE", 
    LNMASTER."AC_REPAYMODE", LNMASTER."AC_INSTALLMENT", 
    SCHEMAST."S_NAME" SCHEME_NAME , 
    COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,0) CLOSING_BALANCE , 
    COALESCE(VWTMPZBALANCEINSTALL.RECPAY_INT_AMOUNT,0) RECPAY_INT_AMOUNT , 
case "AC_REPAYMODE" 
WHEN  '.$var2.'
    
THEN		 
   Case		
    LEFT(CAST(((extract( year FROM cast("AC_EXPIRE_DATE" as date) ) - extract( year FROM cast('.$date.' as date) )) *12)
    + extract(MONTH FROM cast("AC_EXPIRE_DATE"  as date) ) - extract(MONTH FROM cast('.$date.' as date)) AS CHARACTER VARYING),1)
	WHEN '.$var1.' 
	THEN 1 
	ELSE 0
   END 
	
ELSE
   
		((extract(YEAR FROM CAST(
	
	(CASE (LEFT(CAST(((extract( year FROM cast("AC_EXPIRE_DATE" as date) ) - extract( year FROM cast('.$date.' as date) )) *12)
    + extract(MONTH FROM cast("AC_EXPIRE_DATE"  as date) ) - extract(MONTH FROM cast('.$date.' as date)) AS CHARACTER VARYING),1))
    WHEN '.$var1.' 
	THEN cast("AC_EXPIRE_DATE"  as date) 
	ELSE CAST('.$date.' AS DATE)
	END)   AS DATE))
    -extract( year FROM cast(LNMASTER."AC_OPDATE" as date))*12)
	
	+ extract(MONTH FROM cast((CASE (LEFT(CAST(((extract( year FROM cast("AC_EXPIRE_DATE" as date) ) - extract( year FROM cast('.$date.' as date) )) *12)
    + extract(MONTH FROM cast("AC_EXPIRE_DATE"  as date) ) - extract(MONTH FROM cast('.$date.' as date)) AS CHARACTER VARYING),1))
    WHEN '.$var1.' 
	THEN cast("AC_EXPIRE_DATE"   as date)
	ELSE CAST('.$date.' AS DATE)
	END)   AS DATE) )
		
	- extract(MONTH FROM cast('.$date.' as date))
	
	)
	
		
END
		
/ (CASE "AC_REPAYMODE" 
   WHEN '.$M.' THEN  1  
   WHEN '.$Q.' THEN  3  
   WHEN '.$H.' THEN  6 
   WHEN '.$Y.' THEN  12  
   WHEN '.$var2.' THEN  1 
  END )TOTINST ,
    
   Case(LEFT(CAST(((extract( year FROM cast("AC_EXPIRE_DATE" as date) ) - extract( year FROM cast('.$date.' as date) )) *12)
   + extract(MONTH FROM cast("AC_EXPIRE_DATE"  as date) ) - extract(MONTH FROM cast('.$date.' as date)) AS CHARACTER VARYING),1))
			 
  WHEN '.$var1.' 
  THEN (COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,0))
  ELSE 
		(COALESCE(VWTMPZBALANCEINSTALL.CLOSING_BALANCE,0) 
		- (CAST("AC_SANCTION_AMOUNT" AS FLOAT) - (CAST("AC_INSTALLMENT" AS INTEGER)* 
 ((extract( year FROM cast(LNMASTER."AC_OPDATE" as date) ) 
			 - extract( year FROM cast('.$date1.' as date) ) *12)
			 + extract(MONTH FROM cast(LNMASTER."AC_OPDATE" as date) )
			 - extract(MONTH FROM cast('.$date1.' as date) )) 

   / CASE "AC_REPAYMODE" 
	 WHEN '.$M.' THEN  1  
	 WHEN '.$Q.' THEN  3 
	 WHEN '.$H.' THEN  6 
	 WHEN '.$Y.' THEN  12 
	 END)))
  END DUE_PRM, 0 CURRENT_INT 
  FROM LNMASTER
		
LEFT OUTER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.id
		
		
LEFT OUTER JOIN(SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", 
 LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT" 
, (COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$var.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END,0) 
   + COALESCE(CAST(LOANTRAN.TRAN_AMOUNT AS FLOAT),0)
   + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE
,  (COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$var.' 
	THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END,0)
+ COALESCE(LOANTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 
+ COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$var.' THEN CAST("AC_RECBLEODUEINT_OP" AS FLOAT)
		   ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT) END,0)
+ COALESCE(LOANTRAN.OTHER10_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT 
   FROM LNMASTER
   
LEFT OUTER JOIN( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",
  COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$var.' 
			   THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0)TRAN_AMOUNT 
, COALESCE(SUM(CASE"TRAN_DRCR"  WHEN '.$var.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END),0) RECPAY_INT_AMOUNT 
, COALESCE(SUM(CASE"TRAN_DRCR" WHEN '.$var.' THEN "OTHER10_AMOUNT" ELSE (-1) * "OTHER10_AMOUNT" END),0) OTHER10_AMOUNT
 FROM LOANTRAN 
WHERE CAST( "TRAN_DATE"  AS Date)<= cast('.$date.' as date)
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_AMOUNT","TRAN_ACNO"
) LOANTRAN 
ON LNMASTER."BANKACNO" =  LOANTRAN."TRAN_ACNO"


LEFT OUTER JOIN( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
 COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$var.' 
	THEN CAST("TRAN_AMOUNT" AS FLOAT)
	ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)END),0) DAILY_AMOUNT 
, COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$var.' 
THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT)  
ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)END),0) DAILY_RECPAY_INT_AMOUNT  
, COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$var.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
		ELSE (-1) * CAST("OTHER10_AMOUNT" AS FLOAT) END ),0) DAILY_OTHER10_AMOUNT  
FROM DAILYTRAN 
 WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$date.' AS DATE)
                 AND CAST("TRAN_STATUS"  AS INTEGER)= 1 
                 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO","TRAN_AMOUNT"
 ) DAILYTRAN
 ON LNMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
 
Where      LNMASTER."AC_ACNOTYPE"  = LOANTRAN."TRAN_ACNOTYPE"
            AND LNMASTER."AC_TYPE"  = CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER)
            AND LNMASTER."AC_ACNOTYPE"  = DAILYTRAN."TRAN_ACNOTYPE"
            AND LNMASTER."AC_TYPE"  = CAST(DAILYTRAN."TRAN_ACTYPE" AS INTEGER) AND LNMASTER."AC_TYPE"= '.$scheme.'
            and ((LNMASTER."AC_OPDATE" IS NULL) 
				 OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= TO_DATE('.$date.','.$dd.')))
            AND ((LNMASTER."AC_CLOSEDT" IS NULL)
				 OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE('.$date.','.$dd.')))
  )VWTMPZBALANCEINSTALL
ON LNMASTER."BANKACNO" = VWTMPZBALANCEINSTALL."BANKACNO" 
		 		
Where 
    VWTMPZBALANCEINSTALL.CLOSING_BALANCE <> 0 
) TMP';}


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$total_payable_interest = $recpay_int_amount + $current_int;
$t5=0;
$t4=0;
$t3=0;
$t2=0;
$t6=0;
$t7=0;
$t8=0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else
if ($row['closing_balance'] < 0) {
    $netType = 'Dr';
} else {
    $netType = 'Cr';
}
if ($row['AC_INSTALLMENT'] < 0) {
    $netType = 'Dr';
} else {
    $netType = 'Cr';
}
if ($row['totinst'] < 0) {
    $netType = 'Dr';
} else {
    $netType = 'Cr';
}


    while ($row = pg_fetch_assoc($sql)) {
        $tmp = [

        
         
           
            $t2=$t2+$row['due_prm'],
            $t3=$t3+$row['AC_SANCTION_AMOUNT'],
            $t4=$t4+$row['current_int'],
            $t5=$t5+$row['closing_balance'],
            $t6=$t6+$row['recpay_int_amount'],
            $t7=$t7+$row['totinst'],
            $t8=$t8+$row['AC_INSTALLMENT'],
            
            $total_payable_interest=$recpay_int_amount + $row['total_payable_interest'],

           
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'BANKACNO' => $row['BANKACNO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_SANCTION_AMOUNT' =>  sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))).' ',
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_EXPIRE_DATE' => $row['AC_EXPIRE_DATE'],
            'AC_REPAYMODE' => $row['AC_REPAYMODE'],
            'AC_INSTALLMENT' => sprintf("%.2f", (abs($row['AC_INSTALLMENT']))).' ',
            'scheme_name' => $row['scheme_name'],
            'closing_balance' =>sprintf("%.2f", (abs($row['closing_balance']))).' ',   
            'recpay_int_amount' => sprintf("%.2f", ($row['recpay_int_amount'] + 0.0)),
            'totinst' => sprintf("%.2f", (abs($row['totinst']))).' ', 
            'due_prm' => sprintf("%.2f", ($row['due_prm'] + 0.0)),
            'current_int'=>sprintf("%.2f", ($row['current_int'] + 0.0)),
            // 'LAST_EXEC_DATE' => $row['LAST_EXEC_DATE'],
            // 'SYSADD_LOGIN' => $row['SYSADD_LOGIN'],
            // 'ac_name' => $row['ac_name'],
            // 'UserCode' => $row['UserCode'],
            // 'NAME' => $row['NAME'],
            // 'startDate' => $startDate,
            // 'endDate' => $endDate,
         

            'FLAG'=>$FLAG,
            'bankName'=>$bankName,
        
            'branchName' => $branchName,
            'date1' => $date1_,
            'date' => $date_,
            // 'etdate' => $etdate,
            // 'GRAND_TOTAL'=>$GRAND_TOTAL,
            // 'schmtotal'=>$schmtotal,
            'total_payable_interest'=>sprintf("%.2f", ($row['total_payable_interest'] + 0.0)),
            't1'=>sprintf("%.2f", $t1+ 0.0),
            't3'=>sprintf("%.2f", $t3 + 0.0),
            't2'=>sprintf("%.2f",$t2+ 0.0),
            't4'=>sprintf("%.2f", $t4 + 0.0),
            't5'=>sprintf("%.2f", $t5 + 0.0),
            't6'=>sprintf("%.2f", $t6 + 0.0),
            't7'=>sprintf("%.2f", (abs($t7))).' ',
            't8'=>sprintf("%.2f", (abs($t8))).' ',
        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();


//echo $query;
//print_r($data);
$config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
//}
?>
