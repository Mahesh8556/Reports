<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/LoanOverduetwodates.jrxml';
$space = "' '";
$data = [];
$faker = Faker\Factory::create('en_US');
$i = 0;
$initial = 0;
$space = "' '";
$D = "'D'";
// $date="'31/03/2022'";
$P = "'P'";
// $date1="'30/09/2023'";
$DUEBAL = "'DUEBAL'";
$dateFormat = "'DD/MM/YYYY'";
$dateFormat1 = "'dd/mm/yyyy'";
$LN = "'LN'";
$CC = "'CC'";
$DS = "'DS'";
$One = "'1'";
$schemeName = $_GET['AC_TYPE'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$mininstal = $_GET['NUMBER1'];
$maxinstall = $_GET['NUMBER2'];
$date = $_GET['DATE1'];
$date1 = $_GET['DATE2'];
$Branch = $_GET['BranchName'];
$bankname = $_GET['BANK'];
$comma = "','";


$bankname = str_replace("'", "", $bankname);
$Branch1 = str_replace("'", "", $Branch);
$date3 = str_replace("'", "", $date);
$date2 = str_replace("'", "", $date1);


$checktype;
$BRANCH_CODE == '0'? $checktype='true': $checktype='false';
//  echo $checktype;

if($BRANCH_CODE == '0'){
$query='SELECT 
S."DUEINSTALLMENT", S."OVERDUEDATE", * 
FROM 
  (
    SELECT 
      SCHEMAST."S_APPL", 
      SCHEMAST."S_APPL" || ' .$space.' || SCHEMAST."S_NAME" "SCHEME", 
      LNMASTER."AC_ACNOTYPE", 
      LNMASTER."AC_TYPE", 
      LNMASTER."AC_NO", 
      LNMASTER."AC_NAME", 
      LNMASTER."BANKACNO", 
      LNMASTER."AC_OPDATE", 
      LNMASTER."idmasterID", 
      LNMASTER."AC_CLOSEDT", 
      DIRECTORMASTER.ID || '.$space. ' || DIRECTORMASTER."NAME" "DIRECTORMASTER", 
      "AC_RECOMMEND_BY" "DIRECTOR", 
      AUTHORITYMASTER.ID || '.$space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER", 
      "AC_AUTHORITY" "AUTHORITY", 
      RECOVERYCLEARKMASTER.ID || '.$space. ' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER", 
      "AC_RECOVERY_CLERK", 
      CUSTOMERADDRESS."AC_CTCODE" "CITY", 
      CITYMASTER."CITY_NAME" "CITYNAME", 
      CUSTOMERADDRESS."AC_ADDR" || ' .$space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS", 
      LNMASTER."AC_MORATORIUM_PERIOD", 
      LNMASTER."AC_SANCTION_AMOUNT", 
      LNMASTER."AC_GRACE_PERIOD", 
      LNMASTER."AC_REPAYMODE", 
      IDMASTER."AC_MOBILENO", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT) ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END, 
          0
        ) + COALESCE(
          CAST(TRANTABLE."TRAN_AMOUNT" AS FLOAT), 
          0
        )
      ) "CLOSING_BALANCE", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END, 
          0
        ) + COALESCE(
          CAST(
            TRANTABLE."RECPAY_INT_AMOUNT" AS FLOAT
          ), 
          0
        ) + COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) ELSE (-1) * CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) END, 
          0
        ) + COALESCE(
          CAST(
            TRANTABLE."OTHER10_AMOUNT" AS FLOAT
          ), 
          0
        )
      ) "RECPAY_INT_AMOUNT", 
      LNMASTER."AC_INSTALLMENT", 
      OIRINTBALANCE(
        SCHEMAST."S_APPL", LNMASTER."BANKACNO", 
        '.$date1.', 0
      ) "OVERDUEINTEREST", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
        CAST(
          LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      )else 0 end ) "TOTALINSTALLMENTS", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then   CEIL(
        DUEBALANCE(
          CAST(
            SCHEMAST."S_APPL" AS CHARACTER VARYING
          ), 
          LNMASTER."BANKACNO", 
          '.$date1.', 
          '.$DUEBAL.', 
          0
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      )else 0 end ) "DUEINSTALLMENT", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
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
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      ) else 0 end )"PAIDINSTALLMENTS", 
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
        CAST('.$date1.' AS CHARACTER VARYING), 
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
        CAST(
          LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING
        )
      ) "OVERDUEDATE" 
     
    FROM 
      LNMASTER 
      LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE" 
      LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(
        LNMASTER."AC_RECOMMEND_BY" AS INTEGER
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
              COALESCE(
                CAST("TRAN_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_AMOUNT", 0)
            ), 
            0
          ) "TRAN_AMOUNT", 
          COALESCE(
            SUM(
              COALESCE(
                CAST("RECPAY_INT_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_RECPAY_INT_AMOUNT", 0)
            ), 
            0
          ) "RECPAY_INT_AMOUNT", 
          COALESCE(
            SUM(
              COALESCE(
                CAST("OTHER10_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_OTHER10_AMOUNT", 0)
            ), 
            0
          ) "OTHER10_AMOUNT" 
        FROM 
          (
            SELECT 
              * 
            FROM 
              (
                SELECT 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "TRAN_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "RECPAY_INT_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "OTHER10_AMOUNT", 
                  0 "DAILY_AMOUNT", 
                  0 "DAILY_RECPAY_INT_AMOUNT", 
                  0 "DAILY_OTHER10_AMOUNT" 
                FROM 
                  LOANTRAN 
                WHERE 
                  CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateFormat.') 
                  AND "TRAN_ACNOTYPE" IN ('.$LN.', '.$CC.', '.$DS.') 
                GROUP BY 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO" 
                UNION ALL 
                SELECT 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_RECPAY_INT_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_OTHER10_AMOUNT", 
                  0 "TRAN_AMOUNT", 
                  0 "RECPAY_INT_AMOUNT", 
                  0 "OTHER10_AMOUNT" 
                FROM 
                  DAILYTRAN 
                WHERE 
                  CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateFormat.') 
                  AND "TRAN_STATUS" = '.$One .' 
                  AND "TRAN_ACNOTYPE" IN ('.$LN.', '.$CC.', '.$DS.') 
                GROUP BY 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO"
              ) RS 
            ORDER BY 
              "TRAN_ACNOTYPE", 
              "TRAN_ACTYPE", 
              "TRAN_ACNO"
          ) AMOUNT 
        GROUP BY 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO"
      ) TRANTABLE 
    WHERE 
      LNMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE" 
      AND LNMASTER."AC_TYPE" = CAST(
        TRANTABLE."TRAN_ACTYPE" AS INTEGER
      ) 
      AND LNMASTER."BANKACNO" = TRANTABLE."TRAN_ACNO" 
      AND "AC_TYPE" = '.$schemeName.'
      AND LNMASTER."status" = 1 
      AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
    ORDER BY 
      SCHEMAST."S_APPL", 
      LNMASTER."BANKACNO"
  ) AS S 
  WHERE CAST(S."DUEINSTALLMENT" AS INTEGER) <= '.$maxinstall.'
	AND CAST(S."DUEINSTALLMENT" AS INTEGER) > '.$mininstal.'
	AND CAST(S."OVERDUEDATE" AS DATE) <= TO_DATE('.$date1.','.$dateFormat.')
	AND (CAST(S."OVERDUEDATE" AS DATE) > TO_DATE('.$date.','.$dateFormat1.'))';

  
      }
      else{
        $query='SELECT 
S."DUEINSTALLMENT", S."OVERDUEDATE", * 
FROM 
  (
    SELECT 
      SCHEMAST."S_APPL", 
      SCHEMAST."S_APPL" || ' .$space.' || SCHEMAST."S_NAME" "SCHEME", 
      LNMASTER."AC_ACNOTYPE", 
      LNMASTER."AC_TYPE", 
      LNMASTER."AC_NO", 
      LNMASTER."AC_NAME", 
      LNMASTER."BANKACNO", 
      LNMASTER."AC_OPDATE", 
      LNMASTER."idmasterID", 
      LNMASTER."AC_CLOSEDT", 
      DIRECTORMASTER.ID || '.$space. ' || DIRECTORMASTER."NAME" "DIRECTORMASTER", 
      "AC_RECOMMEND_BY" "DIRECTOR", 
      AUTHORITYMASTER.ID || '.$space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER", 
      "AC_AUTHORITY" "AUTHORITY", 
      RECOVERYCLEARKMASTER.ID || '.$space. ' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER", 
      "AC_RECOVERY_CLERK", 
      OWNBRANCHMASTER.ID || '.$space. ' || OWNBRANCHMASTER."NAME" "BRANCH", 
      LNMASTER."BRANCH_CODE", 
      CUSTOMERADDRESS."AC_CTCODE" "CITY", 
      CITYMASTER."CITY_NAME" "CITYNAME", 
      CUSTOMERADDRESS."AC_ADDR" || ' .$space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS", 
      LNMASTER."AC_MORATORIUM_PERIOD", 
      LNMASTER."AC_SANCTION_AMOUNT", 
      LNMASTER."AC_GRACE_PERIOD", 
      LNMASTER."AC_REPAYMODE", 
      IDMASTER."AC_MOBILENO", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT) ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT) END, 
          0
        ) + COALESCE(
          CAST(TRANTABLE."TRAN_AMOUNT" AS FLOAT), 
          0
        )
      ) "CLOSING_BALANCE", 
      (
        COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END, 
          0
        ) + COALESCE(
          CAST(
            TRANTABLE."RECPAY_INT_AMOUNT" AS FLOAT
          ), 
          0
        ) + COALESCE(
          CASE LNMASTER."AC_OP_CD" WHEN '.$D .' THEN CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) ELSE (-1) * CAST(
            LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT
          ) END, 
          0
        ) + COALESCE(
          CAST(
            TRANTABLE."OTHER10_AMOUNT" AS FLOAT
          ), 
          0
        )
      ) "RECPAY_INT_AMOUNT", 
      LNMASTER."AC_INSTALLMENT", 
      OIRINTBALANCE(
        SCHEMAST."S_APPL", LNMASTER."BANKACNO", 
        '.$date1.', 0
      ) "OVERDUEINTEREST", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
        CAST(
          LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      ) else 0 end )"TOTALINSTALLMENTS", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
        DUEBALANCE(
          CAST(
            SCHEMAST."S_APPL" AS CHARACTER VARYING
          ), 
          LNMASTER."BANKACNO", 
          '.$date1.', 
          '.$DUEBAL.', 
          0
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      ) else 0 end )"DUEINSTALLMENT", 
      (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(
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
        ) / CAST(
          LNMASTER."AC_INSTALLMENT" AS FLOAT
        )
      )else 0 end ) "PAIDINSTALLMENTS", 
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
        CAST('.$date1.' AS CHARACTER VARYING), 
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
        CAST(
          LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING
        )
      ) "OVERDUEDATE" 
     
    FROM 
      LNMASTER 
      LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE" 
      LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(
        LNMASTER."AC_RECOMMEND_BY" AS INTEGER
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
              COALESCE(
                CAST("TRAN_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_AMOUNT", 0)
            ), 
            0
          ) "TRAN_AMOUNT", 
          COALESCE(
            SUM(
              COALESCE(
                CAST("RECPAY_INT_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_RECPAY_INT_AMOUNT", 0)
            ), 
            0
          ) "RECPAY_INT_AMOUNT", 
          COALESCE(
            SUM(
              COALESCE(
                CAST("OTHER10_AMOUNT" AS FLOAT), 
                0
              ) + COALESCE("DAILY_OTHER10_AMOUNT", 0)
            ), 
            0
          ) "OTHER10_AMOUNT" 
        FROM 
          (
            SELECT 
              * 
            FROM 
              (
                SELECT 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "TRAN_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "RECPAY_INT_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "OTHER10_AMOUNT", 
                  0 "DAILY_AMOUNT", 
                  0 "DAILY_RECPAY_INT_AMOUNT", 
                  0 "DAILY_OTHER10_AMOUNT" 
                FROM 
                  LOANTRAN 
                WHERE 
                  CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateFormat.') 
                  AND "TRAN_ACNOTYPE" IN ('.$LN.', '.$CC.', '.$DS.') 
                GROUP BY 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO" 
                UNION ALL 
                SELECT 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_RECPAY_INT_AMOUNT", 
                  COALESCE(
                    SUM(
                      CASE "TRAN_DRCR" WHEN '.$D .' THEN CAST ("OTHER10_AMOUNT" AS FLOAT) ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT) END
                    ), 
                    0
                  ) "DAILY_OTHER10_AMOUNT", 
                  0 "TRAN_AMOUNT", 
                  0 "RECPAY_INT_AMOUNT", 
                  0 "OTHER10_AMOUNT" 
                FROM 
                  DAILYTRAN 
                WHERE 
                  CAST("TRAN_DATE" AS date) <= TO_DATE('.$date1.', '.$dateFormat.') 
                  AND "TRAN_STATUS" = '.$One .' 
                  AND "TRAN_ACNOTYPE" IN ('.$LN.', '.$CC.', '.$DS.') 
                GROUP BY 
                  "TRAN_ACNOTYPE", 
                  "TRAN_ACTYPE", 
                  "TRAN_ACNO"
              ) RS 
            ORDER BY 
              "TRAN_ACNOTYPE", 
              "TRAN_ACTYPE", 
              "TRAN_ACNO"
          ) AMOUNT 
        GROUP BY 
          "TRAN_ACNOTYPE", 
          "TRAN_ACTYPE", 
          "TRAN_ACNO"
      ) TRANTABLE 
    WHERE 
      LNMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE" 
      AND LNMASTER."AC_TYPE" = CAST(
        TRANTABLE."TRAN_ACTYPE" AS INTEGER
      ) 
      AND LNMASTER."BANKACNO" = TRANTABLE."TRAN_ACNO" 
      AND "AC_TYPE" = '.$schemeName.'
      AND LNMASTER."BRANCH_CODE" IN ('.$BRANCH_CODE.')
      AND LNMASTER."status" = 1 
      AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL 
    ORDER BY 
      SCHEMAST."S_APPL", 
      LNMASTER."BANKACNO"
  ) AS S 
  WHERE CAST(S."DUEINSTALLMENT" AS INTEGER) <= '.$maxinstall.'
	AND CAST(S."DUEINSTALLMENT" AS INTEGER) > '.$mininstal.'
	AND CAST(S."OVERDUEDATE" AS DATE) <= TO_DATE('.$date1.','.$dateFormat.')
	AND (CAST(S."OVERDUEDATE" AS DATE) > TO_DATE('.$date.','.$dateFormat1.'))';

  // echo $query;

      }


  //  echo $query;

