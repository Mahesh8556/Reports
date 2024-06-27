<?php
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/Loan_Cash_Credit_Ac_Open_Wise_Report.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
//connect mysql database connection 
//$conn = mysqli_connect('localhost','root','','test');
//get data from enquiry table
$date = "'02/08/2021'";
$query = ' SELECT LNMASTER."AC_ACNOTYPE",LNMASTER."AC_TYPE",LNMASTER."AC_NO",LNMASTER."AC_NAME",
LNMASTER."AC_EXPIRE_DATE",LNMASTER."AC_SANCTION_DATE" , LNMASTER."AC_SANCTION_AMOUNT", 
LNMASTER."AC_MONTHS" , LNMASTER."AC_SECURITY_AMT", SCHEMAST."S_NAME",LNMASTER."AC_OPDATE"
FROM 
( SELECT RENEWALHISTORY."AC_ACNOTYPE",RENEWALHISTORY."AC_TYPE",RENEWALHISTORY."AC_NO",
LNMASTER."AC_NAME",LNMASTER."AC_EXPIRE_DATE",LNMASTER."AC_SANCTION_DATE",LNMASTER."AC_OPDATE",
LNMASTER."AC_SANCTION_AMOUNT",LNMASTER."AC_MONTHS" , LNMASTER."AC_SECURITY_AMT",
MAX(RENEWALHISTORY."RENEWAL_DATE") as renewaldate,  	
CASE   WHEN RENEWALHISTORY."RENEWAL_DATE" IS NULL THEN LNMASTER."AC_OPDATE"  
ELSE RENEWALHISTORY."RENEWAL_DATE" END AC_OPDATE 	
FROM LNMASTER,RENEWALHISTORY WHERE COALESCE(cast(RENEWALHISTORY."AC_NO" as bigint),0) <> 0 
AND LNMASTER."AC_ACNOTYPE" = RENEWALHISTORY."AC_ACNOTYPE" 	
AND LNMASTER."AC_TYPE" = RENEWALHISTORY."AC_TYPE" 	
AND LNMASTER."AC_NO" = cast(RENEWALHISTORY."AC_NO" as bigint)	
GROUP BY RENEWALHISTORY."AC_ACNOTYPE",RENEWALHISTORY."AC_TYPE",RENEWALHISTORY."AC_NO",
LNMASTER."AC_NAME",LNMASTER."AC_EXPIRE_DATE",LNMASTER."AC_SANCTION_DATE",LNMASTER."AC_SANCTION_AMOUNT",
LNMASTER."AC_MONTHS" , LNMASTER."AC_SECURITY_AMT",RENEWALHISTORY."RENEWAL_DATE",LNMASTER."AC_OPDATE"
) LNMASTER , SCHEMAST , GUARANTERDETAILS,RENEWALHISTORY	
    WHERE LNMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE" 	
    AND LNMASTER."AC_TYPE" = SCHEMAST."S_APPL" 	
AND LNMASTER."AC_ACNOTYPE" = RENEWALHISTORY."AC_ACNOTYPE" 	
AND LNMASTER."AC_TYPE" = RENEWALHISTORY."AC_TYPE" 	
AND LNMASTER."AC_NO" = RENEWALHISTORY."AC_NO"	
AND LNMASTER."AC_ACNOTYPE" = GUARANTERDETAILS."AC_ACNOTYPE" 	
AND LNMASTER."AC_TYPE" = cast(GUARANTERDETAILS."AC_TYPE" as integer)  	
AND LNMASTER."AC_NO" = GUARANTERDETAILS."AC_NO" 
and (cast(guaranterdetails."EXP_DATE" as date) is null 
or cast(guaranterdetails."EXP_DATE" as date) > '.$date.' :: date) 	
AND  CASE   WHEN cast(RENEWALHISTORY."RENEWAL_DATE" as date) IS NULL THEN 
cast(LNMASTER."AC_OPDATE" as date) ELSE cast(RENEWALHISTORY."RENEWAL_DATE" as date) END >= '.$date.' :: date 
AND  CASE   WHEN cast(RENEWALHISTORY."RENEWAL_DATE" as date) IS NULL THEN 
cast(LNMASTER."AC_OPDATE" as date) ELSE cast(RENEWALHISTORY."RENEWAL_DATE" as date) END <= '.$date.' :: date  
ORDER BY LNMASTER."AC_ACNOTYPE",LNMASTER."AC_TYPE",LNMASTER."AC_OPDATE"';
$sql = pg_query($conn,$query);


$i = 0;
$GRAND_TOTAL = 0;
$TOTAL = 0;
$type = 0;
while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_SECURITY_AMT'];
    $TOTAL = $TOTAL + $row['AC_SANCTION_AMOUNT'];
    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $subAC_SECURITY_AMT = $subAC_SECURITY_AMT + $row['AC_SECURITY_AMT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $subAC_SECURITY_AMT = 0;
        $subAC_SECURITY_AMT = $subAC_SECURITY_AMT + $row['AC_SECURITY_AMT'];
    }
    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $subAC_SANCTION_AMOUNT = $subAC_SECURITY_AMT + $row['AC_SANCTION_AMOUNT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $subAC_SANCTION_AMOUNT = 0;
        $subAC_SANCTION_AMOUNT = $subAC_SANCTION_AMOUNT + $row['AC_SANCTION_AMOUNT'];
    }
    $tmp=[
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'], 
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_EXPIRE_DATE' => $row['AC_EXPIRE_DATE'],
        'AC_SANCTION_DATE' => $row['AC_SANCTION_DATE'],
        'AC_SANCTION_AMOUNT' => $row['AC_SANCTION_AMOUNT'],
        'AC_MONTHS' => $row['AC_MONTHS'],
        'AC_SECURITY_AMT' => $row['AC_SECURITY_AMT'],
        'S_NAME' => $row['S_NAME'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'field5' =>$GRAND_TOTAL,
        'field4' =>$TOTAL,
        'field3' =>$subAC_SECURITY_AMT,
        'field2' =>$subAC_SANCTION_AMOUNT,
    ];
    $data[$i]=$tmp;
    $i++;
}


$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');