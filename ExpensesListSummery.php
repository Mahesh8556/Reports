<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ExpensesListSummery.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'01/01/2000' ";
$endDate = "'30/06/2020'";
$dateformate = "'DD/MM/YYYY'";
$D = "'D'";
$report_type = "'EXPENSES'";

$query = 'SELECT 
          coalesce(case when "TRAN_DRCR" = '.$D.' Then
          cast("TRAN_AMOUNT" as float) else 0 end, 0) as drtranamt,
          accotran."TRAN_ACNO", accotran."TRAN_AMOUNT",
          acmaster."AC_NAME", 
          glreportlink."REPORT_TYPE",glreportlink."CODE"
          FROM accotran 
          Inner Join acmaster on
          cast(accotran."TRAN_ACNO" as integer) = acmaster."AC_NO"  
          Inner Join glreportlink on
          accotran."TRAN_ACNOTYPE"  = glreportlink."AC_ACNOTYPE" Where
          cast(glreportlink."CODE" as integer) = 1 and 
          glreportlink."REPORT_TYPE" = '.$report_type.' and 
          cast("TRAN_DATE" as date)
          between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
          ORDER BY accotran."TRAN_DATE" ';
 

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_DTOTAL = 0;
$GRAND_CTOTAL = 0;

while($row = pg_fetch_assoc($sql))  
{
  // grand-total
  $GRAND_DTOTAL = $GRAND_DTOTAL + $row['drtranamt'];

  if($type == ''){
    $type = $row['TRAN_ACNO'];
  }
  if($type == $row['TRAN_ACNO']){
    $SCHM_DTOTAL = $SCHM_DTOTAL + $row['drtranamt'];
  }else{
    $type = $row['TRAN_ACNO'];
    $SCHM_DTOTAL = 0;
    $SCHM_DTOTAL = $SCHM_DTOTAL + $row['drtranamt'];
  }

    $tmp=[
        'TRAN_DATE' => $row['TRAN_DATE'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'AC_NAME'=> $row['AC_NAME'],
        'drtranamt'=> $row['drtranamt'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'dgrandtot' => $GRAND_DTOTAL

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
    
 ?>

