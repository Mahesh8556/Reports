<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/DepositCashAmt.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$d = "'%C%'";
$startDate = "'01/04/2016'";
$endDate = "'11/11/2022'";
$dateformate ="'DD/MM/YYYY'";

// $startDate = $_GET['startDate'];
// $endDate = $_GET['endDate'];
// $dateformate = "'DD/MM/YYYY'";


$query = 'SELECT 
            cast (depotran."TRAN_ACNO" as bigint),
            depotran."TRAN_DRCR",
            depotran."TRAN_AMOUNT",
            depotran."TRAN_ACNOTYPE",
            depotran."TRAN_ACTYPE",
            dpmaster."AC_ASON_DATE",
            dpmaster."AC_NAME",
            ownbranchmaster."NAME"
            from depotran
            inner join dpmaster on cast(RIGHT(depotran."TRAN_ACNO" ,6)as bigint) =dpmaster."AC_NO" 
            inner join ownbranchmaster on dpmaster."BRANCH_CODE" =ownbranchmaster."id"
            where 
            depotran."TRAN_DRCR" LIKE '.$d.'
            order by  depotran."TRAN_ACNOTYPE",depotran."TRAN_ACNO" asc';
$sql =  pg_query($conn,$query);

$i = 0;
$GROUP_TOTAL = 0 ;
$GRAND_TOTAL = 0 ;
while($row = pg_fetch_assoc($sql))
{
       // grand-total
       $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];
       // group-total 
       $vartype=$row['TRAN_ACNO'];
       if($type == ''){
           $type = $row['TRAN_ACNO'];
       }
       if($type == $row['TRAN_ACNO']){
           $GROUP_TOTAL = $GROUP_TOTAL + $row['TRAN_AMOUNT'];
       }else{
           $type = $row['AC_TYPE'];
           $GROUP_TOTAL = 0 ;
           $GROUP_TOTAL = $GROUP_TOTAL + $row['TRAN_AMOUNT'];
       }
    $tmp=[

        'TRAN_ACNO'=> (int)$row['TRAN_ACNO'],
        'TRAN_DRCR'=> $row['TRAN_DRCR'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'TRAN_ACNOTYPE' => $row['TRAN_ACNOTYPE'],
        'TRAN_ACTYPE' => $row['TRAN_ACTYPE'],     
        'AC_NAME' => $row['AC_NAME'],   
        'NAME' => $row['NAME'],   
        'accountwisetotal' => $GROUP_TOTAL,   
        'grand_amt_total' => $GRAND_TOTAL,   
        'NAME' => $row['NAME'],   
        
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


