<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/BnkODRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$tem_perOD = "'%PeriodicallyOverDraft%'";
$tem_perOD = "'%TemporaryOverDraft%'";
$startDate = "'03/03/2016'";
$endDate = "'28/03/2022'";
$dateformate ="'DD/MM/YYYY'";
 
 $bankName = $_GET['bankName'];
 $branch = $_GET['branch'];
 $startingcode = $_GET['startingcode'];
 $endingcode = $_GET['endingcode'];
 $schemecode = $_GET['schemecode'];
 $tem_perOD = $_GET['tem_perOD'];

$bankName = str_replace("'", "", $bankName);
// $branchName = str_replace("'", "", $branchName);


$query = 'SELECT todtran."AC_NO",todtran."AC_ODDATE",todtran."AC_TYPE",
          cast(todtran."AC_ODAMT" as int),lnmaster."AC_NAME", todtran."AC_SODAMT",ownbranchmaster."NAME"
          from todtran
          inner join lnmaster on cast(todtran."AC_NO" as bigint) = lnmaster."AC_NO"
          inner join ownbranchmaster on lnmaster."BRANCH_CODE" =ownbranchmaster."id"
          where 
          todtran."AC_SODAMT" LIKE '.$tem_perOD.'AND
          todtran."AC_SODAMT" LIKE '.$tem_perOD.' and
          ownbranchmaster."CODE" = '.$branch.'
          and cast(todtran."AC_ODDATE" as date) 
          between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
          order by  todtran."AC_TYPE" asc';

        //    echo $query;

$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0 ;
$GROUP_TOTAL = 0 ;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

$type = '';

while($row = pg_fetch_assoc($sql)){
   
    $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_ODAMT']; 
    $vartype=$row['AC_TYPE'];

    if($type == ''){
        $type = $row['AC_TYPE'];
    }
    if($type == $row['AC_TYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_ODAMT'];
    }else{
        $type = $row['AC_TYPE'];
        $GROUP_TOTAL = 0 ;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_ODAMT'];
    }

        $tmp=[
        'AC_NO'=> $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_ODDATE' => $row['AC_ODDATE'],
        'AC_ODAMT' => $row['AC_ODAMT'],
        'BRANCH_CODE' => $row['BRANCH_CODE'],
        'grandtotal' =>  $GRAND_TOTAL ,
        'grouptotal' =>  $GROUP_TOTAL,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'NAME' => $row['NAME'],

        'bankName' => $bankName,
        'branch'=> $branch,
        'startingcode' => $startingcode,
        'endingcode' => $endingcode,
        'schemecode' => $schemecode,
        'tem_perOD' => $tem_perOD,
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

    }
?>
    

