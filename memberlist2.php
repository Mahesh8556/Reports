<?php
include "main.php";
require_once('dbconnect.php');

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/memberlist2.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
//connect mysql database connection 
//$conn = mysqli_connect('localhost','root','','test');
//get data from enquiry table
$date = "'02/08/2022'";
$query = 'SELECT shmaster."AC_NO",shmaster."REF_ACNO",shmaster."AC_NAME",customeraddress."AC_ADDR",shmaster."AC_CATG",citymaster."CITY_NAME",citymaster."TALUKA_CODE",shmaster."AC_RETIRE_DATE" from shmaster
inner join
customeraddress on shmaster.ID = customeraddress.ID
inner join
categorymaster on shmaster."AC_CATG" = categorymaster."CODE"
inner join
citymaster on shmaster."id"=citymaster."id"
and shmaster."id"=citymaster."id" 
where  shmaster."BRANCH_CODE" = 1
and cast(shmaster."AC_OPDATE" as date) <= '.$date.'::date';
$sql = pg_query($conn,$query);


$i = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'AC_NO' => $row['AC_NO'], 
        'REF_ACNO' => $row['REF_ACNO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_ADDR' => $row['AC_ADDR'],
        'AC_CATG' => $row['AC_CATG'],
        'CITY_NAME' => $row['CITY_NAME'],
        'TALUKA_CODE' => $row['TALUKA_CODE'],
        'AC_RETIRE_DATE' => $row['AC_RETIRE_DATE'],
        
    ];
    $data[$i]=$tmp;
    $i++;
}


$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');