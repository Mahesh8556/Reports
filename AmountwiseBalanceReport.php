<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/AmountwiseBalanceReport.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$sdate = $_GET['sdate'];
$scheme = $_GET['scheme'];
$startingacc = $_GET['startingacc'];
$endingacc = $_GET['endingacc'];
$branch = $_GET['branch'];

$sdate = "'01/04/2022'";
$date = "'12/08/2022'";
$bcode = "'1'";
$amount = "'AMOUNT'";

$query = ' SELECT COUNT(VWALLMASTER."ac_no"),
           ledgerbalance(cast (schemast."S_APPL" as character varying),
           VWALLMASTER."ac_no",'.$sdate.',0,1)as ledgerbalance,
           VWALLMASTER."ac_no",VWALLMASTER."ac_name",OWNBRANCHMASTER."NAME",VWALLMASTER."ac_type",
           SCHEMAST."S_NAME",SIZEWISEBALANCE."AMOUNT_FROM",SIZEWISEBALANCE."AMOUNT_TO",schemast."S_APPL"
           FROM VWALLMASTER
           INNER JOIN SCHEMAST ON 
           VWALLMASTER."ac_type" = SCHEMAST."id"
           INNER JOIN DAILYTRAN ON 
           VWALLMASTER."ac_no" = DAILYTRAN."TRAN_ACNO"
           INNER JOIN OWNBRANCHMASTER ON
           VWALLMASTER."branch_code" = OWNBRANCHMASTER."id"
           INNER JOIN SIZEWISEBALANCE ON 
           VWALLMASTER."ac_acnotype" = SIZEWISEBALANCE."ACNOTYPE"
           WHERE cast(DAILYTRAN."TRAN_DATE" as date) >= '.$startDate.'::date
           AND VWALLMASTER."branch_code" = '.$branch.'
           AND VWALLMASTER."ac_type" = '.$scheme.'
           AND SIZEWISEBALANCE."SLAB_TYPE" = '.$amount.'
           AND "ac_no" between '.$startingacc.' and '.$endingacc.' 
           GROUP BY schemast."S_APPL",VWALLMASTER."ac_no",VWALLMASTER."ac_name",
           OWNBRANCHMASTER."NAME",VWALLMASTER."ac_type",SCHEMAST."S_NAME",SIZEWISEBALANCE."AMOUNT_FROM",
           SIZEWISEBALANCE."AMOUNT_TO" ';
          
$sql =  pg_query($conn,$query);

$i = 0;

$balance_total = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $balance_total = $balance_total + $row['ledgerbalance'];

    if($type == ''){
        $type = $row['S_NAME'];
    }
    if($type == $row['S_NAME']){
        $schem_ledgerbal = $schem_ledgerbal + $row['ledgerbalance'];
    }else{
        $type = $row['S_NAME'];
        $schem_ledgerbal = 0;
        $schem_ledgerbal = $schem_ledgerbal + $row['ledgerbalance'];
    }

    $tmp=[
        'ledgerbalance' => $row['ledgerbalance'],
        'ac_no'=> $row['ac_no'],
        'ac_name' => $row['ac_name'],
        'S_NAME' => $row['S_NAME'],
        'NAME' => $row['NAME'],
        'ac_opdate' => $row['ac_opdate'],
        'S_APPL' => $row['S_APPL'],
        'AMOUNT_FROM' => $row['AMOUNT_FROM'],
        'AMOUNT_TO' => $row['AMOUNT_TO'],
        'count' => $row['count'],
        'ac_type' => $row['ac_type'],
        'schem_ledgerbal' => $schem_ledgerbal,
        'balance_total' => $balance_total,

        'bankName' => $bankName,
        'startDate' => $startDate,
        'sdate' => $sdate,
        'scheme' => $scheme,
        'startingacc' => $startingacc,
        'endingacc' => $endingacc,
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
    
}
?>


<!-- Balance Certificate 

SELECT 
ledgerbalance(cast (SCHEMAST."S_APPL" as character varying),
VWALLMASTER."ac_no",'10/08/2022',0,1)as ledgerbalance,
VWALLMASTER."ac_name",VWALLMASTER."ac_no",VWALLMASTER."ac_acnotype"
FROM VWALLMASTER
INNER JOIN SCHEMAST ON 
VWALLMASTER."ac_type" = SCHEMAST."id"
where cast(VWALLMASTER."ac_opdate" as date) = '12/08/2022'::date 
and vwallmaster."ac_acnotype" = 'TD'

-->