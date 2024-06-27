<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;


$filename = __DIR__.'/od.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');




// variables

$branchcode = $_GET['branchcode']; 
$Date = $_GET['Date'];
$branchname = $_GET['branchname'];
$Bankname = $_GET['Bankname'];

$sb= "in ('SB','CA')";
// $ca= "'CA'";


$Bankname1 = str_replace("'", "", $Bankname);
$branchname1 = str_replace("'", "", $branchname);
$Date1 = str_replace("'", "", $Date);

// echo $branchname1 ;



//get data from table

$query =

'SELECT VWALLMASTER.AC_ACNOTYPE,
VWALLMASTER.AC_TYPE,
VWALLMASTER.AC_NO,
VWALLMASTER.AC_NAME,
SCHEMAST."S_APPL",
SCHEMAST."S_NAME",
(COALESCE(CAST(TODTRAN."AC_ODAMT" AS float) ,0) + COALESCE( CAST(TODTRAN."AC_SODAMT" AS float),0)) AS TODAMOUNT
FROM VWALLMASTER
LEFT OUTER JOIN TODTRAN ON (CAST(VWALLMASTER.AC_TYPE AS CHARACTER varying) = TODTRAN."AC_TYPE"
AND VWALLMASTER.AC_NO = CAST(TODTRAN."AC_NO" AS CHARACTER varying))
LEFT JOIN SCHEMAST ON SCHEMAST.ID=CAST (TODTRAN."AC_TYPE" AS INTEGER)
WHERE "RELEASE_DATE" IS NULL
AND (CAST("AC_ODDATE" AS DATE) = DATE('.$Date.') or "AC_ODDATE" is null)
and VWALLMASTER."branch_code"='.$branchcode.'
AND VWALLMASTER."ac_acnotype" '.$sb.'
and 	(COALESCE(CAST(TODTRAN."AC_ODAMT" AS float),
			0) + COALESCE(CAST(TODTRAN."AC_SODAMT" AS float),

									0)) <> 0
ORDER BY ac_acnotype,AC_TYPE,AC_NO';

// echo $query ;
$sql =  pg_query($conn,$query);
$GRAND_TOTAL1 = 0;
$i = 0;
while($row = pg_fetch_assoc($sql)){
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["todamount"];
    $tmp=[
        'AC_NO'=> $row['ac_no'],
        'AC_NAME'=> $row['ac_name'],
        'TODAMOUNT'=> sprintf("%.2f", (abs($row['todamount'] +0.0))),
        'schemename' => $row["S_APPL"]. '    ' . $row['S_NAME'],
        'Bankname' => $Bankname1,
        'branchname' => $branchname1,
        "Date" => $Date1,
        'branchcode' => $branchcode,
        'total' => sprintf("%.2f", (abs($GRAND_TOTAL1+0.0))),
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

