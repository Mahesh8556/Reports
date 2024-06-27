<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/StandingInstruct.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSTest user=postgres password=admin");

$Branch = $_GET['Branch'];
$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$branchName = $_GET['branchName'];


$dateformate = "'DD/MM/YYYY'";

// $bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
// $branchName = str_replace("'", "", $branchName);

$query = 'SELECT standinstruction."INSTRUCTION_NO",standinstruction."DR_AC_NO",standinstruction."DR_PARTICULARS", 
          standinstruction."CR_AC_NO",standinstruction."CR_PARTICULARS",standinstruction."INSTRUCTION_DATE", 
          standinstruction."REVOKE_DATE",
          standinstruction."SI_FREQUENCY",standinstruction."LAST_EXEC_DATE",schemast."S_NAME" as dname,schemast1."S_NAME" as cname,
          standinstruction."PAYINT_AMOUNT" from 
          standinstruction 
          inner join schemast on schemast.id = standinstruction."DR_ACTYPE"
          inner join schemast as schemast1 on schemast1.id = standinstruction."CR_ACTYPE"
          WHERE standinstruction."BRANCH_CODE" = '.$Branch.' AND cast(standinstruction."REVOKE_DATE" as date) 
          between CAST('.$stdate.' as date) and CAST('.$etdate.' as date)';



$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['PAYINT_AMOUNT'];

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'SIREC_DATE' => $row['INSTRUCTION_DATE'],
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'INSTRUCTION_DATE'=> $row['INSTRUCTION_DATE'],
        'NAME' => $branchName,
        'SCHEMEC' => $row['cname'],
        'DR_AC_NO' => $row['DR_AC_NO'],
        'DR_PARTICULARS' => $row['DR_PARTICULARS'],
        'SCHEMED'=> $row['dname'],
        'CR_AC_NO'=> $row['CR_AC_NO'],
        'CR_PARTICULARS'=> $row['CR_PARTICULARS'],
        'SI_FREQUENCY'=> $row['SI_FREQUENCY'],
        'LAST_EXEC_DATE'=> $row['LAST_EXEC_DATE'],
        'SI_PERIOD'=> $row['SI_PERIOD'],
        'TRAN_AMOUNT'=> sprintf("%.2f",((int)$row['PAYINT_AMOUNT'] + 0.0 ) ),
        'totalamt' => sprintf("%.2f", ($GRAND_TOTAL + 0.0)),

        'stdate_' => $stdate_,
        'etdate' => $etdate_,
        'REVOKE_INST' => $REVOKE_INST,
        'Branch' => $Branch,
        'bankName' => $bankName,
        'type'=> 'REVOKE'
    
    ];
    $data[$i]=$tmp;
    $i++;
    // echo "<pre>";
    // print_r($tmp);
    // echo "</pre>";
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}    
?>
