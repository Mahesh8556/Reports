<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ScrollBookCredit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$opDate   = $_GET['opDate'];

$Startdate = $_GET['Startdate'];
$stype = $_GET['stype'];
$branch = $_GET['branch'];
$ccode = $_GET['ccode'];
$pcode = $_GET['pcode'];
$rdio = $_GET['rdio'];
// $line_Solid = $_GET['line_Solid'];

$bankName = str_replace("'", "", $bankName);
$Startdate_ = str_replace("'", "", $Startdate);
$stype = str_replace("'", "", $stype);
// $branchName = str_replace("'", "", $branchName);
$ccode = str_replace("'", "", $ccode);
$pcode = str_replace("'", "", $pcode);

$dateformat = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$int = "'1'";
$trtype = "'CS'";
$title= 'Cash';

if($stype =='cash'){
    $title = 'Cash';
    $rdio = "('CS')";
    $trtype = "('CS')";
}else{
    $rdio = "'JV'";
    $trtype = "('JV','TR')";
    $rdio = "('JV','TR')";
    $title = 'Transfer';
}
$line_Solid = 'D';
$DRCR = "'DRCR'";


// $query = 'SELECT 
//          (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT" as float)   else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("TRAN_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("INTEREST_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("RECPAY_INT_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float)  else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("PENAL_INT_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("REC_PENAL_INT_AMOUNT" as float)  else 0 end, 0)) as CrReceiptAmt,
//           "TRAN_NO","TRAN_ACNOTYPE",historytran."TRAN_ACTYPE","CASHIER_CODE","TRAN_ACNO",cast("TOKEN_NO" as character varying),"SERIAL_NO",
//           schemast."S_NAME",vwallmaster."ac_name",vwallmaster."ac_acnotype",ownbranchmaster."NAME",historytran."TRAN_TYPE",
//           historytran."TRAN_DRCR" 
//           from historytran 
//            Inner Join ownbranchmaster on
//            historytran."BRANCH_CODE" = ownbranchmaster."id"
//            Inner Join vwallmaster on 
//            cast (vwallmaster."ac_no" as bigint) = cast(historytran."TRAN_ACNO" as bigint)
//            Inner Join schemast on
//            historytran."TRAN_ACTYPE" = schemast."id"
//            WHERE cast("TRAN_STATUS" as integer) = '.$int.' and ';
//            if($stype =='cash'){
//             $query .='"TRAN_TYPE" = '.$trtype.''; 
//             }else{
//                $query .='"TRAN_TYPE" <> '.$trtype.' '; 
//             }
//            $query .='and "TRAN_DRCR" = '.$c.'
//            and cast("TRAN_DATE" as date) = '.$Startdate.'::date 
//            and historytran."BRANCH_CODE" = '.$branch.'
//            Union 
//            SELECT 
//           (coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER1_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER2_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER3_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER4_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER5_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER6_AMOUNT" as float)   else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER7_AMOUNT" as float) else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER8_AMOUNT" as float) else 0 end, 0)
//           + coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER9_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("OTHER10_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("TRAN_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("INTEREST_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("RECPAY_INT_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("DD_COMMISSION_AMT" as float)  else 0 end, 0) + 
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("PENAL_INT_AMOUNT" as float)  else 0 end, 0) +
//           coalesce(case when "TRAN_DRCR" = '.$c.' Then cast("REC_PENAL_INT_AMOUNT" as float)  else 0 end, 0)) as CrReceiptAmt,
//           "TRAN_NO","TRAN_ACNOTYPE",cast(dailytran."TRAN_ACTYPE" as integer),"OFFICER_CODE","TRAN_ACNO","TOKEN_NO","SERIAL_NO",
//           schemast."S_NAME",vwallmaster."ac_name",vwallmaster."ac_acnotype",ownbranchmaster."NAME",dailytran."TRAN_TYPE",
//           dailytran."TRAN_DRCR" 
//           from dailytran 
//            Inner Join ownbranchmaster on
//            dailytran."BRANCH_CODE" = ownbranchmaster."id"
//            Inner Join vwallmaster on 
//            cast (vwallmaster."ac_no" as bigint) = cast(dailytran."TRAN_ACNO" as bigint)
//            Inner Join schemast on
//            cast(dailytran."TRAN_ACTYPE" as integer) = schemast."id"
//            WHERE cast("TRAN_STATUS" as integer) = '.$int.' and ';


 //    if($stype =='cash'){
        //     $query .='"TRAN_TYPE" = '.$trtype.''; 
        //     }else{
        //        $query .='"TRAN_TYPE" <> '.$trtype.' '; 
        //     }
        //    $query .='and "TRAN_DRCR" = '.$c.'
        //    and cast("TRAN_DATE" as date) = '.$Startdate.'::date
        //    and dailytran."BRANCH_CODE" = '.$branch.'  ORDER BY "TRAN_NO" ASC';



