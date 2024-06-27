<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ScrollBookDebit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$opDate   = $_GET['opDate'];
$Startdate = $_GET['Startdate'];
$stype = $_GET['stype'];
$branch = $_GET['branch'];
$ccode = $_GET['ccode'];
$pcode = $_GET['pcode'];
// $rdio = $_GET['rdio'];
// $line_Solid = $_GET['line_Solid'];

//$bankName = str_replace("'", "", $bankName);
$Startdate_ = str_replace("'", "", $Startdate);
$stype = str_replace("'", "", $stype);
// $branchName = str_replace("'", "", $branchName);
//$ccode = str_replace("'", "", $ccode);
//$pcode = str_replace("'", "", $pcode);

$dateformat = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$int = "'1'";
$title = '';



// echo $stype;
$trtype = "'CS'";
if($stype =='cash'){
    $rdio = "'CS'";
    $trtype = "'CS'";
    $trtype = "('CS')";
    $title = 'Cash';
}else{
    $rdio = "'TR'";
    $title = 'Transfer';
    $trtype = "'TR'";
    $trtype = "('JV','TR')";

}
$line_Solid = 'D';
$DRCR = "'DRCR'";
// $B = "'KALE'";



$query='SELECT ( coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER1_AMOUNT"  else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER2_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER3_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER4_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER5_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER6_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER7_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER8_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER9_AMOUNT" else 0 end, 0) + 
coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER10_AMOUNT" else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "TRAN_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "INTEREST_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "RECPAY_INT_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "DD_COMMISSION_AMT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "PENAL_INT_AMOUNT" else 0 end, 0) + 
 coalesce (case when "TRAN_DRCR" = '.$d.' Then "REC_PENAL_INT_AMOUNT" else 0 end, 0)) DrPaymentAmt, 
 "TRAN_NO","TRAN_ACNOTYPE", historytran."TRAN_ACTYPE","CASHIER_CODE","TRAN_ACNO","TOKEN_NO",
 "SERIAL_NO", schemast."S_NAME",SCHEMAST."S_APPL",vwallmaster."ac_name", vwallmaster."ac_acnotype",ownbranchmaster."NAME",
 historytran."TRAN_TYPE", historytran."TRAN_DRCR" 
 from historytran Inner Join ownbranchmaster on historytran."BRANCH_CODE" = ownbranchmaster."id" 
 Inner Join vwallmaster on cast (vwallmaster."ac_no" as bigint) = cast(historytran."TRAN_ACNO" as bigint) 
 Inner Join schemast on historytran."TRAN_ACTYPE" = schemast."id" 
 WHERE "TRAN_STATUS"= '.$int.' and "TRAN_TYPE" IN '.$trtype.' 
 and"TRAN_TYPE" IN '.$trtype.'and "TRAN_DRCR" = '.$d.' 
 AND CAST("TRAN_DATE" AS date) = cdate('.$Startdate.')
 ---and CAST("CASHIER_CODE" AS INTEGER)='.$ccode.' 
 and historytran."BRANCH_CODE" = '.$branch.' 
 Union 
 SELECT ( coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER1_AMOUNT" else 0 end, 0) +
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER2_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER3_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER4_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER5_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER6_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER7_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER8_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER9_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "OTHER10_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "TRAN_AMOUNT"  else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "INTEREST_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "RECPAY_INT_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "DD_COMMISSION_AMT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "PENAL_INT_AMOUNT" else 0 end, 0) + 
 coalesce(case when "TRAN_DRCR" = '.$d.' Then "REC_PENAL_INT_AMOUNT" else 0 end, 0)) DrPaymentAmt, 
 "TRAN_NO","TRAN_ACNOTYPE",cast(dailytran."TRAN_ACTYPE" as integer), "OFFICER_CODE",
 "TRAN_ACNO","TOKEN_NO","SERIAL_NO", schemast."S_NAME",SCHEMAST."S_APPL",vwallmaster."ac_name",
 vwallmaster. "ac_acnotype",ownbranchmaster."NAME",dailytran."TRAN_TYPE", 
 dailytran."TRAN_DRCR" from dailytran 
 Inner Join ownbranchmaster on dailytran."BRANCH_CODE" = ownbranchmaster."id" 
 Inner Join vwallmaster on cast (vwallmaster."ac_no" as bigint) = cast(dailytran. "TRAN_ACNO" as bigint) 
 Inner Join schemast on cast(dailytran."TRAN_ACTYPE" as integer) = schemast."id" 
 WHERE "TRAN_STATUS"= '.$int.' and "TRAN_TYPE" IN '.$trtype.'
 AND CAST("TRAN_DATE" AS date) = cdate('.$Startdate.')
 ---and CAST("CASHIER_CODE" AS INTEGER)='.$ccode.' 
 and dailytran."BRANCH_CODE" = '.$branch.' ORDER BY "TRAN_NO" ASC ';

