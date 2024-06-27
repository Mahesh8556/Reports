<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/duebalancesummary.jrxml';

$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$AC_TYPE=$_GET['schemeid']; 
$bankName  = $_GET['bankName'];
$BranchName = $_GET['branchName'];
// $schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['branch'];
$date=$_GET['date'];
$TRAN_STATUS="'1'";
$DUEBAL="'DUEBAL'";
$LN="'LN'";
$CC="'CC'";
$DS="'DS'";
$P="'P'";
$Space="' '";
$dd="'DD/MM/YYYY'";
$bankName = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);
$date1 = str_replace("'", "", $date);
$D="'D'";



$query='SELECT
"TOTAL_SUMMARY"."S_APPL"               ,
"TOTAL_SUMMARY"."S_NAME"               ,
"TOTAL_SUMMARY"."TOTAL_NO_OF_AC"       ,
"TOTAL_SUMMARY"."TOTAL_CLOSING_BALANCE",
"DUE_SUMMARY"."DUE_NO_OF_AC"           ,
"DUE_SUMMARY"."DUE_BALANCE"            ,
"EXP_SUMMARY"."EXP_NO_OF_AC"           ,
"EXP_SUMMARY"."EXP_CLOSING_BALANCE"
FROM
(
        SELECT
                SCHEMAST."S_APPL"        ,
                SCHEMAST."S_NAME"        ,
                VWTMPZLNBALANCE."AC_TYPE",
                SUM(COALESCE(
                        CASE
                                "CLOSING_BALANCE"
                        WHEN
                                0
                        THEN
                                0
                        ELSE
                                1
                        END , 0)) "TOTAL_NO_OF_AC",
                SUM(VWTMPZLNBALANCE."CLOSING_BALANCE") "TOTAL_CLOSING_BALANCE"
        FROM
                SCHEMAST ,
                LNMASTER ,
                (
                        SELECT
                                LNMASTER."AC_ACNOTYPE",
                                LNMASTER."AC_TYPE"    ,
                                LNMASTER."BANKACNO"   ,
                                LNMASTER."AC_NO"      ,
                                LNMASTER."AC_OPDATE"  ,
                                LNMASTER."AC_CLOSEDT" ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."TRAN_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_AMOUNT" AS FLOAT),0)) "CLOSING_BALANCE" ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                LNMASTER."AC_RECBLEINT_OP"
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_RECBLEINT_OP" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST("AC_RECBLEODUEINT_OP" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."OTHER10_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_OTHER10_AMOUNT" AS FLOAT),0)) "RECPAY_INT_AMOUNT"
                        FROM
                                LNMASTER
                        LEFT OUTER JOIN
                                (
                                        SELECT
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO"    ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                        END),0) "TRAN_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        END),0) "RECPAY_INT_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        END),0) "OTHER10_AMOUNT"
                                        FROM
                                                LOANTRAN
                                        WHERE
                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$date.', '.$dd.')
                                        GROUP BY
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO" ) LOANTRAN
                        ON
                                LNMASTER."AC_TYPE"  = LOANTRAN."TRAN_ACTYPE"
                        AND     LNMASTER."BANKACNO" =  LOANTRAN."TRAN_ACNO"
                        LEFT OUTER JOIN
                                (
                                        SELECT
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO"    ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_RECPAY_INT_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_OTHER10_AMOUNT"
                                        FROM
                                                DAILYTRAN
                                        WHERE
                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$date.', '.$dd.')
                                        AND     "TRAN_STATUS"             = '.$TRAN_STATUS.'
                                        GROUP BY
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO" ) DAILYTRAN
                        ON
                                LNMASTER."AC_TYPE"  = DAILYTRAN."TRAN_ACTYPE"
                        AND     LNMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
                        Where
                                (
                                        (
                                                CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL)
                                OR (
                                                CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$date.','.$dd.')))
                        AND     (
                                        (
                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL)
                                OR (
                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$date.','.$dd.'))) ) VWTMPZLNBALANCE
        Where
                SCHEMAST."S_ACNOTYPE"             = LNMASTER."AC_ACNOTYPE"
        AND     SCHEMAST.ID                       = LNMASTER."AC_TYPE"
        AND     LNMASTER."AC_ACNOTYPE"            = VWTMPZLNBALANCE."AC_ACNOTYPE"
        AND     LNMASTER."AC_TYPE"                = VWTMPZLNBALANCE."AC_TYPE"
        AND     LNMASTER."BANKACNO"               = VWTMPZLNBALANCE."BANKACNO"
        
        AND     VWTMPZLNBALANCE."CLOSING_BALANCE" > 0
        GROUP BY
                "S_APPL"                                   ,
                "S_NAME"                                   ,
                VWTMPZLNBALANCE."AC_TYPE" ) "TOTAL_SUMMARY",
(
        SELECT
                SCHEMAST."S_APPL"        ,
                SCHEMAST."S_NAME"        ,
                VWTMPZLNOVERDUE."AC_TYPE",
                SUM(COALESCE(
                        CASE
                                VWTMPZLNOVERDUE."DUEBALANCE"
                        WHEN
                                0
                        THEN
                                0
                        ELSE
                                1
                        END,0)) "DUE_NO_OF_AC",
                SUM(VWTMPZLNOVERDUE."DUEBALANCE") "DUE_BALANCE"
        From
                SCHEMAST ,
                LNMASTER ,
                (
                        SELECT
                                SCHEMAST."S_APPL"                                                                   ,
                                SCHEMAST."S_APPL" || '.$Space.' || SCHEMAST."S_NAME" "SCHEME"                              ,
                                LNMASTER."AC_ACNOTYPE"                                                              ,
                                LNMASTER."AC_TYPE"                                                                  ,
                                LNMASTER."AC_NO"                                                                    ,
                                LNMASTER."AC_NAME"                                                                  ,
                                LNMASTER."BANKACNO"                                                                 ,
                                LNMASTER."AC_OPDATE"                                                                ,
                                LNMASTER."idmasterID"                                                               ,
                                LNMASTER."AC_CLOSEDT"                                                               ,
                                DIRECTORMASTER.ID ||'.$Space.' || DIRECTORMASTER."NAME" "DIRECTORMASTER"                   ,
                                "AC_RECOMMEND_BY" "DIRECTOR"                                                        ,
                                AUTHORITYMASTER.ID || '.$Space.' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER"               ,
                                "AC_AUTHORITY" "AUTHORITY"                                                          ,
                                RECOVERYCLEARKMASTER.ID || '.$Space.' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER",
                                "AC_RECOVERY_CLERK"                                                                 ,
                                OWNBRANCHMASTER.ID || '.$Space.' || OWNBRANCHMASTER."NAME" "BRANCH"                        ,
                                LNMASTER."BRANCH_CODE"                                                              ,
                                CUSTOMERADDRESS."AC_CTCODE" "CITY"                                                  ,
                                CITYMASTER."CITY_NAME" "CITYNAME"                                                   ,
                                CUSTOMERADDRESS."AC_ADDR" || '.$Space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS"             ,
                                LNMASTER."AC_MORATORIUM_PERIOD"                                                     ,
                                LNMASTER."AC_SANCTION_AMOUNT"                                                       ,
                                LNMASTER."AC_GRACE_PERIOD"                                                          ,
                                LNMASTER."AC_REPAYMODE"                                                             ,
                                IDMASTER."AC_MOBILENO"                                                              ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        END,0)       + COALESCE(CAST(TRANTABLE."TRAN_AMOUNT" AS FLOAT),0) ) "CLOSING_BALANCE" ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                LNMASTER."AC_RECBLEINT_OP"
                                        ELSE
                                                (-1) * LNMASTER."AC_RECBLEINT_OP"
                                        END,0)       + COALESCE(CAST(TRANTABLE."RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                        END,0)       + COALESCE(CAST(TRANTABLE."OTHER10_AMOUNT" AS FLOAT),0) ) "RECPAY_INT_AMOUNT"                                                                                                                    ,
                                LNMASTER."AC_INSTALLMENT"                                                                                                                                                                                             ,
                                OIRINTBALANCE(SCHEMAST."S_APPL",LNMASTER."BANKACNO",'.$date.',0) "OVERDUEINTEREST"                                                                                                                                 ,
                                (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                        /CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS"                                                                                                       ,
                                (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  ceil(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0)/ CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"DUEINSTALLMENT"                                                                                                         ,
                                (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then ceil((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                       - DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0) )/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS",
                                DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0) "DUEBALANCE"                                                                                                                                                             ,
                                "AC_EXPIRE_DATE"                                                                                                                                                                                                                                                              ,
                                overduedate(CAST(SCHEMAST."S_APPL" AS INTEGER),LNMASTER."BANKACNO", CAST('.$date.' AS CHARACTER VARYING) , CAST(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0) AS CHARACTER VARYING), CAST(LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING)) "OVERDUEDATE"
                        FROM
                                LNMASTER
                        LEFT JOIN
                                SCHEMAST
                        ON
                                SCHEMAST.ID= LNMASTER."AC_TYPE"
                        LEFT JOIN
                                DIRECTORMASTER
                        ON
                                DIRECTORMASTER.ID=CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER)
                        LEFT JOIN
                                RECOVERYCLEARKMASTER
                        ON
                                RECOVERYCLEARKMASTER.ID=CAST(LNMASTER."AC_RECOVERY_CLERK" AS INTEGER)
                        LEFT JOIN
                                AUTHORITYMASTER
                        ON
                                AUTHORITYMASTER.ID=LNMASTER."AC_AUTHORITY"
                        LEFT JOIN
                                OWNBRANCHMASTER
                        ON
                                OWNBRANCHMASTER.ID=LNMASTER."BRANCH_CODE"
                        LEFT JOIN
                                CUSTOMERADDRESS
                        ON
                                CUSTOMERADDRESS."idmasterID" =LNMASTER."idmasterID"
                        AND     CUSTOMERADDRESS."AC_ADDTYPE" ='.$P.'
                        LEFT JOIN
                                CITYMASTER
                        ON
                                CITYMASTER.ID= CUSTOMERADDRESS."AC_CTCODE"
                        LEFT JOIN
                                IDMASTER
                        ON
                                IDMASTER.ID=LNMASTER."idmasterID",
                                (
                                        SELECT
                                                "TRAN_ACNOTYPE"                                                                                                      ,
                                                "TRAN_ACTYPE"                                                                                                        ,
                                                "TRAN_ACNO"                                                                                                          ,
                                                COALESCE(SUM(COALESCE(CAST("TRAN_AMOUNT" AS FLOAT),0)       + COALESCE("DAILY_AMOUNT",0)),0) "TRAN_AMOUNT"                 ,
                                                COALESCE(SUM(COALESCE(CAST("RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE("DAILY_RECPAY_INT_AMOUNT",0)),0) "RECPAY_INT_AMOUNT",
                                                COALESCE(SUM(COALESCE(CAST("OTHER10_AMOUNT" AS FLOAT),0)    + COALESCE("DAILY_OTHER10_AMOUNT",0)),0) "OTHER10_AMOUNT"
                                        FROM
                                                (
                                                        SELECT
                                                                *
                                                        FROM
                                                                (
                                                                        SELECT
                                                                                "TRAN_ACNOTYPE",
                                                                                "TRAN_ACTYPE"  ,
                                                                                "TRAN_ACNO"    ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                        END),0) "TRAN_AMOUNT" ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                        END),0) "RECPAY_INT_AMOUNT" ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                        END),0) "OTHER10_AMOUNT",
                                                                                0 "DAILY_AMOUNT"                ,
                                                                                0 "DAILY_RECPAY_INT_AMOUNT"     ,
                                                                                0 "DAILY_OTHER10_AMOUNT"
                                                                        FROM
                                                                                LOANTRAN
                                                                        WHERE
                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$date.', '.$dd.')
                                                                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                                                                   '.$CC.' ,
                                                                                                   '.$DS.')
                                                                        GROUP BY
                                                                                "TRAN_ACNOTYPE",
                                                                                "TRAN_ACTYPE"  ,
                                                                                "TRAN_ACNO"
                                                                        
                                                                        UNION ALL
                                                                        
                                                                        SELECT
                                                                                "TRAN_ACNOTYPE",
                                                                                "TRAN_ACTYPE"  ,
                                                                                "TRAN_ACNO"    ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                        END),0) "DAILY_AMOUNT" ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                        END),0) "DAILY_RECPAY_INT_AMOUNT" ,
                                                                                COALESCE(SUM(
                                                                                        CASE
                                                                                                "TRAN_DRCR"
                                                                                        WHEN
                                                                                                '.$D.'
                                                                                        THEN
                                                                                                CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                        ELSE
                                                                                                (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                        END),0) "DAILY_OTHER10_AMOUNT" ,
                                                                                0 "TRAN_AMOUNT"                        ,
                                                                                0 "RECPAY_INT_AMOUNT"                  ,
                                                                                0 "OTHER10_AMOUNT"
                                                                        FROM
                                                                                DAILYTRAN
                                                                        WHERE
                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$date.', '.$dd.')
                                                                        AND     "TRAN_STATUS"             = '.$TRAN_STATUS.'
                                                                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                                                                   '.$CC.' ,
                                                                                                   '.$DS.')
                                                                        GROUP BY
                                                                                "TRAN_ACNOTYPE",
                                                                                "TRAN_ACTYPE"  ,
                                                                                "TRAN_ACNO") RS
                                                        ORDER BY
                                                                "TRAN_ACNOTYPE",
                                                                "TRAN_ACTYPE"  ,
                                                                "TRAN_ACNO")AMOUNT
                                        GROUP BY
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO" ) TRANTABLE
                        Where
                                LNMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE"
                        AND     LNMASTER."AC_TYPE"     = CAST(TRANTABLE."TRAN_ACTYPE" AS INTEGER)
                        AND     LNMASTER."BANKACNO"    =  TRANTABLE."TRAN_ACNO"
                      
                        AND     LNMASTER."status"=1
                        AND     LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
                        ORDER BY
                                SCHEMAST."S_APPL",
                                LNMASTER."BANKACNO" ) VWTMPZLNOVERDUE
        Where
                SCHEMAST."S_ACNOTYPE"                   = LNMASTER."AC_ACNOTYPE"
        AND     SCHEMAST.ID                             = LNMASTER."AC_TYPE"
        AND     LNMASTER."AC_ACNOTYPE"                  = VWTMPZLNOVERDUE."AC_ACNOTYPE"
        AND     LNMASTER."AC_TYPE"                      = VWTMPZLNOVERDUE."AC_TYPE"
        AND     LNMASTER."BANKACNO"                     = VWTMPZLNOVERDUE."BANKACNO"
       
        AND     CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) > TO_DATE('.$date.','.$dd.')
        AND     "CLOSING_BALANCE"                       > 0
        GROUP BY
                SCHEMAST."S_APPL"                        ,
                SCHEMAST."S_NAME"                        ,
                VWTMPZLNOVERDUE."AC_TYPE" ) "DUE_SUMMARY",
