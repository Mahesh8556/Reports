<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepreciationDeadstockListSummary.jrxml';

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
           deprcategory."NAME" as dprname,itemmaster."BRANCH_CODE",
           ownbranchmaster."NAME",itemmaster."DEPR_CATEGORY"
           FROM (
               SELECT sum(coalesce(cast(itemmaster."OP_BALANCE" as integer) , 0))+
                      sum(coalesce(case when deadstockdetail."TRAN_DRCR" = '.$d.' Then 
                      cast(deadstockdetail."TRAN_AMOUNT" as integer) else 0 end, 0))-
                      sum(coalesce(cast(itemmaster."OP_QUANTITY" as integer)  , 0))+
                      sum(coalesce(case when deadstockdetail."TRAN_DRCR" = '.$c.' Then 
                      cast(deadstockdetail."TRAN_AMOUNT" as integer) else 0 end, 0))as openingbalance,
                      sum(deadstockdetail."TRAN_AMOUNT") as depr_amt,itemmaster."BRANCH_CODE",itemmaster."DEPR_CATEGORY",
                      sum(cast(itemmaster."PURCHASE_VALUE" as float)) as purchase_value
                      FROM deadstockdetail
                      Inner Join itemmaster on 
                      deadstockdetail."ITEM_CODE" =  itemmaster."id" 
                      Where cast(itemmaster."PURCHASE_DATE" as date) = '.$date.' ::date 
                      Group By itemmaster."BRANCH_CODE",itemmaster."DEPR_CATEGORY"
                    )itemmaster
           Inner Join ownbranchmaster on 
           itemmaster."BRANCH_CODE" = ownbranchmaster."id"
           Inner Join deprcategory on 
           cast(itemmaster."DEPR_CATEGORY" as integer) = deprcategory."CODE"
           And itemmaster."BRANCH_CODE" = '.$bcode.' ';

$sql =  pg_query($conn,$query);

$i = 0;

$closing_bal = 0 ;
$purchase_value = 0;
$opening_balance = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql))  
{
  
     $closing_bal = $row['openingbalance'] - $row['depr_amt'];

    $tmp=[
        'PURCHASE_DATE' => $row['PURCHASE_DATE'],
        'SUPPLIER_NAME' => $row['SUPPLIER_NAME'],
        'purchase_value'=> $row['purchase_value'],
        'ITEM_RATE'=> $row['ITEM_RATE'],
        'DEPR_RATE' => $row['DEPR_RATE'],
        'DEPR_CATEGORY' =>  $row['DEPR_CATEGORY'] ,
        'depr_amt' =>  $row['depr_amt'] ,
        'NAME' =>  $row['NAME'] ,
        'dprname' => $row['dprname'],
        'openingbalance' =>  $row['openingbalance'] ,
        'quantity' =>  $row['quantity'] ,
        'closing_bal' => $closing_bal,

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




<!-- SELECT 
itemmaster."purchase_value" ,
itemmaster."depr_amt",
itemmaster."openingbalance",
deprcategory."NAME" as dprname,itemmaster."BRANCH_CODE",
ownbranchmaster."NAME",itemmaster."DEPR_CATEGORY"
FROM (
    SELECT sum(coalesce(cast(itemmaster."OP_BALANCE" as integer) , 0))+
           sum(coalesce(case when deadstockdetail."TRAN_DRCR" = 'D' Then 
           cast(deadstockdetail."TRAN_AMOUNT" as integer) else 0 end, 0))-
           sum(coalesce(cast(itemmaster."OP_QUANTITY" as integer)  , 0))+
           sum(coalesce(case when deadstockdetail."TRAN_DRCR" = 'C' Then 
           cast(deadstockdetail."TRAN_AMOUNT" as integer) else 0 end, 0))as openingbalance,
           sum(deadstockdetail."TRAN_AMOUNT") as depr_amt,itemmaster."BRANCH_CODE",itemmaster."DEPR_CATEGORY",
           sum(cast(itemmaster."PURCHASE_VALUE" as float)) as purchase_value
           FROM deadstockdetail
           Inner Join itemmaster on 
           deadstockdetail."ITEM_CODE" =  itemmaster."id" 
    Where cast(itemmaster."PURCHASE_DATE" as date) = '01/02/2022' ::date 
           Group By itemmaster."BRANCH_CODE",itemmaster."DEPR_CATEGORY"
          )itemmaster
Inner Join ownbranchmaster on 
itemmaster."BRANCH_CODE" = ownbranchmaster."id"
Inner Join deprcategory on 
cast(itemmaster."DEPR_CATEGORY" as integer) = deprcategory."CODE"
And itemmaster."BRANCH_CODE" = '1' -->