$sql =  pg_query($conn, $query);
$GrandTotal = 0;
$GrandTotal1 = 0;
$GrandTotal2 = 0;
$GrandTotal3 = 0;
$GrandTotal4 = 0;
$GrandTotal5 = 0;
$GrandTotal6 = 0;
$GrandTotal_1 = 0;
$GrandTotal_2 = 0;
$GrandTotal_3 = 0;
$GrandTotal_4 = 0;
$GrandTotal_5 = 0;
$GrandTotal_6 = 0;


while ($row = pg_fetch_assoc($sql)) {

    if (isset($varsd)) {
        if ($varsd == $row['S_APPL']) {
            $sc[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar += $row['AC_SANCTION_AMOUNT'];
        } else {
            //empty array before adding new
            $sumVar = 0;
            $sc = array_diff($sc, $sc);
            $varsd = $row['S_APPL'];
            $sc[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar += $row['AC_SANCTION_AMOUNT'];
        }
    } else {
        $sumVar = 0;
        $varsd = $row['S_APPL'];
        $sc[] = $row['AC_SANCTION_AMOUNT'];
        $sumVar += $row['AC_SANCTION_AMOUNT'];
    }
    $result[$varsd] = $sc;
    $sumArray[$varsd] = $sumVar;

    $GrandTotal1 = $GrandTotal1 + $row["AC_INSTALLMENT"];
    if (isset($varsd1)) {
        if ($varsd1 == $row['S_APPL']) {
            $sc1[] = $row['AC_INSTALLMENT'];
            $sumVar1 += $row['AC_INSTALLMENT'];
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar1 = 0;
            $sc1 = array_diff($sc1, $sc1);
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['AC_INSTALLMENT'];
            $sumVar1 += $row['AC_INSTALLMENT'];
            //    echo "else1 part";
        }
    } else {
        $sumVar1 = 0;
        $varsd1 = $row['S_APPL'];
        $sc1[] = $row['AC_INSTALLMENT'];
        $sumVar1 += $row['AC_INSTALLMENT'];
        // echo "2nd else part";
    }
    $result1[$varsd1] = $sc1;
    $sumArray1[$varsd1] = $sumVar1;

    $GrandTotal2 = $GrandTotal2 + $row["RECPAY_INT_AMOUNT"];
    if (isset($varsd2)) {
        if ($varsd2 == $row['S_APPL']) {
            $sc2[] = $row['RECPAY_INT_AMOUNT'];
            $sumVar2 += $row['RECPAY_INT_AMOUNT'];
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar2 = 0;
            $sc2 = array_diff($sc2, $sc2);
            $varsd2 = $row['S_APPL'];
            $sc2[] = $row['RECPAY_INT_AMOUNT'];
            $sumVar2 += $row['RECPAY_INT_AMOUNT'];
            //    echo "else1 part";
        }
    } else {
        $sumVar2 = 0;
        $varsd2 = $row['S_APPL'];
        $sc2[] = $row['RECPAY_INT_AMOUNT'];
        $sumVar2 += $row['RECPAY_INT_AMOUNT'];
        // echo "2nd else part";
    }
    $result2[$varsd2] = $sc2;
    $sumArray2[$varsd2] = $sumVar2;

    $GrandTotal3 = $GrandTotal3 + $row["LEDGER_BALANCE"];
    if (isset($varsd3)) {
        if ($varsd3 == $row['S_APPL']) {
            $sc3[] = $row['LEDGER_BALANCE'];
            $sumVar3 += $row['LEDGER_BALANCE'];
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar3 = 0;
            $sc3 = array_diff($sc3, $sc3);
            $varsd3 = $row['S_APPL'];
            $sc3[] = $row['LEDGER_BALANCE'];
            $sumVar3 += $row['LEDGER_BALANCE'];
            //    echo "else1 part";
        }
    } else {
        $sumVar3 = 0;
        $varsd3 = $row['S_APPL'];
        $sc3[] = $row['LEDGER_BALANCE'];
        $sumVar3 += $row['LEDGER_BALANCE'];
        // echo "2nd else part";
    }
    $result3[$varsd3] = $sc3;
    $sumArray3[$varsd3] = $sumVar3;

    $GrandTotal4 = $GrandTotal4 + $row["DUEBALANCE"];
    if (isset($varsd4)) {
        if ($varsd4 == $row['S_APPL']) {
            $sc4[] = $row['DUEBALANCE'];
            $sumVar4 += $row['DUEBALANCE'];
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar4 = 0;
            $sc4 = array_diff($sc4, $sc4);
            $varsd4 = $row['S_APPL'];
            $sc4[] = $row['DUEBALANCE'];
            $sumVar4 += $row['DUEBALANCE'];
            //    echo "else1 part";
        }
    } else {
        $sumVar4 = 0;
        $varsd4 = $row['S_APPL'];
        $sc4[] = $row['DUEBALANCE'];
        $sumVar4 += $row['DUEBALANCE'];
        // echo "2nd else part";
    }
    $result4[$varsd4] = $sc4;
    $sumArray4[$varsd4] = $sumVar4;

    $GrandTotal5 = $GrandTotal5 + $row["DUEINSTALLMENT"];
    if (isset($varsd5)) {
        if ($varsd5 == $row['S_APPL']) {
            $sc5[] = $row['DUEINSTALLMENT'];
            $sumVar5 += $row['DUEINSTALLMENT'];
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar5 = 0;
            $sc5 = array_diff($sc5, $sc5);
            $varsd5 = $row['S_APPL'];
            $sc5[] = $row['DUEINSTALLMENT'];
            $sumVar5 += $row['DUEINSTALLMENT'];
            //    echo "else1 part";
        }
    } else {
        $sumVar5 = 0;
        $varsd5 = $row['S_APPL'];
        $sc5[] = $row['DUEINSTALLMENT'];
        $sumVar5 += $row['DUEINSTALLMENT'];
        // echo "2nd else part";
    }
    $result5[$varsd5] = $sc5;
    $sumArray5[$varsd5] = $sumVar5;
    $sum = abs($row['DUEINSTALLMENT'] + $row['RECPAY_INT_AMOUNT']);
    $GrandTotal6 = $GrandTotal6 + $sum;
    if (isset($varsd6)) {
        if ($varsd6 == $row['S_APPL']) {
            $sc6[] = $sum;
            $sumVar6 += $sum;
            // echo $sumVar;
            // echo "if part";
        } else {
            $sumVar6 = 0;
            $sc6 = array_diff($sc6, $sc6);
            $varsd6 = $row['S_APPL'];
            $sc6[] = $sum;
            $sumVar6 += $sum;
            //    echo "else1 part";
        }
    } else {
        $sumVar6 = 0;
        $varsd6 = $row['S_APPL'];
        $sc6[] = $sum;
        $sumVar6 += $sum;
        // echo "2nd else part";
    }
    $result6[$varsd6] = $sc6;
    $sumArray6[$varsd6] = $sumVar6;

    $query1 = 'select "lnmasterID",  string_agg("AC_NAME",' . $comma . ') "GUARANTER" from GUARANTERDETAILS where
    GUARANTERDETAILS."lnmasterID"=13 AND "EXP_DATE" IS NULL group by "lnmasterID"';
    $sql1 =  pg_query($conn, $query1);

    echo $GrandTotal;
    // if{
    //     $GrandTotal=0;
    //     $amc = $row['AC_SANCTION_AMOUNT'],
    // }
    while ($row1 = pg_fetch_assoc($sql1)) {
        $Gname = $row1['GUARANTER'];
    }
    $sum = abs($row['DUEINSTALLMENT'] + $row['RECPAY_INT_AMOUNT']);
    // $GrandTotal= ((float)$GrandTotal+(float)$row['AC_SANCTION_AMOUNT']);
    $GrandTotal = $GrandTotal + $row['AC_SANCTION_AMOUNT'];
    $GrandTotal_1 = $GrandTotal_1 + $row['AC_INSTALLMENT'];
    $GrandTotal_2 = $GrandTotal_2 + $row['RECPAY_INT_AMOUNT'];
    $GrandTotal_3 = $GrandTotal_3 + $row['LEDGER_BALANCE'];
    $GrandTotal_4 = $GrandTotal_4 + $row['DUEBALANCE'];
    $GrandTotal_5 = $GrandTotal_5 + $row['CLOSING_BALANCE'];
    $GrandTotal_6 = $GrandTotal_6 + $row['CLOSING_BALANCE'];



    // $GrandTotal = (floatval($GrandTotal) + floatval($row['AC_SANCTION_AMOUNT']));
    $temp = [
        'date1' => $date3,
        'date2' => $date2,
        'branch' => $Branch1,
        'bank' => $bankname,
        'schemeNo' => $row['S_APPL'],
        'schemeName' => $row['SCHEME'],       
        'Ac_No' => $row['AC_NO'],
        'AccountHolderName' => $row['AC_NAME'],
        'GuarantorName' => $Gname,
        'AccountHolderPhoneNumber' => $row['AC_MOBILENO'],
        'City' => $row['CITYNAME'],
        'OpeningDate' => $row['AC_OPDATE'],
        'ExpiryDate' => $row['AC_EXPIRE_DATE'],
        'SanctionedLoanAmount' => $row['AC_SANCTION_AMOUNT'],
        'InstallmentAmount' => $row['AC_INSTALLMENT'],
        'ReceivableInterest' => $row['RECPAY_INT_AMOUNT'],
        'OutstandingBalance' => $row['CLOSING_BALANCE'],
        'DueBalance' => $row['DUEBALANCE'],
        'DueInstallment' => $row['DUEINSTALLMENT'],
        'TotalOverdueAmount' => $row['CLOSING_BALANCE'],
        'bankac' =>$row['BANKACNO'],

        // 'SA' => sprintf("%.2f", ($sumArray[$varsd])) . ' ' . $netType,
        // 'IA' => sprintf("%.2f", ($sumArray1[$varsd1])) . ' ' . $netType,
        // 'RI' => sprintf("%.2f", ($sumArray2[$varsd2])) . ' ' . $netType,
        // 'OB' => sprintf("%.2f", ($sumArray3[$varsd3])) . ' ' . $netType,
        // 'DB' => sprintf("%.2f", ($sumArray4[$varsd4])) . ' ' . $netType,
        // 'DI' => sprintf("%.2f", ($sumArray5[$varsd5])) . ' ' . $netType,
        // 'TOA' => sprintf("%.2f", ($sumArray6[$varsd6])) . ' ' . $netType,
        // "TSA" => sprintf("%.2f", ($GRAND_TOTAL) + 0.0),
        // "TIA" => sprintf("%.2f", ($GRAND_TOTAL1) + 0.0),
        // "TRI" => sprintf("%.2f", ($GRAND_TOTAL2) + 0.0),
        // "TOB" => sprintf("%.2f", ($GRAND_TOTAL3) + 0.0),
        // "TDB" => sprintf("%.2f", ($GRAND_TOTAL4) + 0.0),
        // "TDI" => sprintf("%.2f", ($GRAND_TOTAL5) + 0.0),
        // "TTOA" => sprintf("%.2f", ($GRAND_TOTAL6) + 0.0),
        "TotalSactionAmount" => sprintf("%.2f", ($GrandTotal) + 0.0),
        "TotalinstAmt" => sprintf("%.2f", ($GrandTotal_1) + 0.0),
        "TotalRace" => sprintf("%.2f", ($GrandTotal_2) + 0.0),
        "Totalout" => sprintf("%.2f", ($GrandTotal_3) + 0.0),
        "Totaldue" => sprintf("%.2f", ($GrandTotal_4) + 0.0),
        "OutstandingBal" => sprintf("%.2f", ($GrandTotal_5) + 0.0),
        "totalbal" => sprintf("%.2f", ($GrandTotal_6) + 0.0),



    ];
  
    $data[$i] = $temp;
    $i++;
}

ob_end_clean();
$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    ?>
