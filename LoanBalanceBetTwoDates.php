<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
ini_set('MAX_EXECUTION_TIME',4200);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/LoanBalanceBetTwoDatesSummary.jrxml';
$filename1 = __DIR__ . '/LoanBalanceBetTwoDatesDetails.jrxml';
// $filename1 = __DIR__ . '/loanbalbetweenTwodatesDetail.jrxml';



$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


//variables

$edate = $_GET['edate'];
$branched = $_GET['branched']; // branch code
$bankName = $_GET['bankName'];
$branchName = $_GET['branchName'];
$schemeid = $_GET['schemeid'];

$stadate = $_GET['stadate'];
// $edate = $_GET['edate'];
// $branched = $_GET['branched']; // branch code
// $bankName = $_GET['bankName'];
// $branchName = $_GET['branchName'];
$flag   = $_GET['flag'];
// $schemeid = $_GET['schemeid'];

$branchName_ = str_replace("'", "", $branchName);
$bankName_ = str_replace("'", "", $bankName);
$stadate_ = str_replace("'", "", $stadate);
$edate_ = str_replace("'", "", $edate);

$dateformate = "'DD/MM/YYYY'";
$C = "'C'";
$D = "'D'";
$zero = "'0'";
$day1 = "'1 day'";
$TRAN_STATUS = "'2'";
// $actype= "'actype'";


// echo $flag;

$checktype;
$flag == 'detail' ? $checktype = 'true' : $checktype = 'false';
//  echo $checktype;


