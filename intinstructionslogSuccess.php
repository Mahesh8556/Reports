<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
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
$success = "'S'";
$dash = "'-'";



$query = 'SELECT 
     INTINSTRUCTION."CR_ACTYPE", INTINSTRUCTION."CR_AC_NO",
	SUBSTR(INTINSTRUCTION."CR_AC_NO",7,3)||'.$dash.'|| RIGHT(INTINSTRUCTION."CR_AC_NO",6) ||'.$dash.'|| vwallmaster.ac_name AS "CRACNO",
INTINSTRUCTION."DR_ACTYPE", INTINSTRUCTION."DR_AC_NO", 
SUBSTR(INTINSTRUCTION."DR_AC_NO",7,3)||'.$dash.'|| RIGHT(INTINSTRUCTION."DR_AC_NO",6)||'.$dash.'||  view1.ac_name AS "DRACNO",
INTINSTRUCTION."SI_FREQUENCY", INTINSTRUCTIONSLOG."TRAN_DATE", INTINSTRUCTIONSLOG."TRAN_TIME", 
INTINSTRUCTIONSLOG."TRAN_AMOUNT", INTINSTRUCTIONSLOG."INSTRUCTION_NO", INTINSTRUCTIONSLOG."DAILYTRAN_TRAN_NO", 
INTINSTRUCTIONSLOG."EXPECTED_EXECUTION_DATE", INTINSTRUCTIONSLOG."LAST_INT_DATE", INTINSTRUCTIONSLOG."PARTICULARS"  
FROM INTINSTRUCTION
left join vwallmaster on vwallmaster.ac_no=INTINSTRUCTION."CR_AC_NO"
left join vwallmaster view1 on view1.ac_no=INTINSTRUCTION."DR_AC_NO"
INNER JOIN INTINSTRUCTIONSLOG ON INTINSTRUCTIONSLOG."INSTRUCTION_NO" = INTINSTRUCTION."INSTRUCTION_NO"
WHERE INTINSTRUCTIONSLOG."SUCCESS_STATUS"='.$success.'
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) >= CAST('.$stadate.' AS DATE) 
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
AND INTINSTRUCTION."BRANCH_CODE" = '.$branched.'
AND INTINSTRUCTION."SI_FREQUENCY"='.$frequency.'';

// if($sort == 'Debit'){
//     $query .= ' order by INTINSTRUCTION."DR_AC_NO" ASC';
// }else{
//     $query .= ' order by INTINSTRUCTION."CR_AC_NO" ASC';
// }

// echo $query;


$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
$SchemeTotal = 0;
$GrandTotal = 0;
while($row = pg_fetch_assoc($sql)){
    if($row['SI_FREQUENCY'] == 'M')
      {
        $frequency = 'Monthly';
      }
      else if($row['SI_FREQUENCY'] == 'Q')
      {
        $frequency = 'Querterly';
      }
      else if($row['SI_FREQUENCY'] == 'F')
      {
        $frequency = 'Fixed Querterly';
      }else if($row['SI_FREQUENCY'] == 'H')
      {
        $frequency = 'Half Yearly';
      }else if($row['SI_FREQUENCY'] == 'None')
      {
        $frequency = 'None';
      }
    $SchemeTotal = $SchemeTotal + $row['TRAN_AMOUNT'];
    $GrandTotal  = $SchemeTotal;
    $tmp=[
        'NAME'=> $branchName,
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'TRAN_DATE'     => $row['TRAN_DATE'],
        'DR_ACNOTYPE'   => $dr_acnotype,
        'DR_ACTYPE'     => $row['DR_ACTYPE'],
        'DR_AC_NO'      => $row['DRACNO'],
        "DR_NAME"       => $drName, 
        'DAILYTRAN_TRAN_NO'=> $row['DAILYTRAN_TRAN_NO'],
        'EXPECTED_EXECUTION_DATE' => $row['EXPECTED_EXECUTION_DATE'],
        'LAST_INT_DATE' => $row['LAST_INT_DATE'],
        'TRAN_AMOUNT'   => sprintf("%.2f",($row['TRAN_AMOUNT'] + 0.0 ) ),
        'CR_ACNOTYPE'   => $cr_acnotype,
        'CR_ACTYPE'     => $row['CR_ACTYPE'],
        'CR_AC_NO'      => $row['CRACNO'],
        "CR_NAME"       => $crName,
        'PARTICULARS'   => $row['PARTICULARS'],
        'SI_FREQUENCY'  =>  $frequency,
        'SchemeTotal'   => sprintf("%.2f",($SchemeTotal + 0.0 ) ),
        'GrandTotal'    => sprintf("%.2f",($GrandTotal + 0.0 ) ),
        'bankName' => $bankName,
        'stadate_'=> $stadate_,
        'edate_'=> $edate_,
        'branched'=> $branched,
        'success'=> $success,
        'frequency'=> $frequency,
        'startscheme'=> $startscheme,
        // 'sort'=> $sort,
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

