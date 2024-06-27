<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/standinstructlogFailure.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$stadate = $_GET['stadate'];
$edate = $_GET['edate'];
$branched = $_GET['branched'];
$failure = $_GET['failure'];
$startscheme = $_GET['startscheme'];
$sort = $_GET['sort'];
$frequency = $_GET['frequency'];

$bankName = str_replace("'", "", $bankName);
$stadate_ = str_replace("'", "", $stadate);
$edate_ = str_replace("'", "", $edate);
// $branched = str_replace("'", "", $branchName);
$startscheme = str_replace("'", "", $startscheme);

$failure = "'%F%'";
$dateformate = "'DD/MM/YYYY'";

$query = 'SELECT standinstructionlog."INSTRUCTION_NO",standinstructionlog."TRAN_DATE",standinstructionlog."DAILYTRAN_TRAN_NO",
          standinstructionlog."EXPECTED_EXECUTION_DATE",standinstructionlog."TRAN_AMOUNT",ownbranchmaster."id",
          standinstructionlog."PARTICULARS",standinstructionlog."SUCCESS_STATUS",ownbranchmaster."NAME"
          from standinstructionlog, standinstruction
          Inner Join ownbranchmaster on standinstruction."BRANCH_CODE" = ownbranchmaster."id"
          where standinstructionlog."SUCCESS_STATUS" = '.$failure.'
          and standinstruction."SI_FREQUENCY" = '.$frequency.'
          and standinstruction."BRANCH_CODE" = '.$branched.'
          and cast("TRAN_DATE" as date) 
          between to_date('.$stadate.','.$dateformate.') and to_date('.$edate.','.$dateformate.')  ';

            // echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    $tmp=[

        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'TRAN_DATE'=> $row['TRAN_DATE'],
        'DAILYTRAN_TRAN_NO' => $row['DAILYTRAN_TRAN_NO'],
        'EXPECTED_EXECUTION_DATE' => $row['EXPECTED_EXECUTION_DATE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'PARTICULARS' => $row['PARTICULARS'],
        'SUCCESS_STATUS' => $row['SUCCESS_STATUS'],
        'NAME' => $row['NAME'],

        'bankName' => $bankName,
        'stadate_'=> $stadate_,
        'edate_'=> $edate_,
        'branched'=> $branched,
        'failure'=> $failure,
        'startscheme'=> $startscheme,
        'sort'=> $sort,
        'success' => $success,
        'frequency' => $frequency,
     
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
