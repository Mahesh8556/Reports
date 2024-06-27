<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_time_limit(500);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DormantAccountList1.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$o = "'0'";
$dateformate = "'DD/MM/YYYY'"; 

$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$scheme = $_GET['scheme'];
$Rstartingacc = $_GET['Rstartingacc'];
$Rendingacc = $_GET['Rendingacc'];
$branch = $_GET['branch'];
$Rdio = $_GET['Rdio'];
$Rdiosort = $_GET['Rdiosort'];
$checkbox1 = $_GET['checkbox1'];
$checkbox2 = $_GET['checkbox2'];
$checkbox3 = $_GET['checkbox3'];

$bankName = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
$scheme = str_replace("'", "", $scheme);
// $branchName = str_replace("'", "", $branchName);
$Rstartingacc = str_replace("'", "", $Rstartingacc);
$Rendingacc = str_replace("'", "", $Rendingacc);
$Rdio = str_replace("'", "", $Rdio);
$Rdiosort = str_replace("'", "", $Rdiosort);

$query = ' SELECT 
           coalesce(ledgerbalance(cast (schemast."S_APPL" as character varying),
           dpmaster."BANKACNO",'.$startDate.',0,1),'.$o.') as ledger_balance,
           dpmaster."AC_ACNOTYPE", dpmaster."AC_TYPE", ownbranchmaster."NAME",
           dpmaster."AC_NO", dpmaster."AC_OPDATE" as tran_Date,dpmaster."AC_NAME", 
           dpmaster."AC_OPDATE"
           From dpmaster
           Inner Join ownbranchmaster on
           dpmaster."BRANCH_CODE" = ownbranchmaster."id" 
           Inner join schemast on dpmaster."AC_TYPE" = schemast."id"
           where cast("AC_OPDATE" as date) = '.$startDate.'::date 
           and dpmaster."BRANCH_CODE" = '.$branch.'
           Union 
           SELECT 
           coalesce(ledgerbalance(cast (schemast."S_APPL" as character varying),
           pgmaster."BANKACNO",'.$startDate.',0,1),'.$o.') as ledger_balance,
           pgmaster."AC_ACNOTYPE", pgmaster."AC_TYPE", ownbranchmaster."NAME",
           pgmaster."AC_NO", pgmaster."AC_OPDATE" as tran_Date, pgmaster."AC_NAME", 
           pgmaster."AC_OPDATE"
           From pgmaster
           Inner Join ownbranchmaster on
           pgmaster."BRANCH_CODE" = ownbranchmaster."id" 
           Inner join schemast on pgmaster."AC_TYPE" = schemast."id"
           where cast("AC_OPDATE" as date) = '.$startDate.'::date
           and pgmaster."BRANCH_CODE" = '.$branch.'
           Union 
           Select 
           coalesce(ledgerbalance(cast (schemast."S_APPL" as character varying),
           lnmaster."BANKACNO",'.$startDate.',0,1),'.$o.') as ledger_balance,
           lnmaster."AC_ACNOTYPE", lnmaster."AC_TYPE", ownbranchmaster."NAME",
           lnmaster."AC_NO", lnmaster."AC_OPDATE" as tran_Date, lnmaster."AC_NAME", 
           lnmaster."AC_OPDATE"
           From lnmaster
           Inner Join ownbranchmaster on
           lnmaster."BRANCH_CODE" = ownbranchmaster."id" 
           Inner join schemast on lnmaster."AC_TYPE" = schemast."id"
           where cast("AC_OPDATE" as date) = '.$startDate.'::date
           and lnmaster."BRANCH_CODE" = '.$branch.'
           Union 
           Select
           coalesce(ledgerbalance(cast (schemast."S_APPL" as character varying),
           shmaster."BANKACNO",'.$startDate.',0,1),'.$o.') as ledger_balance, 
           shmaster."AC_ACNOTYPE", shmaster."AC_TYPE", ownbranchmaster."NAME",
           shmaster."AC_NO", shmaster."AC_OPDATE" as tran_Date, shmaster."AC_NAME", 
           shmaster."AC_OPDATE"
           From shmaster
           Inner Join ownbranchmaster on
           shmaster."BRANCH_CODE" = ownbranchmaster."id" 
           Inner join schemast on shmaster."AC_TYPE" = schemast."id"
           where cast("AC_OPDATE" as date) = '.$startDate.'::date
           and shmaster."BRANCH_CODE" = '.$branch.' ';

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + 15000;

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'tran_Date' => $row['AC_OPDATE'],
        'ledger_balance' => $row['ledger_balance'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_TYPE' => $row['AC_TYPE'],
        'NAME' => $row['NAME'],
        'balgrandtot'=> $GRAND_TOTAL,
        'startdate'=> $startdate,
        'enddate'=> $enddate,

        'bankName' => $bankName,
        'startDate_' => $startDate_,
        'scheme' => $scheme,
        'Rstartingacc' => $Rstartingacc,
        'Rendingacc' => $Rendingacc,
        'branch' => $branch,
        'Rdio' => $Rdio,
        'Rdiosort' => $Rdiosort,
        'checkbox1' => $checkbox1,
        'checkbox2' => $checkbox2,
        'checkbox3' => $checkbox3,
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

    

