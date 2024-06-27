<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/standingInstructionDebit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 

$date = "'31/12/2021'";
$dateformat ="'DD/MM/YYYY'";

$stadate = $_GET['stadate'];
$edate = $_GET['edate'];
$Branch = $_GET['Branch'];
$status = $_GET['status'];
// $bankName = $_GET['bankName'];

$bankName = str_replace("'", "", $bankName);
$stadate_ = str_replace("'", "", $stadate);
$edate_ = str_replace("'", "", $edate);
// $branchName = str_replace("'", "", $branchName);

$query='SELECT standinstruction."INSTRUCTION_NO",
        standinstruction."CR_ACTYPE",
        standinstruction."CR_AC_NO", 
        standinstruction."DR_ACTYPE", 
        standinstruction."DR_AC_NO", 
        dpmaster."AC_NAME",
        standinstructionlog."TRAN_AMOUNT" 
        from standinstructionlog, standinstruction
        inner join dpmaster on standinstruction."CR_AC_NO" =dpmaster."AC_NO" 
        inner join ownbranchmaster on standinstruction."BRANCH_CODE" =ownbranchmaster."id" 
        where standinstruction."REVOKE_DATE" is null and
        AND ownbranchmaster."CODE" = '.$Branch.' 
        cast("INSTRUCTION_DATE" as date) = '.$date.'::date
        order by  standinstruction."DR_ACTYPE" asc';
        
$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'CR_ACTYPE' => $row['CR_ACTYPE'],
        'CR_AC_NO' => $row['CR_AC_NO'],  
        'DR_ACNOTYPE' => $row['DR_ACNOTYPE'], 
        'DR_ACTYPE' => $row['DR_ACTYPE'],
        'DR_AC_NO' => $row['DR_AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],

        'bankName' => $bankName,
        'stadate_' => $stadate_,
        'edate_' => $edate_,
        'Branch' => $Branch,
        'status' => $status,
        'startDate' => $startDate,
        'endDate' => $endDate,
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

