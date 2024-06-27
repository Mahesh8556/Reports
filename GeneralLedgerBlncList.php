<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/GeneralLedgerBlcList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$dateformate = "'DD/MM/YYYY'";

$c = "'C'";
$d = "'D'";

$query = 'SELECT 
          coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as CreditAmt,
          coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as DebitAmt,
          dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_ACNOTYPE",ownbranchmaster."NAME",
          cast(dpmaster."AC_TYPE" as character varying),cast(dpmaster."AC_OP_BAL" as float),
          dpmaster."AC_OP_CD" FROM dpmaster,ownbranchmaster
          Union
          SELECT 
          coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as CreditAmt,
          coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as DebitAmt,
          acmaster."AC_NO",acmaster."AC_NAME",acmaster."AC_ACNOTYPE",ownbranchmaster."NAME",
          acmaster."AC_TYPE",cast(acmaster."AC_OP_BAL" as float),acmaster."AC_OP_CD"
          FROM acmaster,ownbranchmaster
          Union
          SELECT
          coalesce(case when "AC_OP_CD" = '.$c.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as CreditAmt,
          coalesce(case when "AC_OP_CD" = '.$d.' Then cast("AC_OP_BAL" as float) else 0 end, 0)as DebitAmt,
          shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_ACNOTYPE",ownbranchmaster."NAME",
          cast(shmaster."AC_TYPE" as character varying),cast(shmaster."AC_OP_BAL" as float),
          shmaster."AC_OP_CD" 
          FROM shmaster,ownbranchmaster';
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;
$CTOTAL = 0;
$DTOTAL = 0;

while($row = pg_fetch_assoc($sql)){ 

    $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_OP_BAL'];
    $CTOTAL = $CTOTAL + $row['creditamt'];
    $DTOTAL = $DTOTAL + $row['debitamt'];

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_OP_BAL'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $GROUP_TOTAL = 0;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_OP_BAL'];
    }

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_OP_BAL'=> ($row['AC_OP_BAL']),
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_TYPE'=> $row['AC_TYPE'],
        'AC_OP_CD'=> $row['AC_OP_CD'],
        'creditamt'=> $row['creditamt'],
        'debitamt'=> $row['debitamt'],
        'NAME'=> $row['NAME'],
        'grandbaltot'=> $GRAND_TOTAL,
        'crtotal'=> $CTOTAL,
        'drtotal'=> $DTOTAL,
        'schemtotal'=> $GROUP_TOTAL,
    ];
    $data[$i]=$tmp;
    $i++;
    
    // echo "<pre>";
    // print_r($tmp);
    // echo "</pre>";
}

// $var = gettype('AC_OP_BAL');
// print_r($var);

ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?>
    

