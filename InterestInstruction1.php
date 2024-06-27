<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Interestinstruct.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$dateformate = "'DD/MM/YYYY'";

$stdate = $_GET['stdate'];
$bankName = $_GET['bankName'];
$etdate = $_GET['etdate'];
$revoke = $_GET['revoke'];
$branch = $_GET['branch'];

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);


// $query = 'SELECT intinstruction."DR_AC_NO",intinstruction."DR_PARTICULARS",intinstruction."CR_AC_NO",
//           intinstruction."CR_PARTICULARS",intinstruction."INSTRUCTION_DATE",intinstruction."SI_FREQUENCY",
//           intinstruction."LAST_EXEC_DATE", intinstruction."DR_ACTYPE", intinstruction."CR_ACTYPE",
//           intinstruction."SYSADD_LOGIN",ownbranchmaster."NAME"
//           from intinstruction
//           Inner Join ownbranchmaster on intinstruction."BRANCH_CODE" = ownbranchmaster."id"

//           where cast("INSTRUCTION_DATE" as date) 
//           and intinstruction."BRANCH_CODE" = '.$branch.'
//           between to_date('.$stdate.','.$dateformate.') and to_date('.$etdate.','.$dateformate.') ';

    $query = 'SELECT "intinstruction"."INSTRUCTION_NO", cast("intinstruction"."INSTRUCTION_DATE" as date), "intinstruction"."FROM_DATE", "intinstruction"."DR_ACTYPE", "intinstruction"."DR_AC_NO", "intinstruction"."DR_PARTICULARS", "intinstruction"."CR_ACTYPE", "intinstruction"."CR_AC_NO", "intinstruction"."CR_PARTICULARS", "intinstruction"."SI_FREQUENCY", "intinstruction"."LAST_EXEC_DATE", "intinstruction"."USER_CODE", "vwallmaster"."ac_name", "vwallmaster"."ac_name",intinstruction."SYSADD_LOGIN"
    FROM intinstruction 
    left outer join "vwallmaster" 
    on "intinstruction"."CR_ACTYPE" = cast("vwallmaster"."ac_type" as integer) AND 
    cast("intinstruction"."CR_AC_NO" as bigint) = cast("vwallmaster"."ac_no" as bigint) AND 
    "intinstruction"."DR_ACTYPE" = cast("vwallmaster"."ac_type" as bigint) AND 
    "intinstruction"."DR_ACTYPE" = cast("vwallmaster"."ac_type" as bigint) AND 
    cast("intinstruction"."DR_AC_NO" as bigint) = cast("vwallmaster"."ac_no" as bigint) 
    and
    cast("intinstruction"."INSTRUCTION_DATE" as date)
    between cast( ' . $stdate_ . ' as date) and cast(' . $etdate_ . ' as date)
    and intinstruction."BRANCH_CODE" = '.$branch.'';

        //    echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'SIREC_DATE' => $row['SIREC_DATE'],
        'SI_NO'=> $row['SI_NO'],
        'DR_ACTYPE' => $row['DR_ACTYPE'],
        'DR_AC_NO' => $row['DR_AC_NO'],
        'DR_PARTICULARS' => $row['DR_PARTICULARS'],
        'CR_ACTYPE' => $row['CR_ACTYPE'],
        'SCHEME'=> $row['SCHEME'],
        'CR_AC_NO'=> $row['CR_AC_NO'],
        'CR_PARTICULARS'=> $row['CR_PARTICULARS'],
        'INSTRUCTION_DATE' => $row['INSTRUCTION_DATE'],
        'SI_FREQUENCY'=> $row['SI_FREQUENCY'],
        'LAST_EXEC_DATE'=> $row['LAST_EXEC_DATE'],
        'SYSADD_LOGIN'=> $row['SYSADD_LOGIN'],
        'startDate' => $startDate,
        'endDate' => $endDate,

        'branch' => $branch,
        'stdate_'=> $stdate_,
        'etdate_'=> $etdate_,
        'revoke'=> $revoke,
        'bankName' => $bankName,
    ];
    $data[$i]=$tmp;
    $i++;
    // echo "<pre>";
    // print_r($tmp);
    // echo "</pre>";
}
ob_end_clean();



// $config = ['driver'=>'array','data'=>$data];

// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');
    
    
}
?>
