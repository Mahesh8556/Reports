<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DeadStockRegist.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $startDate = "'03/03/2016'";
// $endDate = "'28/03/2016'";
$dateformate = "'DD/MM/YYYY'";
// variables  
$bankName = $_GET['bankName'];
$startingdate = $_GET['startingdate'];
$endingdate = $_GET['endingdate'];
$branchName = $_GET['branchName'];
$startingcode = $_GET['startingcode'];
$endingcode = $_GET['endingcode'];
$groupby = $_GET['groupby'];
$checkbox = $_GET['checkbox'];

$bankName = str_replace("'", "", $bankName);
$startingdate1 = str_replace("'", "", $startingdate);
$endingdate_ = str_replace("'", "", $endingdate);
$branchName = str_replace("'", "", $branchName);
$startingcode = str_replace("'", "", $startingcode);
$endingcode = str_replace("'", "", $endingcode);
$D = "'D'";
$C = "'C'";


$query = 'SELECT ITEMMASTER."ITEM_CODE" ,ITEMMASTER."ITEM_TYPE", ITEMMASTER."ITEM_NAME", ITEMMASTER."GL_ACNO" 
,  ( COALESCE(OPENING_TRANTABLE.OP_TRAN_AMT,0)  ) OP_BALANCE 
,  ( COALESCE(OPENING_TRANTABLE.OP_ITEM_QTY ,0)  ) OP_QUANTITY 
, DEADSTOCK."TRAN_NO", DEADSTOCK."TRAN_DATE", DEADSTOCK."NARRATION" , DEADSTOCK.DR_AMOUNT, DEADSTOCK.CR_SALES_AMOUNT 
, DEADSTOCK."RESO_NO" , DEADSTOCK."RESO_DATE" , DEADSTOCK."TRAN_ENTRY_TYPE" , DEADSTOCK.ITEM_QTY, DEADSTOCK.ITEM_RATE,DEADSTOCK."TRAN_SUPPLIER_NAME"
, DEADSTOCK.CR_BREAKAGE_AMOUNT  FROM ITEMMASTER 
LEFT OUTER JOIN ( SELECT "ITEM_TYPE" , "ITEM_CODE", COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN "TRAN_AMOUNT" ELSE (-1) * "TRAN_AMOUNT" END),0) OP_TRAN_AMT 
      , COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN "ITEM_QTY" ELSE (-1) * "ITEM_QTY" END),0) OP_ITEM_QTY  FROM (SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
      , DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY",DEADSTOCKDETAIL."TRAN_AMOUNT" 
      , DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
      , DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
      From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
      Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
      AND DEADSTOCKDETAIL."TRAN_NO" = DEADSTOCKHEADER."TRAN_NO" 
      AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER)  AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
      AND ((DEADSTOCKHEADER."TRAN_STATUS" = 1 and CAST(deadstockheader."TRAN_DATE" AS DATE) <= CAST('.$endingdate.' as date)) 
      or (CAST(deadstockheader."TRAN_DATE" AS DATE) = CAST('.$endingdate.' AS DATE) and deadstockheader."TRAN_STATUS" = 0)) 
      AND CAST(DEADSTOCKDETAIL."ITEM_TYPE" as integer) = 1
      AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= CAST('.$startingdate.' AS DATE)) S WHERE CAST("TRAN_DATE" AS DATE) < CAST('.$endingdate.' AS DATE)
      GROUP BY "ITEM_TYPE", "ITEM_CODE" 
  ) OPENING_TRANTABLE ON  OPENING_TRANTABLE."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER)
