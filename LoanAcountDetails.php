<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LoanAcountDetails.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


 //variables


$START_DATE = $_GET['START_DATE'];
$END_DATE = $_GET['END_DATE'];    
$BRANCH = $_GET['BRANCH'];

$dateformate = "'DD/MM/YYYY'";



 $query = '.$START_DATE.' . '.$START_DATE.'. '.$END_DATE.';

           // echo $query; 

          
$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql))
{ 

     $tmp=[
        
        
       'BRANCH' => $BRANCH,
       'START_DATE' => $START_DATE,
       'END_DATE'  => $END_DATE,
         ];
    
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?>  