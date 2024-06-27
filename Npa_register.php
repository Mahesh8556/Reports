<?php
ob_start();
include "main.php";
require_once('dbconnect.php');


use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/NPARegister1.jrxml';

$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$AC_TYPE=$_GET['AC_TYPE']; 
$bankName  = $_GET['bankName'];
$BranchName = $_GET['BranchName'];
$schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['BRANCH_CODE'];
$date=$_GET['date'];
$bankName = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);
$date2 = str_replace("'", "", $date);

// $date="'31/03/2021'";
$ST="'ST'";
$Space="' '";
$S="'S'";
$flag=$_GET['FLAG'];
$date1="'DD/MM/YYYY'";
if($flag==1) //Flag is Off
{
$query= 'SELECT  substring (NPADATA."AC_NO" from 4 for 3)AS "BRANCH_CODE",LNMASTER."AC_NO" ,
     SCHEMAST."S_NAME", NPADATA."NPA_CLASS", NPADATA."SUB_CLASS_NO" ,SCHEMAST."S_APPL",SCHEMAST."S_NAME",
     NPADATA."SECU_STATUS",	NPADATA."UNSECURE_PROV_AMT",CASE  "SECU_STATUS" WHEN '.$S.' THEN  CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT) ELSE 0 END "SECURE_PROV_AMT",
	CASE  "SECU_STATUS" WHEN '.$S.' THEN  0  ELSE CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT)  END "UNSECURE_PROV_AMT1",
     NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", 
     NPADATA."AC_NO" AS LNAC_NO, LNMASTER."AC_NAME", NPADATA."REPORT_DATE" ,  
     NPADATA."AC_SANCTION_AMOUNT", 
     LNMASTER."AC_SANCTION_DATE", LNMASTER."AC_OPDATE", 
     NPADATA."LEDGER_BALANCE", NPADATA."OVERDUE_DATE", 
     NPADATA."OVERDUE_AMOUNT", CAST(NPADATA."DUE_INSTALLMENT" AS INTEGER), 
     NPADATA."AC_NPA_DATE", NPADATA."RECEIVABLE_INTEREST", 
     NPADATA."PROV_ON_AMT",  
     NPADATA."NPA_PROVISION_AMT" AS "SECURE_PROV_AMT", 
     NPADATA."NPA_PERCENTAGE", 
     NPADATA."UNSECURE_PERCENTAGE", NPADATA."NPA_PERCENTAGE" AS "SECURE_PERCENTAGE",
     (CAST("NPA_PROVISION_AMT" AS FLOAT) + CAST("UNSECURE_PROV_AMT" AS FLOAT)) "TOTAL_PROVISION_AMT" 
     FROM NPADATA, LNMASTER, SCHEMAST  
	 WHERE  NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"   
     AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE" 
     AND LNMASTER."AC_TYPE"='.$schemeCode.'   
     AND NPADATA."AC_NO" = LNMASTER."BANKACNO"   
     AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"   
     AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID  
    AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date.'AS DATE) 
	AND NPADATA."NPA_CLASS" = '.$ST.'';

    if($BRANCH_CODE != '0'){
        $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
    // $query .=' order by DPMASTER."BANKACNO" ASC';
}
// echo $query;
if($flag==2) //Flag is ON
{
    $query= 'SELECT   substring (NPADATA."AC_NO" from 4 for 3)AS "BRANCH_CODE",LNMASTER."AC_NO" ,
    SCHEMAST."S_NAME", NPADATA."NPA_CLASS", NPADATA."SUB_CLASS_NO" ,
    NPADATA."SECU_STATUS",NPADATA."UNSECURE_PROV_AMT",
    CASE  "SECU_STATUS" WHEN '.$S.' THEN  CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT) ELSE 0 END "SECURE_PROV_AMT",
    CASE  "SECU_STATUS" WHEN '.$S.' THEN  0  ELSE CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT)  END "UNSECURE_PROV_AMT1",
    NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", 
    NPADATA."AC_NO" AS LNAC_NO, LNMASTER."AC_NAME", NPADATA."REPORT_DATE" ,  
    NPADATA."AC_SANCTION_AMOUNT", 
    LNMASTER."AC_SANCTION_DATE", LNMASTER."AC_OPDATE", 
    NPADATA."LEDGER_BALANCE", NPADATA."OVERDUE_DATE", 
    NPADATA."OVERDUE_AMOUNT", CAST(NPADATA."DUE_INSTALLMENT" AS INTEGER), 
    NPADATA."AC_NPA_DATE", NPADATA."RECEIVABLE_INTEREST", 
    NPADATA."PROV_ON_AMT",  
    NPADATA."NPA_PROVISION_AMT" AS "SECURE_PROV_AMT", 
    NPADATA."NPA_PERCENTAGE", 
    NPADATA."UNSECURE_PERCENTAGE", NPADATA."NPA_PERCENTAGE" AS "SECURE_PERCENTAGE",
    (CAST("NPA_PROVISION_AMT" AS FLOAT) + CAST("UNSECURE_PROV_AMT" AS FLOAT)) "TOTAL_PROVISION_AMT" 
    FROM NPADATA, LNMASTER, SCHEMAST  
    WHERE  NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"   
    AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"  
    AND LNMASTER."AC_TYPE"='.$schemeCode.' 
    AND NPADATA."AC_NO" = LNMASTER."BANKACNO"   
    AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"   
    AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID  
    AND CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$date.','.$date1.')
     ';
     if($BRANCH_CODE != '0'){
        $query .=' AND LNMASTER."BRANCH_CODE" ='.$BRANCH_CODE.'';
    }
}
// echo "LNAC_NO";
// echo $query;
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

        // 2 Ledger Balance
        $GRAND_TOTAL2= $GRAND_TOTAL2 + $row["LEDGER_BALANCE"];
        if(isset($varsd2)){
            if($varsd2 == $row['S_APPL']){
                $sc2[] = $row['LEDGER_BALANCE']; 
                $sumVar2 += $row['LEDGER_BALANCE'];
               // echo "if part";
            }
            else{
                $sumVar2=0;
                $sc2 = array_diff($sc2, $sc2);
                $varsd2 = $row['S_APPL'];
                $sc2[] = $row['LEDGER_BALANCE'];
                $sumVar2 += $row['LEDGER_BALANCE'];
             //   echo "else1 part";
            }
        }else{
            $sumVar2=0;
            $varsd2 = $row['S_APPL'];
            $sc2[] = $row['LEDGER_BALANCE'];
            $sumVar2 += $row['LEDGER_BALANCE'];
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
                $sc3 = array_diff($sc2, $sc2);
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
        $result3[$varsd3] = $sc2;
        $sumArray3[$varsd3] = $sumVar2;

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
                $sc4 = array_diff($sc3, $sc3);
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

        
        
        // 5  Provisiom Amount
        $GRAND_TOTAL5= $GRAND_TOTAL5 + $row["PROV_ON_AMT"];
        if(isset($varsd5)){
            if($varsd5 == $row['S_APPL']){
                $sc5[] = $row['PROV_ON_AMT']; 
                $sumVar5 += $row['PROV_ON_AMT'];
               // echo "if part";
            }
            else{
                $sumVar5=0;
                $sc5 = array_diff($sc5, $sc5);
                $varsd5 = $row['S_APPL'];
                $sc5[] = $row['PROV_ON_AMT'];
                $sumVar5 += $row['PROV_ON_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar5=0;
            $varsd5 = $row['S_APPL'];
            $sc5[] = $row['PROV_ON_AMT'];
            $sumVar5 += $row['PROV_ON_AMT'];
           // echo "2nd else part";
        }
        $result5[$varsd5] = $sc5;
        $sumArray5[$varsd5] = $sumVar5;

         
        // 6 unsecure Provision
        $GRAND_TOTAL6= $GRAND_TOTAL6 + $row["UNSECURE_PROV_AMT"];
        if(isset($varsd6)){
            if($varsd6 == $row['S_APPL']){
                $sc6[] = $row['UNSECURE_PROV_AMT']; 
                $sumVar6 += $row['UNSECURE_PROV_AMT'];
               // echo "if part";
            }
            else{
                $sumVar6=0;
                $sc6 = array_diff($sc6, $sc6);
                $varsd6 = $row['S_APPL'];
                $sc6[] = $row['UNSECURE_PROV_AMT'];
                $sumVar6 += $row['UNSECURE_PROV_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar6=0;
            $varsd6 = $row['S_APPL'];
            $sc6[] = $row['UNSECURE_PROV_AMT'];
            $sumVar6 += $row['UNSECURE_PROV_AMT'];
           // echo "2nd else part";
        }
        $result6[$varsd6] = $sc6;
        $sumArray6[$varsd6] = $sumVar6;

        //
        // 7 Total  Provision
        $GRAND_TOTAL7= $GRAND_TOTAL7 + $row["TOTAL_PROVISION_AMT"];
        if(isset($varsd7)){
            if($varsd7 == $row['S_APPL']){
                $sc7[] = $row['TOTAL_PROVISION_AMT']; 
                $sumVar7 += $row['TOTAL_PROVISION_AMT'];
               // echo "if part";
            }
            
            else{
                $sumVar7=0;
                $sc7 = array_diff($sc7, $sc7);
                $varsd7 = $row['S_APPL'];
                $sc7[] = $row['TOTAL_PROVISION_AMT'];
                $sumVar7 += $row['TOTAL_PROVISION_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar7=0;
            $varsd7 = $row['S_APPL'];
            $sc7[] = $row['TOTAL_PROVISION_AMT'];
            $sumVar7 += $row['TOTAL_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result7[$varsd7] = $sc7;
        $sumArray7[$varsd7] = $sumVar7;



        // 8 Total  Provision

        $GRAND_TOTAL8= $GRAND_TOTAL8 + $row["SECURE_PROV_AMT"];
        if(isset($varsd8)){
            if($varsd8 == $row['S_APPL']){
                $sc8[] = $row['SECURE_PROV_AMT']; 
                $sumVar8 += $row['SECURE_PROV_AMT'];
               // echo "if part";
            }
            
            else{
                $sumVar8=0;
                $sc8 = array_diff($sc8, $sc8);
                $varsd8 = $row['S_APPL'];
                $sc8[] = $row['SECURE_PROV_AMT'];
                $sumVar8 += $row['SECURE_PROV_AMT'];
             //   echo "else1 part";
            }
        }else{
            $sumVar8=0;
            $varsd8 = $row['S_APPL'];
            $sc8[] = $row['TOTAL_PROVISION_AMT'];
            $sumVar8 += $row['TOTAL_PROVISION_AMT'];
           // echo "2nd else part";
        }
        $result8[$varsd8] = $sc8;
        $sumArray8[$varsd8] = $sumVar8;



         // 9 Total  Provision

         $GRAND_TOTAL9= $GRAND_TOTAL9 + $row["UNSECURE_PROV_AMT"];
         if(isset($varsd9)){
             if($varsd9 == $row['S_APPL']){
                 $sc9[] = $row['UNSECURE_PROV_AMT']; 
                 $sumVar9 += $row['UNSECURE_PROV_AMT'];
                // echo "if part";
             }
             
             else{
                 $sumVar9=0;
                 $sc9 = array_diff($sc9, $sc9);
                 $varsd9 = $row['S_APPL'];
                 $sc9[] = $row['UNSECURE_PROV_AMT'];
                 $sumVar9 += $row['UNSECURE_PROV_AMT'];
              //   echo "else1 part";
             }
         }else{
             $sumVar9=0;
             $varsd9 = $row['S_APPL'];
             $sc9[] = $row['TOTAL_PROVISION_AMT'];
             $sumVar9 += $row['TOTAL_PROVISION_AMT'];
            // echo "2nd else part";
         }
         $result9[$varsd8] = $sc9;
         $sumArray9[$varsd9] = $sumVar9;



 $temp =
    [
        'SrNo'=>$row['SERAIL_NO'],
        'Scheme1'=>$row['S_NAME'],
        'branchcode'=>$row['BRANCH_CODE'],
        'AccountNumber'=>$row['AC_NO'],
        'Name'=>$row['AC_NAME'],
        'SanctionAmount'=>$row['AC_SANCTION_AMOUNT'],
        'ApproveDate'=>$row['AC_OPDATE'],
        'LedgerBalance'=>$row['LEDGER_BALANCE'],
        'OverDueDate'=>$row['OVERDUE_DATE'],
        'OverDueAmount'=>$row['OVERDUE_AMOUNT'],
        'DueInstallment'=>$row['DUE_INSTALLMENT'],
        'NPADate'=>$row['AC_NPA_DATE'],
        'ReceivableInterest'=>$row['RECEIVABLE_INTEREST'],
        'ProvisionAmount'=>$row['PROV_ON_AMT'],
        'SecurePercentage'=>$row['SECURE_PERCENTAGE'],
        'SecureProvision'=>$row['SECURE_PROV_AMT'],
        'UnsecurePercentage'=>$row['UNSECURE_PERCENTAGE'],
        'UnsecureProvision'=>$row['UNSECURE_PROV_AMT'],
        'TotalProvision'=>$row['TOTAL_PROVISION_AMT'],
        'NPAClass'=>$row['NPA_CLASS'],
        'Scheme1'=>$row['S_APPL'],
        // 'SchemeName'=>$row['S_NAME'],
        'scheme'=>$AC_TYPE,

        'SAmount' =>sprintf("%.2f", ($sumArray1[$varsd1])).' '.$netType,
        'LedBalance' =>sprintf("%.2f", ($sumArray2[$varsd2])).' '.$netType,
        'OverDueAmnt'=>sprintf("%.2f", ($sumArray3[$varsd3])).' '.$netType,
        'ReceivableIntr'=>sprintf("%.2f", ($sumArray4[$varsd4])).' '.$netType,
        'ProvisionAmnt'=>sprintf("%.2f", ($sumArray5[$varsd5])).' '.$netType,
        'UnsecureProv'=>sprintf("%.2f", ($sumArray6[$varsd6])).' '.$netType,
        'TotalProv'=>sprintf("%.2f", ($sumArray7[$varsd7])).' '.$netType,
        

        "SAmnt" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        "LdBalance" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        "ODueAmount" => sprintf("%.2f",($GRAND_TOTAL3) + 0.0 ) ,
        "RInterest" => sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        "PrAmount" => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,
        "UnsecurePrv" => sprintf("%.2f",($GRAND_TOTAL6) + 0.0 ) ,
        "TotalProvison" => sprintf("%.2f",($GRAND_TOTAL7) + 0.0 ) ,
        "secamtpro"=>  sprintf("%.2f",($GRAND_TOTAL8) + 0.0 ) ,
        "unsecamtpro"=> sprintf("%.2f",($GRAND_TOTAL9) + 0.0 ) ,

        'type'=> 'Standard Account',
        "bankName"  => $bankName,
        "BranchName"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        "flag"=>$flag,
        "date"=>$date2,
        
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