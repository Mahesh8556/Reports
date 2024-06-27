<?php
 ob_start();
include "main.php";
require_once('dbconnect.php'); 


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/InterestInstruct.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$stdate_ = $_GET['stdate'];
$bankName = $_GET['bankName'];
$etdate_ = $_GET['etdate'];
$revoke = $_GET['revoke'];
$branch = $_GET['branch'];
$branchName = $_GET['branchName'];


$bankName = str_replace("'", '', $bankName);
$stdate = str_replace("'", '', $stdate_);
$etdate = str_replace("'", '', $etdate_);
$branchName = str_replace("'", '', $branchName);
$revoke_ = str_replace("'", '', $revoke);
$SI_FREQUENCY = "'M'";
$SUCCESS_STATUS = "'S'";

// echo $branch;


// $query='SELECT 
// INTINSTRUCTION."INSTRUCTION_NO", INTINSTRUCTION."INSTRUCTION_DATE", INTINSTRUCTION."FROM_DATE", 
// INTINSTRUCTION."DR_ACTYPE", INTINSTRUCTION."DR_AC_NO", INTINSTRUCTION."DR_PARTICULARS", 
// INTINSTRUCTION."CR_ACTYPE", INTINSTRUCTION."CR_AC_NO", INTINSTRUCTION."CR_PARTICULARS", 
// INTINSTRUCTION."SI_FREQUENCY", INTINSTRUCTION."LAST_EXEC_DATE", INTINSTRUCTION."SYSADD_LOGIN",
// VWALLMASTER."ac_name" acName1, VWALLMASTER_DR."ac_name"
// FROM INTINSTRUCTION
// LEFT OUTER JOIN VWALLMASTER ON VWALLMASTER.acno = CAST(INTINSTRUCTION."CR_AC_NO" AS bigint)
// LEFT OUTER JOIN VWALLMASTER as VWALLMASTER_DR ON VWALLMASTER_DR.acno = CAST(INTINSTRUCTION."DR_AC_NO" AS integer)
// WHERE  intinstruction."BRANCH_CODE" = '.$branch.'
// AND VWALLMASTER.ac_type = INTINSTRUCTION."DR_ACTYPE"
// AND VWALLMASTER_DR.ac_type = INTINSTRUCTION."CR_ACTYPE"';

$query = 'SELECT INTINSTRUCTION."CR_ACTYPE",
INTINSTRUCTION."CR_AC_NO",
INTINSTRUCTION."DR_ACTYPE",
INTINSTRUCTION."DR_AC_NO",
INTINSTRUCTION."SI_FREQUENCY",
INTINSTRUCTIONSLOG."TRAN_DATE",
INTINSTRUCTIONSLOG."TRAN_TIME",
INTINSTRUCTIONSLOG."TRAN_AMOUNT",
INTINSTRUCTIONSLOG."INSTRUCTION_NO",
INTINSTRUCTIONSLOG."DAILYTRAN_TRAN_NO",
INTINSTRUCTIONSLOG."EXPECTED_EXECUTION_DATE",
INTINSTRUCTIONSLOG."LAST_INT_DATE",
INTINSTRUCTION."DR_PARTICULARS",
INTINSTRUCTION."CR_PARTICULARS",
INTINSTRUCTION."INSTRUCTION_DATE",
INTINSTRUCTION."SYSADD_LOGIN",
INTINSTRUCTION."FROM_DATE",
INTINSTRUCTION."INSTRUCTION_NO",
INTINSTRUCTION."INSTRUCTION_DATE",
INTINSTRUCTION."LAST_EXEC_DATE",
INTINSTRUCTIONSLOG."PARTICULARS"
FROM INTINSTRUCTION
INNER JOIN INTINSTRUCTIONSLOG ON INTINSTRUCTIONSLOG."INSTRUCTION_NO" = INTINSTRUCTION."INSTRUCTION_NO"
WHERE INTINSTRUCTIONSLOG."SUCCESS_STATUS" = '.$SUCCESS_STATUS.'
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) >= CAST('.$stdate_.' AS DATE)
AND CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) <= CAST('.$etdate_.' AS DATE)
AND INTINSTRUCTION."BRANCH_CODE" = '.$branch.'
AND INTINSTRUCTION."SI_FREQUENCY" = '.$SI_FREQUENCY.'

';

echo $revoke;

$type = '';

if($revoke == '1'){
    $type='Revoke';
    $query .= ' AND intinstruction."REVOKE_DATE" IS NOT NULL AND (cast("REVOKE_DATE" as date) between cast('. $stdate_ .' as date) 
    AND cast('. $etdate_ .' as date)) ORDER BY INTINSTRUCTION."CR_AC_NO" ,CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) ASC';
}else{
    $type='Active';
    $query .= ' AND (cast("INSTRUCTION_DATE" as date) between cast('. $stdate_ .' as date) 
    AND cast('. $etdate_ .' as date)) ORDER BY INTINSTRUCTION."CR_AC_NO" ,CAST(INTINSTRUCTIONSLOG."TRAN_DATE" AS DATE) ASC';
}


// echo $query;

$branchData = pg_query($conn,'select * from ownbranchmaster where id ='.$branch);
while($row = pg_fetch_assoc($branchData)){
    $branchName = $row['NAME'];
}
$sql =pg_query($conn,$query);
$i = 0;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else{

    while ($row = pg_fetch_assoc($sql)) {

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
        //cr schemast data fetch
        $cr_schemast = pg_query($conn,'select * from schemast where id = '.$row['DR_ACTYPE']);
        $dr_schemast = pg_query($conn,"select * from schemast where id = ".$row['CR_ACTYPE']);
        $drAcType = '000';
        $crAcType = '000';
        while($row1 = pg_fetch_assoc($cr_schemast)){
            $drAcType = $row1['S_APPL'];
        }

        while($row2 = pg_fetch_assoc($dr_schemast)){
            $crAcType = $row2['S_APPL'];
        }
        $tmp = [
            'SIREC_DATE' => $row['FROM_DATE'],
            'SI_NO' => $row['INSTRUCTION_NO'],
            'DR_ACTYPE' => $drAcType,
            'DR_AC_NO' => $row['DR_AC_NO'],
            'DR_PARTICULARS' => $row['DR_PARTICULARS'],
            'CR_ACTYPE' => $crAcType,
            'SCHEME' => $row['SCHEME'],
            'CR_AC_NO' => $row['CR_AC_NO'],
            'CR_PARTICULARS' => $row['CR_PARTICULARS'],
            'INSTRUCTION_DATE' => $row['INSTRUCTION_DATE'],
            'SI_FREQUENCY' => $frequency,
            'LAST_EXEC_DATE' => $row['LAST_EXEC_DATE'],
            'SYSADD_LOGIN' => $row['SYSADD_LOGIN'],
            'ac_namecr' => $row['acname1'],
            'ac_name' => $row['ac_name'],
            'UserCode' => $row['SYSADD_LOGIN'],
            'tilldate' => $row['LAST_EXEC_DATE'],

            
            'NAME' => $branchName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
         


            'branch' => $branch,
            'stdate_' => $stdate,
            'etdate_' => $etdate,
            'revoke' => $revoke,
            'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
    
}
    ob_end_clean();
    $config = ['driver' => 'array', 'data' => $data];
    // // echo $query;
    // print_r($data);
    $repandt = new PHPJasperXML();
    $repandt->load_xml_file($filename)    
        ->setDataSource($config)
        ->export('Pdf');
    
    
}
?>
