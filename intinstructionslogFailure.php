<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/intinstructionslogSuccess1.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$bankName   = $_GET['bankName'];
$branchName = $_GET['branchName']; 
$stadate    = $_GET['stadate'];
// echo $stadate;
$edate = $_GET['edate'];
$branched = $_GET['branched'];
$success = $_GET['success'];
// $failure = $_GET['failure'];
$frequency = $_GET['frequency'];
$startscheme = $_GET['startscheme'];
// $sort = $_GET['sort'];

$bankName = str_replace("'", "", $bankName);
$stadate_ = str_replace("'", "", $stadate);
$edate_ = str_replace("'", "", $edate);
$startscheme = str_replace("'", "", $startscheme);
$branchName = str_replace("'","",$branchName);

$dateformat ="'DD/MM/YYYY'";
$success = "'F'";


$query = 'SELECT  INTINSTRUCTION."CR_ACTYPE", INTINSTRUCTION."CR_AC_NO",
INTINSTRUCTION."DR_ACTYPE", INTINSTRUCTION."DR_AC_NO", INTINSTRUCTION."SI_FREQUENCY", INTINSTRUCTIONSLOG."TRAN_DATE", INTINSTRUCTIONSLOG."TRAN_TIME", 
INTINSTRUCTIONSLOG."TRAN_AMOUNT", INTINSTRUCTIONSLOG."INSTRUCTION_NO", INTINSTRUCTIONSLOG."DAILYTRAN_TRAN_NO", 
INTINSTRUCTIONSLOG."EXPECTED_EXECUTION_DATE", INTINSTRUCTIONSLOG."LAST_INT_DATE", INTINSTRUCTIONSLOG."PARTICULARS"  
FROM INTINSTRUCTION
INNER JOIN INTINSTRUCTIONSLOG ON INTINSTRUCTIONSLOG."INSTRUCTION_NO" = INTINSTRUCTION."INSTRUCTION_NO"
WHERE INTINSTRUCTIONSLOG."SUCCESS_STATUS"='.$success.'
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) >= CAST('.$stadate.' AS DATE)  
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
AND INTINSTRUCTION."BRANCH_CODE" = '.$branched.'';
// echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
$SchemeTotal = 0;
$GrandTotal = 0;
while($row = pg_fetch_assoc($sql)){
    $crName = '-';
    $drName = '-';
    $dr_acnotype = '-';
    $cr_acnotype = '-';
    
    //get name from vwallmaster using cr / dr acno
    if($row['DR_ACTYPE'] !=''){
    $getData = pg_query($conn,"SELECT * FROM VWALLMASTER 
    inner join schemast on schemast.id = VWALLMASTER.ac_type where ac_type='".$row['DR_ACTYPE']."' AND acno='".$row['DR_AC_NO']."'");
    while($row1 = pg_fetch_assoc($getData)){
        $drName = $row1['ac_name'];
        $dr_acnotype = $row1['S_ACNOTYPE'];
    }}

    if($row['CR_ACTYPE'] !=''){
    $getData = pg_query($conn,"SELECT * FROM VWALLMASTER 
    inner join schemast on schemast.id = VWALLMASTER.ac_type where VWALLMASTER.ac_type='".$row['CR_ACTYPE']."' AND VWALLMASTER.acno='".$row['CR_AC_NO']."'");
    while($row1 = pg_fetch_assoc($getData)){
        $crName = $row1['ac_name'];
        $cr_acnotype = $row1['S_ACNOTYPE'];
    }}
    
    $SchemeTotal = $SchemeTotal + $row['TRAN_AMOUNT'];
    $GrandTotal  = $SchemeTotal;
    $tmp=[
        'NAME'=> $branchName,
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'TRAN_DATE'     => $row['TRAN_DATE'],
        'DR_ACNOTYPE'   => $dr_acnotype,
        'DR_ACTYPE'     => $row['DR_ACTYPE'],
        'DR_AC_NO'      => $row['DR_AC_NO'],
        "DR_NAME"       => $drName,
        'DAILYTRAN_TRAN_NO'=> $row['DAILYTRAN_TRAN_NO'],
        'EXPECTED_EXECUTION_DATE' => $row['EXPECTED_EXECUTION_DATE'],
        'LAST_INT_DATE' => $row['LAST_INT_DATE'],
        'TRAN_AMOUNT'   => sprintf("%.2f",($row['TRAN_AMOUNT'] + 0.0 ) ),
        'CR_ACNOTYPE'   => $cr_acnotype,
        'CR_ACTYPE'     => $row['CR_ACTYPE'],
        'CR_AC_NO'      => $row['CR_AC_NO'],
        "CR_NAME"       => $crName,
        'PARTICULARS'   => $row['PARTICULARS'],
        'SI_FREQUENCY'  => $row['SI_FREQUENCY'],
        'SchemeTotal'   => sprintf("%.2f",($SchemeTotal + 0.0 ) ),
        'GrandTotal'    => sprintf("%.2f",($GrandTotal + 0.0 ) ),
        'bankName' => $bankName,
        'stadate_'=> $stadate_,
        'edate_'=> $edate_,
        'branched'=> $branched,
        'success'=> $success,
        'frequency'=> $frequency,
        'startscheme'=> $startscheme,
        'sort'=> $sort,
    ];
    $data[$i]=$tmp;
    $i++;
}



ob_end_clean();
// echo $query;
// print_r($data);
$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

}
?>  

