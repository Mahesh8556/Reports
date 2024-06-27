<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;


$filename = __DIR__.'/nominee_list2.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');




// variables

$scheme = $_GET['scheme']; 
$Date = $_GET['Date'];
$branchname = $_GET['branchname'];
$bankname = $_GET['bankname'];
$branchcode = $_GET['branchcode'];
$status="1";


//string replacements
$bankname1 = str_replace("'", "", $bankname);
$branchname1 = str_replace("'", "", $branchname);
$Date1 = str_replace("'", "", $Date);


//get data from table

$query =




'SELECT  DPMASTER."AC_ACNOTYPE", DPMASTER."AC_TYPE", DPMASTER."AC_NO", DPMASTER."AC_NAME", NOMINEELINK."AC_NNAME",
NOMINEELINK."AC_NRELA", SCHEMAST."S_NAME" FROM nomineelink, dpmaster LEFT OUTER JOIN schemast ON
(DPMASTER."AC_TYPE" = SCHEMAST.ID) WHERE DPMASTER."id" = NOMINEELINK."DPMasterID" AND
( cast(DPMASTER."AC_OPDATE" as date) IS NULL OR cast(DPMASTER."AC_OPDATE" as date) <= DATE('.$Date.')) AND
(cast(DPMASTER."AC_CLOSEDT" as date) IS NULL OR cast(DPMASTER."AC_CLOSEDT" as date) >= DATE('.$Date.')) AND
DPMASTER."AC_TYPE" ='.$scheme.' and DPMASTER."status"='.$status.' AND "SYSCHNG_LOGIN" 
IS NOT NULL AND DPMASTER."BRANCH_CODE"='.$branchcode.' ORDER BY DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE", DPMASTER."AC_NO"';








$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql)){

    // $addacno=$addacno + $row['AC_NO'];

    $tmp=[

        // 'flag'=>1,
        'flag'=>0,

        'AC_NRELA'=> $row['AC_NRELA'],
        'AC_NNAME'=> $row['AC_NNAME'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_NO' =>$row['AC_NO'],
        'date' => $Date1,
        'scheme' => $scheme,
        'branchname' => $branchname1,
        'bankname' => $bankname1,
        'branchcode' => $branchcode,
        
       
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
// 
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>    