if ($flag == 'summary') {

        //Summary
        $query = ' 
SELECT "AC_ACNOTYPE" , "AC_TYPE" , COALESCE( SUM(TOT_CREDITAMT) ,0) TOT_CREDITAMT , SCHEMAST."S_APPL", SCHEMAST."S_NAME",  COALESCE( SUM(CAST("LAST_BALANCE" AS FLOAT)) ,0) LAST_BALANCE,
COALESCE(SUM(TOT_CREDITINTAMT),0) TOT_CREDITINTAMT, COALESCE(SUM(TOT_CREDITOTHERAMT),0) TOT_CREDITOTHERAMT, 
COALESCE(SUM(TOT_SANCTIONAMT),0)TOT_SANCTIONAMT ,COALESCE( SUM(CAST("CLOSING_BALANCE" AS FLOAT)),0)TOT_CLOSING_BALANCE 
From  
(  SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" , LNMASTER."AC_NO" , COALESCE(LNMASTER."AC_MEMBTYPE",0) AC_MEMBTYPE , LNMASTER."AC_MEMBNO", LNMASTER."AC_CUSTID", 
      LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT" , LNMASTER."AC_NAME" ,
--LNMASTER."AC_ADDR1", LNMASTER."AC_ADDR2", LNMASTER."AC_ADDR3",  LNMASTER."AC_CTCODE",
( COALESCE(LOANTRAN."TRAN_AMOUNT",0) + 
      COALESCE(DAILYTRAN."DAILY_AMOUNT",0))TOT_CREDITAMT , 
      ( COALESCE(DAILYTRAN."INT_AMOUNT",0)) TOT_CREDITINTAMT, 
      (COALESCE(LOANTRAN."OTHER_AMOUNT",0) + COALESCE(DAILYTRAN."OTHER_AMOUNT",0)) TOT_CREDITOTHERAMT, ( COALESCE(INTTRAN."POST_INT_AMOUNT",0))  POST_INT_AMOUNT,  
      COALESCE(LNMASTER."AC_SANCTION_AMOUNT" ,0)TOT_SANCTIONAMT , LOANTRAN."CLOSING_BALANCE", ( COALESCE(DRLOANTRAN."DR_TRAN_AMOUNT",0) + COALESCE(DAILYTRAN."DR_DAILY_AMOUNT",0))TOT_DEBITAMT ,INTRATE."INT_RATE", LOANTRAN."LAST_BALANCE" 
      From 
       ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , COALESCE(SUM("TRAN_AMOUNT"),0) "TRAN_AMOUNT", 
          (COALESCE(SUM("INTEREST_AMOUNT"), 0) + COALESCE(SUM("RECPAY_INT_AMOUNT"), 0) + 
          COALESCE(SUM("PENAL_INTEREST"), 0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"), 0) + 
          COALESCE(SUM("OTHER10_AMOUNT"), 0)) INT_AMOUNT , (COALESCE(SUM("OTHER1_AMOUNT"), 0) + 
          COALESCE(SUM("OTHER2_AMOUNT"), 0) + COALESCE(SUM("OTHER3_AMOUNT"),0) + COALESCE(SUM("OTHER4_AMOUNT"),0) + 
          COALESCE(SUM("OTHER5_AMOUNT"), 0) + COALESCE(SUM("OTHER6_AMOUNT"),0) + COALESCE(SUM("OTHER7_AMOUNT"),0) + 
          COALESCE(SUM("OTHER8_AMOUNT"), 0) + COALESCE(SUM("OTHER9_AMOUNT"),0)) "OTHER_AMOUNT", 0 "POST_INT_AMOUNT" ,
                  VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE" , VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE" AS "LAST_BALANCE"
      FROM LOANTRAN , LNMASTER, 
                      ( 	  SELECT  LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NO",
               ledgerbalance( SUBSTR(LNMASTER."BANKACNO", 4, 6), LNMASTER."BANKACNO",
               TO_CHAR(TO_DATE('.$stadate.', '.$dateformate.') - INTERVAL '.$day1.', '.$dateformate.'), 102, 1, 1 ) AS "CLOSING_BALANCE"
              FROM  LNMASTER
               WHERE  LNMASTER."AC_TYPE" IN ('.$schemeid.')
                      ) VWTMPZBALTOTCRBTNDATELN ,
                
                      (SELECT  LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NO",
               ledgerbalance( SUBSTR(LNMASTER."BANKACNO", 4, 6), LNMASTER."BANKACNO",
               TO_CHAR(TO_DATE('.$stadate.', '.$dateformate.') - INTERVAL '.$day1.', '.$dateformate.'), 102, 1, 1 ) AS "CLOSING_BALANCE"
              FROM  LNMASTER
               WHERE  LNMASTER."AC_TYPE" IN ('.$schemeid.')
                      ) VWTMPZCLBALTOTCRBTNDATELN		   
                 WHERE "TRAN_DRCR" = '.$C.' AND CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$stadate.', '.$dateformate.')  
              AND LNMASTER."AC_ACNOTYPE"  = VWTMPZCLBALTOTCRBTNDATELN."AC_ACNOTYPE"
              AND LNMASTER."AC_TYPE"  = VWTMPZCLBALTOTCRBTNDATELN."AC_TYPE"
              AND LNMASTER."AC_NO" =  VWTMPZCLBALTOTCRBTNDATELN."AC_NO"  
              AND LNMASTER."AC_ACNOTYPE"  = VWTMPZBALTOTCRBTNDATELN."AC_ACNOTYPE"    
              AND LNMASTER."AC_TYPE"  = VWTMPZBALTOTCRBTNDATELN."AC_TYPE" 
              AND LNMASTER."AC_NO" =  VWTMPZBALTOTCRBTNDATELN."AC_NO"    
          AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.','.$dateformate.') 
                      AND LNMASTER."BRANCH_CODE" = '.$branched.'
      GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" , VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE", VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE"
      ) LOANTRAN , 
      ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , COALESCE(SUM("TRAN_AMOUNT"),0) "DR_TRAN_AMOUNT",(COALESCE(SUM("INTEREST_AMOUNT"), 0) + COALESCE(SUM("RECPAY_INT_AMOUNT"), 0) 
      + COALESCE(SUM("PENAL_INTEREST"), 0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"), 0) + COALESCE(SUM("OTHER10_AMOUNT"), 0)) "INT_AMOUNT" , (COALESCE(SUM("OTHER1_AMOUNT"), 0) 
      + COALESCE(SUM("OTHER2_AMOUNT"), 0) + COALESCE(SUM("OTHER3_AMOUNT"),0) + COALESCE(SUM("OTHER4_AMOUNT"),0) + COALESCE(SUM("OTHER5_AMOUNT"), 0) + COALESCE(SUM("OTHER6_AMOUNT"),0) + COALESCE(SUM("OTHER7_AMOUNT"),0) 
      + COALESCE(SUM("OTHER8_AMOUNT"), 0) + COALESCE(SUM("OTHER9_AMOUNT"),0)) "DR_OTHER_AMOUNT", 0 "POST_INT_AMOUNT"  FROM LOANTRAN WHERE "TRAN_DRCR" = '.$D.' 
      AND CAST("TRAN_DATE" AS DATE) >= TO_DATE ('.$stadate.', '.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.','.$dateformate.') GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
     ) DRLOANTRAN , 
     ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , COALESCE(SUM("TRAN_AMOUNT"),0) "TRAN_AMOUNT", 
          0 "INT_AMOUNT" , 0 "OTHER_AMOUNT" , COALESCE(SUM("TRAN_AMOUNT"),0) "POST_INT_AMOUNT" FROM LOANTRAN 
      WHERE "TRAN_DRCR" = '.$D.' AND "IS_INTEREST_ENTRY"= '.$zero.' 
      AND CAST("TRAN_DATE" AS DATE)>= TO_DATE ('.$stadate.', '.$dateformate.') 
          AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.','.$dateformate.') 
      GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
      ) INTTRAN ,
( select "AC_ACNOTYPE" , "AC_TYPE" , "AC_NO", max("EFFECT_DATE") "EFFECT_DATE" , "INT_RATE" from lnacintrate 
       group by  "AC_ACNOTYPE" , "AC_TYPE" , "AC_NO", "INT_RATE" ) INTRATE, 
     ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE" , "TRAN_ACNO" , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$C .' THEN  "TRAN_AMOUNT"  ELSE 0 END),0) "DAILY_AMOUNT", 
       COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  "TRAN_AMOUNT"  ELSE 0 END),0) "DR_DAILY_AMOUNT",  
          (COALESCE(SUM("INTEREST_AMOUNT"), 0) + COALESCE(SUM("RECPAY_INT_AMOUNT"), 0) + 
          COALESCE(SUM("PENAL_INT_AMOUNT"), 0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"), 0) + 
          COALESCE(SUM("OTHER10_AMOUNT"), 0)) "INT_AMOUNT" , (COALESCE(SUM("OTHER1_AMOUNT"), 0) + 
          COALESCE(SUM("OTHER2_AMOUNT"), 0) + COALESCE(SUM("OTHER3_AMOUNT"),0) + COALESCE(SUM("OTHER4_AMOUNT"),0) + 
          COALESCE(SUM("OTHER5_AMOUNT"), 0) + COALESCE(SUM("OTHER6_AMOUNT"),0) + COALESCE(SUM("OTHER7_AMOUNT"),0) + 
          COALESCE(SUM("OTHER8_AMOUNT"), 0) + COALESCE(SUM("OTHER9_AMOUNT"),0)) "OTHER_AMOUNT" , 0 "POST_INT_AMOUNT" 
          FROM DAILYTRAN WHERE "TRAN_DRCR" = '.$C.' AND CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$stadate.', '.$dateformate.') AND 
          CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.', '.$dateformate.') AND "TRAN_STATUS" = '.$TRAN_STATUS.' 
               AND DAILYTRAN."BRANCH_CODE" = '.$branched.'
          GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"         
) DAILYTRAN , 
LNMASTER 
       Where LNMASTER."AC_ACNOTYPE"  = LOANTRAN."TRAN_ACNOTYPE"
      AND LNMASTER."AC_TYPE"  = LOANTRAN."TRAN_ACTYPE"
      AND LNMASTER."AC_ACNOTYPE"  = DRLOANTRAN."TRAN_ACNOTYPE"  
      AND LNMASTER."AC_TYPE"  = DRLOANTRAN."TRAN_ACTYPE"
      AND LNMASTER."BANKACNO" =  DRLOANTRAN."TRAN_ACNO" 
      AND LNMASTER."AC_ACNOTYPE"  = INTRATE."AC_ACNOTYPE" 
      AND LNMASTER."AC_TYPE"  = INTRATE."AC_TYPE"
      AND LNMASTER."AC_NO" = INTRATE."AC_NO"
      AND LNMASTER."BANKACNO" =  LOANTRAN."TRAN_ACNO"
      AND LNMASTER."AC_ACNOTYPE"  = DAILYTRAN."TRAN_ACNOTYPE" 
      AND LNMASTER."AC_TYPE"  = DAILYTRAN."TRAN_ACTYPE"
      AND LNMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
      AND LNMASTER."AC_ACNOTYPE"  = INTTRAN."TRAN_ACNOTYPE" 
      AND LNMASTER."AC_TYPE"  = INTTRAN."TRAN_ACTYPE"
      AND LNMASTER."BANKACNO" =  INTTRAN."TRAN_ACNO"
     
               AND LNMASTER."AC_TYPE" IN ('.$schemeid.') ) S , SCHEMAST
