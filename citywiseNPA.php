<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/citiwiseNPA.jrxml';
$filename1 = __DIR__.'/citiwiseNPA1.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');



$bankname = $_GET['bankname'];
$Branch = $_GET['Branch'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$date = $_GET['Npa_Date'];
// $edate = $_GET['edate'];
$ACNOTYPE=$_GET['ACNOTYPE'];
$AC_TYPE=$_GET['AC_TYPE'];
$FROM=$_GET['FROM'];
$TO=$_GET['TO'];
$npa_per=$_GET['npa_per'];
$flag=$_GET['flag'];
$percentZero=$_GET['percentZero'];
$percentTen=$_GET['percentTen'];
$percentFive=$_GET['percentFive'];
$percentTwenty=$_GET['percentTwenty'];
$C="'C'";
// $date="'31/03/2022'";
$dd="'DD/MM/YYYY'";
$num="'50100'";

// if($BRANCH_CODE==0){
        // $consolidate="Consolidate";
// }

// $tdate="'31/03/2021'";



$Branch1 = str_replace("'", "", $Branch);
$ACNOTYPE = str_replace("'", "", $ACNOTYPE);
$bankname1 = str_replace("'", "", $bankname);
// $edate1 = str_replace("'", "", $edate);
$date1 = str_replace("'", "", $date);
$percentZero = str_replace("'", "", $percentZero);
$percentTen = str_replace("'", "", $percentTen);
$percentFive = str_replace("'", "", $percentFive);
$percentTwenty = str_replace("'", "", $percentTwenty);


$checktype;
 $flag == 1? $checktype='true': $checktype='false';
 echo $checktype;
 if($flag == '1'){
$query='SELECT
NPADATA."AC_ACNOTYPE"                                                                                  ,
NPADATA."AC_TYPE"                                                                                      ,
NPADATA."AC_NO"                                                                                        ,
NPADATA."AC_NPA_DATE"                                                                                  ,
NPADATA."AC_OPDATE"                                                                                    ,
NPADATA."AC_EXPIRE_DATE"                                                                               ,
NPADATA."OVERDUE_DATE"                                                                                 ,
NPADATA."AC_SANCTION_AMOUNT"                                                                           ,
NPADATA."AC_SECURITY_AMT"                                                                              ,
NPADATA."LEDGER_BALANCE"                                                                               ,
NPADATA."OVERDUE_AMOUNT"                                                                               ,
NPADATA."NPA_PROVISION_AMT"                                                                            ,
LNMASTER."AC_CLOSEDT"                                                                                  ,
NPADATA."RECEIVABLE_INTEREST"                                                                          ,
NPADATA."NPA_CLASS"                                                                                    ,
NPADATA."SUB_CLASS_NO"                                                                                 ,
NPADATA."NPA_PERCENTAGE"                                                                               ,
NPADATA."TOBE_RECOVER_AMT"                                                                             ,
LNMASTER."AC_NAME"                                                                                     ,
SCHEMAST."S_NAME"                                                                                      ,
COALESCE(LOANTRAN.CREDIT_AMOUNT, 0) + COALESCE("DAILYTRAN"."DAILY_CREDIT_AMOUNT", 0) AS "CREDIT_AMOUNT",
CITYMASTER."CITY_NAME"                                                               AS "COMMON_NAME",
CUSTOMERADDRESS."AC_CTCODE"
FROM
LNMASTER
INNER JOIN IDMASTER ON LNMASTER."AC_CUSTID"= IDMASTER."AC_NO"
INNER JOIN CUSTOMERADDRESS ON IDMASTER.ID=CUSTOMERADDRESS."idmasterID"

LEFT JOIN CITYMASTER ON CITYMASTER."CITY_CODE" = CUSTOMERADDRESS."AC_CTCODE"
LEFT JOIN NPADATA ON NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
AND     NPADATA."AC_TYPE"                   = LNMASTER."AC_TYPE"
AND     NPADATA."AC_NO"                     = CAST(LNMASTER."BANKACNO" AS CHARACTER VARYING)
AND     CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$date.', '.$dd.')
LEFT  JOIN
SCHEMAST
ON
NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = SCHEMAST."id"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
INNER  JOIN
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
                                "TRAN_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "RECPAY_INT_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER10_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER1_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER2_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER3_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER4_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER5_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER6_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER7_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER8_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER9_AMOUNT"
                        ELSE
                                0
                        END)) AS CREDIT_AMOUNT
        FROM
                LOANTRAN
        WHERE
                CAST("TRAN_DATE" AS DATE) = TO_DATE('.$date.','.$dd.') 
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") LOANTRAN
ON
NPADATA."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = LOANTRAN."TRAN_ACTYPE"
AND     NPADATA."AC_NO"       = LOANTRAN."TRAN_ACNO"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
and CUSTOMERADDRESS."AC_CTCODE" BETWEEN '.$FROM.' AND '.$TO.'
LEFT JOIN
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
                                "TRAN_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "RECPAY_INT_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER10_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER1_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER2_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER3_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER4_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER5_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER6_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER7_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER8_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER9_AMOUNT"
                        ELSE
                                0
                        END)) AS "DAILY_CREDIT_AMOUNT"
        FROM
                DAILYTRAN
        WHERE
                CAST("TRAN_DATE" AS DATE) = TO_DATE('.$date.', '.$dd.') 
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") "DAILYTRAN"
ON
NPADATA."AC_ACNOTYPE" = "DAILYTRAN"."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = "DAILYTRAN"."TRAN_ACTYPE"
AND     NPADATA."AC_NO"       = "DAILYTRAN"."TRAN_ACNO"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
and CUSTOMERADDRESS."AC_CTCODE" BETWEEN '.$FROM.' AND '.$TO.'
';

