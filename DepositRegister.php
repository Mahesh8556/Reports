<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepositRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'03/03/2016'";
$endDate = "'28/03/2016'";
// variables
// $startDate = $_GET['startDate'];
// $endDate = $_GET['endDate'];
$dateformate = "'DD/MM/YYYY'";


$query = 'SELECT dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_OPDATE",dpmaster."AC_ASON_DATE",
          dpmaster."AC_SCHMAMT",dpmaster."AC_MONTHS",dpmaster."AC_DAYS",dpmaster."AC_INTRATE",
          dpmaster."AC_EXPDT",dpmaster."AC_MATUAMT",dpmaster."AC_ACNOTYPE",
          ownbranchmaster."NAME",customeraddress."AC_ADDR"
          From dpmaster
          Inner Join ownbranchmaster on
          dpmaster."BRANCH_CODE" = ownbranchmaster."id" 
          Inner Join customeraddress on 
          dpmaster."idmasterID" = customeraddress."idmasterID"';

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_DETOT = 0;
$MATUR_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_DETOT = $GRAND_DETOT + $row['AC_SCHMAMT'];
    $MATUR_TOTAL = $MATUR_TOTAL + $row['AC_MATUAMT'];

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $SCHM_TOTAL = $SCHM_TOTAL + $row['AC_SCHMAMT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $SCHM_TOTAL = 0;
        $SCHM_TOTAL = $SCHM_TOTAL + $row['AC_SCHMAMT'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_MATUAMT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $GROUP_TOTAL = 0;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_MATUAMT'];
    }

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_ADDR' => $row['AC_ADDR'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_ASON_DATE' => $row['AC_ASON_DATE'],
        'AC_SCHMAMT' => $row['AC_SCHMAMT'],
        'AC_MONTHS'=> $row['AC_MONTHS'],
        'AC_DAYS'=> $row['AC_DAYS'],
        'AC_INTRATE'=> $row['AC_INTRATE'],
        'AC_NNAME' => $row['AC_NNAME'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_MATUAMT'=> $row['AC_MATUAMT'],
        'AC_ASON_DATE'=> $row['AC_ASON_DATE'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'NAME'=> $row['NAME'],
        'granddepoamt'=> $GRAND_DETOT,
        'grandmatuamt'=> $MATUR_TOTAL,
        'schmdepo'=> $SCHM_TOTAL,
        'schmematu'=> $GROUP_TOTAL,

    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

} 
 ?>
    

