<?php
ob_start();
include "main.php";
require_once('dbconnect.php');


use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/RecommandedByDirectowiseNPARegisterReport.jrxml';

// $filename = __DIR__.'/recommandedbydirector.jrxml';
$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$AC_TYPE=$_GET['AC_TYPE'];
$bankName  = $_GET['bankName'];
$BranchName=$_GET['BranchName'];
$bankName = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);


$date=$_GET['NPA_DATE'];
$BRANCH_CODE=$_GET['BRANCH_CODE'];
$startdir=$_GET['startdir'];
$enddir=$_GET['enddir'];
$dd="'DD/MM/YYYY'";
$TOBE_RECOVER_AMT= 0;
$date1 = str_replace("'", "", $date);
// $date="'31/03/2024'";
// $SDate="'31/03/2021'";
// $EDate="'31/03/2022'";
// $STDate="'31/03/2023'";
// $EDDate="'31/03/2024'";
$ST="'ST'";
$Space="' '";
$S="'S'";
$C="'C'";
$LN="'LN'";
$CC="'CC'";
$DS="'DS'";

if($BRANCH_CODE=='0'){
$query='SELECT
NPADATA."AC_ACNOTYPE"                                 ,
NPADATA."AC_TYPE"                                     ,
NPADATA."AC_NO"                                       ,
NPADATA."AC_NPA_DATE"                                 ,
NPADATA."AC_OPDATE"                                   ,
NPADATA."AC_EXPIRE_DATE"                              ,
NPADATA."OVERDUE_DATE"                                ,
NPADATA."AC_SANCTION_AMOUNT"                          ,
NPADATA."AC_SECURITY_AMT"                             ,
NPADATA."LEDGER_BALANCE"                              ,
NPADATA."OVERDUE_AMOUNT"                              ,
NPADATA."NPA_PROVISION_AMT"                           ,
LNMASTER."AC_CLOSEDT"                                 ,
NPADATA."RECEIVABLE_INTEREST"                         ,
NPADATA."NPA_CLASS"                                   ,
NPADATA."SUB_CLASS_NO"                                ,
NPADATA."NPA_PERCENTAGE"                              ,
NPADATA."TOBE_RECOVER_AMT"                            ,
LNMASTER."AC_NAME"                                    ,
SCHEMAST."S_APPL"                                     ,
SCHEMAST."S_NAME"                                     ,
SCHEMAST."S_APPL" || '.$Space.' || SCHEMAST."S_NAME" "SCHEME",
"CREDIT_AMOUNT"                                       ,
LNMASTER."AC_RECOMMEND_BY" COMMON_CODE                ,
DIRECTORMASTER."NAME" COMMON_NAME
FROM
NPADATA       ,
LNMASTER      ,
SCHEMAST      ,
DIRECTORMASTER,
(
        SELECT
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO"    ,
                COALESCE(SUM(CREDIT_AMOUNT + DAILY_CREDIT_AMOUNT), 0) "CREDIT_AMOUNT"
        FROM
                (
                        SELECT
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"    ,
                                (SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER1_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER2_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER3_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER4_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER5_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER6_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER7_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER8_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER9_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END)) CREDIT_AMOUNT,
                                0 DAILY_CREDIT_AMOUNT
                        FROM
                                LOANTRAN
                        WHERE
                                CAST("TRAN_DATE" AS DATE) BETWEEN TO_DATE('.$date.', '.$dd.') AND TO_DATE('.$date.', '.$dd.')
                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                   '.$CC.' ,
                                                   '.$DS.')
                        GROUP BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"
                        
                        UNION
                        
                        SELECT
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"    ,
                                0 CREDIT_AMOUNT,
                                (COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER1_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER2_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER3_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER4_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER5_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER6_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER7_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER8_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER9_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0)) DAILY_CREDIT_AMOUNT
                        FROM
                                DAILYTRAN
                        WHERE
                                CAST("TRAN_DATE" AS DATE) BETWEEN TO_DATE('.$date.', '.$dd.') AND TO_DATE('.$date.', '.$dd.')
                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                   '.$CC.' ,
                                                   '.$DS.')
                        GROUP BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"
                        ORDER BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO")RS
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") AMOUNT
WHERE
NPADATA."AC_ACNOTYPE"              = LNMASTER."AC_ACNOTYPE"
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"
AND     NPADATA."AC_NO"                    = LNMASTER."BANKACNO"
AND     NPADATA."AC_ACNOTYPE"              = AMOUNT."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"                  = AMOUNT."TRAN_ACTYPE"
AND     NPADATA."AC_NO"                    = AMOUNT."TRAN_ACNO"
AND     NPADATA."AC_ACNOTYPE"              = SCHEMAST."S_ACNOTYPE"
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) ='.$AC_TYPE.'

