<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/ToBeRecoveryForNonNPAReport.jrxml';

$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$dd="'DD/MM/YYYY'";
$AC_TYPE=$_GET['AC_TYPE'];
$bankName  = $_GET['bankName'];
$BranchName = $_GET['BranchName'];
$schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['BRANCH_CODE'];
$bankName = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);
$TOBE_RECOVER_AMT= 0;
//$dt="''31/02/2022'";
// $date="'31/03/2023'";
$date=$_GET['dt'];
$date1 = str_replace("'", "", $date);
$ST="'ST'";
$Space="' '";
$S="'S'";
$flag=$_GET['FLAG'];
if($flag==0) //Flag is Off
{

$query='SELECT 

NPADATA."AC_ACNOTYPE",
NPADATA."OVERDUE_DATE",
NPADATA."CURRENT_INTEREST",
NPADATA."AC_TYPE",
NPADATA."AC_NO",
NPADATA."AC_NPA_DATE",
NPADATA."AC_OPDATE",
NPADATA."AC_EXPIRE_DATE",
NPADATA."AC_SANCTION_AMOUNT",
NPADATA."AC_SECURITY_AMT",
NPADATA."LEDGER_BALANCE",
NPADATA."OVERDUE_AMOUNT",
(NPADATA."OVERDUE_AMOUNT" + NPADATA."CURRENT_INTEREST") "TOTAL_OVERDUE",
NPADATA."NPA_PROVISION_AMT",
NPADATA."RECEIVABLE_INTEREST",
NPADATA."NPA_CLASS",
NPADATA."NPA_PERCENTAGE",
NPADATA."TOBE_RECOVER_AMT",
LNMASTER."AC_NAME",
SCHEMAST."S_APPL",
SCHEMAST."S_NAME",
LNMASTER."AC_INSTALLMENT",
NPADATA."AMT_TOBE_RECOVER",
SCHEMAST."S_APPL" || ' .$Space.' || SCHEMAST."S_NAME" "SCHEME"
FROM NPADATA,
LNMASTER,
SCHEMAST
WHERE NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"
AND NPADATA."AC_NO" = LNMASTER."BANKACNO"
AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID
AND CAST(NPADATA."AC_TYPE" AS INTEGER)  ='.$schemeCode.'

AND CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$date.','.$dd.')
AND NPADATA."NPA_CLASS" <> '.$ST.'';
if($BRANCH_CODE != '0'){
    $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
}

$query .=' ORDER BY NPADATA."AC_ACNOTYPE",
NPADATA."AC_TYPE",
NPADATA."AC_NO"';
}
// echo $query;
if($flag==1) // Flag is ON
{
    $query='SELECT NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", NPADATA."AC_NO", NPADATA."OVERDUE_DATE",
    NPADATA."CURRENT_INTEREST",
    NPADATA."AC_NPA_DATE", NPADATA."AC_OPDATE"  , NPADATA."AC_EXPIRE_DATE", NPADATA."AC_SANCTION_AMOUNT", 
    NPADATA."AC_SECURITY_AMT", NPADATA."LEDGER_BALANCE"  , NPADATA."OVERDUE_AMOUNT", 
    NPADATA."NPA_PROVISION_AMT", NPADATA."RECEIVABLE_INTEREST", NPADATA."NPA_CLASS"  , 
    NPADATA."NPA_PERCENTAGE", NPADATA."TOBE_RECOVER_AMT", LNMASTER."AC_NAME", SCHEMAST."S_APPL",SCHEMAST."S_NAME",
    LNMASTER."AC_INSTALLMENT",NPADATA."AMT_TOBE_RECOVER" ,  SCHEMAST."S_APPL"||'.$Space.' ||SCHEMAST."S_NAME"  "SCHEME"
    FROM NPADATA, LNMASTER, SCHEMAST  
    Where NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"  
    AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"
    AND NPADATA."AC_NO" = LNMASTER."BANKACNO"   
    AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"   
    AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID  
    AND CAST(NPADATA."AC_TYPE" AS INTEGER)  ='.$schemeCode.'
    
    AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date.' AS DATE) 
    AND "TOBE_RECOVER_AMT" <> '.$TOBE_RECOVER_AMT.'  
    AND NPADATA."NPA_CLASS" ='.$ST.'';
    if($BRANCH_CODE != '0'){
        $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
    
    $query .='  Order By NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO"';
}

//  echo $query;    

$sql =  pg_query($conn,$query);

while($row = pg_fetch_assoc($sql))
{
    
        // Sanction Amount
        
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

        // 2 Securrity Amount
        $GRAND_TOTAL2= $GRAND_TOTAL2 + $row["AC_SECURITY_AMT"];
        if(isset($varsd2)){
            if($varsd2 == $row['S_APPL']){
                $sc2[] = $row['AC_SECURITY_AMT']; 
                $sumVar2 += $row['AC_SECURITY_AMT'];
               // echo "if part";
            }
            else{
                $sumVar2=0;
                $sc2 = array_diff($sc2, $sc2);
                $varsd2 = $row['S_APPL'];
                $sc2[] = $row['AC_SECURITY_AMT'];
                $sumVar2 += $row['AC_SECURITY_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar2=0;
            $varsd2 = $row['S_APPL'];
            $sc2[] = $row['AC_SECURITY_AMT'];
            $sumVar2 += $row['AC_SECURITY_AMT'];
           // echo "2nd else part";
        }
        $result2[$varsd2] = $sc2;
        $sumArray2[$varsd2] = $sumVar2;

        //3  Over Due amount
        $GRAND_TOTAL3= $GRAND_TOTAL3 + $row["OVERDUE_AMOUNT"];
        if(isset($varsd3)){
            if($varsd3 == $row['S_APPL']){
                $sc3[] = $row['OVERDUE_AMOUNT']; 
                $sumVar3 += $row['OVERDUE_AMOUNT'];
               // echo "if part";
            }
            else{
                $sumVar3=0;
                $sc3 = array_diff($sc3, $sc3);
                $varsd3 = $row['S_APPL'];
                $sc3[] = $row['OVERDUE_AMOUNT'];
                $sumVar3 += $row['OVERDUE_AMOUNT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar3=0;
            $varsd3 = $row['S_APPL'];
            $sc3[] = $row['OVERDUE_AMOUNT'];
            $sumVar3 += $row['OVERDUE_AMOUNT'];
           // echo "2nd else part";
        }
        $result3[$varsd3] = $sc3;
        $sumArray3[$varsd3] = $sumVar3;

        // 4 Receivable Interest
        $GRAND_TOTAL4= $GRAND_TOTAL4 + $row["RECEIVABLE_INTEREST"];
        if(isset($varsd4)){
            if($varsd4 == $row['S_APPL']){
                $sc4[] = $row['RECEIVABLE_INTEREST']; 
                $sumVar4 += $row['RECEIVABLE_INTEREST'];
               // echo "if part";
            }
            else{
                $sumVar4=0;
                $sc4 = array_diff($sc4, $sc4);
                $varsd4 = $row['S_APPL'];
                $sc4[] = $row['RECEIVABLE_INTEREST'];
                $sumVar4 += $row['RECEIVABLE_INTEREST'];
             //   echo "else1 part";
            }
        }else{
            $sumVar4=0;
            $varsd4 = $row['S_APPL'];
            $sc4[] = $row['RECEIVABLE_INTEREST'];
            $sumVar4 += $row['RECEIVABLE_INTEREST'];
           // echo "2nd else part";
        }
        $result4[$varsd4] = $sc4;
        $sumArray4[$varsd4] = $sumVar4;

        // 5 Total Out Standing Amount
        $GRAND_TOTAL5= $GRAND_TOTAL5 + $row["TOTAL_OVERDUE"];
        if(isset($varsd5)){
            if($varsd5 == $row['S_APPL']){
                $sc5[] = $row['TOTAL_OVERDUE']; 
                $sumVar5 += $row['TOTAL_OVERDUE'];
               // echo "if part";
            }
            else{
                $sumVar5=0;
                $sc5 = array_diff($sc5, $sc5);
                $varsd5 = $row['S_APPL'];
                $sc5[] = $row['TOTAL_OVERDUE'];
                $sumVar5 += $row['TOTAL_OVERDUE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar5=0;
            $varsd5 = $row['S_APPL'];
            $sc5[] = $row['TOTAL_OVERDUE'];
            $sumVar5 += $row['TOTAL_OVERDUE'];
           // echo "2nd else part";
        }
        $result5[$varsd5] = $sc5;
        $sumArray5[$varsd5] = $sumVar5;
        
        // 6 Due Amount
        $GRAND_TOTAL6= $GRAND_TOTAL6 + $row["LEDGER_BALANCE"];
        if(isset($varsd6)){
            if($varsd6 == $row['S_APPL']){
                $sc6[] = $row['LEDGER_BALANCE']; 
                $sumVar6 += $row['LEDGER_BALANCE'];
               // echo "if part";
            }
            else{
                $sumVar6=0;
                $sc6 = array_diff($sc6, $sc6);
                $varsd6 = $row['S_APPL'];
                $sc6[] = $row['LEDGER_BALANCE'];
                $sumVar6 += $row['LEDGER_BALANCE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar6=0;
            $varsd6 = $row['S_APPL'];
            $sc6[] = $row['LEDGER_BALANCE'];
            $sumVar6 += $row['LEDGER_BALANCE'];
           // echo "2nd else part";
        }
        $result6[$varsd6] = $sc6;
        $sumArray6[$varsd6] = $sumVar6;

        // 7 NPA Provision Amount
        $GRAND_TOTAL7= $GRAND_TOTAL7 + $row["NPA_PROVISION_AMT"];
        if(isset($varsd7)){
            if($varsd7 == $row['S_APPL']){
                $sc7[] = $row['NPA_PROVISION_AMT']; 
                $sumVar7 += $row['NPA_PROVISION_AMT'];
               // echo "if part";
            }
            else{
                $sumVar7=0;
                $sc7 = array_diff($sc7, $sc7);
                $varsd7 = $row['S_APPL'];
                $sc7[] = $row['NPA_PROVISION_AMT'];
                $sumVar7 += $row['NPA_PROVISION_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar7=0;
            $varsd7 = $row['S_APPL'];
            $sc7[] = $row['NPA_PROVISION_AMT'];
            $sumVar7 += $row['NPA_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result7[$varsd7] = $sc7;
        $sumArray7[$varsd7] = $sumVar7;

        // 8 NPA Recovery Amount
        $GRAND_TOTAL8= $GRAND_TOTAL8 + $row["TOBE_RECOVER_AMT"];
        if(isset($varsd8)){
            if($varsd8 == $row['S_APPL']){
                $sc8[] = $row['TOBE_RECOVER_AMT']; 
                $sumVar8 += $row['TOBE_RECOVER_AMT'];
               // echo "if part";
            }
            else{
                $sumVar8=0;
                $sc8 = array_diff($sc8, $sc8);
                $varsd8 = $row['S_APPL'];
                $sc8[] = $row['TOBE_RECOVER_AMT'];
                $sumVar8 += $row['TOBE_RECOVER_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar8=0;
            $varsd8 = $row['S_APPL'];
            $sc8[] = $row['TOBE_RECOVER_AMT'];
            $sumVar8 += $row['TOBE_RECOVER_AMT'];
           // echo "2nd else part";
        }
        $result8[$varsd8] = $sc8;
        $sumArray8[$varsd8] = $sumVar8;

        $temp =
        [
        
        
        'AccountNumber'=>$row['AC_NO'],
        'Name'=>$row['AC_NAME'],
        'ACOpenDate'=>$row['AC_OPDATE'],
        'ExpiryDate'=> $row['AC_EXPIRE_DATE'],
        'OverDueDate'=>$row['OVERDUE_DATE'],
        'NPADate'=>$row['AC_NPA_DATE'],
        'SanctionAmount'=>$row['AC_SANCTION_AMOUNT'],
        'SecurityAmount'=>$row['AC_SECURITY_AMT'],
        'OverDueAmount'=>$row['OVERDUE_AMOUNT'],
        'ReceivableInterest'=>$row['RECEIVABLE_INTEREST'],
        //'CurrentInterest'
        'TotalOutstandingAmount'=>$row['TOTAL_OVERDUE'],
        'DueAmount'=>$row['LEDGER_BALANCE'],
        'Class'=>$row['NPA_CLASS'],
        'CurrentNPAProvision'=>$row['NPA_PROVISION_AMT'],
        'NPARecoveryAmount'=>$row['TOBE_RECOVER_AMT'],
        'Scheme'=>$row['S_APPL'],
        'SchemeName'=>$row['S_NAME'],
        'CURRENT_INTEREST'=>$row['CURRENT_INTEREST'],
        'date'=>$date1,

        //SchemewiseTotal

        'SAmount' =>sprintf("%.2f", ($sumArray1[$varsd1])).' '.$netType,
        'SecureAmount'=>sprintf("%.2f", ($sumArray2[$varsd2])).' '.$netType,
        'OvrDueAmt'=>sprintf("%.2f", ($sumArray3[$varsd3])).' '.$netType,
        'ReceivableIntr'=>sprintf("%.2f", ($sumArray4[$varsd4])).' '.$netType,
        'TotaloutAmt'=>sprintf("%.2f", ($sumArray5[$varsd5])).' '.$netType,
        'DueAmnt'=>sprintf("%.2f", ($sumArray6[$varsd6])).' '.$netType,
        'CurrentNPAProv'=>sprintf("%.2f", ($sumArray7[$varsd7])).' '.$netType,
        'NPARecoveryAmnt'=>sprintf("%.2f", ($sumArray8[$varsd8])).' '.$netType,

        // Grand total

        'SNAmount' => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        'SecurityAmnt'=> sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        'OvrDAmt' => sprintf("%.2f",($GRAND_TOTAL3) + 0.0 ) ,
        'RCInterest'=>sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        'TotalOutAmnt' => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,
        'DuAmnt' => sprintf("%.2f",($GRAND_TOTAL6) + 0.0 ) ,
        'NPAProvision' => sprintf("%.2f",($GRAND_TOTAL7) + 0.0 ) ,
        'NPARAmount' => sprintf("%.2f",($GRAND_TOTAL8) + 0.0 ) ,

        "bankName"  => $bankName,
        "BranchName"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        "flag"=>$flag,
        'type'=> 'Standard Account',
    ];
    $data[$i]=$temp;
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