<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/nparegisterpercentage.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');



$bankname = $_GET['bankname'];
$flagRadio = $_GET['flagRadio'];
$Branch = $_GET['Branch'];
$sdate = $_GET['Npa_Date'];
// $edate = $_GET['edate'];
$ACNOTYPE=$_GET['ACNOTYPE'];
$AC_TYPE=$_GET['AC_TYPE'];
$From=$_GET['From'];
$radioValue=$_GET['radioValue'];

$To=$_GET['To'];
$percentZero=$_GET['percentZero'];
$percentTen=$_GET['percentTen'];
$percentFive=$_GET['percentFive'];
$percentTwenty=$_GET['percentTwenty'];
// $AC_TYPE="'10'";
$trandr="'D'";
$transtatus="'1'";
$CHG="'CHG'";
$NTG="'NTG'";
$BRANCH_CODE  = $_GET['BRANCH_CODE'];
$ST="'ST'";
$SS="'SS'";
$dd="'DD/MM/YYYY'";
// $TD="'TD'";




$Branch1 = str_replace("'", "", $Branch);
$bankname1 = str_replace("'", "", $bankname);
$sdate1 = str_replace("'", "", $sdate);
$percentZero = str_replace("'", "", $percentZero);
$percentTen = str_replace("'", "", $percentTen);
$percentFive = str_replace("'", "", $percentFive);
$percentTwenty = str_replace("'", "", $percentTwenty);

// $edate1 = str_replace("'", "", $edate);
if($BRANCH_CODE=='0'){
$query='SELECT NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", NPADATA."AC_NO", NPADATA."AC_NPA_DATE", 
NPADATA."AC_OPDATE"  , NPADATA."AC_EXPIRE_DATE", NPADATA."AC_SANCTION_AMOUNT", NPADATA."OVERDUE_DATE",
NPADATA."AC_SECURITY_AMT", NPADATA."LEDGER_BALANCE"  , NPADATA."OVERDUE_AMOUNT", 
NPADATA."NPA_PROVISION_AMT", NPADATA."RECEIVABLE_INTEREST", NPADATA."NPA_CLASS", 
NPADATA."SUB_CLASS_NO"  , NPADATA."NPA_PERCENTAGE", LNMASTER."AC_NAME", 
SCHEMAST."S_NAME"  FROM NPADATA , LNMASTER , SCHEMAST, OWNBRANCHMASTER
Where NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"  
AND NPADATA."AC_TYPE" = LNMASTER."AC_TYPE"  
AND CAST(NPADATA."AC_NO" AS BIGINT) = CAST(LNMASTER."BANKACNO" AS BIGINT)
AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"  
AND NPADATA."AC_TYPE" = SCHEMAST.ID
AND NPADATA."AC_TYPE" = '.$AC_TYPE.' 
AND OWNBRANCHMASTER."CODE"= CAST(SUBSTR(NPADATA."AC_NO",4,3) AS INTEGER)
AND CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$sdate.','.$dd.') 

AND OWNBRANCHMASTER.ID=2


AND NPADATA."AC_SANCTION_AMOUNT" BETWEEN '.$From.' and '.$To.'
AND NPADATA."LEDGER_BALANCE" BETWEEN '.$From.' and '.$To.'
Order By NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO" ';
}
else{
    $query='SELECT NPADATA."AC_ACNOTYPE", NPADATA."AC_TYPE", NPADATA."AC_NO", NPADATA."AC_NPA_DATE", 
    NPADATA."AC_OPDATE"  , NPADATA."AC_EXPIRE_DATE", NPADATA."AC_SANCTION_AMOUNT", NPADATA."OVERDUE_DATE",
    NPADATA."AC_SECURITY_AMT", NPADATA."LEDGER_BALANCE"  , NPADATA."OVERDUE_AMOUNT", 
    NPADATA."NPA_PROVISION_AMT", NPADATA."RECEIVABLE_INTEREST", NPADATA."NPA_CLASS", 
    NPADATA."SUB_CLASS_NO"  , NPADATA."NPA_PERCENTAGE", LNMASTER."AC_NAME", 
    SCHEMAST."S_NAME"  FROM NPADATA , LNMASTER , SCHEMAST, OWNBRANCHMASTER
    Where NPADATA."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"  
    AND NPADATA."AC_TYPE" = LNMASTER."AC_TYPE"  
    AND CAST(NPADATA."AC_NO" AS BIGINT) = CAST(LNMASTER."BANKACNO" AS BIGINT)
    AND NPADATA."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"  
    AND NPADATA."AC_TYPE" = SCHEMAST.ID
    AND NPADATA."AC_TYPE" = '.$AC_TYPE.' 
    AND OWNBRANCHMASTER."CODE"= CAST(SUBSTR(NPADATA."AC_NO",4,3) AS INTEGER)
    AND CAST(NPADATA."REPORT_DATE" AS DATE) = TO_DATE('.$sdate.','.$dd.') 
    
    AND OWNBRANCHMASTER.ID=2
    
    
    AND NPADATA."AC_SANCTION_AMOUNT" BETWEEN '.$From.' and '.$To.'
    AND NPADATA."LEDGER_BALANCE" BETWEEN '.$From.' and '.$To.'
    Order By NPADATA."AC_ACNOTYPE" , NPADATA."AC_TYPE" , NPADATA."AC_NO" ';
    }
	// echo $query1 ;

