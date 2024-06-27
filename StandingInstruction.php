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
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName  = $_GET['bankName'];
$stdate    = $_GET['stdate'];
$etdate    = $_GET['etdate'];
$branchName = $_GET['branchName'];
// echo $etdate;
// echo $Branch;
$REVOKE_INST = $_GET['REVOKE_INST'];
$Branch  = $_GET['Branch'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
// $branchName = str_replace("'", "", $branchName);

// $query = 'SELECT standinstruction."INSTRUCTION_NO",standinstruction."DR_AC_NO",standinstruction."DR_PARTICULARS",
//           standinstruction."CR_AC_NO",standinstruction."CR_PARTICULARS",standinstruction."INSTRUCTION_DATE",
//           standinstruction."SI_FREQUENCY",standinstruction."LAST_EXEC_DATE",schemast."S_NAME" as dname,schemast1."S_NAME" as cname,
//           standinstruction."PAYINT_AMOUNT"
//           from standinstruction
//           inner join schemast on schemast.id = standinstruction."DR_ACTYPE"
//           inner join schemast as schemast1 on schemast1.id = standinstruction."CR_ACTYPE"
//           WHERE standinstruction."BRANCH_CODE" = '.$Branch.' 
//           and cast("INSTRUCTION_DATE" as date) 
//           between cast('.$stdate.' as date) and cast('.$etdate.' as date) ';



$query='SELECT STANDINSTRUCTION."INSTRUCTION_NO",
STANDINSTRUCTION."DR_AC_NO",
STANDINSTRUCTION."DR_PARTICULARS",
STANDINSTRUCTION."CR_AC_NO",
STANDINSTRUCTION."CR_PARTICULARS",
STANDINSTRUCTION."INSTRUCTION_DATE",
STANDINSTRUCTION."REVOKE_DATE",
STANDINSTRUCTION."SI_FREQUENCY",
STANDINSTRUCTION."LAST_EXEC_DATE",
SCHEMAST."S_NAME" AS DNAME,
SCHEMAST1."S_NAME" AS CNAME,
STANDINSTRUCTION."PAYINT_AMOUNT"
FROM STANDINSTRUCTION
INNER JOIN SCHEMAST ON SCHEMAST.ID = STANDINSTRUCTION."DR_ACTYPE"
INNER JOIN SCHEMAST AS SCHEMAST1 ON SCHEMAST1.ID = STANDINSTRUCTION."CR_ACTYPE"
WHERE STANDINSTRUCTION."BRANCH_CODE" = '.$Branch.'
AND CAST(STANDINSTRUCTION."INSTRUCTION_DATE" AS date) BETWEEN CAST('.$stdate.' AS date) AND CAST('.$etdate.' AS date)
AND CAST(STANDINSTRUCTION."REVOKE_DATE" AS DATE) IS NULL';
        //   echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'SIREC_DATE' => $row['SIREC_DATE'],
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
        'TRAN_AMOUNT'=>sprintf("%.2f",($row['PAYINT_AMOUNT'] + 0.0 ) ),
        'totalamt' =>sprintf("%.2f", ($GRAND_TOTAL + 0.0)),

        'stdate_' => $stdate_,
        'etdate' => $etdate_,
        'REVOKE_INST' => $REVOKE_INST,
        'Branch' => $Branch,
        'bankName' => $bankName,
        'type'=> 'Active'
    ];
    $data[$i]=$tmp;
    $i++;
    
}
// echo $query;
ob_end_clean();

// print_r($data)
$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}   
?>
