<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/StandingInstructionCredit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformat ="'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$FREQUENCY = $_GET['FREQUENCY'];
$Branch = $_GET['Branch'];
$STATUS = $_GET['STATUS'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
// $branchName = str_replace("'", "", $branchName);

$query ='SELECT standinstruction."INSTRUCTION_NO",
         standinstruction."CR_ACNOTYPE",
         standinstruction."CR_AC_NO", 
         standinstruction."DR_ACNOTYPE", 
         standinstruction."DR_ACTYPE", 
         standinstruction."DR_AC_NO", 
         dpmaster."AC_NAME",
         standinstruction."TRAN_AMOUNT" 
         from dpmaster,standinstruction 
         Inner Join ownbranchmaster on 
         standinstruction."BRANCH_CODE" = ownbranchmaster."id"
         where standinstruction."REVOKE_DATE" is null and
         AND standinstruction."BRANCH_CODE" = '.$Branch.' 
         cast("INSTRUCTION_DATE" as date) = '.$stdate.'::date
         order by  standinstruction."CR_ACNOTYPE" asc';
         
$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $tmp=[

        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'CR_ACNOTYPE'=> $row['CR_ACNOTYPE'],
        'CR_AC_NO' => $row['CR_AC_NO'],  
        'DR_ACNOTYPE' => $row['DR_ACNOTYPE'], 
        'DR_ACTYPE' => $row['DR_ACTYPE'],
        'DR_AC_NO' => $row['DR_AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'CR_ACTYPE'=> $row['CR_ACTYPE'],

        'bankName' => $bankName,
        'stdate_' => $stdate_,
        'Branch' => $Branch,
        'STATUS' => $STATUS,
        'FREQUENCY' => $FREQUENCY,
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
