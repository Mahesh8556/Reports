<?php
include "main.php";
ob_start(); 
// memory size
$len = 268435456;
// ini_set('memory_limit', '256M');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;


$filename = __DIR__.'/Nomineelist.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
$conn = pg_connect("host=127.0.0.1 dbname=cbsdb_live user=postgres password=admin");


// variables
$startDate = "'01/04/2020'";
$endDate = "'01/07/2020'";
$dateformate = "'DD/MM/YYYY'";
$startDate = $_GET['startDate'];
// $endDate = $_GET['enddate'];
$dateformate = "'DD/MM/YYYY'";
$scheme = $_GET['scheme']; 
$branch = $_GET['branch'];
$bankName = $_GET['bankName'];

//get data from table

$query ='SELECT nomineelink."AC_NRELA",nomineelink."AC_NNAME",shmaster."AC_NO"
        from nomineelink
        inner join shmaster on nomineelink."sharesID" =shmaster."id"
        Union All
        Select nomineelink."AC_NRELA",nomineelink."AC_NNAME",dpmaster."AC_NO"
        from nomineelink
        inner join dpmaster on nomineelink."DPMasterID" =dpmaster."id"
        Union All
        Select nomineelink."AC_NRELA",nomineelink."AC_NNAME",pgmaster."AC_NO"
        from nomineelink
        inner join pgmaster on nomineelink."pigmyAID" =pgmaster."id"
        where cast("AC_CLOSEDT" as date) 
        between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')';

$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'AC_NRELA'=> $row['AC_NRELA'],
        'AC_NNAME'=> $row['AC_NNAME'],
        'AC_NO' => $row['AC_NO'],
        'AC_ACNOTYPE' => $_GET['scheme'],
        'branch' => $branch, 
        'startDate' => $startDate,
        'endDate' => $endDate,
        'scheme' => $scheme,
        'bankName' => $bankName,
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

