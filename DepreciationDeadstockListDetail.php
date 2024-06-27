<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepreciationDeadstockListDetail.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=CBSTest user=postgres password=admin");

// $bankName = $_GET['bankName'];
// $Date = $_GET['Date'];
// $branch = $_GET['branch'];
// $startingcode = $_GET['startingcode'];
// $endingcode = $_GET['endingcode'];

// $bankName = str_replace("'", "", $bankName);
// $Date_ = str_replace("'", "", $Date);
// $branchName = str_replace("'", "", $branchName);
// $startingcode = str_replace("'", "", $startingcode);
// $endingcode = str_replace("'", "", $endingcode);

$dateformate = "'DD/MM/YYYY'";
$date = "'01/02/2022'";
$c = "'C'";
$d = "'D'";
$bcode = "'1'";

$query = ' SELECT 
           itemmaster."purchase_value" ,
           itemmaster."depr_amt",
           itemmaster."openingbalance",
           itemmaster."quantity",
           itemmaster."rate",
           itemmaster."depr_rate",
           deprcategory."NAME" as dprname,itemmaster."BRANCH_CODE",itemmaster."SUPPLIER_NAME",
           ownbranchmaster."NAME",itemmaster."DEPR_CATEGORY",itemmaster."PURCHASE_DATE",
           itemmaster."item_name",itemmaster."item_code"
           FROM (
               SELECT coalesce(cast(itemmaster."OP_BALANCE" as integer) , 0)+
                      coalesce(case when deadstockdetail."TRAN_DRCR" = '.$d.' Then 
                      cast(deadstockdetail."TRAN_AMOUNT" as integer) else 0 end, 0)-
                      coalesce(cast(itemmaster."OP_QUANTITY" as integer)  , 0)+
                      coalesce(case when deadstockdetail."TRAN_DRCR" = '.$c.' Then 
                      cast(DEADSTOCKDETAIL."TRAN_AMOUNT" as integer) else 0 end, 0)as openingbalance,
                      coalesce(cast(itemmaster."OP_QUANTITY" as integer) , 0)+
                      coalesce(case when deadstockdetail."TRAN_DRCR" = '.$d.' Then 
                      cast(deadstockdetail."ITEM_QTY" as integer) else 0 end, 0)-
                      coalesce(cast(itemmaster."OP_QUANTITY" as integer)  , 0)+
                      coalesce(case when deadstockdetail."TRAN_DRCR" = '.$c.' Then 
                      cast(deadstockdetail."ITEM_QTY" as integer) else 0 end, 0)as quantity,
                      deadstockdetail."TRAN_AMOUNT" as depr_amt,itemmaster."BRANCH_CODE",itemmaster."DEPR_CATEGORY",
                      cast(itemmaster."PURCHASE_VALUE" as float) as purchase_value,itemmaster."PURCHASE_DATE",
                      deadstockdetail."DEPR_RATE",itemmaster."SUPPLIER_NAME",deadstockdetail."ITEM_RATE" as rate,
                      itemmaster."id",deadstockdetail."DEPR_RATE" as depr_rate,
                      itemmaster."ITEM_CODE" as item_code,itemmaster."ITEM_NAME" as item_name
                      FROM deadstockdetail
                      Inner Join itemmaster on 
                      deadstockdetail."ITEM_CODE" =  itemmaster."id" 
                      Where cast(itemmaster."PURCHASE_DATE" as date) = '.$date.' ::date 
                     )itemmaster
           Inner Join ownbranchmaster on 
           itemmaster."BRANCH_CODE" = ownbranchmaster."id"
           Inner Join deprcategory on 
           cast(itemmaster."DEPR_CATEGORY" as integer) = deprcategory."CODE"
           where itemmaster."BRANCH_CODE" = '.$bcode.' ';

$sql =  pg_query($conn,$query);

$i = 0;

$closing_bal = 0 ;
$purchasevgtot = 0;
$openingbalgtot = 0;
$closebalgtot = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql))  
{
  
     $closing_bal = $row['openingbalance'] - $row['depr_amt'];
     $purchasevgtot = $purchasevgtot + $row['purchase_value'];
     $openingbalgtot = $openingbalgtot + $row['openingbalance'];
     $closebalgtot = $closebalgtot + $closing_bal;

    if($type == ''){
        $type = $row['dprname'];
    }
    if($type == $row['dprname']){
        $schem_purchasv = $schem_purchasv + $row['purchase_value'];
    }else{
        $type = $row['dprname'];
        $schem_purchasv = 0;
        $schem_purchasv = $schem_purchasv + $row['purchase_value'];
    }

    if($type == ''){
        $type = $row['dprname'];
    }
    if($type == $row['dprname']){
        $schem_openbal = $schem_openbal + $row['openingbalance'];
    }else{
        $type = $row['dprname'];
        $schem_openbal = 0;
        $schem_openbal = $schem_openbal + $row['openingbalance'];
    }

    if($type == ''){
        $type = $row['dprname'];
    }
    if($type == $row['dprname']){
        $schem_closebal = $schem_closebal + $closing_bal;
    }else{
        $type = $row['dprname'];
        $schem_closebal = 0;
        $schem_closebal = $schem_closebal + $closing_bal;
    }


    $tmp=[
        'PURCHASE_DATE' => $row['PURCHASE_DATE'],
        'SUPPLIER_NAME' => $row['SUPPLIER_NAME'],
        'purchase_value'=> $row['purchase_value'],
        'rate'=> $row['rate'],
        'depr_rate' => $row['depr_rate'],
        'DEPR_CATEGORY' =>  $row['DEPR_CATEGORY'] ,
        'depr_amt' =>  $row['depr_amt'] ,
        'NAME' =>  $row['NAME'] ,
        'dprname' => $row['dprname'],
        'openingbalance' =>  $row['openingbalance'] ,
        'quantity' =>  $row['quantity'] ,
        'item_code' => $row['item_code'],
        'item_name' => $row['item_name'],
        'closingbalance' => $closing_bal,
        'purchasevgtot' => $purchasevgtot,
        'openingbalgtot' => $openingbalgtot,
        'closebalgtot' => $closebalgtot,
        'schem_purchasv' => $schem_purchasv,
        'schem_openbal' => $schem_openbal,
        'schem_closebal' => $schem_closebal,

        'branch' => $branch,
        'startingcode' => $startingcode,
        'endingcode' => $endingcode,
        'bankName' => $bankName,
        'date' => $date,
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



