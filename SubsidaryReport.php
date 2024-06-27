<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Subsidaryreport.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");


$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$scheme_code = $_GET['scheme_code'];
$Rdio  = $_GET['Rdio'];
$branch = $_GET['branch'];

// echo $startDate ;
$bankName = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
$scheme_code = str_replace("'", "", $scheme_code);
$Rdio = str_replace("'", "", $Rdio);

$dateformat = "'DD/MM/YYYY'";
$schm1 = "'CS'";
$schm2 = "'TR'";
$d = "'D'";
$c = "'C'";
$TD = "'TD'";
// $B = "'KALE'";

$query = 'SELECT 
(case when "TRAN_TYPE" = '.$schm1.' then '.$schm1.' else
 '.$schm1.' end) as TRAN_TYPE,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("TRAN_AMOUNT" as float) else 0 end, 0) as CrTranAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("TRAN_AMOUNT" as float) else 0 end, 0) as DrTranAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then
 cast("INTEREST_AMOUNT" as float) else 0 end, 0) as CrIntAmt ,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("INTEREST_AMOUNT" as float) else 0 end, 0) as DrIntAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) as CrRecpayIntAmt ,
coalesce(case when "TRAN_DRCR" ='.$d.' Then 
cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) as DrRecpayIntAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) as CrPenalIntAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) as DrPenalIntAmt ,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0) as CrRecPenalIntAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0) as DrRecPenalIntAmt,
 (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT"  as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT"  as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0)) as CrOtherAmt,
 ( coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER6_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER9_AMOUNT" as float) else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER10_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0)) DrOtherAmt,
cast("TRAN_ACNO" as bigint),dailytran."TRAN_ACNOTYPE",dailytran."NARRATION",dailytran."USER_CODE",dailytran."OFFICER_CODE",dailytran."TRAN_DATE",
dailytran."TRAN_NO",ownbranchmaster."NAME",cast(dailytran."TRAN_ACTYPE" as integer),ownbranchmaster."CODE",
schemast."S_NAME",schemast."S_APPL"
FROM dailytran
Inner join ownbranchmaster on
dailytran."BRANCH_CODE" = ownbranchmaster."id" 
Inner Join schemast on 
cast(dailytran."TRAN_ACTYPE" as integer) = schemast."id"
where cast("TRAN_STATUS" as integer)=1 and 
cast("TRAN_DATE" as date) = '.$startDate.'::date 
and dailytran."BRANCH_CODE" = '.$branch.'
and cast(dailytran."TRAN_ACTYPE" as integer) = '.$scheme_code.'
Union 
SELECT 
(case when "TRAN_TYPE" = '.$schm1.' then '.$schm1.' else
 '.$schm1.' end) as TRAN_TYPE,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("TRAN_AMOUNT" as float) else 0 end, 0) as CrTranAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("TRAN_AMOUNT" as float) else 0 end, 0) as DrTranAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then
 cast("INTEREST_AMOUNT" as float) else 0 end, 0) as CrIntAmt ,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("INTEREST_AMOUNT" as float) else 0 end, 0) as DrIntAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) as CrRecpayIntAmt ,
coalesce(case when "TRAN_DRCR" ='.$d.' Then 
cast("RECPAY_INT_AMOUNT" as float) else 0 end, 0) as DrRecpayIntAmt,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) as CrPenalIntAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("PENAL_INT_AMOUNT" as float) else 0 end, 0) as DrPenalIntAmt ,
coalesce(case when "TRAN_DRCR" ='.$c.' Then 
cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0) as CrRecPenalIntAmt,
coalesce(case when "TRAN_DRCR" = '.$d.' Then
 cast("REC_PENAL_INT_AMOUNT" as float) else 0 end, 0) as DrRecPenalIntAmt,
 (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT" as float)   else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float)  else 0 end, 0)) as CrOtherAmt,
 ( coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER1_AMOUNT" as float)  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER2_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER3_AMOUNT" as float)  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER4_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER5_AMOUNT" as float)  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER6_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER7_AMOUNT" as float)  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER8_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("OTHER10_AMOUNT" as float)  else 0 end, 0)