AND     CAST(NPADATA."REPORT_DATE" AS DATE)         = TO_DATE('.$date.','.$dd.')
AND     CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER) = DIRECTORMASTER.ID
AND     CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER) <> 0

AND LNMASTER."AC_RECOMMEND_BY"    BETWEEN '.$startdir.' AND '.$enddir.'
ORDER BY COMMON_CODE,
NPADATA."AC_ACNOTYPE",
NPADATA."AC_TYPE"    ,
NPADATA."AC_NO"';
}
else{
        $query='SELECT
NPADATA."AC_ACNOTYPE"                                 ,
NPADATA."AC_TYPE"                                     ,
NPADATA."AC_NO"                                       ,
NPADATA."AC_NPA_DATE"                                 ,
NPADATA."AC_OPDATE"                                   ,
NPADATA."AC_EXPIRE_DATE"                              ,
NPADATA."OVERDUE_DATE"                                ,
NPADATA."AC_SANCTION_AMOUNT"                          ,
NPADATA."AC_SECURITY_AMT"                             ,
NPADATA."LEDGER_BALANCE"                              ,
NPADATA."OVERDUE_AMOUNT"                              ,
NPADATA."NPA_PROVISION_AMT"                           ,
LNMASTER."AC_CLOSEDT"                                 ,
NPADATA."RECEIVABLE_INTEREST"                         ,
NPADATA."NPA_CLASS"                                   ,
NPADATA."SUB_CLASS_NO"                                ,
NPADATA."NPA_PERCENTAGE"                              ,
NPADATA."TOBE_RECOVER_AMT"                            ,
LNMASTER."AC_NAME"                                    ,
SCHEMAST."S_APPL"                                     ,
SCHEMAST."S_NAME"                                     ,
SCHEMAST."S_APPL" || '.$Space.' || SCHEMAST."S_NAME" "SCHEME",
"CREDIT_AMOUNT"                                       ,
LNMASTER."AC_RECOMMEND_BY" COMMON_CODE                ,
DIRECTORMASTER."NAME" COMMON_NAME
FROM
NPADATA       ,
LNMASTER      ,
SCHEMAST      ,
DIRECTORMASTER,
(
        SELECT
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO"    ,
                COALESCE(SUM(CREDIT_AMOUNT + DAILY_CREDIT_AMOUNT), 0) "CREDIT_AMOUNT"
        FROM
                (
                        SELECT
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"    ,
                                (SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER1_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER2_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER3_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER4_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER5_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER6_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER7_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER8_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END) + SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER9_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END)) CREDIT_AMOUNT,
                                0 DAILY_CREDIT_AMOUNT
                        FROM
                                LOANTRAN
                        WHERE
                                CAST("TRAN_DATE" AS DATE) BETWEEN TO_DATE('.$date.', '.$dd.') AND TO_DATE('.$date.', '.$dd.')
                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                   '.$CC.' ,
                                                   '.$DS.')
                        GROUP BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"
                        
                        UNION
                        
                        SELECT
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"    ,
                                0 CREDIT_AMOUNT,
                                (COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("TRAN_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER10_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER1_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER2_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER3_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER4_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER5_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER6_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER7_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER8_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0) + COALESCE(SUM(
                                        CASE
                                                "TRAN_DRCR"
                                        WHEN
                                                '.$C.'
                                        THEN
                                                CAST("OTHER9_AMOUNT" AS FLOAT)
                                        ELSE
                                                0
                                        END), 0)) DAILY_CREDIT_AMOUNT
                        FROM
                                DAILYTRAN
                        WHERE
                                CAST("TRAN_DATE" AS DATE) BETWEEN TO_DATE('.$date.', '.$dd.') AND TO_DATE('.$date.', '.$dd.')
                        AND     "TRAN_ACNOTYPE" IN ('.$LN.',
                                                   '.$CC.' ,
                                                   '.$DS.')
                        GROUP BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO"
                        ORDER BY
                                "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE"  ,
                                "TRAN_ACNO")RS
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") AMOUNT
WHERE
NPADATA."AC_ACNOTYPE"              = LNMASTER."AC_ACNOTYPE"
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"
AND     NPADATA."AC_NO"                    = LNMASTER."BANKACNO"
AND     NPADATA."AC_ACNOTYPE"              = AMOUNT."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"                  = AMOUNT."TRAN_ACTYPE"
AND     NPADATA."AC_NO"                    = AMOUNT."TRAN_ACNO"
AND     NPADATA."AC_ACNOTYPE"              = SCHEMAST."S_ACNOTYPE"
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID
AND     CAST(NPADATA."AC_TYPE" AS INTEGER) ='.$AC_TYPE.'
AND     LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'
AND     CAST(NPADATA."REPORT_DATE" AS DATE)         = TO_DATE('.$date.','.$dd.')
AND     CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER) = DIRECTORMASTER.ID
AND     CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER) <> 0