$sql =  pg_query($conn,$query);
$GRAND_TOTAL1 = 0;
$i = 0;

$provisionTotal0 = 0;
$provisionTotal1 = 0;
$provisionTotal2 = 0;
$provisionTotal3 = 0;

// echo $flagRadio;
// $checktype;
//  $flagRadio == 1? $checktype='true': $checktype='false';
//  echo $checktype;

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

      $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["closing_balance"];
	  $date = new DateTime($row['tran_date']);
	  if ($row['closing_balance'] < 0) {
	    $netType = 'Cr';
	} else {
	    $netType = 'Dr';
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
    $tmp=[
      'AC_NO' => $row['AC_NO'],
        'AC_OPDATE' => $row['AC_OPDATE'],
		'AC_EXPIRE_DATE'=>$row['AC_EXPIRE_DATE'],
		'AC_SANCTION_AMOUNT'=>$row['AC_SANCTION_AMOUNT'],
		'AC_SECURITY_AMT'=>$row['AC_SECURITY_AMT'],
		'OVERDUE_AMOUNT'=>$row['OVERDUE_AMOUNT'],
		'RECEIVABLE_INTEREST'=>$row['RECEIVABLE_INTEREST'],
		'LEDGER_BALANCE'=>$row['LEDGER_BALANCE'],
		'NPA_CLASS'=>$row['NPA_CLASS'],
		'NPA_DATE'=>$row['AC_NPA_DATE'],
		'LEDGER_BALANCE'=>$row['LEDGER_BALANCE'],
		'OVERDUE_DATE'=>$row['OVERDUE_DATE'],
        'NPA_PROVISION_AMT'=>$row['NPA_PROVISION_AMT'],
        'NPA_PERCENTAGE'=>$row['NPA_PERCENTAGE'],
        // 'TRAN_DATE' => $date->format('d/m/Y'),
        'CLOSING_BALANCE' =>sprintf("%.2f", (abs($row['closing_balance']+0.0))),
        'AC_NAME' => $row['AC_NAME'],
        'ACNOTYPE' => $ACNOTYPE,
        'AC_TYPE' => $AC_TYPE,
        'Branch' => $Branch1,
        'sdate' => $sdate1,
        'BRANCH_CODE'=>$BRANCH_CODE,
		'scheme' => $row["S_APPL"].' '. $row['S_NAME'],
        // 'edate' => $edate1,
        'bankname' => $bankname1,
		'netType' => $netType,
        'percentZero'=>$percentZero,
        'percentTen'=>$percentTen,
        'percentFive'=>$percentFive,
        'percentTwenty'=>$percentTwenty,
        // "total" => sprintf("%.2f", (abs($GRAND_TOTAL1+0.0))),
        'provisionTotal0' => $provisionTotal0,
        'provisionTotal1' => $provisionTotal1,
        'provisionTotal2' => $provisionTotal2,
        'provisionTotal3' => $provisionTotal3,
        "SAmnt" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        'SecurityAmnt'=> sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        'OvrDAmt' => sprintf("%.2f",($GRAND_TOTAL3) + 0.0 ) ,
        'RCInterest'=>sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        "From"=>$From,
        "To"=>$To,
        "flag" => 1,
        "flagRadio"=>$flagRadio,
        "zero"=> '0.00',
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
// 
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>    