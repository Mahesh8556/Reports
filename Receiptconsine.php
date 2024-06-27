<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(500);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Receiptconsine.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$start2date = $_GET['start2date'];
$end1date = $_GET['end1date'];
$branched = $_GET['branched'];
// $tran = $_GET['tran'];
$print = $_GET['print'];
$penal = $_GET['penal'];

$bankName = str_replace("'", "", $bankName);
$start2date_ = str_replace("'", "", $start2date);
$end1date_ = str_replace("'", "", $end1date);
// $branchName = str_replace("'", "", $branchName);

$dateformate = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$cs = "'CS'";
$tr = "'TR'";

$query = 'SELECT 
          acmaster."AC_NAME", acmaster."AC_NO",ownbranchmaster."NAME",
          sum(coalesce(case when "TRAN_TYPE" = '.$cs.' and "TRAN_DRCR" = '.$c.' Then  cast("TRAN_AMOUNT" as float) else 0 end,0)) +
          sum(coalesce(case when "TRAN_TYPE" = '.$tr.' and "TRAN_DRCR" = '.$c.' Then cast("TRAN_AMOUNT" as float) else 0 end,0)) as crtranamt,
          sum(coalesce(case when "TRAN_TYPE" = '.$cs.' and "TRAN_DRCR" = '.$d.' Then cast("TRAN_AMOUNT" as float) else 0 end,0)) +
          sum(coalesce(case when "TRAN_TYPE" = '.$tr.' and "TRAN_DRCR" = '.$d.' Then  cast("TRAN_AMOUNT" as float) else 0 end,0)) as drtranamt
          From acmaster 
          Inner Join accotran on
          acmaster."AC_NO" = accotran."TRAN_ACNO"
          Inner Join ownbranchmaster on
          acmaster."BRANCH_CODE" = ownbranchmaster."id" 
          where cast("TRAN_DATE" as date) 
          between to_date('.$start2date.','.$dateformate.') and to_date('.$end1date.','.$dateformate.') 
          and acmaster."BRANCH_CODE" = '.$branched.'
          Group By  acmaster."AC_NAME", acmaster."AC_NO",ownbranchmaster."NAME" ';
        
        //   echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_CTOTAL = 0;
$GRAND_DTOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_CTOTAL = $GRAND_CTOTAL + $row['crtranamt'];
    $GRAND_DTOTAL = $GRAND_DTOTAL + $row['drtranamt'];

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'NAME'=> $row['NAME'],
        'crtranamt' => $row['crtranamt'],
        'drtranamt' => $row['drtranamt'],
        'cgrandtot' => $GRAND_CTOTAL,
        'dgrandtot' => $GRAND_DTOTAL,   
    
        'bankName' => $bankName,
        'start2date_' => $start2date_,
        'end1date_' => $end1date_,
        'branched' => $branched,
        // 'tran' => $tran,
        'print' => $print,
        'penal' => $penal,
    ];
    $data[$i]=$tmp;
    $i++;
    // echo "<pre>";
    // print_r($tmp);
    // echo "</pre>";
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}
?>   

