<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/schemeLoanOverIncreDecre.jrxml';

$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$AC_TYPE=$_GET['schemeid']; 
$bankName  = $_GET['bankName'];
$BranchName = $_GET['branchName'];
// $schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['branch'];
$sdate=$_GET['stadate'];
$edate=$_GET['edate'];

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
$sdate1 = str_replace("'", "", $sdate);
$edate1 = str_replace("'", "", $edate);
$D="'D'";




$query='SELECT
"AC_ACNOTYPE" ,                                              
"AC_TYPE" ,
SCHEMAST."S_NAME",                                                  
SUM(COALESCE("FIRST_DUE_BALANCE",0))"FIRST_DUE_BALANCE"     ,
SUM(COALESCE("FIRST_DUE_ACCOUNTS" , 0))"FIRST_DUE_ACCOUNTS" ,
SUM(COALESCE("LAST_DUE_BALANCE",0))"LAST_DUE_BALANCE"       ,
SUM(COALESCE("LAST_DUE_ACCOUNTS" , 0))"LAST_DUE_ACCOUNTS"
FROM SCHEMAST,
(
        SELECT
                VWTMPFINALVIEW1."AC_ACNOTYPE"                       ,
                VWTMPFINALVIEW1."AC_TYPE"                           ,
                VWTMPFINALVIEW1."DUE_BALANCE" "FIRST_DUE_BALANCE"   ,
                VWTMPFINALVIEW1."TOT_ACCOUNTS" "FIRST_DUE_ACCOUNTS" ,
                0 "LAST_DUE_BALANCE"                                ,
                0 "LAST_DUE_ACCOUNTS"
        FROM
                (
                        SELECT
                                "AC_ACNOTYPE"                               ,
                                "AC_TYPE"                                   ,
                                SUM(COALESCE("DUE_BALANCE",0))"DUE_BALANCE" ,
                                SUM(COALESCE("TOT_ACCOUNTS",0))"TOT_ACCOUNTS"
                        From
                                (
                                        SELECT
                                                LNMASTER."AC_ACNOTYPE"                                               ,
                                                LNMASTER."AC_TYPE"                                                   ,
                                                LNMASTER."BANKACNO"                                                  ,
                                                LNMASTER."AC_NO"                                                     ,
                                                LNMASTER."AC_NAME"                                                   ,
                                                LNMASTER."AC_OPDATE"                                                 ,
                                                LNMASTER."AC_SANCTION_AMOUNT"                                        ,
                                                LNMASTER."AC_EXPIRE_DATE"                                            ,
                                                1 "TOT_ACCOUNTS"                                                     ,
                                                COALESCE(VWTMPZBALODVIEW1."CLOSING_BALANCE",0) "LEDGER_BALANCE"      ,
                                                COALESCE(VWTMPZBALODVIEW1."RECPAY_INT_AMOUNT",0) "RECPAY_INT_AMOUNT" ,
                                                COALESCE(VWTMPODVIEW1."DUEBALANCE",0) "DUE_BALANCE"                  ,
                                                VWTMPODVIEW1."OVERDUEDATE" AS "OVERDUE_DATE"
                                        From
                                                LNMASTER,
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
                                                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$sdate.', '.$dd.')
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
                                                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$sdate.', '.$dd.')
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
                                                                                CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$sdate.','.$dd.')))
                                                        AND     (
                                                                        (
                                                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL)
                                                                OR (
                                                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$sdate.','.$dd.'))) ) VWTMPZBALODVIEW1,
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
OIRINTBALANCE(SCHEMAST."S_APPL",LNMASTER."BANKACNO",'.$sdate.',0) "OVERDUEINTEREST"                                                                                                                                 ,
(case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)
/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS"                                                                                                       ,
(case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then   ceil(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),
LNMASTER."BANKACNO",'.$sdate.','.$DUEBAL.',0)/ CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT))else 0 end ) "DUEINSTALLMENT"                                                                                                         ,
(case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  ceil((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                       - DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$sdate.','.$DUEBAL.',0) )/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS",
                                                                DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$sdate.','.$DUEBAL.',0) "DUEBALANCE"                                                                                                                                                             ,
                                                                "AC_EXPIRE_DATE"                                                                                                                                                                                                                                                              ,
                                                                overduedate(CAST(SCHEMAST."S_APPL" AS INTEGER),LNMASTER."BANKACNO", CAST('.$sdate.' AS CHARACTER VARYING) , CAST(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$sdate.','.$DUEBAL.',0) AS CHARACTER VARYING), CAST(LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING)) "OVERDUEDATE"
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
                                                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$sdate.', '.$dd.')
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
                                                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$sdate.', '.$dd.')
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
                                                        AND     LNMASTER."status"      =1
                                                        AND     LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
                                                        ORDER BY
                                                                SCHEMAST."S_APPL",
                                                                LNMASTER."BANKACNO" ) VWTMPODVIEW1
                                        WHERE
                                                LNMASTER."AC_TYPE" IN ('.$AC_TYPE.')
                                        
                                        AND     LNMASTER."AC_TYPE"    = VWTMPZBALODVIEW1."AC_TYPE"
                                        AND     LNMASTER."BANKACNO"   = VWTMPZBALODVIEW1."BANKACNO"
                                        AND     LNMASTER."AC_TYPE"    = VWTMPODVIEW1."AC_TYPE"
                                        AND     LNMASTER."BANKACNO"   = VWTMPODVIEW1."BANKACNO"
                                        AND     (
                                                        CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$sdate.','.$dd.'))
                                        AND     (
                                                        CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE( '.$sdate.', '.$dd.')) ) TMP
                        WHERE
                                "DUE_BALANCE" <> 0
                        GROUP BY
                                "AC_ACNOTYPE" ,
                                "AC_TYPE" ) VWTMPFINALVIEW1
        
        UNION ALL
        
        SELECT
                VWTMPFINALVIEW2."AC_ACNOTYPE"                    ,
                VWTMPFINALVIEW2."AC_TYPE"                        ,
                0 "FIRST_DUE_BALANCE"                            ,
                0 "FIRST_DUE_ACCOUNTS"                           ,
                VWTMPFINALVIEW2."DUE_BALANCE" "LAST_DUE_BALANCE" ,
                VWTMPFINALVIEW2."TOT_ACCOUNTS" "LAST_DUE_ACCOUNTS"
        FROM
                (
                        SELECT
                                "AC_ACNOTYPE"                               ,
                                "AC_TYPE"                                   ,
                                SUM(COALESCE("DUE_BALANCE",0))"DUE_BALANCE" ,
                                SUM(COALESCE("TOT_ACCOUNTS",0))"TOT_ACCOUNTS"
                        From
                                (
                                        SELECT
                                                LNMASTER."AC_ACNOTYPE"                                               ,
                                                LNMASTER."AC_TYPE"                                                   ,
                                                LNMASTER."BANKACNO"                                                  ,
                                                LNMASTER."AC_NO"                                                     ,
                                                LNMASTER."AC_NAME"                                                   ,
                                                LNMASTER."AC_OPDATE"                                                 ,
                                                LNMASTER."AC_SANCTION_AMOUNT"                                        ,
                                                LNMASTER."AC_EXPIRE_DATE"                                            ,
                                                1 "TOT_ACCOUNTS"                                                     ,
                                                COALESCE(VWTMPZBALODVIEW2."CLOSING_BALANCE",0) "LEDGER_BALANCE"      ,
                                                COALESCE(VWTMPZBALODVIEW2."RECPAY_INT_AMOUNT",0) "RECPAY_INT_AMOUNT" ,
                                                COALESCE(VWTMPODVIEW2."DUEBALANCE",0) "DUE_BALANCE"                  ,
                                                VWTMPODVIEW2."OVERDUEDATE" AS "OVERDUE_DATE"
                                        From
                                                LNMASTER,
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
                                                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.', '.$dd.')
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
                                                                                CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.', '.$dd.')
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
                                                                                CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$edate.','.$dd.')))
                                                        AND     (
                                                                        (
                                                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL)
                                                                OR (
                                                                                CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$edate.','.$dd.'))) ) VWTMPZBALODVIEW2,
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
                                                                OIRINTBALANCE(SCHEMAST."S_APPL",LNMASTER."BANKACNO",'.$edate.',0) "OVERDUEINTEREST"                                                                                                                                 ,
                                                                (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then   CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                        /CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS"                                                                                                       ,
                                                                (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  ceil(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$edate.','.$DUEBAL.',0)/ CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT))else 0 end ) "DUEINSTALLMENT"                                                                                                         ,
                                                                (case  when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  ceil((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                       - DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$edate.','.$DUEBAL.',0) )/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS",
                                                                DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$edate.','.$DUEBAL.',0) "DUEBALANCE"                                                                                                                                                             ,
                                                                "AC_EXPIRE_DATE"                                                                                                                                                                                                                                                              ,
                                                                overduedate(CAST(SCHEMAST."S_APPL" AS INTEGER),LNMASTER."BANKACNO", CAST('.$edate.' AS CHARACTER VARYING) , CAST(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$edate.','.$DUEBAL.',0) AS CHARACTER VARYING), CAST(LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING)) "OVERDUEDATE"
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
                                                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$edate.', '.$dd.')
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
                                                                                                                cast("TRAN_DATE" as date) <= TO_DATE('.$edate.', '.$dd.')
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
                                                        AND     LNMASTER."status"      =1
                                                        AND     LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
                                                        ORDER BY
                                                                SCHEMAST."S_APPL",
                                                                LNMASTER."BANKACNO" ) VWTMPODVIEW2
                                        WHERE
                                                LNMASTER."AC_TYPE" IN ('.$AC_TYPE.')
                                      
                                        AND     LNMASTER."AC_TYPE"    = VWTMPZBALODVIEW2."AC_TYPE"
                                        AND     LNMASTER."BANKACNO"   = VWTMPZBALODVIEW2."BANKACNO"
                                        AND     LNMASTER."AC_TYPE"    = VWTMPODVIEW2."AC_TYPE"
                                        AND     LNMASTER."BANKACNO"   = VWTMPODVIEW2."BANKACNO"
                                        AND     (
                                                        CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$edate.','.$dd.'))
                                        AND     (
                                                        CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE( '.$edate.', '.$dd.')) ) TMP
                        WHERE
                                "DUE_BALANCE" <> 0
                        GROUP BY
                                "AC_ACNOTYPE" ,
                                "AC_TYPE" ) VWTMPFINALVIEW2 ) s
                                WHERE "AC_TYPE"=SCHEMAST.ID
GROUP BY
"AC_ACNOTYPE" ,
"AC_TYPE","S_NAME"';
// echo $query;
$sql =  pg_query($conn,$query);

$GTotal=0;
while($row = pg_fetch_assoc($sql))
{
    $GTotal = $row['FIRST_DUE_BALANCE'] - $row['LAST_DUE_BALANCE'];

    if ($GTotal  < 0) {
        $netType = 'Increase';
    } else {
        $netType = 'Decrease';
    }
 $temp =
    [
        "bankName"  => $bankName,
        "BranchName"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        "FIRST_DUE_BALANCE"=>$row['FIRST_DUE_BALANCE'],
        "FIRST_DUE_ACCOUNTS"=>$row['FIRST_DUE_ACCOUNTS'],
        "LAST_DUE_BALANCE"=>$row['LAST_DUE_BALANCE'],
        "LAST_DUE_ACCOUNTS"=>$row['LAST_DUE_ACCOUNTS'],
        "sdate"=>$sdate1,
        "edate"=>$edate1,

        "scheme"=>$row['S_NAME'],
        "AC_ACNOTYPE"=>$row['AC_ACNOTYPE'],


    //     "DueBal"=> sprintf("%.2f",$row['DUE_BALANCE']+ 0.0 ),
    //     "ExpCloseBal"=> sprintf("%.2f",$row['EXP_CLOSING_BALANCE']+ 0.0 ),
    //     "FIRST_DUE_ACCOUNTS" => $FIRST_DUE_ACCOUNTS,
    //     "LAST_DUE_ACCOUNTS" => $LAST_DUE_ACCOUNTS ,
    //     "GFIRST_DUE_BALANCE" => $FIRST_DUE_BALANCE ,
       "GTotal"=>abs($GTotal),
       "netType"=>$netType,
    
    

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