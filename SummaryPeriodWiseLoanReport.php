<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/summarydueperiodwiseloan.jrxml';
$filename1 = __DIR__.'/detaildueperiodwiseloan.jrxml';


$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$AC_TYPE=$_GET['schemeid']; 
$bankName  = $_GET['bankName'];
$BranchName = $_GET['branchName'];
// $schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['branch'];
$flag=$_GET['flag'];
$date=$_GET['date'];
$TRAN_STATUS="'1'";
$DUEBAL="'DUEBAL'";
$LN="'LN'";
$CC="'CC'";
$DS="'DS'";
$P="'P'";
$M="'M'";
$Q="'Q'";
$H="'H'";
$Y="'Y'";
$D="'D'";
$zero="'0'";
$dash="'-'";
$Space="' '";
$dd="'DD/MM/YYYY'";
$PERIOD="'PERIOD'";
$LN="'LN'";


$bankName_ = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);
$date1 = str_replace("'", "", $date);

$checktype;
 $flag == 1? $checktype='true': $checktype='false';
//  echo $checktype;
 if($flag == 0)
 {
$query='SELECT
"FROM_MONTHS"                                ,
"TO_MONTHS"                                  ,
SUM("LOAN_ACCOUNTS") "TOTAL_LOAN_ACCOUNTS"   ,
SUM("LEDGER_BALANCE") "TOTAL_LEDGER_BALANCE" ,
SUM("OVERDUE_BALANCE") "TOTAL_OVERDUE_BALANCE"
FROM
(
        SELECT DISTINCT
                (TMP."AC_NO") ACNO,
                TMP."AC_ACNOTYPE" ,
                TMP."AC_TYPE"     ,
                TMP."AC_NO"       ,
                TMP."AC_NAME"     ,
                "AC_INSTALLMENT"  ,
                (
                        CASE
                                LEFT((COALESCE(TMP."OVERDUE_BALANCE",0))::text,1)
                        WHEN
                                '.$dash.'
                        THEN
                                0
                        WHEN
                                '.$zero.'
                        THEN
                                '.$zero.'
                        ELSE
                                TMP."OVERDUE_BALANCE"
                        END) "OVERDUE_BALANCE" ,
                TMP."OVERDUE_MONTHS"           ,
                TMP."LEDGER_BALANCE"           ,
                SIZEWISEBALANCE."FROM_MONTHS"  ,
                SIZEWISEBALANCE."TO_MONTHS"    ,
                CASE
                        "LEDGER_BALANCE"
                WHEN
                        0
                THEN
                        0
                ELSE
                        1
                END "LOAN_ACCOUNTS"
        FROM
                SIZEWISEBALANCE ,
                (
                        SELECT
                                LNMASTER."AC_ACNOTYPE"                                       ,
                                LNMASTER."AC_TYPE"                                           ,
                                LNMASTER."AC_NO"                                             ,
                                LNMASTER."BANKACNO"                                          ,
                                LNMASTER."AC_NAME"                                           ,
                                LNMASTER."AC_INSTALLMENT"                                    ,
                                COALESCE(VWTMPZODMONTHLOAN."DUEBALANCE",0) "OVERDUE_BALANCE" ,
                                CASE
                                        LNMASTER."AC_ACNOTYPE"
                                WHEN
                                        '.$CC.'
                                THEN
                                        extract(year from age(TO_DATE('.$date.','.$dd.'),
                                        CASE
                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                        WHEN
                                                Null
                                        THEN
                                                TO_DATE('.$date.','.$dd.')
                                        ELSE
                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                        END)) * 12 + extract(month from age(TO_DATE('.$date.','.$dd.'),
                                        CASE
                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                        WHEN
                                                Null
                                        THEN
                                                TO_DATE('.$date.','.$dd.')
                                        ELSE
                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                        END))
                                ELSE
                                        CASE
                                                CAST(SCHEMAST."IS_GOLD_LOAN" AS INTEGER)
                                        WHEN
                                                1
                                        THEN
                                                extract(year from age(TO_DATE('.$date.','.$dd.'),
                                                CASE
                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                WHEN
                                                        Null
                                                THEN
                                                        TO_DATE('.$date.','.$dd.')
                                                ELSE
                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                END)) * 12 + extract(month from age(TO_DATE('.$date.','.$dd.'),
                                                CASE
                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                WHEN
                                                        Null
                                                THEN
                                                        TO_DATE('.$date.','.$dd.')
                                                ELSE
                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                END))
                                        ELSE
                                                FLOOR(
                                                        CASE
                                                                COALESCE(LNMASTER."AC_INSTALLMENT",0)
                                                        WHEN
                                                                0
                                                        THEN
                                                                0
                                                        ELSE
                                                                (COALESCE(VWTMPZODMONTHLOAN."DUEBALANCE",0)/ COALESCE(LNMASTER."AC_INSTALLMENT",0)) * COALESCE(
                                                                        CASE
                                                                                LNMASTER."AC_REPAYMODE"
                                                                        WHEN
                                                                                '.$M.'
                                                                        THEN
                                                                                1
                                                                        WHEN
                                                                                '.$Q.'
                                                                        THEN
                                                                                3
                                                                        WHEN
                                                                                '.$H.'
                                                                        THEN
                                                                                6
                                                                        WHEN
                                                                                '.$Y.'
                                                                        THEN
                                                                                12
                                                                        END,0)
                                                        END)
                                        END
                                END "OVERDUE_MONTHS" ,
                                COALESCE(VWTMPZODMONTHLOAN."CLOSING_BALANCE",0) "LEDGER_BALANCE"
                        From
                                LNMASTER ,
                                SCHEMAST ,
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
                                               (case when LNMASTER."AC_INSTALLMENT" <> 0 then CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                        /CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT))   else 0 end )"TOTALINSTALLMENTS"                                                                                                       ,
                                               (case when LNMASTER."AC_INSTALLMENT" <> 0 then ceil(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0)/ CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end ) "DUEINSTALLMENT"                                                                                                         ,
                                               (case when LNMASTER."AC_INSTALLMENT" <> 0  then (case ceil((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)                                                       - DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0) )/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS",
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
                                                LNMASTER."BANKACNO" ) VWTMPZODMONTHLOAN
                        Where
                                LNMASTER."AC_TYPE"  = SCHEMAST.ID
                        AND     LNMASTER."AC_TYPE"  =  VWTMPZODMONTHLOAN."AC_TYPE"
                        AND     LNMASTER."BANKACNO" =  VWTMPZODMONTHLOAN."BANKACNO"
                        AND     (
                                        CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL
                                OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$date.','.$dd.'))
                        AND     (
                                        CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL
                                OR CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$date.','.$dd.')) ) TMP
        WHERE
                SIZEWISEBALANCE."ACNOTYPE"  = '.$LN.'
        AND     SIZEWISEBALANCE."SLAB_TYPE" = '.$PERIOD.'
        AND     "OVERDUE_MONTHS"            >  SIZEWISEBALANCE."FROM_MONTHS"
        AND     "OVERDUE_MONTHS"            <= SIZEWISEBALANCE."TO_MONTHS" ) TMP1
WHERE
"LEDGER_BALANCE" <> 0
GROUP BY
"FROM_MONTHS" ,
"TO_MONTHS"';
                        }
else if($flag == 1){
$query='SELECT DISTINCT
(TMP2."AC_NO") "ACNO1",
"AC_TYPE" "ACTYPE"   ,
"AC_MOBILENO",
TMP2.*
FROM
(
        SELECT
                TMP1.* ,
                "LOAN_ACCOUNTS" "TOTAL_LOAN_ACCOUNTS"
        FROM
                (
                        SELECT DISTINCT
                                (TMP."BANKACNO") "ACNO",
                                TMP."AC_ACNOTYPE"      ,
                                TMP."AC_TYPE"          ,
                                TMP."AC_NO"            ,
                                TMP."AC_NAME"          ,
                                "AC_INSTALLMENT"       ,
                                (
                                        CASE
                                                LEFT((COALESCE(TMP."OVERDUE_BALANCE",0))::text,1)
                                        WHEN
                                                '.$dash.'
                                        THEN
                                                '.$zero.'
                                        WHEN
                                                '.$zero.'
                                        THEN
                                                '.$zero.'
                                        ELSE
                                                TMP."OVERDUE_BALANCE"
                                        END) "OVERDUE_BALANCE" ,
                                TMP."OVERDUE_MONTHS"           ,
                                TMP."LEDGER_BALANCE"           ,
                                SIZEWISEBALANCE."FROM_MONTHS"  ,
                                SIZEWISEBALANCE."TO_MONTHS"    ,
                                TMP."AC_MOBILENO",
                                CASE
                                        "LEDGER_BALANCE"
                                WHEN
                                        0
                                THEN
                                        0
                                ELSE
                                        1
                                END "LOAN_ACCOUNTS"
                        FROM
                                SIZEWISEBALANCE ,
                                (
                                        SELECT
                                                LNMASTER."AC_ACNOTYPE"                                       ,
                                                LNMASTER."AC_TYPE"                                           ,
                                                LNMASTER."AC_NO"                                             ,
                                                LNMASTER."BANKACNO"                                          ,
                                                LNMASTER."AC_NAME"                                           ,
                                                LNMASTER."AC_INSTALLMENT"                                    ,
                                                COALESCE(VWTMPZODMONTHLOAN."DUEBALANCE",0) "OVERDUE_BALANCE" ,
                                                VWTMPZODMONTHLOAN."AC_MOBILENO",
                                                CASE
                                                        LNMASTER."AC_ACNOTYPE"
                                                WHEN
                                                        '.$CC.'
                                                THEN
                                                        extract(year from age(TO_DATE('.$date.','.$dd.'),
                                                        CASE
                                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                        WHEN
                                                                Null
                                                        THEN
                                                                TO_DATE('.$date.','.$dd.')
                                                        ELSE
                                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                        END)) * 12 + extract(month from age(TO_DATE('.$date.','.$dd.'),
                                                        CASE
                                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                        WHEN
                                                                Null
                                                        THEN
                                                                TO_DATE('.$date.','.$dd.')
                                                        ELSE
                                                                CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                        END))
                                                ELSE
                                                        CASE
                                                                SCHEMAST."IS_GOLD_LOAN"
                                                        WHEN
                                                                '.$TRAN_STATUS.'
                                                        THEN
                                                                extract(year from age(TO_DATE('.$date.','.$dd.'),
                                                                CASE
                                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                                WHEN
                                                                        Null
                                                                THEN
                                                                        TO_DATE('.$date.','.$dd.')
                                                                ELSE
                                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                                END)) * 12 + extract(month from age(TO_DATE('.$date.','.$dd.'),
                                                                CASE
                                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                                WHEN
                                                                        Null
                                                                THEN
                                                                        TO_DATE('.$date.','.$dd.')
                                                                ELSE
                                                                        CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE)
                                                                END))
                                                        ELSE
                                                                FLOOR(
                                                                        CASE
                                                                                COALESCE(LNMASTER."AC_INSTALLMENT",0)
                                                                        WHEN
                                                                                0
                                                                        THEN
                                                                                0
                                                                        ELSE
                                                                                (COALESCE(VWTMPZODMONTHLOAN."DUEBALANCE",0)/ COALESCE(LNMASTER."AC_INSTALLMENT",0)) * COALESCE(
                                                                                        CASE
                                                                                                LNMASTER."AC_REPAYMODE"
                                                                                        WHEN
                                                                                                '.$M.'
                                                                                        THEN
                                                                                                1
                                                                                        WHEN
                                                                                                '.$Q.'
                                                                                        THEN
                                                                                                3
                                                                                        WHEN
                                                                                                '.$H.'
                                                                                        THEN
                                                                                                6
                                                                                        WHEN
                                                                                                '.$Y.'
                                                                                        THEN
                                                                                                12
                                                                                        END,0)
                                                                        END)
                                                        END
                                                END "OVERDUE_MONTHS" ,
                                                COALESCE(VWTMPZODMONTHLOAN."CLOSING_BALANCE",0) "LEDGER_BALANCE"
                                        From
                                                LNMASTER ,
                                                SCHEMAST ,
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
(case when LNMASTER."AC_INSTALLMENT" <> 0 then   
CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS"                                                                                                       ,
(case when LNMASTER."AC_INSTALLMENT" <> 0  then 
ceil(DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0)/ CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"DUEINSTALLMENT"                                                                                                         ,
(case when LNMASTER."AC_INSTALLMENT" <> 0 then  
 ceil((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT)- DueBalance(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$date.','.$DUEBAL.',0) )/CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"PAIDINSTALLMENTS",
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
                                                        AND     LNMASTER."BRANCH_CODE" IN ('.$BRANCH_CODE.')
                                                        AND     LNMASTER."status"=1
                                                        AND     LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
                                                        ORDER BY
                                                                SCHEMAST."S_APPL",
                                                                LNMASTER."BANKACNO" ) VWTMPZODMONTHLOAN
                                        Where
                                                LNMASTER."AC_TYPE" = SCHEMAST.ID
                                        AND     LNMASTER."AC_TYPE" IN('.$AC_TYPE.')
                                        AND     LNMASTER."AC_TYPE"     =  VWTMPZODMONTHLOAN."AC_TYPE"
                                        AND     LNMASTER."BANKACNO"    =  VWTMPZODMONTHLOAN."BANKACNO"
                                        AND     LNMASTER."BRANCH_CODE" = '.$BRANCH_CODE.'
                                        AND     (
                                                        CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE('.$date.','.$dd.'))
                                        AND     (
                                                        CAST(LNMASTER."AC_OPDATE" AS DATE) IS NULL
                                                OR CAST(LNMASTER."AC_OPDATE" AS DATE) <= TO_DATE('.$date.','.$dd.')) ) TMP
                        WHERE
                                SIZEWISEBALANCE."ACNOTYPE"  = '.$LN.'
                        AND     SIZEWISEBALANCE."SLAB_TYPE" = '.$PERIOD.'
                        AND     "OVERDUE_MONTHS"            >  SIZEWISEBALANCE."FROM_MONTHS"
                        AND     "OVERDUE_MONTHS"            <= SIZEWISEBALANCE."TO_MONTHS" ) TMP1
        WHERE
                COALESCE("LEDGER_BALANCE",0) <> 0 )TMP2';
                        }
echo $query;
$sql =  pg_query($conn,$query);


$GRAND_TOTAL = 0;
$GRAND_TOTAL1 =0;
$GRAND_TOTAL2=0;
$TAC_INSTALLMENT=0;
$TLEDGER_BALANCE=0;
$TOVERDUE_BALANCE=0;
while($row = pg_fetch_assoc($sql))
{
        // Sanction Amount
       
 // Data transformation
        $GRAND_TOTAL += $row['TOTAL_LOAN_ACCOUNTS'];
        $GRAND_TOTAL1 += $row['TOTAL_LEDGER_BALANCE'];
        $GRAND_TOTAL2 += $row['TOTAL_OVERDUE_BALANCE'];
        
        $TAC_INSTALLMENT += $row['AC_INSTALLMENT'];
        $TLEDGER_BALANCE +=$row['LEDGER_BALANCE'];
        $TOVERDUE_BALANCE +=$row['OVERDUE_BALANCE'];

       
        $temp =
        [
            
            "bankName"  => $bankName_,
            "BranchName"=>$BranchName,
            "FROM_MONTHS"=>$row['FROM_MONTHS'],
            "TO_MONTHS"=>$row['TO_MONTHS'],
            "AC_NO"=>$row['AC_NO'],
            "AC_NAME"=>$row['AC_NAME'],
            "AC_INSTALLMENT"=>$row['AC_INSTALLMENT'],
            "LEDGER_BALANCE"=>sprintf("%.2f",$row['LEDGER_BALANCE'] + 0.0 ),
            "OVERDUE_BALANCE"=>sprintf("%.2f",$row['OVERDUE_BALANCE'] + 0.0 ),
            "AC_MOBILENO"=>$row['AC_MOBILENO'],
            "AC_ACNOTYPE"=>$row['AC_ACNOTYPE'],

            "TOTAL_LOAN_ACCOUNTS"=>$row['TOTAL_LOAN_ACCOUNTS'],
            "TOTAL_LEDGER_BALANCE"=>sprintf("%.2f",$row['TOTAL_LEDGER_BALANCE'] + 0.0 ), 
            "TOTAL_OVERDUE_BALANCE"=>sprintf("%.2f",$row['TOTAL_OVERDUE_BALANCE'] + 0.0 ), 
            "date"=>$date1,
            "scheme"=>$row['SCHEME'],
            "GRAND_TOTAL"=>$GRAND_TOTAL,
            "GRAND_TOTAL1"=>sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ),
            "GRAND_TOTAL2"=>sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ),
            "TAC_INSTALLMENT"=>sprintf("%.2f",($TAC_INSTALLMENT) + 0.0 ),
            "TLEDGER_BALANCE"=>sprintf("%.2f",($TLEDGER_BALANCE) + 0.0 ),
            "TOVERDUE_BALANCE"=>sprintf("%.2f",($TOVERDUE_BALANCE) + 0.0 ),
    
        ];
        $data[$i]=$temp;
        $i++;
}
// ob_end_clean();
// if($flag == 1)
// {
//     $config = ['driver'=>'array','data'=>$data];
//     // print_r($data);
//     $report = new PHPJasperXML();
//     $report->load_xml_file($filename1)    
//          ->setDataSource($config)
//          ->export('Pdf');
// }
// else if($flag == 0)
// {
//     $config = ['driver'=>'array','data'=>$data];
// // print_r($data);
// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//      ->setDataSource($config)
//      ->export('Pdf');
// }
?>