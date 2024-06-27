<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/BnkDeadbalDepr.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
  
$dateformate ="'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$date = $_GET['date'];
$startingcode = $_GET['startingcode'];
$endingcode = $_GET['endingcode'];
$branch = $_GET['branch'];

// $bankName = str_replace("'", "", $bankName);
// $date_ = str_replace("'", "", $edate);
// $branchName = str_replace("'", "", $branchName);
// $startingcode = str_replace("'", "", $startingcode);
// $endingcode = str_replace("'", "", $endingcode);

$C = "'C'";
$D = "'D'";

$query = ' SELECT 
coalesce(cast(ITEMMASTER."OP_BALANCE" as integer)  , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$C.' Then 
cast(DEADSTOCKDETAIL."TRAN_AMOUNT" as integer) else 0 end, 0)as deadcamt,
coalesce(cast(ITEMMASTER."OP_BALANCE" as integer) , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$D.' Then 
cast(DEADSTOCKDETAIL."TRAN_AMOUNT" as integer) else 0 end, 0)as deaddamt,
coalesce(cast(ITEMMASTER."OP_BALANCE" as integer) , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$D.' Then 
cast(DEADSTOCKDETAIL."TRAN_AMOUNT" as integer) else 0 end, 0)-
coalesce(cast(ITEMMASTER."OP_QUANTITY" as integer)  , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$C.' Then 
cast(DEADSTOCKDETAIL."TRAN_AMOUNT" as integer) else 0 end, 0)as debitamt,
coalesce(cast(ITEMMASTER."OP_QUANTITY" as integer)  , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$C.' Then 
cast(DEADSTOCKDETAIL."ITEM_QTY" as integer) else 0 end, 0)as deadcqty,
coalesce(cast(ITEMMASTER."OP_QUANTITY" as integer) , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$D.' Then 
cast(DEADSTOCKDETAIL."ITEM_QTY" as integer) else 0 end, 0)as deaddqty,
coalesce(cast(ITEMMASTER."OP_QUANTITY" as integer) , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$D.' Then 
cast(DEADSTOCKDETAIL."ITEM_QTY" as integer) else 0 end, 0)-
coalesce(cast(ITEMMASTER."OP_QUANTITY" as integer)  , 0)+
coalesce(case when DEADSTOCKDETAIL."TRAN_DRCR" = '.$C.' Then 
cast(DEADSTOCKDETAIL."ITEM_QTY" as integer) else 0 end, 0)as debitqty,
ITEMMASTER."ITEM_NAME",ITEMMASTER."ITEM_CODE",OWNBRANCHMASTER."NAME"
FROM ITEMMASTER
INNER JOIN DEADSTOCKDETAIL ON
ITEMMASTER."ITEM_CODE" = DEADSTOCKDETAIL."ITEM_CODE" 
INNER JOIN OWNBRANCHMASTER ON
ITEMMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id" 
WHERE ITEMMASTER."BRANCH_CODE" = '.$branch.'
and cast(ITEMMASTER."PURCHASE_DATE" as date) = '.$date.'::date  ';

$sql =  pg_query($conn,$query);

$i = 0;
$PURCHASE_Total = 0 ;
$OPBAL_Total = 0 ;
$DEPRAMT_Total = 0 ;
$CLOSEBAL_Total = 0 ;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql))
{
    $tmp=[
    // grand-total
    $PURCHASE_Total = $PURCHASE_Total + $row['PURCHASE_VALUE'],
    $OPBAL_Total = $OPBAL_Total + $row['PURCHASE_VALUE'],
    $DEPRAMT_Total = $DEPRAMT_Total + $row['TRAN_AMOUNT'],
    $CLOSEBAL_Total = $CLOSEBAL_Total + $row['PURCHASE_VALUE'],

        'ITEM_CODE' => $row['ITEM_CODE'],
        'ITEM_NAME' => $row['ITEM_NAME'],
        'PURCHASE_DATE'=> $row['PURCHASE_DATE'],
        'SUPPLIER_NAME'=> $row['SUPPLIER_NAME'],
        'PURCHASE_VALUE'=> $row['PURCHASE_VALUE'],
        'PURCHASE_RATE'=> $row['PURCHASE_RATE'],
        'PURCHASE_OP_QUANTITY'=> $row['PURCHASE_OP_QUANTITY'],
        'DEPR_RATE'=> $row['DEPR_RATE'],
        'TRAN_AMOUNT'=> $row['TRAN_AMOUNT'],
        'BRANCH_CODE' => $row['BRANCH_CODE'],
        'NAME'=>$row['NAME'],
        'debitqty'=> $row['debitqty'],
        'debitamt'=> $row['debitamt'],
        'purchasetotal' =>  $PURCHASE_Total ,
        'DepramtTotal' =>  $DEPRAMT_Total ,

        'date'=> $date,
        'startingcode' => $startingcode,
        'endingcode' => $endingcode,
        'branch' => $branch,
        'bankName' => $bankName,
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
    

