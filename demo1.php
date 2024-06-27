<?php
include "main.php";

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/Phpdemo1.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect mysql database connection 
$conn = mysqli_connect('localhost','root','','test');
//get data from enquiry table
$sql =  mysqli_query($conn,"SELECT * FROM employe");
$i = 0;
while($row = mysqli_fetch_array($sql)){
    $tmp=[
        'id' => $row['id'], 
        'name' => $row['name'],
        'department'=> $row['department'],
        'salary' => $row['salary']
        
    ];
    $data[$i]=$tmp;
    $i++;
}


$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

    