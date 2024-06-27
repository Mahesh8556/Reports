<?php
ob_start();
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/NPA_ANALYSIS_REPORT1.jrxml';
$data = [];
$faker = Faker\Factory::create('en_US');
$BranchCode=$_GET['BRANCH_CODE'];
$constValue = "'980'";
$ST="'ST'";
$number="'980'";
$date1=$_GET['DATE1'];
$date2=$_GET['DATE2'];
$flag=$_GET['FLAG'];
$NUM1=$_GET['VAR1'];
$NUM2=$_GET['VAR2'];
$BRANCH_NAME= $_GET['BRANCH_NAME'];
$BANK_NAME=$_GET['BANK_NAME'];

$branch_name =str_replace("'", "", $BRANCH_NAME);
$bank_name =str_replace("'", "", $BANK_NAME);
$d1=str_replace("'", "", $date1);   
$d2=str_replace("'", "", $date2);

$query1_Gross_Advance = 'select SUM(cast("LEDGER_BALANCE" as float))/100000 "GROSS_ADVANCE1" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO"
where CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_Advance1 =  pg_query($conn,$query1_Gross_Advance);

$query2_Gross_Advance = 'select SUM(cast("LEDGER_BALANCE" as float))/100000 "GROSS_ADVANCE2" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO"
where CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date2.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_Advance2 =  pg_query($conn,$query2_Gross_Advance);

$query1_Gross_NPA = 'select SUM(cast("LEDGER_BALANCE" as float))/100000 "GROSS_NPA1" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO" where "NPA_CLASS" <> '.$ST.' 
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_NPA1 =  pg_query($conn,$query1_Gross_NPA);

$query2_Gross_NPA = 'select SUM(cast("LEDGER_BALANCE" as float))/100000 "GROSS_NPA2" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO" where "NPA_CLASS" <> '.$ST.' 
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date2.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_NPA2 =  pg_query($conn,$query2_Gross_NPA);

$query1_NPA_Provision ='select SUM(CAST("NPA_PROVISION_AMT" AS FLOAT))/100000 "NPA_PRV_AMT1" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO" where "NPA_CLASS" <> '.$ST.'
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date1.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_NPA_PROVISION1 =  pg_query($conn,$query1_NPA_Provision);

$query2_NPA_Provision = 'select SUM(CAST("NPA_PROVISION_AMT" AS FLOAT))/100000 "NPA_PRV_AMT2" from NPADATA
LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"= NPADATA."AC_NO" where "NPA_CLASS" <> '.$ST.'
AND CAST(NPADATA."REPORT_DATE" AS DATE) = CAST('.$date2.' AS DATE) 
AND LNMASTER."BRANCH_CODE" IN ('.$BranchCode.')';
$Gross_NPA_PROVISION2 = pg_query($conn,$query2_NPA_Provision);

echo $query2_Gross_Advance;

//consolidated
if($flag==0){
    //overdue
    $query1_Overdue='SELECT ABS(CAST(glconsolidatedledgerbalance('.$constValue.',cast('.$NUM1.' as character varying),'.$date1.',1,0) AS FLOAT)/100000) "vOIRAmount1"';
    $Overdue1 =  pg_query($conn,$query1_Overdue);
    
    $query2_Overdue='SELECT ABS(CAST(glconsolidatedledgerbalance('.$constValue.',cast('.$NUM1.' as character varying),'.$date2.',1,0) AS FLOAT)/100000) "vOIRAmount2"';
    $Overdue2 = pg_query($conn,$query2_Overdue);

    //anamat
    $query_Anamat1='SELECT ABS(CAST(glconsolidatedledgerbalance('.$number.',cast('.$NUM2.' as character varying),'.$date1.',1,0) AS FLOAT)/100000) "vAnamatAmt1"';
    $Anamat1 =  pg_query($conn,$query_Anamat1);
    //echo $query_Anamat1;

    $query_Anamat2='SELECT ABS(CAST(glconsolidatedledgerbalance('.$number.',cast('.$NUM2.' as character varying),'.$date2.',1,0) AS FLOAT)/100000) "vAnamatAmt2"';
    $Anamat2 = pg_query($conn,$query_Anamat2);
}

