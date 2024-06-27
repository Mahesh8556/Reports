<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/SpecialRevokeInstruct.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$branch = $_GET['branch'];
$revoke = $_GET['revoke'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
// $branchName = str_replace("'", "", $branchName);

$query = 'SELECT specialinstruction."INSTRUCTION_DATE", specialinstruction."INSTRUCTION_NO", specialinstruction."TRAN_ACNO",
          specialinstruction."DETAILS", specialinstruction."FROM_DATE", specialinstruction."TO_DATE",
          specialinstruction."SYSADD_LOGIN", specialinstruction."SYSCHNG_LOGIN",ownbranchmaster."id",
          ownbranchmaster."NAME" 
          FROM specialinstruction, ownbranchmaster 
          WHERE cast("REVOKE_DATE" as date) 
          between to_date('.$stdate.','.$dateformate.') and to_date('.$etdate.','.$dateformate.')
          and ownbranchmaster."id" = '.$branch.'  ';
          
$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'INSTRUCTION_DATE' => $row['INSTRUCTION_DATE'],
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'NAME' => $row['NAME'],
        'ACCOUNT_NAME' => $row['ACCOUNT_NAME'],
        'DETAILS' => $row['DETAILS'],
        'FROM_DATE'=> $row['FROM_DATE'],
        'TO_DATE'=> $row['TO_DATE'],
        'SYSADD_LOGIN'=> $row['SYSADD_LOGIN'],
        'SYSCHNG_LOGIN' => $row['SYSCHNG_LOGIN'],

        'bankName' => $bankName,
        'revoke' => $revoke,
        'branch' => $branch,
        'etdate_' => $etdate_,
        'stdate_' => $stdate_,
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
