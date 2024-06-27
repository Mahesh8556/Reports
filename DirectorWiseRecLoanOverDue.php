<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/DirectorWiseRecLoanOverDue.jrxml';

$dataset = array();
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$date2 = $_GET['date1'];
$scheme = $_GET['AC_TYPE'];
$branch = $_GET['BRANCH_CODE'];
$branchName=$_GET['BranchName'];
$AC_TYPE = $_GET['AC_TYPE'];
$Dates = $_GET['date1'];


$d = "'D'";
$date1="'30/09/2022'";
$DUEBAL="'DUEBAL'";
$space="' '";
$P = "'P'";
$dateformat="'DD/MM/YYYY'";
$TRAN_STATUS="'1'";
$BODY_MEMBER="'1'";
$bankName = str_replace("'", "", $bankName);
$BranchName1 = str_replace("'", "", $branchName);
$Dates = str_replace("'", "", $date1);



if($branch == '0'){
$query='
SELECT DISTINCT
  * 
FROM 
  (
    SELECT 
      SCHEMAST."S_APPL" || '.$space. ' || SCHEMAST."S_NAME" "SCHEME", 
      LNMASTER."AC_ACNOTYPE", 
      LNMASTER."AC_TYPE", 
      LNMASTER."AC_SANCTION_AMOUNT", 
      LNMASTER."AC_NO", 
      LNMASTER."AC_NAME", 
      LNMASTER."BANKACNO", 
      LNMASTER."AC_OPDATE", 
      LNMASTER."idmasterID", 
      LNMASTER."AC_CLOSEDT", 
      DIRECTORMASTER.ID || '.$space. ' || DIRECTORMASTER."NAME" "AC_RECOMANDED_DIRECTORNAME", 
      "AC_RECOMMEND_BY" "AC_RECOMMEND_DIRECTOR", 
      DIRECTORMAS."NAME" "DIRECTOR_NAME", 
      LNMASTER."AC_DIRECTOR", 
      LNMASTER."AC_DIRECTOR_RELATION", 
      AUTHORITYMASTER.ID || '.$space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER", 
      "AC_AUTHORITY" "AUTHORITY", 
      RECOVERYCLEARKMASTER.ID || ' .$space.' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER", 
      "AC_RECOVERY_CLERK", 
      OWNBRANCHMASTER.ID || '.$space. ' || OWNBRANCHMASTER."NAME" "BRANCH", 
      CUSTOMERADDRESS."AC_CTCODE" "CITY", 
      CITYMASTER."CITY_NAME" "CITYNAME", 
      CUSTOMERADDRESS."AC_ADDR" || ' .$space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS", 
      LNMASTER."AC_MORATORIUM_PERIOD", 
      LNMASTER."AC_GRACE_PERIOD", 
      LNMASTER."AC_REPAYMODE", 
      IDMASTER."AC_MOBILENO", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
			ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END, 
          0
        ) + COALESCE(
          CAST(LOANTRAN."TRAN_AMOUNT" AS FLOAT), 
          0 
        ) 
 + COALESCE(DAILYTRAN."DAILY_AMOUNT", 0)
      )"CLOSING_BALANCE", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$d.' 
			THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END, 
          0
        ) + COALESCE(
          CAST(
            LOANTRAN."RECPAY_INT_AMOUNT" AS FLOAT
          ), 
          0
        ) + COALESCE(
          DAILYTRAN."DAILY_RECPAY_INT_AMOUNT", 
          0
        ) + COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) ELSE (-1) * CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) END, 
          0
        ) + COALESCE(
          CAST(
            LOANTRAN."OTHER10_AMOUNT" AS FLOAT
          ), 
          0
        ) + COALESCE(
          CAST(
            DAILYTRAN."DAILY_OTHER10_AMOUNT" AS FLOAT
          ), 
          0
        )
      ) "RECPAY_INT_AMOUNT", 
      LNMASTER."AC_INSTALLMENT", 
      OIRINTBALANCE(
        SCHEMAST."S_APPL", LNMASTER."BANKACNO", 
        '.$date1.', 0
      ) "OVERDUEINTEREST", 
      (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(
        CAST(
          LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
        ) / (
          CASE WHEN CAST(
            LNMASTER."AC_INSTALLMENT" AS FLOAT
          ) = 0 THEN 1 ELSE CAST(
            LNMASTER."AC_INSTALLMENT" AS FLOAT
          ) END
        )
      ) else 0 end )"TOTALINSTALLMENTS", 
      CEIL(
        DUEBALANCE(
          CAST(
            SCHEMAST."S_APPL" AS CHARACTER VARYING
          ), 
          LNMASTER."BANKACNO", 
          '.$date1.', 
          '.$DUEBAL.', 
          0
        ) / (
          CASE WHEN CAST(
            LNMASTER."AC_INSTALLMENT" AS INTEGER
          ) = 0 THEN 1 ELSE CAST(
            LNMASTER."AC_INSTALLMENT" AS INTEGER
          ) END
        )
      ) "DUEINSTALLMENT", 
      (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(
        (
          CAST(
            LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
          ) - DUEBALANCE(
            CAST(
              SCHEMAST."S_APPL" AS CHARACTER VARYING
            ), 
            LNMASTER."BANKACNO", 
            '.$date1.', 
            '.$DUEBAL.', 
            0
          )
        ) / (
          CASE WHEN CAST(
            LNMASTER."AC_INSTALLMENT" AS FLOAT
          ) = 0 THEN 1 ELSE CAST(
            LNMASTER."AC_INSTALLMENT" AS FLOAT
          ) END
        )
      ) else 0 end ) "PAIDINSTALLMENTS", 
      DUEBALANCE(
        CAST(
          SCHEMAST."S_APPL" AS CHARACTER VARYING
        ), 
        LNMASTER."BANKACNO", 
        '.$date1.', 
        '.$DUEBAL.', 
        0
      ) "DUEBALANCE", 
      "AC_EXPIRE_DATE", 
      OVERDUEDATE(
        SCHEMAST."S_APPL", 
        LNMASTER."BANKACNO", 
        '.$date1.', 
        CAST(
          DUEBALANCE(
            CAST(
              SCHEMAST."S_APPL" AS CHARACTER VARYING
            ), 
            LNMASTER."BANKACNO", 
            '.$date1.', 
            '.$DUEBAL.', 
            0
          ) AS CHARACTER VARYING
        ), 
        CAST (
          LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING
        )
      ) "OVERDUEDATE" 
    FROM 
      LNMASTER 
      LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE" 
      LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(
        LNMASTER."AC_RECOMMEND_BY" AS INTEGER
      ) 
      LEFT JOIN DIRECTORMASTER AS DIRECTORMAS ON DIRECTORMAS.ID = CAST(
        LNMASTER."AC_DIRECTOR" AS INTEGER
      ) 
      LEFT JOIN RECOVERYCLEARKMASTER ON RECOVERYCLEARKMASTER.ID = CAST(
        LNMASTER."AC_RECOVERY_CLERK" AS INTEGER
      ) 
      LEFT JOIN AUTHORITYMASTER ON AUTHORITYMASTER.ID = LNMASTER."AC_AUTHORITY" 
      LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID" 
      AND CUSTOMERADDRESS."AC_ADDTYPE" = '.$P.' 
      LEFT JOIN CITYMASTER ON CITYMASTER.ID = CUSTOMERADDRESS."AC_CTCODE" 
      LEFT JOIN IDMASTER ON IDMASTER.ID = LNMASTER."idmasterID", 
      (
        SELECT 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
				ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "TRAN_AMOUNT", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) 
				ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "RECPAY_INT_AMOUNT", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
				ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "OTHER10_AMOUNT" 
        FROM 
          LOANTRAN 
        WHERE 
          CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateformat.') 
        GROUP BY 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO"
      ) LOANTRAN, 
      (
        SELECT 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT) 
				ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "DAILY_AMOUNT", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) 
				ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "DAILY_RECPAY_INT_AMOUNT", 
          COALESCE(
            SUM(
              CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) 
				ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
            ), 
            0
          ) "DAILY_OTHER10_AMOUNT" 
        FROM 
          DAILYTRAN 
        WHERE 
          CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateformat.') 
          AND "TRAN_STATUS" = '.$TRAN_STATUS.' 
        GROUP BY 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO"
      ) DAILYTRAN 
    WHERE 
      LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" 
      AND LNMASTER."AC_TYPE" = CAST(
        LOANTRAN."TRAN_ACTYPE" AS INTEGER
      ) 
      AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" 
      AND "AC_TYPE" = 22
      AND LNMASTER."status" = 1 
      AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
      AND DIRECTORMASTER."IS_CURRENT_BODY_MEMBER" = '.$BODY_MEMBER.' 
      AND DIRECTORMAS."IS_CURRENT_BODY_MEMBER" = '.$BODY_MEMBER.' 
    ORDER BY 
      SCHEMAST."S_APPL", 
      LNMASTER."BANKACNO"
  ) AS S ';
 }
 else
 {
  $query='
  SELECT DISTINCT
    * 
  FROM 
    (
      SELECT 
        SCHEMAST."S_APPL" || '.$space. ' || SCHEMAST."S_NAME" "SCHEME", 
        LNMASTER."AC_ACNOTYPE", 
        LNMASTER."AC_TYPE", 
        LNMASTER."AC_SANCTION_AMOUNT", 
        LNMASTER."AC_NO", 
        LNMASTER."AC_NAME", 
        LNMASTER."BANKACNO", 
        LNMASTER."AC_OPDATE", 
        LNMASTER."idmasterID", 
        LNMASTER."AC_CLOSEDT", 
        DIRECTORMASTER.ID || '.$space. ' || DIRECTORMASTER."NAME" "AC_RECOMANDED_DIRECTORNAME", 
        "AC_RECOMMEND_BY" "AC_RECOMMEND_DIRECTOR", 
        DIRECTORMAS."NAME" "DIRECTOR_NAME", 
        LNMASTER."AC_DIRECTOR", 
        LNMASTER."AC_DIRECTOR_RELATION", 
        AUTHORITYMASTER.ID || '.$space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER", 
        "AC_AUTHORITY" "AUTHORITY", 
        RECOVERYCLEARKMASTER.ID || ' .$space.' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER", 
        "AC_RECOVERY_CLERK", 
        OWNBRANCHMASTER.ID || '.$space. ' || OWNBRANCHMASTER."NAME" "BRANCH", 
        LNMASTER."BRANCH_CODE", 
        CUSTOMERADDRESS."AC_CTCODE" "CITY", 
        CITYMASTER."CITY_NAME" "CITYNAME", 
        CUSTOMERADDRESS."AC_ADDR" || ' .$space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS", 
        LNMASTER."AC_MORATORIUM_PERIOD", 
        LNMASTER."AC_GRACE_PERIOD", 
        LNMASTER."AC_REPAYMODE", 
        IDMASTER."AC_MOBILENO", 
        (
          COALESCE(
            CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
        ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END, 
            0
          ) + COALESCE(
            CAST(LOANTRAN."TRAN_AMOUNT" AS FLOAT), 
            0 
          ) 
   + COALESCE(DAILYTRAN."DAILY_AMOUNT", 0)
        )"CLOSING_BALANCE", 
        (
          COALESCE(
            CASE LNMASTER."AC_OP_CD" WHEN '.$d.' 
        THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END, 
            0
          ) + COALESCE(
            CAST(
              LOANTRAN."RECPAY_INT_AMOUNT" AS FLOAT
            ), 
            0
          ) + COALESCE(
            DAILYTRAN."DAILY_RECPAY_INT_AMOUNT", 
            0
          ) + COALESCE(
            CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN CAST(
              LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
            ) ELSE (-1) * CAST(
              LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
            ) END, 
            0
          ) + COALESCE(
            CAST(
              LOANTRAN."OTHER10_AMOUNT" AS FLOAT
            ), 
            0
          ) + COALESCE(
            CAST(
              DAILYTRAN."DAILY_OTHER10_AMOUNT" AS FLOAT
            ), 
            0
          )
        ) "RECPAY_INT_AMOUNT", 
        LNMASTER."AC_INSTALLMENT", 
        OIRINTBALANCE(
          SCHEMAST."S_APPL", LNMASTER."BANKACNO", 
          '.$date1.', 0
        ) "OVERDUEINTEREST", 
        (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(
          CAST(
            LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
          ) / (
            CASE WHEN CAST(
              LNMASTER."AC_INSTALLMENT" AS FLOAT
            ) = 0 THEN 1 ELSE CAST(
              LNMASTER."AC_INSTALLMENT" AS FLOAT
            ) END
          )
        )else 0 end ) "TOTALINSTALLMENTS", 
        (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
          DUEBALANCE(
            CAST(
              SCHEMAST."S_APPL" AS CHARACTER VARYING
            ), 
            LNMASTER."BANKACNO", 
            '.$date1.', 
            '.$DUEBAL.', 
            0
          ) / (
            CASE WHEN CAST(
              LNMASTER."AC_INSTALLMENT" AS FLOAT
            ) = 0 THEN 1 ELSE CAST(
              LNMASTER."AC_INSTALLMENT" AS FLOAT
            ) END
          )
        ) else 0 end ) "DUEINSTALLMENT", 
        (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(
          (
            CAST(
              LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
            ) - DUEBALANCE(
              CAST(
                SCHEMAST."S_APPL" AS CHARACTER VARYING
              ), 
              LNMASTER."BANKACNO", 
              '.$date1.', 
              '.$DUEBAL.', 
              0
            )
          ) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS", 
        DUEBALANCE(
          CAST(
            SCHEMAST."S_APPL" AS CHARACTER VARYING
          ), 
          LNMASTER."BANKACNO", 
          '.$date1.', 
          '.$DUEBAL.', 
          0
        ) "DUEBALANCE", 
        "AC_EXPIRE_DATE", 
        OVERDUEDATE(
          SCHEMAST."S_APPL", 
          LNMASTER."BANKACNO", 
          '.$date1.', 
          CAST(
            DUEBALANCE(
              CAST(
                SCHEMAST."S_APPL" AS CHARACTER VARYING
              ), 
              LNMASTER."BANKACNO", 
              '.$date1.', 
              '.$DUEBAL.', 
              0
            ) AS CHARACTER VARYING
          ), 
          CAST (
            LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING
          )
        ) "OVERDUEDATE" 
      FROM 
        LNMASTER 
        LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE" 
        LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(
          LNMASTER."AC_RECOMMEND_BY" AS INTEGER
        ) 
        LEFT JOIN DIRECTORMASTER AS DIRECTORMAS ON DIRECTORMAS.ID = CAST(
          LNMASTER."AC_DIRECTOR" AS INTEGER
        ) 
        LEFT JOIN RECOVERYCLEARKMASTER ON RECOVERYCLEARKMASTER.ID = CAST(
          LNMASTER."AC_RECOVERY_CLERK" AS INTEGER
        ) 
        LEFT JOIN AUTHORITYMASTER ON AUTHORITYMASTER.ID = LNMASTER."AC_AUTHORITY" 
        LEFT JOIN OWNBRANCHMASTER ON OWNBRANCHMASTER.ID = LNMASTER."BRANCH_CODE" 
        LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID" 
        AND CUSTOMERADDRESS."AC_ADDTYPE" = '.$P.' 
        LEFT JOIN CITYMASTER ON CITYMASTER.ID = CUSTOMERADDRESS."AC_CTCODE" 
        LEFT JOIN IDMASTER ON IDMASTER.ID = LNMASTER."idmasterID", 
        (
          SELECT 
            "TRAN_ACNOTYPE", 
            "TRAN_ACTYPE", 
            "TRAN_ACNO", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
          ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "TRAN_AMOUNT", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) 
          ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "RECPAY_INT_AMOUNT", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
          ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "OTHER10_AMOUNT" 
          FROM 
            LOANTRAN 
          WHERE 
            CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateformat.') 
          GROUP BY 
            "TRAN_ACNOTYPE", 
            "TRAN_ACTYPE", 
            "TRAN_ACNO"
        ) LOANTRAN, 
        (
          SELECT 
            "TRAN_ACNOTYPE", 
            "TRAN_ACTYPE", 
            "TRAN_ACNO", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("TRAN_AMOUNT" AS FLOAT) 
          ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "DAILY_AMOUNT", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) 
          ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "DAILY_RECPAY_INT_AMOUNT", 
            COALESCE(
              SUM(
                CASE "TRAN_DRCR" WHEN '.$d.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) 
          ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
              ), 
              0
            ) "DAILY_OTHER10_AMOUNT" 
          FROM 
            DAILYTRAN 
          WHERE 
            CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateformat.') 
            AND "TRAN_STATUS" = '.$TRAN_STATUS.' 
          GROUP BY 
            "TRAN_ACNOTYPE", 
            "TRAN_ACTYPE", 
            "TRAN_ACNO"
        ) DAILYTRAN 
      WHERE 
        LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" 
        AND LNMASTER."AC_TYPE" = CAST(
          LOANTRAN."TRAN_ACTYPE" AS INTEGER
        ) 
        AND LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" 
        AND "AC_TYPE" = 22
        AND LNMASTER."BRANCH_CODE" IN (2) 
        AND LNMASTER."status" = 1 
        AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
        AND DIRECTORMASTER."IS_CURRENT_BODY_MEMBER" = '.$BODY_MEMBER.' 
        AND DIRECTORMAS."IS_CURRENT_BODY_MEMBER" = '.$BODY_MEMBER.' 
      ORDER BY 
        SCHEMAST."S_APPL", 
        LNMASTER."BANKACNO"
    ) AS S ';
 }





 echo $query;



   
