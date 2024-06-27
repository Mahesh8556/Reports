<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/NPA_Classification_Secured_Unsecured.jrxml';
$space="' '";
$data = [];
$faker = Faker\Factory::create('en_US');
$i=0;
$initial=0;
$ST="'ST'";
// $date1="'31/03/2021'";
//$AC_TYPE = $_GET['AC_TYPE'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$flag=$_GET['FLAG'];
$S="'S'";
$U="'U'";
$BRANCH_NAME = $_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];
$date1 = $_GET['PRINT_DATE'];
$zero=0;
$branch_name= str_replace("'","",$BRANCH_NAME);
$bank_name= str_replace("'","",$BANK_NAME);
$print_date= str_replace("'","",$date1);

if($flag==0){

$query='SELECT
"BRANCH_CODE"                      ,
NPACLASSIFICATION."SERIAL_NO"      ,
NPACLASSIFICATION."NPA_CLASS"      ,
NPACLASSIFICATION."NPA_DESCRIPTION",
NPACLASSIFICATION."SUB_CLASS_NO"   ,
"SEC_NO_ACS"                       ,
"SEC_LEDGER_BALANCE"               ,
"SEC_RECV_INT"                     ,
"SEC_PROV_ON_AMT"                  ,
"SECURED_PERCENT"                  ,
"SEC_TOT_PROV"                     ,
"UNSEC_NO_ACS"                     ,
"UNSEC_LEDGER_BALANCE"             ,
"UNSEC_RECV_INT"                   ,
"UNSEC_PROV_ON_AMT"                ,
"UNSECURED_PERCENT"                ,
"UNSEC_TOT_PROV"
FROM
NPAMASTER
LEFT JOIN
NPACLASSIFICATION
ON
NPACLASSIFICATION."NPAClassID" = NPAMASTER.ID,
(
        SELECT
                "NPA_CLASS"           ,
                "SUB_CLASS_NO"        ,
                LNMASTER."BRANCH_CODE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                1
                        ELSE
                                0
                        END) "SEC_NO_ACS",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("LEDGER_BALANCE" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_LEDGER_BALANCE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("RECEIVABLE_INTEREST" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_RECV_INT",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("PROV_ON_AMT" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_PROV_ON_AMT",
                SUM(COALESCE(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                CAST("NPA_PROVISION_AMT" AS FLOAT)
                        ELSE
                                0
                        END, 0)) "SEC_TOT_PROV",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                1
                        ELSE
                                0
                        END) "UNSEC_NO_ACS",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("LEDGER_BALANCE" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_LEDGER_BALANCE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("RECEIVABLE_INTEREST" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_RECV_INT",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("PROV_ON_AMT" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_PROV_ON_AMT",
                SUM(COALESCE(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                CASE
                                        CAST("UNSECURE_PROV_AMT" AS FLOAT)
                                WHEN
                                        0
                                THEN
                                        CAST("NPA_PROVISION_AMT" AS FLOAT)
                                ELSE
                                        CAST("UNSECURE_PROV_AMT" AS FLOAT)
                                END
                        ELSE
                                0
                        END, 0)) "UNSEC_TOT_PROV"
        FROM
                NPADATA
        LEFT JOIN
                LNMASTER
        ON
                LNMASTER."BANKACNO" = NPADATA."AC_NO"
        WHERE
                CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE)
        
        GROUP BY
                "NPA_CLASS"   ,
                "SUB_CLASS_NO",
                LNMASTER."BRANCH_CODE") TMP
WHERE
NPAMASTER."EFFECT_DATE" =
(
        SELECT
                MAX("EFFECT_DATE")
        FROM
                NPAMASTER)
AND     NPACLASSIFICATION."NPA_CLASS"    = TMP."NPA_CLASS"
AND     NPACLASSIFICATION."SUB_CLASS_NO" = TMP."SUB_CLASS_NO"


';
if($BRANCH_CODE != '0'){
        $query .=' AND "BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
    
//     $query .='  Order By NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO"';

}
// echo $query;
if($flag==1){

$query='SELECT
"BRANCH_CODE"                      ,
NPACLASSIFICATION."SERIAL_NO"      ,
NPACLASSIFICATION."NPA_CLASS"      ,
NPACLASSIFICATION."NPA_DESCRIPTION",
NPACLASSIFICATION."SUB_CLASS_NO"   ,
"SEC_NO_ACS"                       ,
"SEC_LEDGER_BALANCE"               ,
"SEC_RECV_INT"                     ,
"SEC_PROV_ON_AMT"                  ,
"SECURED_PERCENT"                  ,
"SEC_TOT_PROV"                     ,
"UNSEC_NO_ACS"                     ,
"UNSEC_LEDGER_BALANCE"             ,
"UNSEC_RECV_INT"                   ,
"UNSEC_PROV_ON_AMT"                ,
"UNSECURED_PERCENT"                ,
"UNSEC_TOT_PROV"
FROM
NPAMASTER
LEFT JOIN
NPACLASSIFICATION
ON
NPACLASSIFICATION."NPAClassID" = NPAMASTER.ID,
(
        SELECT
                "NPA_CLASS"           ,
                "SUB_CLASS_NO"        ,
                LNMASTER."BRANCH_CODE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                1
                        ELSE
                                0
                        END) "SEC_NO_ACS",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("LEDGER_BALANCE" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_LEDGER_BALANCE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("RECEIVABLE_INTEREST" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_RECV_INT",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                COALESCE(CAST("PROV_ON_AMT" AS FLOAT), 0)
                        ELSE
                                0
                        END) "SEC_PROV_ON_AMT",
                SUM(COALESCE(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$S.'
                        THEN
                                CAST("NPA_PROVISION_AMT" AS FLOAT)
                        ELSE
                                0
                        END, 0)) "SEC_TOT_PROV",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                1
                        ELSE
                                0
                        END) "UNSEC_NO_ACS",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("LEDGER_BALANCE" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_LEDGER_BALANCE",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("RECEIVABLE_INTEREST" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_RECV_INT",
                SUM(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                COALESCE(CAST("PROV_ON_AMT" AS FLOAT), 0)
                        ELSE
                                0
                        END) "UNSEC_PROV_ON_AMT",
                SUM(COALESCE(
                        CASE
                                "SECU_STATUS"
                        WHEN
                                '.$U.'
                        THEN
                                CASE
                                        CAST("UNSECURE_PROV_AMT" AS FLOAT)
                                WHEN
                                        0
                                THEN
                                        CAST("NPA_PROVISION_AMT" AS FLOAT)
                                ELSE
                                        CAST("UNSECURE_PROV_AMT" AS FLOAT)
                                END
                        ELSE
                                0
                        END, 0)) "UNSEC_TOT_PROV"
        FROM
                NPADATA
        LEFT JOIN
                LNMASTER
        ON
                LNMASTER."BANKACNO" = NPADATA."AC_NO"
        WHERE
                CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE)
        GROUP BY
                "NPA_CLASS"   ,
                "SUB_CLASS_NO",
                LNMASTER."BRANCH_CODE") TMP
WHERE
NPAMASTER."EFFECT_DATE" =
(
        SELECT
                MAX("EFFECT_DATE")
        FROM
                NPAMASTER)
AND     NPACLASSIFICATION."NPA_CLASS"    = TMP."NPA_CLASS"
AND     NPACLASSIFICATION."SUB_CLASS_NO" = TMP."SUB_CLASS_NO"
AND     NPACLASSIFICATION."NPA_CLASS"   = '.$ST.'
';    
if($BRANCH_CODE != '0'){
        $query .=' AND "BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
    
    }
    //  echo $query;
$sql =  pg_query($conn,$query);
while($row = pg_fetch_assoc($sql)){

    if(isset($varsd)){
        if($varsd != $row['NPA_CLASS']){
            $sc[] = $row['SEC_NO_ACS']; 
            $sumVar += $row['SEC_NO_ACS'];
           
        }
        else{   
            $sumVar=0;
            $sc = array_diff($sc, $sc);
            $varsd = $row['NPA_CLASS'];
            $sc[] = $row['SEC_NO_ACS'];
            $sumVar += $row['SEC_NO_ACS'];
        
        }
    }else{
        $sumVar=0;
        $varsd = $row['NPA_CLASS'];
        $sc[] = $row['SEC_NO_ACS'];
        $sumVar += $row['SEC_NO_ACS'];
      
    }
    $result[$varsd] = $sc;
    $sumArray[$varsd] = $sumVar;

$GRAND_TOTAL=$GRAND_TOTAL+$row['SEC_NO_ACS'];
    if(isset($varsd1)){
        if($varsd1 != $row['NPA_CLASS']){
            $sc1[] = $row['UNSEC_NO_ACS']; 
            $sumVar1 += $row['UNSEC_NO_ACS'];
           
        }
        else{   
            $sumVar1=0;
            $sc1 = array_diff($sc1, $sc1);
            $varsd1 = $row['NPA_CLASS'];
            $sc1[] = $row['UNSEC_NO_ACS'];
            $sumVar1 += $row['UNSEC_NO_ACS'];
         
        }
    }else{
        $sumVar1=0;
        $varsd1 = $row['NPA_CLASS'];
        $sc1[] = $row['UNSEC_NO_ACS'];
        $sumVar1 += $row['UNSEC_NO_ACS'];
       
    }
    $result1[$varsd1] = $sc1;
    $sumArray1[$varsd1] = $sumVar1;

    if(isset($varsd2)){
        if($varsd2 != $row['NPA_CLASS']){
            $sc2[] = $row['SEC_LEDGER_BALANCE']; 
            $sumVar2 += $row['SEC_LEDGER_BALANCE'];
           
        }
        else{   
            $sumVar2=0;
            $sc2 = array_diff($sc2, $sc2);
            $varsd2 = $row['NPA_CLASS'];
            $sc2[] = $row['SEC_LEDGER_BALANCE'];
            $sumVar2 += $row['SEC_LEDGER_BALANCE'];
         
        }
    }else{
        $sumVar2=0;
        $varsd2 = $row['NPA_CLASS'];
        $sc2[] = $row['SEC_LEDGER_BALANCE'];
        $sumVar2 += $row['SEC_LEDGER_BALANCE'];
       
    }
    $result2[$varsd2] = $sc2;
    $sumArray2[$varsd2] = $sumVar2;


    if(isset($varsd3)){
        if($varsd3 != $row['NPA_CLASS']){
            $sc3[] = $row['UNSEC_LEDGER_BALANCE']; 
            $sumVar3 += $row['UNSEC_LEDGER_BALANCE'];
           
        }
        else{   
            $sumVar3=0;
            $sc3 = array_diff($sc3, $sc3);
            $varsd3 = $row['NPA_CLASS'];
            $sc3[] = $row['UNSEC_LEDGER_BALANCE'];
            $sumVar3 += $row['UNSEC_LEDGER_BALANCE'];
         
        }
    }else{
        $sumVar3=0;
        $varsd3 = $row['NPA_CLASS'];
        $sc3[] = $row['UNSEC_LEDGER_BALANCE'];
        $sumVar3 += $row['UNSEC_LEDGER_BALANCE'];
       
    }
    $result3[$varsd3] = $sc3;
    $sumArray3[$varsd3] = $sumVar3;


    if(isset($varsd4)){
        if($varsd4 != $row['NPA_CLASS']){
            $sc4[] = $row['SEC_RECV_INT']; 
            $sumVar4 += $row['SEC_RECV_INT'];
           
        }
        else{   
            $sumVar4=0;
            $sc4 = array_diff($sc4, $sc4);
            $varsd4 = $row['NPA_CLASS'];
            $sc4[] = $row['SEC_RECV_INT'];
            $sumVar4 += $row['SEC_RECV_INT'];
         
        }
    }else{
        $sumVar4=0;
        $varsd4 = $row['NPA_CLASS'];
        $sc4[] = $row['SEC_RECV_INT'];
        $sumVar4 += $row['SEC_RECV_INT'];
       
    }
    $result4[$varsd4] = $sc4;
    $sumArray4[$varsd4] = $sumVar4;


    if(isset($varsd5)){
        if($varsd5 != $row['NPA_CLASS']){
            $sc5[] = $row['UNSEC_RECV_INT']; 
            $sumVar5 += $row['UNSEC_RECV_INT'];
           
        }
        else{   
            $sumVar5=0;
            $sc5 = array_diff($sc5, $sc5);
            $varsd5 = $row['NPA_CLASS'];
            $sc5[] = $row['UNSEC_RECV_INT'];
            $sumVar5 += $row['UNSEC_RECV_INT'];
         
        }
    }else{
        $sumVar5=0;
        $varsd5 = $row['NPA_CLASS'];
        $sc5[] = $row['UNSEC_RECV_INT'];
        $sumVar5 += $row['UNSEC_RECV_INT'];
       
    }
    $result5[$varsd5] = $sc5;
    $sumArray5[$varsd5] = $sumVar5;


    if(isset($varsd6)){
        if($varsd6 != $row['NPA_CLASS']){
            $sc6[] = $row['SEC_PROV_ON_AMT']; 
            $sumVar6 += $row['SEC_PROV_ON_AMT'];
           
        }
        else{   
            $sumVar6=0;
            $sc6 = array_diff($sc6, $sc6);
            $varsd6 = $row['NPA_CLASS'];
            $sc6[] = $row['SEC_PROV_ON_AMT'];
            $sumVar6 += $row['SEC_PROV_ON_AMT'];
         
        }
    }else{
        $sumVar6=0;
        $varsd6 = $row['NPA_CLASS'];
        $sc6[] = $row['SEC_PROV_ON_AMT'];
        $sumVar6 += $row['SEC_PROV_ON_AMT'];
       
    }
    $result6[$varsd6] = $sc6;
    $sumArray6[$varsd6] = $sumVar6;


    if(isset($varsd7)){
        if($varsd7 != $row['NPA_CLASS']){
            $sc7[] = $row['UNSEC_PROV_ON_AMT']; 
            $sumVar7 += $row['UNSEC_PROV_ON_AMT'];
           
        }
        else{   
            $sumVar7=0;
            $sc7 = array_diff($sc7, $sc7);
            $varsd7 = $row['NPA_CLASS'];
            $sc7[] = $row['UNSEC_PROV_ON_AMT'];
            $sumVar7 += $row['UNSEC_PROV_ON_AMT'];
         
        }
    }else{
        $sumVar7=0;
        $varsd7 = $row['NPA_CLASS'];
        $sc7[] = $row['UNSEC_PROV_ON_AMT'];
        $sumVar7 += $row['UNSEC_PROV_ON_AMT'];
       
    }
    $result7[$varsd7] = $sc7;
    $sumArray7[$varsd7] = $sumVar7;


    if(isset($varsd8)){
        if($varsd8 != $row['NPA_CLASS']){
            $sc8[] = $row['SEC_TOT_PROV']; 
            $sumVar8 += $row['SEC_TOT_PROV'];
           
        }
        else{   
            $sumVar8=0;
            $sc8 = array_diff($sc8, $sc8);
            $varsd8 = $row['NPA_CLASS'];
            $sc8[] = $row['SEC_TOT_PROV'];
            $sumVar8 += $row['SEC_TOT_PROV'];
         
        }
    }else{
        $sumVar8=0;
        $varsd8 = $row['NPA_CLASS'];
        $sc8[] = $row['SEC_TOT_PROV'];
        $sumVar8 += $row['SEC_TOT_PROV'];
       
    }
    $result8[$varsd8] = $sc8;
    $sumArray8[$varsd8] = $sumVar8;


    if(isset($varsd9)){
        if($varsd9 != $row['NPA_CLASS']){
            $sc9[] = $row['UNSEC_TOT_PROV']; 
            $sumVar9 += $row['UNSEC_TOT_PROV'];
           
        }
        else{   
            $sumVar9=0;
            $sc9 = array_diff($sc9, $sc9);
            $varsd9 = $row['NPA_CLASS'];
            $sc9[] = $row['UNSEC_TOT_PROV'];
            $sumVar9 += $row['UNSEC_TOT_PROV'];
         
        }
    }else{
        $sumVar9=0;
        $varsd9 = $row['NPA_CLASS'];
        $sc9[] = $row['UNSEC_TOT_PROV'];
        $sumVar9 += $row['UNSEC_TOT_PROV'];
       
    }
    $result9[$varsd9] = $sc9;
    $sumArray9[$varsd9] = $sumVar9;


    // if(isset($varsd10)){
    //     if($varsd10 != $row['NPA_CLASS']){
    //         $sc10[] = $row['UNSEC_TOT_PROV']; 
    //         $sumVar10 += $row['UNSEC_TOT_PROV'];
           
    //     }
    //     else{   
    //         $sumVar9=0;
    //         $sc9 = array_diff($sc9, $sc9);
    //         $varsd9 = $row['NPA_CLASS'];
    //         $sc9[] = $row['UNSEC_TOT_PROV'];
    //         $sumVar9 += $row['UNSEC_TOT_PROV'];
         
    //     }
    // }else{
    //     $sumVar9=0;
    //     $varsd9 = $row['NPA_CLASS'];
    //     $sc9[] = $row['UNSEC_TOT_PROV'];
    //     $sumVar9 += $row['UNSEC_TOT_PROV'];
       
    // }
    // $result9[$varsd9] = $sc9;
    // $sumArray9[$varsd9] = $sumVar9;


    $sum= ($sumArray2[$varsd2]);
    if($sum!=0){
    $perc= (($row['SEC_LEDGER_BALANCE']/$sum)*100);
    }
    else{
        $perc=$zero;
    }

    $sum2= ($sumArray3[$varsd3]);
    if($sum2!=0){
    $perc2= (($row['UNSEC_LEDGER_BALANCE']/$sum2)*100);
    }
    else{
        $perc2=$zero;
    }

    $temp =[
        'Classification_of_Assets'=>$row['NPA_CLASS'],
        //'Provision_Percentage'=>$row['NPA_PERCENTAGE'],
        'Balance_For_Provision'=>$row['NPA_PROVISION_AMT'],
        'No_of_Secured_Accounts'=>$row['SEC_NO_ACS'],
        'No_of_Unsecured_Amount'=>$row['UNSEC_NO_ACS'],
        'Secured_Ledger_Balance'=>$row['SEC_LEDGER_BALANCE'],
        'Unsecured_Ledger_Balance'=>$row['UNSEC_LEDGER_BALANCE'],
        'Secured_Receivable_Int'=>$row['SEC_RECV_INT'],
        'Unsecured_Receivable_Int'=>$row['UNSEC_RECV_INT'],
        'Secured_Percentage'=>$row['SECURED_PERCENT'],
        'Unsecured_Percentage'=>$row['UNSECURED_PERCENT'],
        'Secured_Total_Provision'=>$row['SEC_TOT_PROV'],
        'Unsecured_Total_Provision'=>$row['UNSEC_TOT_PROV'],
        'Secured_Balance_Provision'=>$row['SEC_PROV_ON_AMT'],
        'Unsecured_Balance_Provision'=>$row['UNSEC_PROV_ON_AMT'],
        'BranchName'=>$branch_name,
        'BankName'=>$bank_name,
        'PrintDate'=>$print_date,
        'NO_OF_SEC_ACC' =>sprintf(($sumArray[$varsd])).' '.$netType,
        'NO_OF_UNSEC_ACC' =>sprintf(($sumArray1[$varsd1])).' '.$netType,
        'GRAND_SEC_PLUS_UNSEC'=>($sumArray[$varsd])+($sumArray1[$varsd1]),
        'LEDGER_BAL_SEC' =>sprintf("%.2f",($sumArray2[$varsd2])).' '.$netType,
        'LEDGER_BAL_UNSEC' =>sprintf("%.2f",($sumArray3[$varsd3])).' '.$netType,
        'LEDGER_SEC_UNSEC'=>sprintf("%.2f",($sumArray2[$varsd2])+($sumArray3[$varsd3])).' '.$netType,
        'REC_INT_SEC' =>sprintf("%.2f",($sumArray4[$varsd4])).' '.$netType,
        'REC_INT_UNSEC' =>sprintf("%.2f",($sumArray5[$varsd5])).' '.$netType,
        'REC_INT_SEC_UNSEC'=>sprintf("%.2f",($sumArray4[$varsd4])+($sumArray5[$varsd5])).' '.$netType,
        'PROV_ON_AMT_SEC' =>sprintf("%.2f",($sumArray6[$varsd6])).' '.$netType,
        'PROV_ON_AMT_UNSEC' =>sprintf("%.2f",($sumArray7[$varsd7])).' '.$netType,
        'PROV_AMT_SEC_UNSEC'=>sprintf("%.2f",($sumArray6[$varsd6])+($sumArray7[$varsd7])).' '.$netType,
        'TOT_PROV_SEC' =>sprintf("%.2f",($sumArray8[$varsd8])).' '.$netType,
        'TOT_PROV_UNSEC' =>sprintf("%.2f",($sumArray9[$varsd9])).' '.$netType,
        'TOT_PROV_SEC_UNSEC'=>sprintf("%.2f",($sumArray8[$varsd8])+($sumArray9[$varsd9])).' '.$netType,
        'Percentage'=>sprintf("%.2f",($perc) + 0.0),
        'Percentage_unsec'=>$perc2,
        'BRANCH_CODE'=>$BRANCH_CODE,
        
    ];
    $data[$i]=$temp;
    $i++;
}
// if(sprintf("%.2f",($sumArray3[$varsd3])).' '.$netType==0){
//     'Percentage_unsec'=>$zero;
// }
// else{
// 'Percentage_unsec'=>(($row['UNSEC_LEDGER_BALANCE']/sprintf("%.2f",($sumArray3[$varsd3])).' '.$netType)*100);
// }
ob_end_clean();
$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
?>