WHERE  CAST("AC_CLOSEDT" AS DATE) > TO_DATE ('.$stadate.', '.$dateformate.')  OR "AC_CLOSEDT" IS NULL 
AND CAST("AC_CLOSEDT" AS DATE) <= TO_DATE ('.$edate.', '.$dateformate.')  OR "AC_CLOSEDT" IS NULL 
AND 
TOT_CREDITAMT + TOT_CREDITINTAMT + TOT_CREDITOTHERAMT + POST_INT_AMOUNT <> 0 
AND S."AC_TYPE"=SCHEMAST.ID
--    AND "BRANCH_CODE" = '.$branched.'
GROUP BY "AC_ACNOTYPE" , "AC_TYPE", "S_APPL", "S_NAME"  ;
';

// echo $query;

}

//Details

else if ($flag == 'detail') {


$query=' (SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", COALESCE(LNMASTER."AC_MEMBTYPE", 0) AC_MEMBTYPE, LNMASTER."AC_MEMBNO", LNMASTER."AC_CUSTID", 
LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT", LNMASTER."AC_NAME", SCHEMAST."S_APPL",
SCHEMAST."S_NAME" ,  (COALESCE(LOANTRAN."TRAN_AMOUNT",  0) + 
COALESCE(DAILYTRAN."DAILY_AMOUNT",  0))TOT_CREDITAMT, (COALESCE(DAILYTRAN."INT_AMOUNT",  0)) TOT_CREDITINTAMT, (COALESCE(LOANTRAN."OTHER_AMOUNT",  0) + 
COALESCE(DAILYTRAN."OTHER_AMOUNT",  0)) TOT_CREDITOTHERAMT, (COALESCE(INTTRAN."POST_INT_AMOUNT",  0)) POST_INT_AMOUNT, COALESCE(LNMASTER."AC_SANCTION_AMOUNT", 
0)TOT_SANCTIONAMT, LOANTRAN."CLOSING_BALANCE", (COALESCE(DRLOANTRAN."DR_TRAN_AMOUNT",  0) + COALESCE(DAILYTRAN."DR_DAILY_AMOUNT",  0))TOT_DEBITAMT, 
INTRATE."INT_RATE", LOANTRAN."LAST_BALANCE" FROM (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM("TRAN_AMOUNT"),  0) "TRAN_AMOUNT", 
(COALESCE(SUM("INTEREST_AMOUNT"),  0) + COALESCE(SUM("RECPAY_INT_AMOUNT"),  0) + COALESCE(SUM("PENAL_INTEREST"),  0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"),  0) + 
COALESCE(SUM("OTHER10_AMOUNT"),  0)) INT_AMOUNT, (COALESCE(SUM("OTHER1_AMOUNT"),  0) + COALESCE(SUM("OTHER2_AMOUNT"),  0) + COALESCE(SUM("OTHER3_AMOUNT"),  0) + 
COALESCE(SUM("OTHER4_AMOUNT"),  0) + COALESCE(SUM("OTHER5_AMOUNT"),  0) + COALESCE(SUM("OTHER6_AMOUNT"),  0) + COALESCE(SUM("OTHER7_AMOUNT"),  0) + 
COALESCE(SUM("OTHER8_AMOUNT"),  0) + COALESCE(SUM("OTHER9_AMOUNT"),  0)) "OTHER_AMOUNT", 0 "POST_INT_AMOUNT", VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE", 
VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE" AS "LAST_BALANCE" FROM LOANTRAN, LNMASTER,  (SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", 
LNMASTER."AC_NO", LEDGERBALANCE(SUBSTR(LNMASTER."BANKACNO",  4, 6),  LNMASTER."BANKACNO", TO_CHAR(TO_DATE('.$stadate.',  '.$dateformate.') - INTERVAL '.$day1.',  
'.$dateformate.'), 102, 1, 1) AS "CLOSING_BALANCE" FROM LNMASTER WHERE LNMASTER."AC_TYPE" IN ('.$schemeid.') ) VWTMPZBALTOTCRBTNDATELN,  (SELECT 
LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NO", LEDGERBALANCE(SUBSTR(LNMASTER."BANKACNO",  4, 6),  LNMASTER."BANKACNO", 
TO_CHAR(TO_DATE('.$stadate.',  '.$dateformate.') - INTERVAL '.$day1.',  '.$dateformate.'), 102, 1, 1) AS "CLOSING_BALANCE" FROM LNMASTER WHERE LNMASTER."AC_TYPE" IN ('.$schemeid.') ) VWTMPZCLBALTOTCRBTNDATELN WHERE "TRAN_DRCR" = '.$C.' AND CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$stadate.',  '.$dateformate.') AND LNMASTER."AC_ACNOTYPE" = 
VWTMPZCLBALTOTCRBTNDATELN."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = VWTMPZCLBALTOTCRBTNDATELN."AC_TYPE" AND LNMASTER."AC_NO" = VWTMPZCLBALTOTCRBTNDATELN."AC_NO" AND 
LNMASTER."AC_ACNOTYPE" = VWTMPZBALTOTCRBTNDATELN."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = VWTMPZBALTOTCRBTNDATELN."AC_TYPE" AND LNMASTER."AC_NO" = 
VWTMPZBALTOTCRBTNDATELN."AC_NO" AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.',  '.$dateformate.') AND LNMASTER."BRANCH_CODE" = '.$branched.' GROUP BY "TRAN_ACNOTYPE", 
"TRAN_ACTYPE", "TRAN_ACNO", VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE", VWTMPZBALTOTCRBTNDATELN."CLOSING_BALANCE") LOANTRAN,  (SELECT "TRAN_ACNOTYPE", 
"TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM("TRAN_AMOUNT"),  0) "DR_TRAN_AMOUNT", (COALESCE(SUM("INTEREST_AMOUNT"),  0) + COALESCE(SUM("RECPAY_INT_AMOUNT"),  0) + 
COALESCE(SUM("PENAL_INTEREST"),  0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"),  0) + COALESCE(SUM("OTHER10_AMOUNT"),  0)) "INT_AMOUNT", 
(COALESCE(SUM("OTHER1_AMOUNT"),  0) + COALESCE(SUM("OTHER2_AMOUNT"),  0) + COALESCE(SUM("OTHER3_AMOUNT"),  0) + COALESCE(SUM("OTHER4_AMOUNT"),  0) + 
COALESCE(SUM("OTHER5_AMOUNT"),  0) + COALESCE(SUM("OTHER6_AMOUNT"),  0) + COALESCE(SUM("OTHER7_AMOUNT"),  0) + COALESCE(SUM("OTHER8_AMOUNT"),  0) + 
COALESCE(SUM("OTHER9_AMOUNT"),  0)) "DR_OTHER_AMOUNT", 0 "POST_INT_AMOUNT" FROM LOANTRAN WHERE "TRAN_DRCR" = '.$D.' AND CAST("TRAN_DATE" AS DATE) >= TO_DATE 
('.$stadate.', '.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.',  '.$dateformate.') GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") 
DRLOANTRAN,  (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM("TRAN_AMOUNT"),  0) "TRAN_AMOUNT", 0 "INT_AMOUNT", 0 "OTHER_AMOUNT", 
COALESCE(SUM("TRAN_AMOUNT"),  0) "POST_INT_AMOUNT" FROM LOANTRAN WHERE "TRAN_DRCR" = '.$D.' AND "IS_INTEREST_ENTRY" = '.$zero.' AND CAST("TRAN_DATE" AS DATE) >= TO_DATE 
('.$stadate.', '.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.',  '.$dateformate.') GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") INTTRAN,  
(SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", MAX("EFFECT_DATE") "EFFECT_DATE", "INT_RATE" FROM LNACINTRATE GROUP BY "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "INT_RATE") 
INTRATE,  (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$C.' THEN "TRAN_AMOUNT" ELSE 0 END),  0) "DAILY_AMOUNT", 
COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN "TRAN_AMOUNT" ELSE 0 END),  0) "DR_DAILY_AMOUNT", (COALESCE(SUM("INTEREST_AMOUNT"),  0) + 
COALESCE(SUM("RECPAY_INT_AMOUNT"),  0) + COALESCE(SUM("PENAL_INT_AMOUNT"),  0) + COALESCE(SUM("REC_PENAL_INT_AMOUNT"),  0) + COALESCE(SUM("OTHER10_AMOUNT"),  0)) 
"INT_AMOUNT", (COALESCE(SUM("OTHER1_AMOUNT"),  0) + COALESCE(SUM("OTHER2_AMOUNT"),  0) + COALESCE(SUM("OTHER3_AMOUNT"),  0) + COALESCE(SUM("OTHER4_AMOUNT"),  0) + 
COALESCE(SUM("OTHER5_AMOUNT"),  0) + COALESCE(SUM("OTHER6_AMOUNT"),  0) + COALESCE(SUM("OTHER7_AMOUNT"),  0) + COALESCE(SUM("OTHER8_AMOUNT"),  0) + 
COALESCE(SUM("OTHER9_AMOUNT"),  0)) "OTHER_AMOUNT", 0 "POST_INT_AMOUNT" FROM DAILYTRAN WHERE "TRAN_DRCR" = '.$C.' AND CAST("TRAN_DATE" AS DATE) >= 
TO_DATE('.$stadate.',  '.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.',  '.$dateformate.') AND "TRAN_STATUS" = '.$TRAN_STATUS.' AND DAILYTRAN."BRANCH_CODE" = '.$branched.' 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") DAILYTRAN, LNMASTER left join SCHEMAST ON  LNMASTER."AC_TYPE" = SCHEMAST.id
 WHERE LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" 