(
        SELECT
                SCHEMAST."S_APPL"         ,
                SCHEMAST."S_NAME"         ,
                VWTMPZLNBALANCE."AC_TYPE" ,
                SUM(COALESCE(
                        CASE
                                VWTMPZLNBALANCE."CLOSING_BALANCE"
                        WHEN
                                0
                        THEN
                                0
                        ELSE
                                1
                        END,0)) "EXP_NO_OF_AC",
                SUM(VWTMPZLNBALANCE."CLOSING_BALANCE") "EXP_CLOSING_BALANCE"
        From
                SCHEMAST ,
                LNMASTER ,
                (
                        SELECT
                                LNMASTER."AC_ACNOTYPE",
                                LNMASTER."AC_TYPE"    ,
                                LNMASTER."BANKACNO"   ,
                                LNMASTER."AC_NO"      ,
                                LNMASTER."AC_OPDATE"  ,
                                LNMASTER."AC_CLOSEDT" ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."TRAN_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_AMOUNT" AS FLOAT),0)) "CLOSING_BALANCE" ,
                                (COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                LNMASTER."AC_RECBLEINT_OP"
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_RECBLEINT_OP" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_RECPAY_INT_AMOUNT" AS FLOAT),0) + COALESCE(
                                        CASE
                                                LNMASTER."AC_OP_CD"
                                        WHEN
                                                '.$D.'
                                        THEN
                                                CAST("AC_RECBLEODUEINT_OP" AS FLOAT)
                                        ELSE
                                                (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                        END,0)       + COALESCE(CAST(LOANTRAN."OTHER10_AMOUNT" AS FLOAT),0) + COALESCE(CAST(DAILYTRAN."DAILY_OTHER10_AMOUNT" AS FLOAT),0)) "RECPAY_INT_AMOUNT"
                        FROM
                                LNMASTER
                        LEFT OUTER JOIN
                                (
                                        SELECT
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO"    ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                        END),0) "TRAN_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        END),0) "RECPAY_INT_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        END),0) "OTHER10_AMOUNT"
                                        FROM
                                                LOANTRAN
                                        WHERE
                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$date.', '.$dd.')
                                        GROUP BY
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO" ) LOANTRAN
                        ON
                                LNMASTER."AC_TYPE"  = LOANTRAN."TRAN_ACTYPE"
                        AND     LNMASTER."BANKACNO" =  LOANTRAN."TRAN_ACNO"
                        LEFT OUTER JOIN
                                (
                                        SELECT
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO"    ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_RECPAY_INT_AMOUNT" ,
                                                COALESCE(SUM(
                                                        CASE
                                                                "TRAN_DRCR"
                                                        WHEN
                                                                '.$D.'
                                                        THEN
                                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        ELSE
                                                                (-1) * CAST("OTHER10_AMOUNT" AS FLOAT)
                                                        END),0) "DAILY_OTHER10_AMOUNT"
                                        FROM
                                                DAILYTRAN
                                        WHERE
                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$date.', '.$dd.')
                                        AND     "TRAN_STATUS"             = '.$TRAN_STATUS.'
                                        GROUP BY
                                                "TRAN_ACNOTYPE",
                                                "TRAN_ACTYPE"  ,
                                                "TRAN_ACNO" ) DAILYTRAN
                        ON
                                LNMASTER."AC_TYPE"  = DAILYTRAN."TRAN_ACTYPE"
                        AND     LNMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
                        Where
                                (
                                        (
                                                CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL)
                                OR (
                                                CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$date.','.$dd.')))
                        AND     (
                                        (
                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL)
                                OR (
                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$date.','.$dd.'))) ) VWTMPZLNBALANCE
        Where
                SCHEMAST."S_ACNOTYPE"                   = LNMASTER."AC_ACNOTYPE"
        AND     SCHEMAST.ID                             = LNMASTER."AC_TYPE"
        AND     LNMASTER."AC_ACNOTYPE"                  = VWTMPZLNBALANCE."AC_ACNOTYPE"
        AND     LNMASTER."AC_TYPE"                      = VWTMPZLNBALANCE."AC_TYPE"
        AND     LNMASTER."BANKACNO"                     = VWTMPZLNBALANCE."BANKACNO"
      
        AND     CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) <= CDATE('.$date.')
        AND     "CLOSING_BALANCE"                       > 0
        GROUP BY
                "S_APPL" ,
                "S_NAME" ,
                VWTMPZLNBALANCE."AC_TYPE" ) "EXP_SUMMARY"
WHERE
"DUE_SUMMARY"."AC_TYPE" = "TOTAL_SUMMARY"."AC_TYPE"
AND     "EXP_SUMMARY"."AC_TYPE" ="TOTAL_SUMMARY"."AC_TYPE"
AND     "TOTAL_SUMMARY"."AC_TYPE" IN ('.$AC_TYPE.')';
// echo $query;
$sql =  pg_query($conn,$query);

$GTOTAL_NO_OF_AC=0;
$GDUE_NO_OF_AC=0;
$GEXP_NO_OF_AC=0;
$GTotalClosingBal=0;
$GDueBal=0;
$GExpCloseBal=0;

while($row = pg_fetch_assoc($sql))
{
        // Sanction Amount
        $GTOTAL_NO_OF_AC +=  $row['TOTAL_NO_OF_AC'];
        $GDUE_NO_OF_AC += $row['DUE_NO_OF_AC'];
        $GEXP_NO_OF_AC += $row['EXP_NO_OF_AC'];
        $GTotalClosingBal += $row['TOTAL_CLOSING_BALANCE'];
        $GDueBal += $row['DUE_BALANCE'];
        $GExpCloseBal += $row['EXP_CLOSING_BALANCE'];
    

 $temp =
    [
        "bankName"  => $bankName,
        "BranchName"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        // "flag"=>$flag,
        "date"=>$date1,
        // "TOTAL_NO_OF_AC"=>$TOTAL_NO_OF_AC,
        "scheme"=>$row['S_NAME'],
        "TOTAL_NO_OF_AC"=>$row['TOTAL_NO_OF_AC'],
        "DUE_NO_OF_AC"=>$row['DUE_NO_OF_AC'],
        "EXP_NO_OF_AC"=>$row['EXP_NO_OF_AC'],

        "TotalClosingBal"=> sprintf("%.2f",$row['TOTAL_CLOSING_BALANCE']+ 0.0 ),
        "DueBal"=> sprintf("%.2f",$row['DUE_BALANCE']+ 0.0 ),
        "ExpCloseBal"=> sprintf("%.2f",$row['EXP_CLOSING_BALANCE']+ 0.0 ),
        "GTOTAL_NO_OF_AC" => $GTOTAL_NO_OF_AC,
        "GDUE_NO_OF_AC" => $GDUE_NO_OF_AC ,
        "GEXP_NO_OF_AC" => $GEXP_NO_OF_AC ,
        "GTotalClosingBal" =>sprintf("%.2f",($GTotalClosingBal) + 0.0 ) ,
        "GDueBal" =>sprintf("%.2f",($GDueBal) + 0.0 ) ,
        "GExpCloseBal" =>sprintf("%.2f",($GExpCloseBal) + 0.0 ) ,
    

    ];
    $data[$i]=$temp;
    $i++;
}
ob_end_clean();
 $config = ['driver'=>'array','data'=>$data];
//print_r($data);
 $report = new PHPJasperXML();
  $report->load_xml_file($filename)    
      ->setDataSource($config)
     ->export('Pdf');
?>