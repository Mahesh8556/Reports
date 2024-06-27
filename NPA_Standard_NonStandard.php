<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/NPA_ANALYSIS_REPORT.jrxml';
$space="' '";
$data = []; 
$faker = Faker\Factory::create('en_US');
$i=0;
$initial=0;
$ST="'ST'";
// $date1="'31/03/2021'";
$AC_TYPE = $_GET['AC_TYPE'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$BRANCH_NAME = $_GET['BRANCH'];
$BANK_NAME = $_GET['BANK_NAME'];
$date1 = $_GET['PRINT_DATE'];
$flag=$_GET['FLAG'];
// $date1=$_GET['TDate'];
$branch_name= str_replace("'","",$BRANCH_NAME);
$bank_name= str_replace("'","",$BANK_NAME);
$print_date= str_replace("'","",$date1);



if($flag==1){
$query=' SELECT  
SCHEMAST."S_APPL",SCHEMAST."S_NAME",SCHEMAST."S_APPL"||'.$space.' ||SCHEMAST."S_NAME"  "SCHEME"
, NPADATA."NPA_CLASS", NPADATA."SUB_CLASS_NO" , NPADATA."AC_SECURITY_AMT", 
NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", 
NPADATA."AC_NO", LNMASTER."AC_NAME", NPADATA."REPORT_DATE" ,  
NPADATA."AC_SANCTION_AMOUNT", 
LNMASTER."AC_SANCTION_DATE", LNMASTER."AC_OPDATE", 
NPADATA."LEDGER_BALANCE", NPADATA."OVERDUE_DATE", 
NPADATA."OVERDUE_AMOUNT", NPADATA."DUE_INSTALLMENT", 
NPADATA."AC_NPA_DATE", NPADATA."RECEIVABLE_INTEREST", 
NPADATA."PROV_ON_AMT",  
NPADATA."NPA_PROVISION_AMT" "SECURE_PROV_AMT", 
NPADATA."NPA_PERCENTAGE", NPADATA."UNSECURE_PROV_AMT",  
NPADATA."UNSECURE_PERCENTAGE", 
(CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT) + CAST(NPADATA."UNSECURE_PROV_AMT" AS FLOAT)) "NPA_PROVISION_AMT" 
FROM NPADATA, LNMASTER, SCHEMAST  WHERE  
NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"   
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"    
AND NPADATA."AC_NO" = LNMASTER."BANKACNO"   
AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"   
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID 
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE) 
AND CAST(NPADATA."AC_TYPE" AS INTEGER) IN ('.$AC_TYPE.')

AND NPADATA."NPA_CLASS" <> '.$ST.''; 

if($BRANCH_CODE != '0'){
    $query .='AND LNMASTER."BRANCH_CODE" IN ('.$BRANCH_CODE.')';
}

$query .=' Order By   NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO"';

}
// echo $query;

if($flag==0){
$query='SELECT  
SCHEMAST."S_APPL",SCHEMAST."S_NAME",SCHEMAST."S_APPL"||'.$space. ' ||SCHEMAST."S_NAME"  "SCHEME"
, NPADATA."NPA_CLASS", NPADATA."SUB_CLASS_NO" , NPADATA."AC_SECURITY_AMT",  
NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", 
NPADATA."AC_NO", LNMASTER."AC_NAME", NPADATA."REPORT_DATE" ,  
NPADATA."AC_SANCTION_AMOUNT", 
LNMASTER."AC_SANCTION_DATE", LNMASTER."AC_OPDATE", 
NPADATA."LEDGER_BALANCE", NPADATA."OVERDUE_DATE", 
NPADATA."OVERDUE_AMOUNT", NPADATA."DUE_INSTALLMENT", 
NPADATA."AC_NPA_DATE", NPADATA."RECEIVABLE_INTEREST", 
NPADATA."PROV_ON_AMT",  
NPADATA."NPA_PROVISION_AMT" "SECURE_PROV_AMT", 
NPADATA."NPA_PERCENTAGE", NPADATA."UNSECURE_PROV_AMT",  
NPADATA."UNSECURE_PERCENTAGE", 
(CAST(NPADATA."NPA_PROVISION_AMT" AS FLOAT) + CAST(NPADATA."UNSECURE_PROV_AMT" AS FLOAT)) "NPA_PROVISION_AMT" 
FROM NPADATA, LNMASTER, SCHEMAST  WHERE  
NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"   
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = LNMASTER."AC_TYPE"    
AND NPADATA."AC_NO" = LNMASTER."BANKACNO"   
AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"   
AND CAST(NPADATA."AC_TYPE" AS INTEGER) = SCHEMAST.ID 
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE) 
AND CAST(NPADATA."AC_TYPE" AS INTEGER) IN ('.$AC_TYPE.')

