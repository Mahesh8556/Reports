<?php

include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/customeridwisedopositeloaninterest.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect mysql database connection 
// $conn = mysqli_connect('localhost','root','','cbsdb');
//get data from enquiry table

$date = "'10/08/2022'";
$df = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$dp = "'DP'";
$ln = "'LN'";

$query = 'SELECT DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE",DPMASTER."AC_NO",DPMASTER."AC_NAME",customeraddress."AC_ADDR",
DPMASTER."AC_REF_RECEIPTNO",DPMASTER."AC_CUSTID",DEPOTRAN."TRAN_DATE",schemast."S_APPL",syspara."ADDRESS",
COALESCE(CASE DEPOTRAN."IS_INTEREST_ENTRY" WHEN 0 THEN 0 ELSE CASE DEPOTRAN."TRAN_DRCR" WHEN '.$c.' 
THEN cast(DEPOTRAN."TRAN_AMOUNT" as float) ELSE 0 END END, 0) INTEREST_AMOUNT , 
COALESCE(CASE DEPOTRAN."TRAN_DRCR" WHEN '.$d.' THEN 
cast(DEPOTRAN."INTEREST_AMOUNT" as float) ELSE 0 END,0) RECPAY_INTEREST_AMOUNT,
'.$dp.' MASTER_TYPE  FROM DEPOTRAN, syspara, DPMASTER  
Inner join schemast on dpmaster."AC_TYPE" = schemast."id"
INNER JOIN CUSTOMERADDRESS ON DPMASTER."AC_CUSTID" = CUSTOMERADDRESS."id"

WHERE 	
DPMASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE"	
AND DPMASTER."AC_TYPE" = cast(DEPOTRAN."TRAN_ACTYPE" as integer)	
AND DPMASTER."AC_NO" = cast(DEPOTRAN."TRAN_ACNO" as bigint)  
AND cast(DEPOTRAN."TRAN_DATE" as date) >= TO_DATE('.$date.','.$df.')  
AND cast(DEPOTRAN."TRAN_DATE" as date) <= TO_DATE('.$date.','.$df.')
AND DPMASTER."AC_CUSTID" =616			
Union 
SELECT LNMASTER."AC_ACNOTYPE",LNMASTER."AC_TYPE",LNMASTER."AC_NO",LNMASTER."AC_NAME",customeraddress."AC_ADDR",
NULL AC_REF_RECEIPTNO,LNMASTER."AC_CUSTID",LOANTRAN."TRAN_DATE",schemast."S_APPL",syspara."ADDRESS",
COALESCE(CASE LOANTRAN."TRAN_DRCR" WHEN '.$d.' THEN cast(LOANTRAN."TRAN_AMOUNT" as integer) ELSE 0 END,0) INTEREST_AMOUNT,
COALESCE(CASE LOANTRAN."TRAN_DRCR" WHEN '.$d.' THEN cast(LOANTRAN."INTEREST_AMOUNT" as integer) ELSE 0 END,0) RECPAY_INTEREST_AMOUNT,
'.$ln.' MASTER_TYPE 
FROM LOANTRAN,  syspara, LNMASTER
Inner join schemast on lnmaster."AC_TYPE" = schemast."id"
INNER JOIN CUSTOMERADDRESS ON LNMASTER."AC_CUSTID" = CUSTOMERADDRESS."id"

WHERE cast(LOANTRAN."IS_INTEREST_ENTRY" as integer) <> 0 
     AND LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" 
     AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as integer)
     AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as bigint)  
     AND cast(LOANTRAN."TRAN_DATE" as date) >= TO_DATE('.$date.','.$df.') 
     AND cast(LOANTRAN."TRAN_DATE" as date) <= TO_DATE('.$date.','.$df.')
     AND LNMASTER."AC_CUSTID" =616
Union 
SELECT LNMASTER."AC_ACNOTYPE",LNMASTER."AC_TYPE",LNMASTER."AC_NO",LNMASTER."AC_NAME",customeraddress."AC_ADDR",
NULL AC_REF_RECEIPTNO,LNMASTER."AC_CUSTID",LOANTRAN."TRAN_DATE",0 INTEREST_AMOUNT,syspara."ADDRESS", schemast."S_APPL",  
(COALESCE(CASE LOANTRAN."TRAN_DRCR" WHEN '.$c.' THEN cast(LOANTRAN."INTEREST_AMOUNT" as float) ELSE 0 END,0) + 
 COALESCE(CASE LOANTRAN."TRAN_DRCR" WHEN '.$c.' THEN cast(LOANTRAN."RECPAY_INT_AMOUNT" as float) ELSE 0 END,0) + 
 COALESCE(CASE LOANTRAN."TRAN_DRCR" WHEN '.$c.' THEN cast(LOANTRAN."PENAL_INTEREST" as float) ELSE 0 END,0)) RECPAY_INTEREST_AMOUNT, 
 '.$ln.' MASTER_TYPE FROM LOANTRAN, syspara, LNMASTER 
Inner join schemast on lnmaster."AC_TYPE" = schemast."id"
INNER JOIN CUSTOMERADDRESS ON LNMASTER."AC_CUSTID" = CUSTOMERADDRESS."id"
WHERE  
    LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" 
     AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as integer)
     AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as bigint)  
     AND cast(LOANTRAN."TRAN_DATE" as date) >= TO_DATE('.$date.','.$df.')
     AND cast(LOANTRAN."TRAN_DATE" as date) <= TO_DATE('.$date.','.$df.') 
     AND LNMASTER."AC_CUSTID" =616 ';

$sql = pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0;
$TOTAL = 0;
$type = 0;
    
while($row = pg_fetch_assoc($sql)){

    // print_r($row);

    $GRAND_TOTAL = $GRAND_TOTAL + $row['recpay_interest_amount'];
    $TOTAL = $TOTAL + $row['interest_amount'];

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $subrecpay_interest_amount = $schemeledger + $row['recpay_interest_amount'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $subrecpay_interest_amount = 0;
        $subrecpay_interest_amount = $subrecpay_interest_amount + $row['recpay_interest_amount'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $subinterest_amount = $subinterest_amount + $row['interest_amount'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $subinterest_amount = 0;
        $subinterest_amount = $subinterest_amount + $row['interest_amount'];
    };
    
    $tmp=[
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        'AC_CUSTID' => $row['AC_CUSTID'],
        'TRAN_DATE' => $row['TRAN_DATE'],
        'interest_amount' => $row['interest_amount'],
        'recpay_interest_amount' => $row['recpay_interest_amount'],
        'master_type' => $row['master_type'],
        'S_APPL' => $row['S_APPL'],
        'field2' => $GRAND_TOTAL,
        'field3' => $TOTAL,
        'field4' => $subrecpay_interest_amount,
        'field5' => $subinterest_amount,
        'ADDRESS' => $row['ADDRESS'],
        'AC_ADDR' => $row['AC_ADDR']


    ];  
    $data[$i]=$tmp;
    $i++;
}


$config = ['driver'=>'array','data'=>$data];
// print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');