$query='SELECT (COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER1_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER2_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER3_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER4_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER5_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER6_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER7_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER8_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER10_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "TRAN_AMOUNT"  ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "INTEREST_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "RECPAY_INT_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "DD_COMMISSION_AMT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "PENAL_INT_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "REC_PENAL_INT_AMOUNT" ELSE 0 END,0)) AS CRRECEIPTAMT,
"TRAN_NO","TRAN_ACNOTYPE",HISTORYTRAN."TRAN_ACTYPE","CASHIER_CODE",	"TRAN_ACNO","TOKEN_NO",
"SERIAL_NO",SCHEMAST."S_NAME",SCHEMAST."S_APPL",VWALLMASTER."ac_name",VWALLMASTER."ac_acnotype",
OWNBRANCHMASTER."NAME",	HISTORYTRAN."TRAN_TYPE",HISTORYTRAN."TRAN_DRCR" 
FROM HISTORYTRAN
INNER JOIN OWNBRANCHMASTER ON HISTORYTRAN."BRANCH_CODE" = OWNBRANCHMASTER."id"
INNER JOIN VWALLMASTER ON CAST(VWALLMASTER."ac_no" AS bigint) = CAST(HISTORYTRAN."TRAN_ACNO" AS bigint)
INNER JOIN SCHEMAST ON HISTORYTRAN."TRAN_ACTYPE" = SCHEMAST."id"
WHERE "TRAN_STATUS" = '.$int.'
AND "TRAN_TYPE" IN '.$trtype.'
AND "TRAN_DRCR" = '.$c.'
AND CAST("TRAN_DATE" AS date) = cdate('.$Startdate.')
AND HISTORYTRAN. "BRANCH_CODE" = '.$branch.'

union
SELECT (COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER1_AMOUNT" ELSE 0 END,0) + 
COALESCE (CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER2_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER3_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER4_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER5_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER6_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER7_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER8_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER9_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "OTHER10_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "TRAN_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "INTEREST_AMOUNT" ELSE 0 END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "RECPAY_INT_AMOUNT" ELSE 0	END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "DD_COMMISSION_AMT" ELSE 0	END,0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "PENAL_INT_AMOUNT" ELSE 0 END,	0) + 
COALESCE(CASE WHEN "TRAN_DRCR" = '.$c.' THEN "REC_PENAL_INT_AMOUNT" ELSE 0	END,0)) AS CRRECEIPTAMT,
"TRAN_NO","TRAN_ACNOTYPE",	CAST(DAILYTRAN."TRAN_ACTYPE" AS integer),"OFFICER_CODE",
"TRAN_ACNO","TOKEN_NO","SERIAL_NO",SCHEMAST."S_NAME",SCHEMAST."S_APPL",VWALLMASTER."ac_name",
VWALLMASTER."ac_acnotype",OWNBRANCHMASTER."NAME",DAILYTRAN. "TRAN_TYPE",DAILYTRAN."TRAN_DRCR"
FROM DAILYTRAN
INNER JOIN OWNBRANCHMASTER ON DAILYTRAN."BRANCH_CODE" = OWNBRANCHMASTER."id"
INNER JOIN VWALLMASTER ON CAST (VWALLMASTER."ac_no" AS bigint) = CAST(DAILYTRAN."TRAN_ACNO" AS bigint)
INNER JOIN SCHEMAST ON CAST (DAILYTRAN."TRAN_ACTYPE" AS integer) = SCHEMAST."id"
WHERE "TRAN_STATUS"= '.$int.'
AND "TRAN_TYPE" IN '.$trtype.'
AND "TRAN_DRCR" = '.$c.'
AND CAST("TRAN_DATE" AS date) = cdate('.$Startdate.')
AND DAILYTRAN."BRANCH_CODE" = '.$branch.'
ORDER BY "TRAN_NO" ASC';

        
            // echo $query;
            

        $getOpeningBal = pg_query($conn,"select ledgerbalance('980','1','$opDate',1,$branch,0)");
        // echo "select ledgerbalance('980','1',$opDate,1,$branch,0)";
        $openingData = pg_fetch_assoc($getOpeningBal);
        $openingBal  = $openingData['ledgerbalance'];
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_RECE = 0;
$GRAND_PAY = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
$total_voucher = 0;
while($row = pg_fetch_assoc($sql)){

    $GRAND_RECE = $GRAND_RECE + $row['crreceiptamt'];
    $GRAND_PAY = $GRAND_PAY + $row['drpaymentamt'];
    $total_voucher = $total_voucher + 1;
    $tmp=[
        'TRAN_DRCR'=> $row['TRAN_DRCR'],
        'crreceiptamt'=> sprintf("%.2f",($row['crreceiptamt'] + 0.0 ) ),
        'drpaymentamt' => $row['drpaymentamt'],
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
        'SCROLL_NO' => $row['SCROLL_NO'],
        'TRAN_TYPE' => $row['TRAN_TYPE'],
        'recegtot' => sprintf("%.2f",($GRAND_RECE + 0.0 ) ),
        'paygtot' => $GRAND_PAY,
        'total_voucher'=> $total_voucher,
        'OpeningBalance' => $openingBal,
        'ClosingBalance' => $GRAND_RECE - $openingBal,
        'bankName' => $bankName,
        'Startdate_' => $Startdate_,
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

ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}   
?>