= LOANTRAN."TRAN_ACTYPE" AND LNMASTER."AC_ACNOTYPE" = DRLOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = DRLOANTRAN."TRAN_ACTYPE" AND LNMASTER."BANKACNO" = 
DRLOANTRAN."TRAN_ACNO" AND LNMASTER."AC_ACNOTYPE" = INTRATE."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = INTRATE."AC_TYPE" AND LNMASTER."AC_NO" = INTRATE."AC_NO" AND 
LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" AND LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = DAILYTRAN."TRAN_ACTYPE" AND 
LNMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO" AND LNMASTER."AC_ACNOTYPE" = INTTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = INTTRAN."TRAN_ACTYPE" AND 
LNMASTER."BANKACNO" = INTTRAN."TRAN_ACNO" AND LNMASTER."AC_TYPE" IN ('.$schemeid.'))
';


//     echo $query;
}

    

$sql =  pg_query($conn, $query);

//summary
$i = 0;
$t1 = 0;
$t2 = 0;
$t3 = 0;
$t4 = 0;
$t5 = 0;
$t6 = 0;

$summtotal = 0;

//details
$dt1 = 0;
$dt2 = 0;
$dt3 = 0;
$dt4 = 0;
$dt5 = 0;
$dt6 = 0;
$dt7 = 0;
$dt8 = 0;

