<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ScrollBookBoth.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$Startdate = $_GET['Startdate'];
$stype = $_GET['stype'];
$branch = $_GET['branch'];
$ccode = $_GET['ccode'];
$pcode = $_GET['pcode'];
$rdio = $_GET['rdio'];

$bankName = str_replace("'", "", $bankName);
$Startdate_ = str_replace("'", "", $Startdate);
$stype = str_replace("'", "", $stype);
// $branchName_ = str_replace("'", "", $branchName);
$ccode = str_replace("'", "", $ccode);
$pcode = str_replace("'", "", $pcode);

$dateformat = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$int = "'1'";
$title = '';


if($stype == 'cash'){
    $rdio = "('CS')";
    $title = 'Cash';
}else{
    $rdio = "('JV','TR')";
    $title = 'Transfer';
}
$DRCR = "'DRCR'";


$query='SELECT (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0) +
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("INTEREST_AMOUNT" as float) else 0 end, 0) +
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0)) as CrReceiptAmt, 
( coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER6_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("INTEREST_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0)) DrPaymentAmt,
 "TRAN_NO","TRAN_ACNOTYPE",historytran."TRAN_ACTYPE","CASHIER_CODE","TRAN_ACNO",
 "TOKEN_NO","SERIAL_NO", schemast."S_NAME",schemast."S_APPL",vwallmaster."ac_name",vwallmaster."ac_acnotype",ownbranchmaster."NAME",historytran."TRAN_TYPE", historytran."TRAN_DRCR" from historytran 
 Inner Join ownbranchmaster on historytran."BRANCH_CODE" = ownbranchmaster."id"
 Inner Join vwallmaster on cast (vwallmaster."ac_no" as bigint) = cast(historytran."TRAN_ACNO" as bigint)
 Inner Join schemast on historytran."TRAN_ACTYPE" = schemast."id"
 WHERE cast("TRAN_STATUS" as integer) = '.$int.' and "TRAN_TYPE" IN '.$rdio.' and
 cast("TRAN_DATE" as date) = '.$Startdate.'::date and historytran."BRANCH_CODE" = '.$branch.' 
 Union SELECT
 (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) +
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) +
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("INTEREST_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0) + 
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) +
  coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0)) as CrReceiptAmt, 
  ( coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER6_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("INTEREST_AMOUNT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0) + 
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) +
   coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0)) DrPaymentAmt,
   "TRAN_NO","TRAN_ACNOTYPE",cast(dailytran."TRAN_ACTYPE" as integer),
   "OFFICER_CODE","TRAN_ACNO","TOKEN_NO","SERIAL_NO", schemast."S_NAME",schemast."S_APPL",vwallmaster."ac_name",vwallmaster."ac_acnotype",ownbranchmaster."NAME",dailytran."TRAN_TYPE", dailytran."TRAN_DRCR" from dailytran
   Inner Join
   ownbranchmaster on dailytran."BRANCH_CODE" = ownbranchmaster."id" 
   Inner Join
   vwallmaster on cast (vwallmaster."ac_no" as bigint) = cast(dailytran."TRAN_ACNO" as bigint)
   Inner Join 
   schemast on cast(dailytran."TRAN_ACTYPE" as integer) = schemast."id" WHERE
   cast("TRAN_STATUS" as integer) = '.$int.' and "TRAN_TYPE" IN '.$rdio.' and cast("TRAN_DATE" as date) = '.$Startdate.'::date 
   and dailytran."BRANCH_CODE" = '.$branch.' ORDER BY "TRAN_NO" ASC';

            // echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_RECE = 0;
$GRAND_PAY = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_RECE = $GRAND_RECE + $row['crreceiptamt'];
    $GRAND_PAY = $GRAND_PAY + $row['drpaymentamt'];

    $tmp=[
        'TRAN_DRCR'=> $row['TRAN_DRCR'],
        'crreceiptamt'=> sprintf("%.2f",($row['crreceiptamt'] + 0.0 ) ),
        'drpaymentamt' => sprintf("%.2f",($row['drpaymentamt'] + 0.0 ) ),
        'TRAN_NO' => $row['TRAN_NO'],
        'TRAN_ACTYPE' => $row['TRAN_ACTYPE'],
        'CASHIER_CODE' => $row['CASHIER_CODE'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'TOKEN_NO' => $row['TOKEN_NO'],
        'SERIAL_NO' => $row['SERIAL_NO'],
        'S_NAME' => $row['S_NAME'],
        'ac_name' => $row['ac_name'],
        'ac_acnotype' => $row['ac_acnotype'],
        'NAME' => $row['NAME'],
        'TRAN_TYPE' => $row['TRAN_TYPE'],
        'paygtot' => sprintf("%.2f",($GRAND_PAY + 0.0 ) ) ,
        'recegtot' => sprintf("%.2f",($GRAND_RECE + 0.0 ) ),

        'bankName' => $bankName,
        'Startdate_' => $Startdate_,
        'stype' => $stype,
        'branch' => $branch,
        'ccode' => $ccode,
        'pcode' => $pcode,
        'rdio' => $rdio,
        'title' => $title,
        'schem_name' => $row['S_APPL'].' '.$row['S_NAME'],

        // 'line_Solid' => $line_Solid,
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
    
}  
?>