AND LNMASTER."AC_RECOMMEND_BY"    BETWEEN '.$startdir.' AND '.$enddir.'
ORDER BY COMMON_CODE,
NPADATA."AC_ACNOTYPE",
NPADATA."AC_TYPE"    ,
NPADATA."AC_NO"';
}
//     echo $query;
    $sql =  pg_query($conn,$query);
    while($row = pg_fetch_assoc($sql))
    {
        // Sanction Amount
        // $GRAND_TOTAL0= $GRAND_TOTAL0 + $row["AC_SANCTION_AMOUNT"];
        // if(isset($varsd)){
        //     if($varsd == $row['S_APPL']){
        //         $sc[] = $row['AC_SANCTION_AMOUNT']; 
        //         $sumVar += $row['AC_SANCTION_AMOUNT'];
        //        // echo "if part";
        //     }
        //     else{
        //         $sumVar=0;
        //         $sc = array_diff($sc, $sc);
        //         $varsd = $row['S_APPL'];
        //         $sc[] = $row['AC_SANCTION_AMOUNT'];
        //         $sumVar = $row['AC_SANCTION_AMOUNT'];
        //      //   echo "else1 part";
        //     }
        // }else{
        //     $sumVar=0;
        //     $varsd = $row['S_APPL'];
        //     $sc[] = $row['AC_SANCTION_AMOUNT'];
        //     $sumVar = $row['AC_SANCTION_AMOUNT'];
        //    // echo "2nd else part";
        // }
        // $result[$varsd] = $sc;
        // $sumArray[$varsd] = $sumVar;


        $GRAND_TOTAL10= $GRAND_TOTAL10 + $row["AC_SANCTION_AMOUNT"];
        
        if(isset($varsd)){
            if($varsd == $row['common_name']){
                $sc[] = $row['AC_SANCTION_AMOUNT']; 
                $sumVar += $row['AC_SANCTION_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar=0;
                $sc= array_diff($sc, $sc);
                $varsd = $row['common_name'];
                $sc[] = $row['AC_SANCTION_AMOUNT'];
                $sumVar += $row['AC_SANCTION_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar=0;
            $varsd = $row['common_name'];
            $sc[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar += $row['AC_SANCTION_AMOUNT'];
           // echo "2nd else part";
        }
        $result[$varsd] = $sc;
        $sumArray[$varsd] = $sumVar;


        $GRAND_TOTAL21= $GRAND_TOTAL21 + $row["AC_SECURITY_AMT"];
        // 1  Securrity Amount
        if(isset($varsd1)){
            if($varsd1 == $row['common_name']){
                $sc1[] = $row['AC_SECURITY_AMT']; 
                $sumVar1 += $row['AC_SECURITY_AMT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['common_name'];
                $sc1[] = $row['AC_SECURITY_AMT'];
                $sumVar1 += $row['AC_SECURITY_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['common_name'];
            $sc1[] = $row['AC_SECURITY_AMT'];
            $sumVar1 += $row['AC_SECURITY_AMT'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;

        //2  Over Due amount
        $GRAND_TOTAL31= $GRAND_TOTAL31 + $row["OVERDUE_AMOUNT"];
        if(isset($varsd2)){
            if($varsd2 == $row['common_name']){
                $sc2[] = $row['OVERDUE_AMOUNT']; 
                $sumVar2 += $row['OVERDUE_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar2=0;
                $sc2 = array_diff($sc2, $sc2);
                $varsd2 = $row['common_name'];
                $sc2[] = $row['OVERDUE_AMOUNT'];
                $sumVar2 += $row['OVERDUE_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar2=0;
            $varsd2 = $row['common_name'];
            $sc2[] = $row['OVERDUE_AMOUNT'];
            $sumVar2 += $row['OVERDUE_AMOUNT'];
           // echo "2nd else part";
        }
        $result2[$varsd2] = $sc2;
        $sumArray2[$varsd1] = $sumVar2;

        // 3 Receivable Interest
        $GRAND_TOTAL41= $GRAND_TOTAL41 + $row["RECEIVABLE_INTEREST"];
        if(isset($varsd3)){
            if($varsd3 == $row['common_name']){
                $sc3[] = $row['RECEIVABLE_INTEREST']; 
                $sumVar3 += $row['RECEIVABLE_INTEREST'];
               // echo "if part";
            }
            else{
                $sumVar3=0;
                $sc3 = array_diff($sc3, $sc3);
                $varsd3 = $row['common_name'];
                $sc3[] = $row['RECEIVABLE_INTEREST'];
                $sumVar3 += $row['RECEIVABLE_INTEREST'];
             //   echo "else1 part";
            }
        }else{
            $sumVar3=0;
            $varsd3 = $row['common_name'];
            $sc3[] = $row['RECEIVABLE_INTEREST'];
            $sumVar3 += $row['RECEIVABLE_INTEREST'];
           // echo "2nd else part";
        }
        $result3[$varsd3] = $sc3;
        $sumArray3[$varsd3] = $sumVar3;

        // 4 Ledger Balance
        $GRAND_TOTAL51 = $GRAND_TOTAL51 + $row["LEDGER_BALANCE"];
        if(isset($varsd4)){
            if($varsd4 == $row['common_name']){
                $sc4[] = $row['LEDGER_BALANCE']; 
                $sumVar4 += $row['LEDGER_BALANCE'];
               // echo "if part";
            }
            else{
                $sumVar4=0;
                $sc4 = array_diff($sc4, $sc4);
                $varsd4 = $row['common_name'];
                $sc4[] = $row['LEDGER_BALANCE'];
                $sumVar4 += $row['LEDGER_BALANCE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar4=0;
            $varsd4 = $row['common_name'];
            $sc4[] = $row['LEDGER_BALANCE'];
            $sumVar4 += $row['LEDGER_BALANCE'];
           // echo "2nd else part";
        }
        $result4[$varsd4] = $sc4;
        $sumArray4[$varsd4] = $sumVar4;
        
        // 5  Provisiom Amount
        $GRAND_TOTAL61= $GRAND_TOTAL61 + $row["NPA_PROVISION_AMT"];
        if(isset($varsd5)){
            if($varsd5 == $row['common_name']){
                $sc5[] = $row['NPA_PROVISION_AMT']; 
                $sumVar5 += $row['NPA_PROVISION_AMT'];
               // echo "if part";
            }
            else{
                $sumVar5=0;
                $sc5 = array_diff($sc5, $sc5);
                $varsd5 = $row['common_name'];
                $sc5[] = $row['NPA_PROVISION_AMT'];
                $sumVar5 += $row['NPA_PROVISION_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar5=0;
            $varsd5 = $row['common_name'];
            $sc5[] = $row['NPA_PROVISION_AMT'];
            $sumVar5 += $row['NPA_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result5[$varsd5] = $sc5;
        $sumArray5[$varsd5] = $sumVar5;

        // 6 Recovery Amount
        $GRAND_TOTAL71= $GRAND_TOTAL71 + $row["TOBE_RECOVER_AMT"];
        if(isset($varsd6)){
            if($varsd6 == $row['common_name']){
                $sc6[] = $row['TOBE_RECOVER_AMT']; 
                $sumVar6 += $row['TOBE_RECOVER_AMT'];
               // echo "if part";
            }
            else{
                $sumVar6=0;
                $sc6 = array_diff($sc6, $sc6);
                $varsd6 = $row['common_name'];
                $sc6[] = $row['TOBE_RECOVER_AMT'];
                $sumVar6 += $row['TOBE_RECOVER_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar6=0;
            $varsd6 = $row['common_name'];
            $sc6[] = $row['TOBE_RECOVER_AMT'];
            $sumVar6 += $row['TOBE_RECOVER_AMT'];
           // echo "2nd else part";
        }
        $result6[$varsd6] = $sc6;
        $sumArray6[$varsd6] = $sumVar6;
        // Credit Amount
        $GRAND_TOTAL81 = $GRAND_TOTAL81 + $row["CREDIT_AMOUNT"];
        if(isset($varsd7)){
            if($varsd7 == $row['common_name']){
                $sc7[] = $row['CREDIT_AMOUNT']; 
                $sumVar7 += $row['CREDIT_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar7=0;
                $sc7 = array_diff($sc7, $sc7);
                $varsd7 = $row['common_name'];
                $sc7[] = $row['CREDIT_AMOUNT'];
                $sumVar7 += $row['CREDIT_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar7=0;
            $varsd7 = $row['common_name'];
            $sc7[] = $row['CREDIT_AMOUNT'];
            $sumVar7 += $row['CREDIT_AMOUNT'];
           // echo "2nd else part";
        }
        $result7[$varsd7] = $sc7;
        $sumArray7[$varsd7] = $sumVar7;



        // Director Wise total 
        // 1 Sanction Amount
        $GRAND_TOTAL11= $GRAND_TOTAL11 + $row["AC_SANCTION_AMOUNT"];
        
        if(isset($varsd8)){
            if($varsd8 == $row['common_name']){
                $sc8[] = $row['AC_SANCTION_AMOUNT']; 
                $sumVar8 += $row['AC_SANCTION_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar8=0;
                $sc8= array_diff($sc, $sc);
                $varsd8 = $row['common_name'];
                $sc8[] = $row['AC_SANCTION_AMOUNT'];
                $sumVar8 += $row['AC_SANCTION_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar8=0;
            $varsd8 = $row['common_name'];
            $sc8[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar8 += $row['AC_SANCTION_AMOUNT'];
           // echo "2nd else part";
        }
        $result8[$varsd8] = $sc8;
        $sumArray8[$varsd8] = $sumVar8;
        
        // 2  security

        $GRAND_TOTAL22= $GRAND_TOTAL22 + $row["AC_SECURITY_AMT"];
       
        if(isset($varsd9)){
            if($varsd9 == $row['common_name']){
                $sc9[] = $row['AC_SECURITY_AMT']; 
                $sumVar9 += $row['AC_SECURITY_AMT'];
               // echo "if part";
            }
            else{
                $sumVar9=0;
                $sc9 = array_diff($sc1, $sc1);
                $vars9 = $row['common_name'];
                $sc9[] = $row['AC_SECURITY_AMT'];
                $sumVar9 += $row['AC_SECURITY_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar9=0;
            $varsd9 = $row['common_name'];
            $sc9[] = $row['AC_SECURITY_AMT'];
            $sumVar9 += $row['AC_SECURITY_AMT'];
           // echo "2nd else part";
        }
        $result9[$varsd9] = $sc9;
        $sumArray9[$varsd9] = $sumVar9;

        // 3 Overdue

        $GRAND_TOTAL32= $GRAND_TOTAL32 + $row["OVERDUE_AMOUNT"];
        
        if(isset($varsd10)){
            if($varsd10 == $row['common_name']){
                $sc10[] = $row['OVERDUE_AMOUNT']; 
                $sumVar10 += $row['OVERDUE_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar10=0;
                $sc10 = array_diff($sc10, $sc10);
                $varsd10 = $row['common_name'];
                $sc10[] = $row['OVERDUE_AMOUNT'];
                $sumVar10 += $row['OVERDUE_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar10=0;
            $varsd10 = $row['common_name'];
            $sc10[] = $row['OVERDUE_AMOUNT'];
            $sumVar10 += $row['OVERDUE_AMOUNT'];
           // echo "2nd else part";
        }
        $result10[$varsd10] = $sc10;
        $sumArray10[$varsd10] = $sumVar10;
        
        // RECEIVABLE_INTEREST
        $GRAND_TOTAL4= $GRAND_TOTAL4 + $row["RECEIVABLE_INTEREST"];
      
        if(isset($varsd11)){
            if($varsd11 == $row['common_name']){
                $sc11[] = $row['RECEIVABLE_INTEREST']; 
                $sumVar11 += $row['RECEIVABLE_INTEREST'];
               // echo "if part";
            }
            else{
                $sumVar11=0;
                $sc11 = array_diff($sc11, $sc11);
                $varsd11 = $row['common_name'];
                $sc11[] = $row['RECEIVABLE_INTEREST'];
                $sumVar11 += $row['RECEIVABLE_INTEREST'];
             //   echo "else1 part";
            }
        }else{
            $sumVar11=0;
            $varsd11 = $row['common_name'];
            $sc11[] = $row['RECEIVABLE_INTEREST'];
            $sumVar11 += $row['RECEIVABLE_INTEREST'];
           // echo "2nd else part";
        }
        $result11[$varsd11] = $sc11;
        $sumArray11[$varsd11] = $sumVar11;

        // Ledger Balance
        $GRAND_TOTAL52 = $GRAND_TOTAL52 + $row["LEDGER_BALANCE"];
       
        if(isset($varsd12)){
            if($varsd12 == $row['common_name']){
                $sc12[] = $row['LEDGER_BALANCE']; 
                $sumVar12 += $row['LEDGER_BALANCE'];
               // echo "if part";
            }
            else{
                $sumVar12=0;
                $sc12 = array_diff($sc12, $sc12);
                $varsd12 = $row['common_name'];
                $sc12[] = $row['LEDGER_BALANCE'];
                $sumVar12 += $row['LEDGER_BALANCE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar12=0;
            $varsd12 = $row['common_name'];
            $sc12[] = $row['LEDGER_BALANCE'];
            $sumVar12 += $row['LEDGER_BALANCE'];
           // echo "2nd else part";
        }
        $result12[$varsd12] = $sc12;
        $sumArray12[$varsd12] = $sumVar12;

        $GRAND_TOTAL62= $GRAND_TOTAL62 + $row["NPA_PROVISION_AMT"];
       

        if(isset($varsd13)){
            if($varsd13 == $row['common_name']){
                $sc13[] = $row['NPA_PROVISION_AMT']; 
                $sumVar13 += $row['NPA_PROVISION_AMT'];
               // echo "if part";
            }
            else{
                $sumVar13=0;
                $sc13 = array_diff($sc13, $sc13);
                $varsd13 = $row['common_name'];
                $sc13[] = $row['NPA_PROVISION_AMT'];
                $sumVar13 += $row['NPA_PROVISION_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar13=0;
            $varsd13 = $row['common_name'];
            $sc13[] = $row['NPA_PROVISION_AMT'];
            $sumVar13 += $row['NPA_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result13[$varsd13] = $sc13;
        $sumArray13[$varsd13] = $sumVar13;

        

        //  Recovery Amount
        $GRAND_TOTAL72= $GRAND_TOTAL72 + $row["TOBE_RECOVER_AMT"];
        
        if(isset($varsd14)){
            if($varsd14 == $row['common_name']){
                $sc14[] = $row['TOBE_RECOVER_AMT']; 
                $sumVar14 += $row['TOBE_RECOVER_AMT'];
               // echo "if part";
            }
            else{
                $sumVar14=0;
                $sc14 = array_diff($sc14, $sc14);
                $varsd14 = $row['common_name'];
                $sc14[] = $row['TOBE_RECOVER_AMT'];
                $sumVar14 += $row['TOBE_RECOVER_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar14=0;
            $varsd14 = $row['common_name'];
            $sc14[] = $row['TOBE_RECOVER_AMT'];
            $sumVar14 += $row['TOBE_RECOVER_AMT'];
           // echo "2nd else part";
        }
        $result14[$varsd14] = $sc14;
        $sumArray14[$varsd14] = $sumVar14;

        // Credit Amount
        $GRAND_TOTAL82 = $GRAND_TOTAL82 + $row["CREDIT_AMOUNT"];
        
        if(isset($varsd15)){
            if($varsd15 == $row['common_name']){
                $sc15[] = $row['CREDIT_AMOUNT']; 
                $sumVar15 += $row['CREDIT_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar15=0;
                $sc15 = array_diff($sc15, $sc15);
                $varsd15 = $row['common_name'];
                $sc15[] = $row['CREDIT_AMOUNT'];
                $sumVar15 += $row['CREDIT_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar15=0;
            $varsd15 = $row['common_name'];
            $sc15[] = $row['CREDIT_AMOUNT'];
            $sumVar15 += $row['CREDIT_AMOUNT'];
           // echo "2nd else part";
        }
        $result15[$varsd15] = $sc15;
        $sumArray15[$varsd15] = $sumVar15;


        $GRAND_TOTAL12=$GRAND_TOTAL10;
        $GRAND_TOTAL20= $GRAND_TOTAL21;
        $GRAND_TOTAL30= $GRAND_TOTAL31;
        $GRAND_TOTAL40= $GRAND_TOTAL41;
        $GRAND_TOTAL50= $GRAND_TOTAL51;
        $GRAND_TOTAL60= $GRAND_TOTAL61;
        $GRAND_TOTAL70= $GRAND_TOTAL71;
        $GRAND_TOTAL80= $GRAND_TOTAL81;


    $temp =
    [
        'SrNo'=>$row['SERAIL_NO'],
        'Scheme'=>$row['SCHEME'],
        'AccountNumber'=>$row['AC_NO'],
        'Name'=>$row['AC_NAME'],
        'ACOpenDate'=>$row['AC_OPDATE'],
        'ExpiryDate'=> $ROW['AC_EXPIRE_DATE'],
        'OverDueDate'=>$row['OVERDUE_DATE'],
        'NPADate'=>$row['AC_NPA_DATE'],
        'date'=>$date1,
        'SanctionAmount'=>$row['AC_SANCTION_AMOUNT'],
        'SecurityAmount'=>$row['AC_SECURITY_AMT'],
        'OverDueAmount'=>$row['OVERDUE_AMOUNT'],
        'ReceivableInterest'=>$row['RECEIVABLE_INTEREST'],
        'LedgerBalance'=>$row['LEDGER_BALANCE'],
        'DueInstallment'=>$row['DUE_INSTALLMENT'],
        'Class'=>$row['NPA_CLASS'],
        'ProvisionAmount'=>$row['NPA_PROVISION_AMT'],
        'ProvisionPercentage'=>$ROW['NPA_PERCENTAGE'],
        'RecoveryAmount'=>$row['TOBE_RECOVER_AMT'],
        'CreditAmount'=>$row['CREDIT_AMOUNT'],
        'Director'=>$row['common_name'],
        'SchemeNo'=>$row['S_APPL'],
        'Sname'=>$row['S_NAME'],

        // SchemeWise

        'SanctionAmt' =>sprintf("%.2f", ($sumArray[$varsd])).' '.$netType,
        'SecurityAmt' =>sprintf("%.2f", ($sumArray1[$varsd1])).' '.$netType,
        'OverDueAmt'=>sprintf("%.2f", ($sumArray2[$varsd2])).' '.$netType,
        'ReceivableInt'=>sprintf("%.2f", ($sumArray3[$varsd3])).' '.$netType,
        'LedgerBal'=>sprintf("%.2f", ($sumArray4[$varsd4])).' '.$netType,
        'ProvisionAmt'=>sprintf("%.2f", ($sumArray5[$varsd5])).' '.$netType,
        'RecoveryAmt'=>sprintf("%.2f", ($sumArray6[$varsd6])).' '.$netType,
        'CreditAmt'=>sprintf("%.2f", ($sumArray7[$varsd7])).' '.$netType,

        //DirectorWise 

        'SAmt' =>sprintf("%.2f", ($sumArray8[$varsd8])).' '.$netType,
        'SecAmt' =>sprintf("%.2f", ($sumArray9[$varsd9])).' '.$netType,
        'OverDueAt'=>sprintf("%.2f", ($sumArray10[$varsd10])).' '.$netType,
        'ReceivableIntrst'=>sprintf("%.2f", ($sumArray11[$varsd11])).' '.$netType,
        'LedBal'=>sprintf("%.2f", ($sumArray12[$varsd12])).' '.$netType,
        'ProvAmt'=>sprintf("%.2f", ($sumArray13[$varsd13])).' '.$netType,
        'RecovrAmt'=>sprintf("%.2f", ($sumArray14[$varsd14])).' '.$netType,
        'CrAmt'=>sprintf("%.2f", ($sumArray15[$varsd15])).' '.$netType,

        // Grand Total
        "SAAmt" => sprintf("%.2f",($GRAND_TOTAL12) + 0.0 ) ,
        "SecrAmt" => sprintf("%.2f",($GRAND_TOTAL20) + 0.0 ) ,
        "OverDAmt" => sprintf("%.2f",($GRAND_TOTAL30) + 0.0 ) ,
        "RInt" => sprintf("%.2f",($GRAND_TOTAL40) + 0.0 ) ,
        "LedgerB" => sprintf("%.2f",($GRAND_TOTAL50) + 0.0 ) ,
        "PrAmt" => sprintf("%.2f",($GRAND_TOTAL60) + 0.0 ) ,
        "RAmt" => sprintf("%.2f",($GRAND_TOTAL70) + 0.0 ) ,
        "CrAmount" => sprintf("%.2f",($GRAND_TOTAL80) + 0.0 ) ,
        
        


        "bankName"  => $bankName,
        "BranchName"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        "Empty"=>"",
        
        
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