$sql =  pg_query($conn,$query);
$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
while($row = pg_fetch_assoc($sql))
{ 

  if(isset($varsd1)){
    if($varsd1 == $row['S_APPL']){
        $sc1[] = $row['AC_SANCTION_AMOUNT']; 
        $sumVar1 += $row['AC_SANCTION_AMOUNT'];
       // echo "if part";
    }
    else{
        $sumVar1=0;
        $sc1= array_diff($sc1, $sc1);
        $varsd1= $row['S_APPL'];
        $sc1[] = $row['AC_SANCTION_AMOUNT'];
        $sumVar += $row['AC_SANCTION_AMOUNT'];
     //   echo "else1 part";
    }
}else{
    $sumVar1=0;
    $varsd1 = $row['S_APPL'];
    $sc1[] = $row['AC_SANCTION_AMOUNT'];
    $sumVar1 += $row['AC_SANCTION_AMOUNT'];
   // echo "2nd else part";
}
$result1[$varsd1] = $sc1;
$sumArray1[$varsd1] = $sumVar1;

// Installment Amount

if(isset($varsd2)){
    if($varsd2 == $row['S_APPL']){
        $sc2[] = $row['AC_INSTALLMENT']; 
        $sumVar2 += $row['AC_INSTALLMENT'];
       // echo "if part";
    }
    else{
        $sumVar2=0;
        $sc2= array_diff($sc2, $sc2);
        $varsd2= $row['S_APPL'];
        $sc2[] = $row['AC_INSTALLMENT'];
        $sumVar2 += $row['AC_INSTALLMENT'];
     //   echo "else1 part";
    }
}else{
    $sumVar2=0;
    $varsd2 = $row['S_APPL'];
    $sc2[] = $row['AC_INSTALLMENT'];
    $sumVar2 += $row['AC_INSTALLMENT'];
   // echo "2nd else part";
}
$result2[$varsd2] = $sc2;
$sumArray2[$varsd2] = $sumVar2;


$GRAND_TOTAL1= $GRAND_TOTAL1 + $row["AC_SANCTION_AMOUNT"];
$GRAND_TOTAL2= $GRAND_TOTAL2 + $row["AC_INSTALLMENT"];



$sum = abs($row['DUEBALANCE'] + $row['RECPAY_INT_AMOUNT'] + $row['OVERDUEINTEREST']);


    $tmp = [
        'S_APPL' => $row['S_APPL'],
        'S_NAME' => $row['S_NAME'],
        'SCHEME' => $row['SCHEME'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NO' => $row['AC_NO'], 
        'AC_NAME' => $row['AC_NAME'],
        'BANKACNO' => $row['BANKACNO'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_CLOSEDT' => $row['AC_CLOSEDT'],
        'bankName' => $bankName,
        'date' => $Dates,
        'scheme' => $row['SCHEME'],
        'branch' => $row['branch'],
        'branchName' => $BranchName1,
        'AC_SANCTION_AMOUNT' => $row['AC_SANCTION_AMOUNT'],
        'CLOSING_BALANCE' => $row['CLOSING_BALANCE'],
        'InstallmentAmount' => $row['AC_INSTALLMENT'],
        'DueInstallment' => $row['DUEINSTALLMENT'],
        'ExpiryDate' => $row['AC_EXPIRE_DATE'],
        'TotalOverdueAmount' => $sum,
        'ReceivableInterest' => $row['OVERDUEINTEREST'],
        // 'MOBILENO' => $row['AC_MOBILENO'],
        'bankac' =>$row['BANKACNO'],
        'DIRNAME' => $row['DIRECTOR_NAME'],
        'acsacam' => $row['AC_SANCTION_AMOUNT'],
        'DueBalance' => $row['DUEBALANCE'],
        'RecIntAmt' => $row['RECPAY_INT_AMOUNT'],

        'totalamt' => sprintf("%.2f",($row['DUEBALANCE'] + 0.0)),
        'totrecint' => sprintf("%.2f",($row['RECPAY_INT_AMOUNT'] + 0.0)),
        'totoverint' => sprintf("%.2f",($row['OVERDUEINTEREST'] + 0.0)),
        'overint' => sprintf("%.2f",($row['DUEINSTALLMENT'] + 0.0)),

       
        //GrandTotal
        "SAmt" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        "InstAmount" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,


    ];
    
    $data[$i] = $tmp;
    $i++;
    
    
 }

 
// ob_end_clean();

// $config = ['driver'=>'array','data'=>$data];
// // print_r($data);
// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');

}
?> 