if($BRANCH_CODE != '0'){
        $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
                  
}


                        else if($flag == 0){
                                $query='SELECT
NPADATA."AC_ACNOTYPE"                                                                                  ,
NPADATA."AC_TYPE"                                                                                      ,
NPADATA."AC_NO"                                                                                        ,
NPADATA."AC_NPA_DATE"                                                                                  ,
NPADATA."AC_OPDATE"                                                                                    ,
NPADATA."AC_EXPIRE_DATE"                                                                               ,
NPADATA."OVERDUE_DATE"                                                                                 ,
NPADATA."AC_SANCTION_AMOUNT"                                                                           ,
NPADATA."AC_SECURITY_AMT"                                                                              ,
NPADATA."LEDGER_BALANCE"                                                                               ,
NPADATA."OVERDUE_AMOUNT"                                                                               ,
NPADATA."NPA_PROVISION_AMT"                                                                            ,
LNMASTER."AC_CLOSEDT"                                                                                  ,
NPADATA."RECEIVABLE_INTEREST"                                                                          ,
NPADATA."NPA_CLASS"                                                                                    ,
NPADATA."SUB_CLASS_NO"                                                                                 ,
NPADATA."NPA_PERCENTAGE"                                                                               ,
NPADATA."TOBE_RECOVER_AMT"                                                                             ,
LNMASTER."AC_NAME"                                                                                     ,
SCHEMAST."S_NAME"                                                                                      ,
COALESCE(LOANTRAN.CREDIT_AMOUNT, 0) + COALESCE("DAILYTRAN"."DAILY_CREDIT_AMOUNT", 0) AS "CREDIT_AMOUNT",
CITYMASTER."CITY_NAME"                                                               AS "COMMON_NAME",
CUSTOMERADDRESS."AC_CTCODE"
FROM
LNMASTER
INNER JOIN IDMASTER ON LNMASTER."AC_CUSTID"= IDMASTER."AC_NO"
INNER JOIN CUSTOMERADDRESS ON IDMASTER.ID=CUSTOMERADDRESS."idmasterID"

LEFT JOIN CITYMASTER ON CITYMASTER."CITY_CODE" = CUSTOMERADDRESS."AC_CTCODE"
LEFT JOIN NPADATA ON NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
AND     NPADATA."AC_TYPE"                   = LNMASTER."AC_TYPE"
AND     NPADATA."AC_NO"                     = CAST(LNMASTER."BANKACNO" AS CHARACTER VARYING)
AND     CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$date.', '.$dd.')
LEFT  JOIN
SCHEMAST
ON
NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = SCHEMAST."id"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
INNER  JOIN
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
                                "TRAN_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "RECPAY_INT_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER10_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER1_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER2_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER3_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER4_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER5_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER6_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER7_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER8_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER9_AMOUNT"
                        ELSE
                                0
                        END)) AS CREDIT_AMOUNT
        FROM
                LOANTRAN
        WHERE
                CAST("TRAN_DATE" AS DATE) = TO_DATE('.$date.','.$dd.') 
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") LOANTRAN
ON
NPADATA."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = LOANTRAN."TRAN_ACTYPE"
AND     NPADATA."AC_NO"       = LOANTRAN."TRAN_ACNO"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
and CUSTOMERADDRESS."AC_CTCODE" BETWEEN '.$FROM.' AND '.$TO.'
LEFT JOIN
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
                                "TRAN_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "RECPAY_INT_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER10_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER1_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER2_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER3_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER4_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER5_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER6_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER7_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER8_AMOUNT"
                        ELSE
                                0
                        END) + SUM(
                        CASE
                                "TRAN_DRCR"
                        WHEN
                                '.$C.'
                        THEN
                                "OTHER9_AMOUNT"
                        ELSE
                                0
                        END)) AS "DAILY_CREDIT_AMOUNT"
        FROM
                DAILYTRAN
        WHERE
                CAST("TRAN_DATE" AS DATE) = TO_DATE('.$date.', '.$dd.') 
        GROUP BY
                "TRAN_ACNOTYPE",
                "TRAN_ACTYPE"  ,
                "TRAN_ACNO") "DAILYTRAN"
