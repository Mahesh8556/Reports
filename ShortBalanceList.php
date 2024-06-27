<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/ShortBalanceList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

// $bankName = $_GET['bankName'];
// $date = $_GET['date'];
// $branch = $_GET['branch'];

$sdate = "'12/08/2022'";
$date = "'06/06/2022'";
$bcode = "'1'";
$sappl = "'201'";

$query = ' SELECT 
           ledgerbalance(cast (schemast."S_APPL" as character varying),
           vwallmaster."ac_no",'.$sdate.',0,1)as ledgerbalance,
           vwallmaster."ac_no", schemast."S_NAME",vwallmaster."ac_opdate",
           ownbranchmaster."NAME",schemast."S_APPL"
           from vwallmaster 
           Inner Join schemast on vwallmaster."ac_type" = schemast."id"
           Inner Join ownbranchmaster on vwallmaster."branch_code" = ownbranchmaster."id"
           Where cast(vwallmaster."ac_opdate" as date) = '.$date.' ::date 
           and vwallmaster."branch_code" = '.$bcode.'
           and schemast."S_APPL" = '.$sappl.'  ';
          
$sql =  pg_query($conn,$query);

$i = 0;

$LEDGER_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $LEDGER_TOTAL = $LEDGER_TOTAL + $row['ledgerbalance'];

    $tmp=[
        'ledgerbalance' => $row['ledgerbalance'],
        'ac_no'=> $row['ac_no'],
        'S_NAME' => $row['S_NAME'],
        'NAME' => $row['NAME'],
        'ac_opdate' => $row['ac_opdate'],
        'S_APPL' => $row['S_APPL'],
        'ledgertotal' => $LEDGER_TOTAL,

        'bankName' => $bankName,
        'branch' => $branch,
        'date' => $date,
        'Date' => $Date,
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