//Branchwise
if($flag==1){
    //overdue
    $query1_Overdue='SELECT ABS(CAST(ledgerbalance('.$constValue.',cast('.$NUM1.' as character varying),'.$date1.',1,102,0) AS FLOAT)/100000) "vOIRAmount1"'; 
    $Overdue1 =  pg_query($conn,$query1_Overdue);

    $query2_Overdue='SELECT ABS(CAST(ledgerbalance('.$constValue.',cast('.$NUM1.' as character varying),'.$date2.',1,0) AS FLOAT)/100000) "vOIRAmount2"';
    $Overdue2 =  pg_query($conn,$query2_Overdue);

    //anamat
    $query_Anamat1='SELECT ABS(CAST(ledgerbalance('.$number.',cast('.$NUM2.' as character varying),'.$date1.',1,102,0) AS FLOAT)/100000) "vAnamatAmt1"';
    $Anamat1 = pg_query($conn,$query_Anamat1);
    // echo $query_Anamat1;
    $query_Anamat2 = 'SELECT ABS(CAST(ledgerbalance('.$number.',cast('.$NUM2.' as character varying),'.$date2.',1,102,0) AS FLOAT)/100000) "vAnamatAmt2"';
    $Anamat2= pg_query($conn,$query_Anamat2);
}
$temp;
$data;
while($row = pg_fetch_assoc($Gross_Advance1)){
    $v= $row['GROSS_ADVANCE1'];
    
}
while($row = pg_fetch_assoc($Gross_Advance2)){
    $var =$row['GROSS_ADVANCE2'];
   
    
}
while($row = pg_fetch_assoc($Gross_NPA1)){
    $var1 =$row['GROSS_NPA1'];

    
}
while($row = pg_fetch_assoc($Gross_NPA2)){
    $var2=$row['GROSS_NPA2'];
    
}
while($row = pg_fetch_assoc($Gross_NPA_PROVISION1)){
    $var3=$row['NPA_PRV_AMT1'];
     
}

while($row = pg_fetch_assoc($Anamat1)){
    $var5=$row['vAnamatAmt1'];
      
}
while($row = pg_fetch_assoc($Anamat2)){
    $var6=$row['vAnamatAmt2'];
 
    
}
while($row = pg_fetch_assoc($Overdue1)){
    $var7=$row['vOIRAmount1'];

}
while($row = pg_fetch_assoc($Overdue2)){
    $var8=$row['vOIRAmount2'];

}
// $val1=($var1-($var7+$var5)-$var3);
// $val2=($v-($var7+$var5)-$var3);
// $val3=($var2-($var8+$var6));
// $val4=($var-($var8+$var6));
// $var11=0;
// $var12=0;
while($row = pg_fetch_assoc($Gross_NPA_PROVISION2)){
    $var4=$row['NPA_PRV_AMT2']; 
    $var11=($var1-($var7+$var5)-$var3);
    $var12=($var2-($var8+$var6)-$var4);
    $netad=($v-($var7+$var5)-$var3);
    $netad2=($var-($var8+$var6)-$var4);
    $temp=[
        'Gross_Advance1'=>sprintf("%.2f",($v)),
        'Gross_Advance2'=>sprintf("%.2f",($var)),
        'Gross_NPA1'=>sprintf("%.2f",($var1)),
        'Gross_NPA2'=>sprintf("%.2f",($var2)),
        'NPA_Provision'=>sprintf("%.2f",($var3)),
        'NPA_Provision2'=>sprintf("%.2f",($var4)),
        'date1'=>$d1,
        'date2'=>$d2,
        'Branch_name'=>$branch_name,
        'Bank_name'=>$bank_name,  
        'BranchCode'=>$BranchCode,
        'Percentage_of_Gross_NPA'=>isset($var1)? sprintf("%.2f",(($v/$var1)*100)):0,
        'Percentage_of_Gross_NPA2'=>isset($var2)? sprintf("%.2f",(($var/$var2)*100)):0,
        'Overdue_Interest_Reserve'=>abs(sprintf("%.2f",$var7)),
        'Overdue_Interest_Reserve1'=>abs(sprintf("%.2f",$var8)),
        'Cerdit_Amount_of_Anamat'=>abs(sprintf("%.2f",$var5)),
        'Credit_Amount_of_Anamat2'=>abs(sprintf("%.2f",$var6)),
        'Total_Deductions1'=>sprintf("%.2f",($var7+$var5)),
        'Total_Deductions2'=>sprintf("%.2f",($var8+$var6)),
        'Net_Advance'=>abs(sprintf("%.2f",($v-($var7+$var5)-$var3))),
        'Net_Advance2'=>abs(sprintf("%.2f",($var-($var8+$var6)-$var4))),
        'Net_NPA'=>abs(sprintf("%.2f",($var1-($var7+$var5)-$var3))),
        'Net_NPA2'=>abs(sprintf("%.2f",($var2-($var8+$var6)-$var4))),

        'Percentage_of_Net_NPA_With_Net_Advance'=>abs(sprintf("%.2f",(($var11/$netad)*100))),
        'Percentage_of_NPA_With_Net_Advance2'=>abs(sprintf("%.2f",(($var12/$netad2)*100)))
];
}
$data[0]=$temp;


ob_end_clean();
$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
?>