// echo $query;

$glscheme="'980'";
$getcashAcno = pg_query($conn,'select "CASH_IN_HAND_ACNO" FROM SYSPARA');
while ( $row = pg_fetch_assoc($getcashAcno)) {

    $cashAcno  = $row['CASH_IN_HAND_ACNO'];

}
// $getOpeningBal = pg_query($conn,'select ledgerbalance('980','1','.$opDate.','1','.$branch.',0)');
$getOpeningBal = pg_query($conn,'select ledgerbalance('.$glscheme.',cast('.$cashAcno.' as character varying),'.$Startdate.',0,'.$branch.',0)');

// echo "select ledgerbalance('980','1',$opDate,1,$branch,0)";
$openingData = pg_fetch_assoc($getOpeningBal);
$openingBal  = $openingData['ledgerbalance'];
// echo 'Working The Task';
// echo $openingBal;
// print_r($openingBal);
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_PAY = 0;
$GRAND_RECE = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql)){

    if($row['drpaymentamt'] !='0'){
        $GRAND_PAY = $GRAND_PAY + $row['drpaymentamt'];

        $total_voucher = $total_voucher + 1;
        $tmp=[
            'TRAN_DRCR'=> $row['TRAN_DRCR'],
            'crreceiptamt'=> $row['crreceiptamt'],
            'drpaymentamt' =>sprintf("%.2f",($row['drpaymentamt'] + 0.0 ) ),
            'TRAN_NO' => $row['TRAN_NO'],
            'TRAN_ACTYPE' => $row['TRAN_ACTYPE'],
            'CASHIER_CODE' => $row['CASHIER_CODE'],
            'TRAN_ACNO' => $row['TRAN_ACNO'],
            'TOKEN_NO' => $row['TOKEN_NO'],
            'SERIAL_NO' => $row['SERIAL_NO'],
            'S_NAME' => $row['S_NAME'],
            'SCROLL_NO' => $row['SCROLL_NO'],
            'ac_name' => $row['ac_name'],
            'ac_acnotype' => $row['ac_acnotype'],
            'NAME' => $row['NAME'],
            'TRAN_TYPE' => $row['TRAN_TYPE'],
            'line_Solid'=> $line_Solid,
            'paygtot' => sprintf("%.2f",($GRAND_PAY + 0.0 ) ) ,
            'recegtot' => $GRAND_RECE,
            'total_voucher'=> $total_voucher,
            'bankName' => $bankName,
            'Startdate_' => $Startdate_,
            'OpeingBalance' => $openingBal,
            'ClosingBalance' => $GRAND_PAY - $openingBal,
            'stype' => $stype,
            'branch' => $branch,
            'ccode' => $ccode,
            'pcode' => $pcode,
            'rdio' => $rdio,
            'title' => $title,
            'schem_name' => $row['S_APPL'].' '.$row['S_NAME'],
        ];
        
        $data[$i]=$tmp;
        $i++;  
    }
}

ob_end_clean();
// echo $query;

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}   
?>