$gr1 = 0;
$gr2 = 0;
$gr3 = 0;
$gr4 = 0;
$gr5 = 0;
$gr6 = 0;
$gr7 = 0;
$gr8 = 0;
$tamt = 0;

$Total=0;

if (pg_num_rows($sql) == 0) {
        include "errormsg.html";
} else {
        while ($row = pg_fetch_assoc($sql)) {
                // $summtotal = $row['cramttot'] + $row['totinterest'] + $row['tototeeramt'];
                $summtotal = $row['tot_creditotheramt'] + $row['tot_creditintamt'] + $row['tot_creditamt'];

                $Total += $summtotal;
                $t1 = $t1 + $row['tot_sanctionamt'];
                $t2 = $t2 + $row['last_balance'];
                $t3 = $t3 + $row['tot_creditamt'];
                $t4 = $t4 + $row['tot_creditintamt'];
                $t5 = $t5 + $row['tot_creditotheramt'];
                // $tamt +=$summtotal;
                // $t6 =$t6+ $Tot_Amt;

                //schemewise total details
                $dt1 = $dt1 + $row['tot_sanctionamt'];
                $dt2 = $dt2 + $row['CLOSING_BALANCE'];
                $dt3 = $dt3 + $row['post_int_amount'];
                $dt4 = $dt4 + $row['tot_creditintamt'];
                $dt5 = $dt5 + $row['AC_CLOSEDT'];
                $dt6 = $dt6 + $row['tot_creditintamt'];
                $dt7 = $dt7 + $row['tot_debitamt'];
                $dt8 = $dt8 + $row['LAST_BALANCE'];

                //gr total

                $gr1 = $gr1 + $dt1;
                $gr2 = $gr2 + $dt2;
                $gr3 = $gr3 + $dt3;
                $gr4 = $gr4 + $dt4;
                $gr5 = $gr5 + $dt5;
                $gr6 = $gr6 + $dt6;
                $gr7 = $gr7 + $dt7;
                $gr8 = $gr8 + $dt8;


                $tmp = [
                        //SUMMARY
                        'sdate' => $stadate_,
                        'edate' => $edate_,
                        'branch' => $branchName_,
                        'BANK_NAME' => $bankName_,
                        'SchCode' => $row['S_APPL'],
                        'SchName' =>  $row['S_NAME'],
                        'Sanction_Amt' => $row['tot_sanctionamt'],
                        'Opbalance' => $row['last_balance'],
                        'TotalCr_Amt' => $row['tot_creditamt'],
                        'TotalCr_Interest' => $row['tot_creditintamt'],
                        'TotOther_Amt' => $row['tot_creditotheramt'],
                        'Tot_Amt' =>  sprintf("%.2f", ($summtotal + 0.0)),
                        'stot' =>  sprintf("%.2f", ($t1 + 0.0)),
                        'optot' => sprintf("%.2f", ($t2 + 0.0)),
                        'cramttot' => sprintf("%.2f", ($t3 + 0.0)),
                        'totinterest' => sprintf("%.2f", ($t4 + 0.0)),
                        'tototeeramt' => sprintf("%.2f", ($t5 + 0.0)),
                        'Total' => sprintf("%.2f", ($Total + 0.0)),

                        //DETAILS
                        'scheme' =>  $row['S_NAME'],
                        'no' => $row['AC_ACNOTYPE'] . ' ' . $row['S_APPL'],
                        'mno' => $row['AC_MEMBNO'],
                        'accno' => $row['AC_NO'],
                        'accname' => $row['AC_NAME'],
                        'panno' => $row['AC_PANNO'],
                        'intrate' => $row['INT_RATE'],
                        'loansdate' => $row['AC_SANCTION_DATE'],
                        'sanamt' => $row['tot_sanctionamt'],
                        'opcash' => $row['CLOSING_BALANCE'],
                        'dramt' => $row['post_int_amount'],
                        'cramt' => $row['tot_creditintamt'],
                        'closedate' => $row['AC_CLOSEDT'],
                        'totamt' => $row['tot_creditintamt'],
                        'debitamt' => $row['tot_debitamt'],
                        'balamt' => $row['LAST_BALANCE'],
                        'tosan' =>  sprintf("%.2f", ($dt1+ 0.0)),
                        'tocash' => sprintf("%.2f", ($dt2+ 0.0)),
                        'dramt' => sprintf("%.2f", ($dt3+ 0.0)),
                        'tocr' => sprintf("%.2f", ($dt4+ 0.0)),
                        'tocldate' => sprintf("%.2f", ($dt5+ 0.0)),
                        'toamt' => sprintf("%.2f", ($dt6+ 0.0)),
                        'todebitamt' => sprintf("%.2f", ($dt7+ 0.0)),
                        'tobal' => sprintf("%.2f", ($dt8+ 0.0)),
                        'grsan' => sprintf("%.2f", ($gr1+ 0.0)),
                        'grcash' => sprintf("%.2f", ($gr2+ 0.0)),
                        'grdramt' => sprintf("%.2f", ($gr3+ 0.0)),
                        'grcramt' => sprintf("%.2f", ($gr4+ 0.0)),
                        'grcldate' => sprintf("%.2f", ($gr5+ 0.0)),
                        'grtoamt' => sprintf("%.2f", ($gr6+ 0.0)),
                        'grdebit' => sprintf("%.2f", ($gr7+ 0.0)),
                        'grbal' => sprintf("%.2f", ($gr8+ 0.0)),


                ];

                $data[$i] = $tmp;
                $i++;
        }
        ob_end_clean();
        if ($flag === 'summary') {
                $config = ['driver' => 'array', 'data' => $data];
                // print_r($data);
                $report = new PHPJasperXML();
                $report->load_xml_file($filename)
                        ->setDataSource($config)
                        ->export('Pdf');
        } else if ($flag === 'detail') {
                $config = ['driver' => 'array', 'data' => $data];
                // print_r($data);
                $report = new PHPJasperXML();
                $report->load_xml_file($filename1)
                        ->setDataSource($config)
                        ->export('Pdf');
        }
}
?> 