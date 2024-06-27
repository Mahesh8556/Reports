<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

// $filename = __DIR__.'/CustomerIdwiseList.jrxml';
$filename = __DIR__.'/customer.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// variables  
$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$edate = $_GET['edate'];
$custid = $_GET['custid'];
$branch = $_GET['branch'];
$pritns = $_GET['pritns'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$custid = str_replace("'", "", $custid);
$pritns = str_replace("'", "", $pritns);

$c = "'C'";
$d = "'D'";
$dateformate = "'DD/MM/YYYY'";


$query='SELECT schemast."S_APPL",schemast."S_NAME",depotran."TRAN_DATE"  , ledgerbalance(cast (schemast."S_APPL"   as character varying), dpmaster."BANKACNO",'.$stdate.',0,1)as ledger_balance, coalesce(case when depotran."TRAN_DRCR" = '.$c.' Then cast(depotran."TRAN_AMOUNT" as integer) else 0 end, 0) as creditamt, coalesce(case when depotran."TRAN_DRCR" = '.$d.' Then cast(depotran."TRAN_AMOUNT" as integer) else 0 end, 0)as debitamt, dpmaster."AC_ACNOTYPE",dpmaster."AC_TYPE",dpmaster."AC_NO",depotran."TRAN_DRCR", dpmaster."AC_MONTHS",dpmaster."AC_INTRATE",customeraddress."id",dpmaster."AC_CLOSEDT", dpmaster."AC_NAME",depotran."TRAN_AMOUNT",ownbranchmaster."NAME" From dpmaster Inner Join depotran on dpmaster."BANKACNO" = depotran."TRAN_ACNO" Inner Join customeraddress on dpmaster."AC_CUSTID" = customeraddress."id" Inner Join ownbranchmaster on dpmaster."BRANCH_CODE" = ownbranchmaster."id" Inner Join schemast on dpmaster."AC_TYPE" = schemast."id" and cast(dpmaster."AC_OPDATE" as date) <= '.$stdate.'::date and dpmaster."BRANCH_CODE" ='.$branch.'  and dpmaster."AC_CUSTID" = '.$custid.' and dpmaster."AC_CLOSEDT" is null order by schemast."S_APPL",  dpmaster."BANKACNO", cast(DEPOTRAN."TRAN_DATE" as date)';
// echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0 ;
$DTOTAL = 0;
$CTOTAL = 0;
$SCHEME_DTOTAL=0;
$SCHEME_CTOTAL=0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    
    $DTOTAL = $DTOTAL + $row['debitamt'];
    $CTOTAL = $CTOTAL + $row['creditamt'];
   
    if($type == ''){
        $type = $row['S_APPL'];
        
    }
    if($type == $row['S_APPL']){
        $SCHEME_DTOTAL = $SCHEME_DTOTAL + $row['debitamt'];
        $SCHEME_CTOTAL = $SCHEME_CTOTAL + $row['creditamt'];
    }else{
        $type = $row['S_APPL'];
        $SCHEME_CTOTAL = 0;
        $SCHEME_DTOTAL = 0;
        $SCHEME_DTOTAL = $SCHEME_DTOTAL + $row['debitamt'];
        $SCHEME_CTOTAL = $SCHEME_CTOTAL + $row['creditamt'];
    }

   

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_CLOSEDT' => $row['AC_CLOSEDT'],
        'AC_CUSTID' => $row['AC_CUSTID'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_MONTHS' => $row['AC_MONTHS'],
        'AC_INTRATE' => $row['AC_INTRATE'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'AC_TYPE'=> $row['AC_TYPE'],
        'AC_ACNOTYPE'=> $row['S_APPL'].' '. $row['S_NAME'],
        'creditamt' => sprintf("%.2f",($row['creditamt'] + 0.0 ) ),
        'debitamt' => sprintf("%.2f",($row['debitamt'] + 0.0 ) ),
        'id' => $row['id'],
        'NAME' => $row['NAME'],
        'ledger_balance' => (abs($row['ledger_balance'])),
        'AC_DAYS' => $row['AC_DAYS'],
        'granddrtot'=> sprintf("%.2f", ($DTOTAL + 0.0)),
        'grandcrtot'=> sprintf("%.2f", ($CTOTAL + 0.0)),
        'schemctotal'=>sprintf("%.2f", ($SCHEME_CTOTAL + 0.0)),
        'schemdtotal'=>sprintf("%.2f", ($SCHEME_DTOTAL + 0.0)),
        'S_APPL'=>$row['S_APPL'],
        'bankName' => $bankName,
        'edate' => $edate,
        'stdate_'=> $stdate_,
        'custid'=> $custid,
        'branch'=> $branch,
        'pritns'=> $pritns,
       
       
        'Name'=>$row['NAME'],
        'IntRate'=>  $row['AC_INTRATE'],
        'grandc_total'=>$row['GRAND_TOTAL']
    
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();
// print_r($data);
$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

}
?>   