AND NPADATA."NPA_CLASS" ='.$ST.'';
if($BRANCH_CODE != '0'){
    $query .='AND LNMASTER."BRANCH_CODE" IN ('.$BRANCH_CODE.')';
}

$query .=' Order By   NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO"';


}
//  echo $query;
$sql =  pg_query($conn,$query);
while($row = pg_fetch_assoc($sql)){
     $GRAND_TOTAL = $GRAND_TOTAL + $row["AC_SANCTION_AMOUNT"];
    // echo $row['S_APPL'];
    // echo "<br>";

    // echo $row["AC_SANCTION_AMOUNT"];
    // echo "<br>";
   
    if(isset($varsd)){
        if($varsd == $row['S_APPL']){
            $sc[] = $row['AC_SANCTION_AMOUNT']; 
            $sumVar += $row['AC_SANCTION_AMOUNT'];
           // echo "if part";
        }
        else{
            $sumVar=0;
            $sc = array_diff($sc, $sc);
            $varsd = $row['S_APPL'];
            $sc[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar += $row['AC_SANCTION_AMOUNT'];
         //   echo "else1 part";
        }
    }else{
        $sumVar=0;
        $varsd = $row['S_APPL'];
        $sc[] = $row['AC_SANCTION_AMOUNT'];
        $sumVar += $row['AC_SANCTION_AMOUNT'];
       // echo "2nd else part";
    }
    $result[$varsd] = $sc;
    $sumArray[$varsd] = $sumVar;
    // echo $sumArray[$varsd];

    $GRAND_TOTAL1= $GRAND_TOTAL1 + $row["AC_SECURITY_AMT"];
    if(isset($varsd1)){
        if($varsd1 == $row['S_APPL']){
            $sc1[] = $row['AC_SECURITY_AMT'];
            $sumVar1 += $row['AC_SECURITY_AMT'];
            // echo $sumVar;
            // echo "if part";
        }
        else{
            $sumVar1=0;
            $sc1 = array_diff($sc1, $sc1);
            $varsd1 = $row['S_APPL'];
            $sc1[] = $row['AC_SECURITY_AMT'];
            $sumVar1 += $row['AC_SECURITY_AMT'];
        //    echo "else1 part";
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
    // echo $sumArray1[$varsd1];

    $GRAND_TOTAL2= $GRAND_TOTAL2 + $row["RECEIVABLE_INTEREST"];
    if(isset($varsd2)){
        if($varsd2 == $row['S_APPL']){
            $sc2[] = $row['RECEIVABLE_INTEREST'];
            $sumVar2 += $row['RECEIVABLE_INTEREST'];
            // echo $sumVar;
            // echo "if part";
        }
        else{
            $sumVar2=0;
            $sc2 = array_diff($sc2, $sc2);
            $varsd2 = $row['S_APPL'];
            $sc2[] = $row['RECEIVABLE_INTEREST'];
            $sumVar2 += $row['RECEIVABLE_INTEREST'];
        //    echo "else1 part";
        }
    }else{
        $sumVar2=0;
        $varsd2 = $row['S_APPL'];
        $sc2[] = $row['RECEIVABLE_INTEREST'];
        $sumVar2 += $row['RECEIVABLE_INTEREST'];
        // echo "2nd else part";
    }
    $result2[$varsd2] = $sc2;
    $sumArray2[$varsd2] = $sumVar2;


    $GRAND_TOTAL3= $GRAND_TOTAL3 + $row["NPA_PERCENTAGE"];
    if(isset($varsd3)){
        if($varsd3 == $row['S_APPL']){
            $sc3[] = $row['NPA_PERCENTAGE'];
            $sumVar3 += $row['NPA_PERCENTAGE'];
            // echo $sumVar;
            // echo "if part";
        }
        else{
            $sumVar3=0;
            $sc3 = array_diff($sc3, $sc3);
            $varsd3 = $row['S_APPL'];
            $sc3[] = $row['NPA_PERCENTAGE'];
            $sumVar3 += $row['NPA_PERCENTAGE'];
        //    echo "else1 part";
        }
    }else{
        $sumVar3=0;
        $varsd3 = $row['S_APPL'];
        $sc3[] = $row['NPA_PERCENTAGE'];
        $sumVar3 += $row['NPA_PERCENTAGE'];
        // echo "2nd else part";
    }
    $result3[$varsd3] = $sc3;
    $sumArray3[$varsd3] = $sumVar3;


    $GRAND_TOTAL4= $GRAND_TOTAL4 + $row["NPA_PROVISION_AMT"];
    if(isset($varsd4)){
        if($varsd4 == $row['S_APPL']){
            $sc4[] = $row['NPA_PROVISION_AMT'];
            $sumVar4 += $row['NPA_PROVISION_AMT'];
            // echo $sumVar;
            // echo "if part";
        }
        else{
            $sumVar4=0;
            $sc4 = array_diff($sc4, $sc4);
            $varsd4 = $row['S_APPL'];
            $sc4[] = $row['NPA_PROVISION_AMT'];
            $sumVar4 += $row['NPA_PROVISION_AMT'];
        //    echo "else1 part";
        }
    }else{
        $sumVar4=0;
        $varsd4 = $row['S_APPL'];
        $sc4[] = $row['NPA_PROVISION_AMT'];
        $sumVar4 += $row['NPA_PROVISION_AMT'];
        // echo "2nd else part";
    }
    $result4[$varsd4] = $sc4;
    $sumArray4[$varsd4] = $sumVar4;


    $GRAND_TOTAL5= $GRAND_TOTAL5 + $row["LEDGER_BALANCE"];
    if(isset($varsd5)){
        if($varsd5 == $row['S_APPL']){
            $sc5[] = $row['LEDGER_BALANCE'];
            $sumVar5 += $row['LEDGER_BALANCE'];
            // echo $sumVar;
            // echo "if part";
        }
        else{
            $sumVar5=0;
            $sc5 = array_diff($sc5, $sc5);
            $varsd5 = $row['S_APPL'];
            $sc5[] = $row['LEDGER_BALANCE'];
            $sumVar5 += $row['LEDGER_BALANCE'];
        //    echo "else1 part";
        }
    }else{
        $sumVar5=0;
        $varsd5 = $row['S_APPL'];
        $sc5[] = $row['LEDGER_BALANCE'];
        $sumVar5 += $row['LEDGER_BALANCE'];
        // echo "2nd else part";
    }
    $result5[$varsd5] = $sc5;
    $sumArray5[$varsd5] = $sumVar5;
    

    $temp =[    
        //"closing_balance" => (int)(abs($row['closing_balance'])),
        'AcNo'=>$row['AC_NO'], 
        'Ac_Name'=>$row['AC_NAME'], 
        'S_name'=>$row['S_NAME'],
        'S_Appl'=>$row['S_APPL'], 
        'Security_Amount'=>sprintf("%.2f",(int)(abs($row['AC_SECURITY_AMT'])) + 0.0 ) ,
        'NPA_Provision_Amount'=>$row['NPA_PROVISION_AMT'], 
        'NPA_Percentage'=>$row['NPA_PERCENTAGE'], 
        'Sanctioned_Amount'=>sprintf("%.2f",(int)(abs($row['AC_SANCTION_AMOUNT'])) + 0.0 ) ,
        'Ac_Balance'=>$row['LEDGER_BALANCE'],
        'NPA_Classification'=>$row['NPA_CLASS'],
        'Remaining_Int_Amount'=>$row['RECEIVABLE_INTEREST'],
        'BranchName'=>$branch_name,
        'BankName'=>$bank_name,
        'PrintDate'=>$print_date,
        'SA' =>sprintf("%.2f", ($sumArray[$varsd])).' '.$netType,
        'SEC_AMT'=>sprintf("%.2f", ($sumArray1[$varsd1])).' '.$netType,
        'REC_INT'=>sprintf("%.2f", ($sumArray2[$varsd2])).' '.$netType,
        'NPA_PERC'=>sprintf("%.2f", ($sumArray3[$varsd3])).' '.$netType,
        'NPA_PROV'=>sprintf("%.2f", ($sumArray4[$varsd4])).' '.$netType,
        'LEDGER_BAL'=>sprintf("%.2f", ($sumArray5[$varsd5])).' '.$netType,
        "Total_Sanction_Amount" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "Total_Security_Amount" =>sprintf("%.2f",($GRAND_TOTAL1) + 0.0),
        "Total_Receivable_Interest" =>sprintf("%.2f",($GRAND_TOTAL2) + 0.0),
        "Total_NPA_Perc" =>sprintf("%.2f",($GRAND_TOTAL3) + 0.0),
        "Total_NPA_Prov" =>sprintf("%.2f",($GRAND_TOTAL4) + 0.0),
        "Total_Ledger_Bal" =>sprintf("%.2f",($GRAND_TOTAL5) + 0.0),
        'flag'=>$flag,
        'type'=> 'Standard ',
        'type1'=>'Non Standard',

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