LEFT OUTER JOIN ( SELECT "ITEM_TYPE" , "ITEM_CODE", "TRAN_NO", "TRAN_DATE" , "TRAN_ENTRY_TYPE", "NARRATION" , COALESCE("ITEM_QTY",0) ITEM_QTY , COALESCE("ITEM_RATE",0) ITEM_RATE, COALESCE(CASE "TRAN_DRCR" WHEN '.$D.' THEN "TRAN_AMOUNT" ELSE 0 END,0) DR_AMOUNT 
      , "RESO_NO" , "RESO_DATE" ,"TRAN_SUPPLIER_NAME", COALESCE(CASE "TRAN_DRCR"  WHEN '.$C.' THEN CASE "TRAN_ENTRY_TYPE" WHEN '.$groupby.' THEN "TRAN_AMOUNT" ELSE 0 END ELSE 0 END,0) CR_BREAKAGE_AMOUNT , COALESCE(CASE "TRAN_DRCR"  WHEN '.$C.' THEN CASE "TRAN_ENTRY_TYPE" WHEN '.$groupby.' THEN 0 ELSE "TRAN_AMOUNT" END ELSE 0 END,0) CR_SALES_AMOUNT
      FROM (SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
      , DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY",DEADSTOCKDETAIL."TRAN_AMOUNT" 
      , DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
      , DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
      From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
      Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
      AND DEADSTOCKDETAIL."TRAN_NO" = DEADSTOCKHEADER."TRAN_NO" 
      AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER)  AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
      AND ((DEADSTOCKHEADER."TRAN_STATUS" = 1
            and CAST(deadstockheader."TRAN_DATE" AS DATE) <= CAST('.$endingdate.' as date)) 
      or (CAST(deadstockheader."TRAN_DATE" AS DATE) = CAST('.$endingdate.' AS DATE) and deadstockheader."TRAN_STATUS" = 0)) 
      AND CAST(DEADSTOCKDETAIL."ITEM_TYPE" as integer) = 1
      AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= CAST('.$startingdate.' AS DATE)) T WHERE CAST("TRAN_DATE" AS DATE) BETWEEN CAST('.$endingdate.' AS DATE) AND CAST('.$endingdate.' AS DATE)) DEADSTOCK ON DEADSTOCK."ITEM_CODE" = ITEMMASTER."ITEM_CODE"
      ORDER BY "ITEM_CODE"';

    //   echo $query;
$sql =  pg_query($conn,$query);

$i = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){
    if($row['op_balance'] !=0){
    $tmp=[
        'TRAN_DATE' => $row['TRAN_DATE'],
        'TRAN_NO' => $row['TRAN_NO'],
        'TRAN_ENTRY_TYPE'=> $row['TRAN_ENTRY_TYPE'],
        'TRAN_SUPPLIER_NAME' => $row['TRAN_SUPPLIER_NAME'],
        'RESO_NO' => $row['RESO_NO'],
        'RESO_DATE' => $row['RESO_DATE'],
        'ITEM_QTY' => $row['item_qty'],
        'ITEM_RATE'=> $row['item_rate'],
        'TRAN_AMOUNT'=> $row['op_balance'],
        'DEPRE_AMT'=> $row['dr_amount'],
        'BREK_AMT' => $row['cr_breakage_amount'],
        'BALANC_AMT'=> $row['cr_sales_amount'],
        'NAME' => $BRANCH_CODE,
        'startDate' => $startDate,
        'enddate' => $enddate,

        'bankName' => $bankName,
        'startingdate'=> $startingdate1,
        'endingdate'=> $endingdate_,
        'branchName'=> $branchName,
        'startingcode'=> $startingcode,
        'endingcode'=> $endingcode,
        'groupby'=> $groupby,
        'checkbox'=> $checkbox,
    ];
    $data[$i]=$tmp;
    $i++;
    // echo "<pre>";
    // print_r($tmp);
    // echo "</pre>";
    }
}

ob_end_clean();
if(count($data) == 0){
    include "errormsg.html";
}else{
    $config = ['driver'=>'array','data'=>$data];

    $report = new PHPJasperXML();
    $report->load_xml_file($filename)    
        ->setDataSource($config)
        ->export('Pdf');
}
// }
?>
    

