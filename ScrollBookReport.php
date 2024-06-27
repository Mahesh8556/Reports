<?php
include "main.php";
ob_start(); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ScrollBookReport.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
$conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

if($conn){
    echo 'success';
}
else{
    echo 'fail';
}

$startDate = "'01/04/2020'";
$endDate = "'01/06/2020'";
$dateformat = "'DD/MM/YYYY'";
$DRCR = "'C'";
$c = "'C'";
$d = "'D'";
$int = "'0'";
$schm = "'CS'";
$line_Solid = 'D';

$query = 'SELECT
         (coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER1_AMOUNT" else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER2_AMOUNT" else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER3_AMOUNT"else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER4_AMOUNT" else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER5_AMOUNT" else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER6_AMOUNT"   else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER7_AMOUNT" else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER8_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER9_AMOUNT"  else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$c.' Then "OTHER10_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$c.' Then "DD_COMMISSION_AMT"  else 0 end, 0)) as CrReceiptAmt,
          ( coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER1_AMOUNT"  else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER2_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER3_AMOUNT"  else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER4_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER5_AMOUNT"  else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER6_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER7_AMOUNT"  else 0 end, 0) + 
         coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER8_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER9_AMOUNT"  else 0 end, 0) +
          coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER10_AMOUNT"  else 0 end, 0)
         + coalesce(case when "TRAN_DRCR" = '.$d.' Then "DD_COMMISSION_AMT" else 0 end, 0)) DrPaymentAmt,
         "TRAN_NO","TRAN_ACNOTYPE",historytran."TRAN_ACTYPE","CASHIER_CODE","TRAN_ACNO",
		 "TOKEN_NO","S_NAME",dpmaster."AC_NAME",ownbranchmaster."NAME",historytran."TRAN_TYPE"
          from schemast,historytran 
          Inner Join ownbranchmaster on
		  historytran."BRANCH_CODE" = ownbranchmaster."id"
		  Inner Join dpmaster on
          cast (dpmaster."BANKACNO" as bigint) = historytran."TRAN_ACNO"
          WHERE cast("TRAN_STATUS" as integer) = '.$int.' and
          "TRAN_TYPE" = '.$schm.' and
          "TRAN_DRCR" = '.$DRCR.'     
         and cast("TRAN_DATE" as date) 
         between to_date('.$startDate.','.$dateformat.') and to_date('.$endDate.','.$dateformat.') ';
          

$sql =  pg_query($conn,$query);


$i = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'TRAN_NO' => $row['TRAN_NO'],
        'SCROLL_NO' => $row['SCROLL_NO'],
        'SCHEME'=> $row['SCHEME'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'ACCOUNT_NAME' => $row['ACCOUNT_NAME'],
        'crreceiptamt' => $row['crreceiptamt'],
        'drpaymentamt' => $row['drpaymentamt'],
        'CASHIER_CODE' => $row['CASHIER_CODE'],
        'TOKEN_NO' => $row['TOKEN_NO'],
        'TRAN_ACNOTYPE' => $row['TRAN_ACNOTYPE'],
        'TRAN_ACTYPE' => $DRCR,
        'S_NAME' => $row['S_NAME'],
        'AC_NAME'=> $row['AC_NAME'],
        'NAME'=> $row['NAME'],
        'TRAN_DRCR'=> $row['TRAN_DRCR'],
        'line_Solid'=> $line_Solid,
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>   