+ coalesce(case when "TRAN_DRCR" = '.$d.' Then cast("DD_COMMISSION_AMT" as float) else 0 end, 0)) DrOtherAmt,
cast("TRAN_ACNO" as bigint),historytran."TRAN_ACNOTYPE",historytran."NARRATION",historytran."USER_CODE",historytran."OFFICER_CODE",
historytran."TRAN_DATE",historytran."TRAN_NO",ownbranchmaster."NAME",historytran."TRAN_ACTYPE",ownbranchmaster."CODE",
schemast."S_NAME",schemast."S_APPL"
FROM historytran 
Inner join ownbranchmaster on
historytran."BRANCH_CODE" = ownbranchmaster."id"  
Inner Join schemast on
historytran."TRAN_ACTYPE" = schemast."id"
where cast("TRAN_STATUS" as integer)=1 and 
cast("TRAN_DATE" as date) = '.$startDate.'::date
and historytran."BRANCH_CODE" = '.$branch.'
and historytran."TRAN_ACTYPE" = '.$scheme_code.' ';

//  echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;
$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
$GRAND_TOTAL3 = 0;
$GRAND_TOTAL4 = 0;
$GRAND_TOTAL5 = 0;
$GRAND_TOTAL8 = 0;
$GRAND_TOTAL9 = 0;
$GRAND_TOTAL10 = 0;
$GRAND_TOTAL11 = 0;
$GRAND_TOTAL12 = 0;
$GRAND_TOTAL13 = 0;
$GRAND_TOTAL14 = 0;
$GRAND_TOTAL15 = 0;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql)){
    $total_voucher = $total_voucher + 1;
    $GRAND_TOTAL6 = 0;
    $GRAND_TOTAL7 = 0;

    $GRAND_TOTAL = $GRAND_TOTAL + round($row['drtranamt'],2);
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + round($row['drotheramt'],2);
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + round($row['drintamt'],2);
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + round($row['crtranamt'],2);
    $GRAND_TOTAL4 = $GRAND_TOTAL4 + round($row['crintamt'],2);
    $GRAND_TOTAL5 = $GRAND_TOTAL5 + round($row['crotheramt'],2);
    $GRAND_TOTAL6 = $GRAND_TOTAL6 + round($row['drtranamt'],2) + round($row['drotheramt'],2)
                                  + round($row['drintamt'],2) + round($row['drrecpayintamt'],2)
                                  + round($row['drpenalintamt'],2) + round($row['drotheramt'],2);
    $GRAND_TOTAL7 = $GRAND_TOTAL7 + round($row['crtranamt'],2) + round($row['crintamt'],2)
                                  + round($row['crrecpayintamt'],2) + round($row['crrecpenalintamt'],2)
                                  + round($row['crpenalintamt'],2) + round($row['crotheramt'],2);
    $GRAND_TOTAL8 = $GRAND_TOTAL8 + $GRAND_TOTAL6 ;
    $GRAND_TOTAL9 = $GRAND_TOTAL9 + $GRAND_TOTAL7 ;   
    $GRAND_TOTAL10 = $GRAND_TOTAL10 + round($row['drrecpayintamt'],2);
    $GRAND_TOTAL11 = $GRAND_TOTAL11 + round($row['drrecpenalintamt'],2);
    $GRAND_TOTAL12 = $GRAND_TOTAL12 + round($row['drpenalintamt'],2);
    $GRAND_TOTAL13 = $GRAND_TOTAL13 + round($row['crrecpayintamt'],2);
    $GRAND_TOTAL14 = $GRAND_TOTAL14 + round($row['crrecpenalintamt'],2);
    $GRAND_TOTAL15 = $GRAND_TOTAL15 + round($row['crpenalintamt'],2);                     
    $tranno = '';
    if(strlen($row['TRAN_ACNO']) == 15){
        $tranno = substr($row['TRAN_ACNO'],9);
    }else{
        $tranno = $row['TRAN_ACNO'];
    }

    $GLName = '';
    if($row['S_APPL'] == '980'){
        $getGLName = pg_query($conn,'select * from acmaster where id='.$row['TRAN_ACNO']);
        while($row1 = pg_fetch_assoc($getGLName)){
            $GLName = $row1['AC_NAME'];
        }
    }
    $tmp=[
        'TRAN_ACNO' => $tranno,
        'NARRATION'=> $row['NARRATION'],
        'drtranamt' => round($row['drtranamt'],2),
        'drtranamt' =>sprintf("%.2f",($row['drtranamt'] + 0.0 ) ),
        'drintamt' => round($row['drintamt'],2),
        'drintamt' =>sprintf("%.2f",($row['drintamt'] + 0.0 ) ),
        'drrecpayintamt' => round($row['drrecpayintamt'],2),
        'drrecpayintamt' =>sprintf("%.2f",($row['drrecpayintamt'] + 0.0 ) ),
        'drrecpenalintamt'=> round($row['drrecpenalintamt'],2),
        'drrecpenalintamt' =>sprintf("%.2f",($row['drrecpenalintamt'] + 0.0 ) ),
        'drpenalintamt'=> round($row['drpenalintamt'],2),
        'drpenalintamt' =>sprintf("%.2f",($row['drpenalintamt'] + 0.0 ) ),
        'drotheramt'=> round($row['drotheramt'],2),   
        'drotheramt' =>sprintf("%.2f",($row['drotheramt'] + 0.0 ) ),
        'crtranamt' => round($row['crtranamt'],2),
        'crtranamt' =>sprintf("%.2f",($row['crtranamt'] + 0.0 ) ),
        'crintamt' => round($row['crintamt'],2),
        'crintamt' =>sprintf("%.2f",($row['crintamt'] + 0.0 ) ),
        'crrecpayintamt' => round($row['crrecpayintamt'],2),
        'crrecpayintamt' =>sprintf("%.2f",($row['crrecpayintamt'] + 0.0 ) ),
        'crrecpenalintamt'=> round($row['crrecpenalintamt'],2),
        'crrecpenalintamt' =>sprintf("%.2f",($row['crrecpenalintamt'] + 0.0 ) ),
        'crpenalintamt'=> round($row['crpenalintamt'],2),
        'crpenalintamt' =>sprintf("%.2f",($row['crpenalintamt'] + 0.0 ) ),
        'crotheramt'=> round($row['crotheramt'],2),
        'crotheramt' =>sprintf("%.2f",($row['crotheramt'] + 0.0 ) ),
        'USER_CODE' => $row['USER_CODE'],
        'TRAN_ACTYPE' => $row['TRAN_ACTYPE'],
        'OFFICER_CODE' => $row['OFFICER_CODE'],
        'TRAN_ACNOTYPE' => $row['TRAN_ACNOTYPE'],
        'TRAN_DATE' => $row['TRAN_DATE'],
        'TRAN_NO' => $row['TRAN_NO'],
        'NAME' => $row['NAME'],
        'CODE' => $row['CODE'],
        'S_APPL' => $row['S_APPL'],
        'S_NAME' => $row['S_NAME'],
        'GLNAME' => $GLName,

        'princtotal' => $GRAND_TOTAL.'.00',
        'dothertotal' => $GRAND_TOTAL1.'.00',
        'dinterestotal' => $GRAND_TOTAL2.'.00',
        'cprinctotal' => $GRAND_TOTAL3.'.00',
        'cinterstotal' => $GRAND_TOTAL4.'.00',
        'cothertotal' => $GRAND_TOTAL5.'.00',
        'dtotalsum' => $GRAND_TOTAL6.'.00',
        'ctotalsum' => $GRAND_TOTAL7.'.00',
        'dgrandtotal' => $GRAND_TOTAL8.'.00',
        'cgrandtotal' => $GRAND_TOTAL9.'.00',
        'dreceivbletotal' => $GRAND_TOTAL10.'.00',
        'drecpenaltotal' => $GRAND_TOTAL11.'.00',
        'dpenaltotal' => $GRAND_TOTAL12.'.00',
        'creceivbletotal' => $GRAND_TOTAL13.'.00',
        'crecpenaltotal' => $GRAND_TOTAL14.'.00',
        'cpenaltotal' => $GRAND_TOTAL15.'.00',
        'total_voucher'=> $total_voucher,
        'bankName' => $bankName,
        'startDate_' => $startDate_,
        'scheme_code' => $scheme_code,
        'Rdio' => $Rdio,
        'branch' => $branch,
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
    print_r($data);
}
?>