ON
NPADATA."AC_ACNOTYPE" = "DAILYTRAN"."TRAN_ACNOTYPE"
AND     NPADATA."AC_TYPE"     = "DAILYTRAN"."TRAN_ACTYPE"
AND     NPADATA."AC_NO"       = "DAILYTRAN"."TRAN_ACNO"
AND     NPADATA."AC_TYPE"     = '.$AC_TYPE.'
and CUSTOMERADDRESS."AC_CTCODE" BETWEEN '.$FROM.' AND '.$TO.'
';
if($BRANCH_CODE != '0'){
        $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
    }                       
}


	// echo $query ;


$sql =  pg_query($conn,$query);
$GRAND_TOTAL1 = 0;
$i = 0;

$provisionTotal0 = 0;
$provisionTotal1 = 0;
$provisionTotal2 = 0;
$provisionTotal3 = 0;
$total=0;

while($row = pg_fetch_assoc($sql)){

if($row['NPA_PERCENTAGE'] == $percentZero){      
        $provisionTotal0 = $provisionTotal0 + $row['NPA_PROVISION_AMT'];
}
if($row['NPA_PERCENTAGE'] == $percentFive){      
        $provisionTotal1 = $provisionTotal1 + $row['NPA_PROVISION_AMT'];
}
if($row['NPA_PERCENTAGE'] == $percentTen){      
        $provisionTotal2 = $provisionTotal2 + $row['NPA_PROVISION_AMT'];
}
if($row['NPA_PERCENTAGE'] == $percentTwenty){      
        $provisionTotal3 = $provisionTotal3 + $row['NPA_PROVISION_AMT'];
}
$GRAND_TOTAL1= $GRAND_TOTAL1 + $row["AC_SANCTION_AMOUNT"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['AC_SANCTION_AMOUNT']; 
                $sumVar1 += $row['AC_SANCTION_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['AC_SANCTION_AMOUNT'];
                $sumVar1 += $row['AC_SANCTION_AMOUNT'];
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



        $GRAND_TOTAL2= $GRAND_TOTAL2 + $row["AC_SECURITY_AMT"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['AC_SECURITY_AMT']; 
                $sumVar1 += $row['AC_SECURITY_AMT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['AC_SECURITY_AMT'];
                $sumVar1 += $row['AC_SECURITY_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['AC_SECURITY_AMT'];
            $sumVar1 += $row['AC_SECURITY_AMT'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;

        $GRAND_TOTAL3= $GRAND_TOTAL3 + $row["OVERDUE_AMOUNT"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['OVERDUE_AMOUNT']; 
                $sumVar1 += $row['OVERDUE_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['OVERDUE_AMOUNT'];
                $sumVar1 += $row['OVERDUE_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['OVERDUE_AMOUNT'];
            $sumVar1 += $row['OVERDUE_AMOUNT'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;

        $GRAND_TOTAL4= $GRAND_TOTAL4 + $row["LEDGER_BALANCE"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['LEDGER_BALANCE']; 
                $sumVar1 += $row['LEDGER_BALANCE'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['LEDGER_BALANCE'];
                $sumVar1 += $row['LEDGER_BALANCE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['LEDGER_BALANCE'];
            $sumVar1 += $row['LEDGER_BALANCE'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;

        $GRAND_TOTAL5= $GRAND_TOTAL5 + $row["NPA_PROVISION_AMT"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['NPA_PROVISION_AMT']; 
                $sumVar1 += $row['NPA_PROVISION_AMT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['NPA_PROVISION_AMT'];
                $sumVar1 += $row['NPA_PROVISION_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['NPA_PROVISION_AMT'];
            $sumVar1 += $row['NPA_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;


        $GRAND_TOTAL6= $GRAND_TOTAL6 + $row["TOBE_RECOVER_AMT"];
        if(isset($varsd1)){
            if($varsd1 == $row['S_APPL']){
                $sc1[] = $row['TOBE_RECOVER_AMT']; 
                $sumVar1 += $row['TOBE_RECOVER_AMT'];
               // echo "if part";
            }
            else{
                $sumVar1=0;
                $sc1 = array_diff($sc1, $sc1);
                $varsd1 = $row['S_APPL'];
                $sc1[] = $row['TOBE_RECOVER_AMT'];
                $sumVar1 += $row['TOBE_RECOVER_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar1=0;
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['TOBE_RECOVER_AMT'];
            $sumVar1 += $row['TOBE_RECOVER_AMT'];
           // echo "2nd else part";
        }
        $result1[$varsd1] = $sc1;
        $sumArray1[$varsd1] = $sumVar1;


    $tmp=[
        'ACNOTYPE' => $row['S_NAME'],
        'AC_TYPE' => $AC_TYPE,
        'Branch' => $Branch1,
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPIRE_DATE'=>$row['AC_EXPIRE_DATE'],
        'edate' => $date1,
        'bankname' => $bankname1,
        'AC_NO'=>$row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'OVERDUE_DATE'=>$row['OVERDUE_DATE'],
        'AC_NPA_DATE'=>$row['AC_NPA_DATE'],
        'AC_SANCTION_AMOUNT'=>$row['AC_SANCTION_AMOUNT'],
        'AC_SECURITY_AMT'=>$row['AC_SECURITY_AMT'],
        'OVERDUE_AMOUNT'=>$row['OVERDUE_AMOUNT'],
        'RECEIVABLE_INTEREST'=>$row['RECEIVABLE_INTEREST'],
        'CLASS'=>$row['NPA_CLASS'],
        'PROVISION_AMT'=>$row['NPA_PROVISION_AMT'],
        'TOBE_RECOVER_AMT'=>$row['TOBE_RECOVER_AMT'],
        'NPA_PERCENTAGE'=>$row['NPA_PERCENTAGE'],
        'LEDGER_BALANCE'=>$row['LEDGER_BALANCE'],
        'CREDIT_AMOUNT'=>$row['CREDIT_AMOUNT'],
        'BRANCHCODE'=>$BRANCH_CODE,
      // 'total'=>$total,
      'total' => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
      'TSecurity_amt' => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
      'TOverdue_amt' => sprintf("%.2f",($GRAND_TOTAL3) + 0.0 ) ,
      'Tbalance_amt' => sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
      'TProvision_amt' => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,
      'Trecovery_amt' => sprintf("%.2f",($GRAND_TOTAL6) + 0.0 ) ,
        'CITY_NAME'=>$row['COMMON_NAME'],
        'percentZero'=>$percentZero,
        'percentTen'=>$percentTen,
        'percentFive'=>$percentFive,
        'percentTwenty'=>$percentTwenty,
        'TpercentZero'=>$TpercentZero,
        'TpercentFive'=> $TpercentFive,
        'provisionTotal0' => $provisionTotal0,
        'provisionTotal1' => $provisionTotal1,
        'provisionTotal2' => $provisionTotal2,
        'provisionTotal3' => $provisionTotal3,
        // 'consolidate'=>$consolidate,

    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
    
    if($flag == 1)
{
    $config = ['driver'=>'array','data'=>$data];
    // print_r($data);
    $report = new PHPJasperXML();
    $report->load_xml_file($filename1)    
         ->setDataSource($config)
         ->export('Pdf');
}
else if($flag == 0)
{
    $config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
}

?>    