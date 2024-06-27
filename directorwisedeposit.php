<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/directorwisedeposit.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$stdate= $_GET['stdate'];
$etdate = $_GET['etdate'];
// $S_APPL = $_GET['S_APPL'];
$branch = $_GET['branch'];
$ac_director = $_GET['ac_director'];
$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$var="'D' ";
$TRAN_STATUS="'1'";

// echo $stdate ;
$bankName = str_replace("'", "" , $bankName);
$stdate1 = str_replace("'", "" , $stdate);
$etdate1 = str_replace("'", "" , $etdate);
$branchName = str_replace("'", "" , $branchName);


$query='SELECT TMP."AC_ACNOTYPE", "AC_TYPE", "bankacno" ,"ac_name",SCHEMAST."S_NAME", "AC_CUSTID",
TMP."AC_OPDATE", "AC_CLOSEDT", "AC_SCHMAMT", "AC_REF_RECEIPTNO",  "AC_INTRATE",
"AC_EXPDT", "depo_amount" , "ac_director" , "director_name" 
 , ( COALESCE("balance",0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) BALANCE
 FROM
 (SELECT "AC_ACNOTYPE", "AC_TYPE", MASTER."BANKACNO" BANKACNO,  MASTER."AC_NAME" AC_NAME,
  MASTER."AC_OPDATE", "AC_CLOSEDT", "AC_SCHMAMT", "AC_EXPDT",  "AC_REF_RECEIPTNO","AC_CUSTID",
  MASTER."AC_INTRATE",SCHEMAST."S_NAME"
     , (COALESCE(DEPOTRAN.TRAN_AMOUNT,0) + COALESCE(CASE MASTER."AC_OP_CD" WHEN '.$var.' 
		THEN CAST(MASTER."AC_OP_BAL" AS FLOAT) ELSE (-1) * CAST(MASTER."AC_OP_BAL" AS FLOAT) END,0))BALANCE
     , COALESCE(CAST(MASTER."AC_SCHMAMT" AS FLOAT),0) DEPO_AMOUNT , 
  IDMAST.id AC_DIRECTOR,IDMAST."AC_NAME" DIRECTOR_NAME 
  FROM DPMASTER MASTER
  
  
    LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
 , SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$var.'  
				THEN CAST("TRAN_AMOUNT"  AS FLOAT)
				ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
 FROM DEPOTRAN
   WHERE CAST("TRAN_DATE"  AS DATE)<= CAST('.$etdate.' AS DATE)
 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") DEPOTRAN
  ON  MASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"

 LEFT OUTER JOIN IDMASTER AS IDMAST ON MASTER."idmasterID" = IDMAST.id
 LEFT OUTER JOIN SCHEMAST ON MASTER."AC_TYPE" = SCHEMAST.id
  
  WHERE MASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE"
 AND MASTER."AC_TYPE" = CAST(DEPOTRAN."TRAN_ACTYPE" AS INTEGER)
 
  AND ((CAST(MASTER."AC_OPDATE" AS DATE) IS NULL)
	   OR (CAST(MASTER."AC_OPDATE" AS DATE)<= CAST('.$etdate.' AS DATE)))
 AND ((CAST(MASTER."AC_CLOSEDT" AS DATE) IS NULL)
	  OR (CAST(MASTER."AC_CLOSEDT" AS DATE) > CAST('.$stdate.' AS DATE)))
  
  
  UNION ALL 
 SELECT "AC_ACNOTYPE", "AC_TYPE",CAST(MASTER."AC_NO" AS CHARACTER VARYING) AC_NO,  MASTER."AC_NAME" AC_NAME,
  MASTER."AC_OPDATE", "AC_CLOSEDT", "AC_SCHMAMT", "AC_EXPDT", "AC_REF_RECEIPTNO", "AC_CUSTID",
  "AC_INTCATA",SCHEMAST."S_NAME",
 (COALESCE(PIGMYTRAN.TRAN_AMOUNT,0)
	  + COALESCE(CASE MASTER."AC_OP_CD" WHEN '.$var.'  
	  THEN CAST( MASTER."AC_OP_BAL" AS FLOAT)
				 ELSE (-1) * CAST(MASTER."AC_OP_BAL"  AS FLOAT)END,0))BALANCE
 , COALESCE(CAST(MASTER."AC_SCHMAMT" AS FLOAT),0) DEPOLOAN_AMOUNT ,
 IDMAST.id AC_DIRECTOR,IDMAST."AC_NAME" DIRECTOR_NAME 
  FROM PGMASTER MASTER 
 
  LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"  , 
   SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$var.'  THEN CAST("TRAN_AMOUNT" AS FLOAT) 
	ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
 FROM PIGMYTRAN 
   WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS DATE)
 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) PIGMYTRAN
 ON MASTER."BANKACNO" = PIGMYTRAN."TRAN_ACNO"
  
   
 LEFT OUTER JOIN IDMASTER  AS IDMAST ON IDMAST.id=MASTER."idmasterID"
  LEFT OUTER JOIN SCHEMAST ON MASTER."AC_TYPE" = SCHEMAST.id

  WHERE MASTER."AC_ACNOTYPE" = PIGMYTRAN."TRAN_ACNOTYPE"
 AND MASTER."AC_TYPE" = CAST(PIGMYTRAN."TRAN_ACTYPE" AS INTEGER)
  AND ((CAST(MASTER."AC_OPDATE"  AS DATE)IS NULL) 
	   OR (CAST(MASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.' AS DATE)))
 AND ((CAST(MASTER."AC_CLOSEDT" AS DATE) IS NULL)
	  OR (CAST(MASTER."AC_CLOSEDT" AS DATE) > CAST('.$stdate.' AS DATE)))
)TMP 
 
LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"  ,
 SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$var.'  THEN CAST("TRAN_AMOUNT"  AS FLOAT)
	ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0))
 DAILY_AMOUNT 
 FROM DAILYTRAN 
 WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS DATE)
 AND "TRAN_STATUS" = '.$TRAN_STATUS.'
 GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) DAILYTRAN
 ON TMP."bankacno"= DAILYTRAN."TRAN_ACNO"
 
 LEFT OUTER JOIN SCHEMAST ON TMP."AC_TYPE" = SCHEMAST.id

 WHERE TMP.BALANCE <> 0';


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$TOTAL = 0;
if ($row['balance'] < 0) {
  $netType = 'Dr';
} else {
  $netType = 'Cr';
}

// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
        $tmp = [

           
          $TOTAL=$TOTAL+$row['balance'],
          $T1=$T1+$row['depo_amount'],

            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'AC_EXPDT' => $row['AC_EXPDT'],
            'bankacno' => $row['bankacno'],
            'ac_name' => $row['ac_name'],
            'S_NAME' => $row['S_NAME'],
            'AC_CUSTID' => $row['AC_CUSTID'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_CLOSEDT' => $row['AC_CLOSEDT'],
            'depo_amount' => $row['depo_amount'],
            'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
            'AC_INTRATE' => $row['AC_INTRATE'],
            'balance' =>sprintf("%.2f", (abs($row['balance']))).' '.$netType,     
           

           
            'stdate' => $stdate1,
            'etdate' => $etdate1,
            // '$S_APPL'=>$S_APPL,
            'branch' => $branch,
            'branchName' => $branchName,
            'ac_director'=>$ac_director,
            'TOTAL' =>$TOTAL, 
            'SUBTOTAL' =>$SUBTOTAL,
            'TRAN_STATUS'=>$TRAN_STATUS,
            'var'=>$var,
            'T1'=>$T1,
            'bankName' => $bankName,


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
    
    
// }
?>
