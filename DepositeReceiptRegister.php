<?php
ob_start(); 
include "main.php";
//require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepositeReceiptRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$conn = pg_connect("host=127.0.0.1 dbname=bank user=postgres password=tushar");

// variables
 $print_date = $_GET['print_date'];
 $ac_type = $_GET['ac_type'];
 $sdate = $_GET['sdate'];
 $edate = $_GET['edate'];

$schemecode = "'TD'";
$dateformat ="'DD/MM/YYYY'";
$ac_type ="'15'";

$query ='SELECT TDRECEIPTISSUE."PRINT_DATE",cast('.$sdate.' as date), TDRECEIPTISSUE."PRINT_TIME",TDRECEIPTISSUE."REASON_OF_DUPLICATE", 
TDRECEIPTISSUE."RECEIPT_NO", dpmaster."AC_NAME", customeraddress."AC_HONO", customeraddress."AC_WARD",customeraddress."AC_ADDR", 
customeraddress."AC_GALLI", dpmaster."AC_ASON_DATE", dpmaster."AC_SCHMAMT", dpmaster."AC_MONTHS", dpmaster."AC_DAYS",
dpmaster."AC_INTRATE", dpmaster."AC_EXPDT", dpmaster."AC_MATUAMT", NOMINEELINK."AC_NNAME" NOMINEE ,dpmaster."AC_TYPE" as "SCHEME", schemast."S_APPL" AS  "SCHEME"
FROM TDRECEIPTISSUE,DPMASTER
inner join schemast on dpmaster."AC_TYPE" = schemast.id
left JOIN customeraddress on customeraddress."id" = dpmaster."idmasterID" 
left outer join NOMINEELINK on dpmaster."id" = nomineelink."DPMasterID"
WHERE TDRECEIPTISSUE."AC_ACNOTYPE"=dpmaster."AC_ACNOTYPE" 
AND CAST(TDRECEIPTISSUE."AC_TYPE" AS integer)= dpmaster."AC_TYPE" 
AND TDRECEIPTISSUE."AC_NO"=dpmaster."AC_NO"
AND dpmaster."AC_ACNOTYPE" = '.$schemecode.'
AND dpmaster."AC_TYPE" = '.$ac_type.''; 

//   echo $query;
          
$sql =  pg_query($conn,$query);

 $i = 0;


while($row = pg_fetch_assoc($sql))
{ 
    

    $tmp=[
        'PRINT_TIME' => $row['PRINT_TIME'],
        'REASON_OF_DUPLICATE' => $row['REASON_OF_DUPLICATE'],
        'RECEPIT_NO'=> $row['RECEPIT_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_NNAME'=> $row['AC_NNAME'],
        'AC_HONO'=> $row['AC_HONO'],
        'AC_WARD' => $row['AC_WARD'],
        'AC_ADDR'=> $row['AC_ADDR'],
        'AC_GALLI' => $row['AC_GALLI'],  
        'AC_ASON_DATE' =>  $row['AC_ASON_DATE'] ,
        'AC_SCHMAMT' =>  $row['AC_SCHMAMT'] ,
        'AC_MONTHS' => $row['AC_MONTHS'] ,
        'AC_DAYS' => $row['AC_DAYS'],
        'AC_INTRATE' => $row['AC_INTRATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_MATUAMT' => $row['AC_MATUAMT'],
        'AC_NNAME' => $row['AC_NNAME'],
        'AC_idmasterID' => $row['AC_idmasterID'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_TYPE'=> $row['AC_TYPE'],
        'AC_NO'=> $row['AC_NO'],
        'SCHEME'=>$row['SCHEME'],
        'ac_type' => '$ac_type',
        'sdate' => $sdate,
        'edate' => $edate ,
        'branch' => 'SULGAON' , 
        'print_date'=>  $print_date,
    ];
    $data[$i]=$tmp;
    $i++;  
}
ob_end_clean();
// echo $query;

$config = ['driver'=>'array','data'=>$data];
// echo $filename;
 //print_r($data)
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>