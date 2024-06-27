<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ExpensesListDetail.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = "'01/01/2000' ";
$endDate = "'30/12/2022'";
$dateformate = "'DD/MM/YYYY'";
$D = "'D'";
$C = "'C'";
$report_type = "'EXPENSES'";

$query = 'SELECT 
          coalesce(case when "TRAN_DRCR" ='.$C.' Then 
          cast("TRAN_AMOUNT" as float) else 0 end, 0) as CrTranAmt,
          coalesce(case when "TRAN_DRCR" = '.$D.' Then
          cast("TRAN_AMOUNT" as float) else 0 end, 0) as DrTranAmt,
          accotran."TRAN_DATE", accotran."NARRATION",accotran."TRAN_DRCR", 
          accotran."TRAN_AMOUNT",accotran."TRAN_TYPE",accotran."TRAN_ACNO",
          accotran."TRAN_ACNOTYPE",accotran."BRANCH_CODE",accotran."TRAN_ACTYPE",
          glreportlink."REPORT_TYPE",glreportlink."CODE"
          From accotran
          Inner Join glreportlink on
          accotran."TRAN_ACNOTYPE"  = glreportlink."AC_ACNOTYPE" Where
          cast(glreportlink."CODE" as integer) = 1 and 
          glreportlink."REPORT_TYPE" = '.$report_type.' and
          cast("TRAN_DATE" as date)
          between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
          Union 
          SELECT 
          coalesce(case when "TRAN_DRCR" ='.$C.' Then 
          cast("TRAN_AMOUNT" as float) else 0 end, 0) as CrTranAmt,
          coalesce(case when "TRAN_DRCR" = '.$D.' Then
          cast("TRAN_AMOUNT" as float) else 0 end, 0) as DrTranAmt,
          dailytran."TRAN_DATE", dailytran."NARRATION",dailytran."TRAN_DRCR", 
          dailytran."TRAN_AMOUNT",dailytran."TRAN_TYPE",cast(dailytran."TRAN_ACNO" as bigint),
          dailytran."TRAN_ACNOTYPE",dailytran."BRANCH_CODE",dailytran."TRAN_ACTYPE",
          glreportlink."REPORT_TYPE",glreportlink."CODE"
          From dailytran
          Inner Join glreportlink on
          cast(dailytran."BRANCH_CODE" as integer)  = cast(glreportlink."CODE" as integer) Where
          cast(glreportlink."CODE" as integer) = 1 and 
          glreportlink."REPORT_TYPE" = '.$report_type.' and
          cast("TRAN_DATE" as date) 
          between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')';

          // echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_DTOTAL = 0;
$GRAND_CTOTAL = 0;
$SUBTRACT = 0;

while($row = pg_fetch_assoc($sql))  
{
  // grand-total
  $GRAND_DTOTAL = $GRAND_DTOTAL + $row['drtranamt'];
  $GRAND_CTOTAL = $GRAND_CTOTAL + $row['crtranamt'];
  $SUBTRACT = $row['drtranamt'] - $row['crtranamt'];

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

  if($type == ''){
    $type = $row['TRAN_ACNO'];
  }
  if($type == $row['TRAN_ACNO']){
    $SCHM_CTOTAL = $SCHM_CTOTAL + $row['crtranamt'];
  }else{
    $type = $row['TRAN_ACNO'];
    $SCHM_CTOTAL = 0;
    $SCHM_CTOTAL = $SCHM_CTOTAL + $row['crtranamt'];
  }

    $tmp=[
        'TRAN_DATE' => $row['TRAN_DATE'],
        'NARRATION' => $row['NARRATION'],
        'crtranamt'=> $row['crtranamt'],
        'drtranamt'=> $row['drtranamt'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'dgrandtot'=> $GRAND_DTOTAL,
        'cgrandtot'=> $GRAND_CTOTAL,
        'dschmamt' => $SCHM_DTOTAL,
        'cschmamt' => $SCHM_CTOTAL,
        'subamt' => $SUBTRACT,

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

