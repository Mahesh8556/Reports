<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DeadstockBalanceList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
//connect pgAdmin database connection 
//  $conn = pg_connect("host=127.0.0.1 dbname=CBSTest user=postgres password=admin");

$bankName = $_GET['bankName'];
$Date = $_GET['Date'];
$BRANCH_CODE = $_GET['branch'];
// $startingcode = $_GET['startingcode'];
// $endingcode = $_GET['endingcode'];
$branchName = $_GET['branchName'];

// $bankName = str_replace("'", "", $bankName);
$Date_ = str_replace("'", "", $Date);
$branchName = str_replace("'", "", $branchName);
$Date1 = str_replace("'", "", $Date);

// $startingcode = str_replace("'", "", $startingcode);
// $endingcode = str_replace("'", "", $endingcode);

$dateformate = "'DD/MM/YYYY'";
$C = "'C'";
$D = "'D'";
$dpr = "'DPR'";
$brk = "'BRK'";
$space = "''";

$query = 'select * from (
    SELECT ITEMMASTER."ITEM_CODE" ,ITEMMASTER."ITEM_TYPE", ITEMMASTER."ITEM_NAME", ITEMMASTER."GL_ACNO" , ITEMMASTER."BRANCH_CODE" 
        ,  ( COALESCE("OP_BALANCE",0)  +  COALESCE(OPENING_TRANTABLE."OP_TRAN_AMT",0)  ) "OP_BALANCE" 
        ,  ( COALESCE("OP_QUANTITY",0)  +  COALESCE(OPENING_TRANTABLE."OP_ITEM_QTY" ,0)  ) "OP_QUANTITY" 
        , DEADSTOCK."TRAN_NO", DEADSTOCK."TRAN_DATE", REPLACE(REPLACE(DEADSTOCK."NARRATION" ,CHR(10),'.$space.'),CHR(13),'.$space.') "NARRATION" 
        , DEADSTOCK."DR_AMOUNT", DEADSTOCK."CR_SALES_AMOUNT" , DEADSTOCK."RESO_NO" , DEADSTOCK."RESO_DATE" 
        , DEADSTOCK."TRAN_ENTRY_TYPE" , DEADSTOCK."ITEM_QTY", DEADSTOCK."ITEM_RATE",DEADSTOCK."TRAN_SUPPLIER_NAME"
        , DEADSTOCK."CR_BREAKAGE_AMOUNT"   FROM ITEMMASTER 
         LEFT OUTER JOIN ( SELECT "ITEM_TYPE" , "ITEM_CODE", COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN "TRAN_AMOUNT" 
         ELSE (-1) * "TRAN_AMOUNT" END),0) "OP_TRAN_AMT" , COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN "ITEM_QTY" 
         ELSE (-1) * "ITEM_QTY" END),0) "OP_ITEM_QTY"  
       FROM 
       ( SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
        , DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY"
        ,DEADSTOCKDETAIL."TRAN_AMOUNT" 
        , DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
        , DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
        , ITEMMASTER."BRANCH_CODE"
        From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
           Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
           AND DEADSTOCKDETAIL."deadstockHeader" = DEADSTOCKHEADER.id 
           AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE"  AS INTEGER)
           AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
           AND ((DEADSTOCKHEADER."TRAN_STATUS" = 1 AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$Date.','.$dateformate.')) 
            OR (CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) = TO_DATE('.$Date.','.$dateformate.') and DEADSTOCKHEADER."TRAN_STATUS" = 0)) 
           AND DEADSTOCKDETAIL."ITEM_TYPE" = 1
           AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$Date.','.$dateformate.')
       )VWTMPDEADSTOCK 
       WHERE CAST("TRAN_DATE" AS DATE) < TO_DATE('.$Date.','.$dateformate.')
              GROUP BY "ITEM_TYPE", "ITEM_CODE" 
          ) OPENING_TRANTABLE ON CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = OPENING_TRANTABLE."ITEM_TYPE" 
        AND ITEMMASTER."ITEM_CODE" = OPENING_TRANTABLE."ITEM_CODE" 
       , ( SELECT "ITEM_TYPE" , "ITEM_CODE", "TRAN_NO", "TRAN_DATE" , "TRAN_ENTRY_TYPE", "NARRATION" , COALESCE("ITEM_QTY",0) "ITEM_QTY" , 
          COALESCE("ITEM_RATE",0) "ITEM_RATE", COALESCE(CASE "TRAN_DRCR" WHEN '.$D.' THEN "TRAN_AMOUNT" ELSE 0 END,0) "DR_AMOUNT" 
              , "RESO_NO" , "RESO_DATE" ,"TRAN_SUPPLIER_NAME", 
          COALESCE(CASE "TRAN_DRCR"  WHEN '.$C.' THEN CASE "TRAN_ENTRY_TYPE" WHEN '.$brk.' THEN "TRAN_AMOUNT" ELSE 0 END ELSE 0 END,0) "CR_BREAKAGE_AMOUNT" , 
          COALESCE(CASE "TRAN_DRCR"  WHEN '.$C.' THEN CASE "TRAN_ENTRY_TYPE" WHEN '.$brk.' THEN 0 ELSE "TRAN_AMOUNT" END ELSE 0 END,0) "CR_SALES_AMOUNT"
              FROM 
       ( SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
        , DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY"
        ,DEADSTOCKDETAIL."TRAN_AMOUNT" 
        , DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
        , DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
        , ITEMMASTER."BRANCH_CODE"
        From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
           Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
         AND DEADSTOCKDETAIL."deadstockHeader" = DEADSTOCKHEADER.id
           AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE"  AS INTEGER)
           AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
           AND ((DEADSTOCKHEADER."TRAN_STATUS" = 1 AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$Date.','.$dateformate.')) 
            OR (CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) = TO_DATE('.$Date.','.$dateformate.') and DEADSTOCKHEADER."TRAN_STATUS" = 0)) 
           AND DEADSTOCKDETAIL."ITEM_TYPE" = 1
           AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$Date.','.$dateformate.')
       )VWTMPDEADSTOCK 
       WHERE CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$Date.','.$dateformate.')
         ) DEADSTOCK  
    WHERE CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = DEADSTOCK."ITEM_TYPE" 
    AND ITEMMASTER."ITEM_CODE" = DEADSTOCK."ITEM_CODE" 
    ) TMP WHERE "BRANCH_CODE" = '.$BRANCH_CODE.'
    AND COALESCE("OP_BALANCE",0) + COALESCE("DR_AMOUNT",0) + COALESCE("CR_SALES_AMOUNT",0) 
    + COALESCE("CR_BREAKAGE_AMOUNT",0) <> 0 ';

//    echo $query;

//string replacements


$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0 ;
// $GROUP_TOTAL = 0 ;
$type = '';

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql))  
{
  // grand-total
    // $total_voucher = $total_voucher + 1;
     $GRAND_TOTAL = $GRAND_TOTAL + $row['OP_BALANCE'];
    $tmp=[
        'ITEM_CODE' => $row['ITEM_CODE'],
        'ITEM_NAME' => $row['ITEM_NAME'],
        'PURCHASE_OP_QUANTITY'=> $row['OP_QUANTITY'],
        'OP_BALANCE'=> sprintf("%.2f",($row['OP_BALANCE']+ 0.0 ) ),
        // 'debitamt' =>sprintf("%.2f",($row['PURCHASE_VALUE'] + 0.0 ) ),
        // 'BRANCH_CODE' => $row['BRANCH_CODE'],
        'branchName' => $branchName,
        'grandtotal' =>  sprintf("%.2f",($GRAND_TOTAL + 0.0 ) ),

        'branch' => $BRANCH_CODE,
        // 'startingcode' => $startingcode,
        // 'endingcode' => $endingcode,
        'Date_' => $Date,
        'bankName' => $bankName,
        'Date' => $Date1,
        // 'total_voucher'=> $total_voucher,
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

