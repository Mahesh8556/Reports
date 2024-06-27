<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/PrematuredAccountCloseList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


 //variables 
$BRANCH = $_GET['BRANCH'];
$START_DATE = $_GET['START_DATE'];
$END_DATE = $_GET['END_DATE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$BANK_NAME = $_GET['BANK_NAME'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];

$PREMATURE_CLOSE = "'1'";
$TRAN_STATUS = "'1'";


$BRANCH = str_replace("'" , "" , $BRANCH);
$AC_ACNOTYPE1 = str_replace("'", "" , $AC_ACNOTYPE);
$AC_TYPE = str_replace("'", "" , $AC_TYPE);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);
$START_DATE1 = str_replace("'", "" , $START_DATE);
$END_DATE2 = str_replace("'", "" , $END_DATE); 
// $bankName = $_GET['bankName'];

$dateformate = "'DD/MM/YYYY'";



 $query = 'SELECT DPMASTER."AC_MATUAMT",
 DPMASTER."id",
 NOMINEELINK."DPMasterID",
 COUNT(NOMINEELINK."id") AS "NO_OF_NOMINEES",
 CAST(DPMASTER."AC_EXPDT" AS DATE),
 DPMASTER."AC_ACNOTYPE",
 DPMASTER."AC_MATUAMT",
 DPMASTER."AC_EXPDT",
 DPMASTER."AC_TYPE",
 DPMASTER."AC_NO",
 SCHEMAST."S_NAME",
 SCHEMAST."S_APPL",
 DPMASTER."AC_OPDATE",
 DPMASTER."AC_MONTHS",
 DPMASTER."AC_DAYS",
 CUSTOMERADDRESS."AC_WARD",
 DEPOCLOSETRAN."TRAN_DATE" PREMATURE_CLOSE_DATE,
 DEPOCLOSETRAN."NET_PAYABLE_AMOUNT" PAYABLE_AMOUNT,
 DEPOCLOSETRAN."INTEREST_RATE",
 NOMINEELINK."AC_NNAME",
 DPMASTER."AC_NAME"
FROM DPMASTER
LEFT OUTER JOIN DEPOCLOSETRAN ON DPMASTER."AC_ACNOTYPE" = DEPOCLOSETRAN."TRAN_ACNOTYPE"
LEFT OUTER JOIN NOMINEELINK ON DPMASTER.ID = NOMINEELINK."DPMasterID"
LEFT OUTER JOIN CUSTOMERADDRESS ON DPMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID"
INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
WHERE DPMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
 AND DPMASTER."AC_TYPE" = DEPOCLOSETRAN."TRAN_ACTYPE"
 AND DPMASTER."BANKACNO" = DEPOCLOSETRAN."TRAN_ACNO"
 AND DEPOCLOSETRAN."TRAN_ACNOTYPE" = '.$AC_ACNOTYPE.'
 AND DEPOCLOSETRAN."TRAN_ACTYPE" = '.$AC_TYPE.'
 AND CAST("TRAN_DATE" AS DATE) BETWEEN DATE('.$START_DATE.') AND DATE('.$END_DATE.')
 AND "IS_PREMATURE_CLOSE" = '.$PREMATURE_CLOSE.'
 AND "TRAN_STATUS"= '.$TRAN_STATUS.'
 AND DEPOCLOSETRAN."BRANCH_CODE"='.$BRANCH_CODE.'
GROUP BY DPMASTER."id",
 NOMINEELINK."DPMasterID",
 DPMASTER."AC_MATUAMT",
 NOMINEELINK."id",
 DPMASTER."AC_EXPDT",
 DPMASTER."AC_ACNOTYPE",
 DPMASTER."AC_TYPE",
 DPMASTER."AC_NO",
 SCHEMAST."S_NAME",
 NOMINEELINK."AC_NNAME",
 SCHEMAST."S_APPL",
 CUSTOMERADDRESS."AC_WARD",
 DEPOCLOSETRAN."TRAN_DATE",
 DEPOCLOSETRAN."NET_PAYABLE_AMOUNT",
 DEPOCLOSETRAN."INTEREST_RATE"';

        // echo $query; 
                  
$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0;
$GRAND_TOTAL1 = 0;
while($row = pg_fetch_assoc($sql))
{ 
    $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_MATUAMT'];
    $GRAND_TOTAL1= $GRAND_TOTAL1 + $row['payable_amount']; 
    
     $tmp=[
         'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_NNAME' => $row['AC_NNAME'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_MATUAMT'=> sprintf("%.2f", ($row["AC_MATUAMT"] + 0.0)),
        'AC_OPDATE'=> $row['AC_OPDATE'],
        'S_NAME' => $row['S_NAME'],
        'INTEREST_RATE' => $row['INTEREST_RATE'],
        'payable_amount' => $row['payable_amount'],
        'AC_MONTHS' => $row['AC_MONTHS'],
        'AC_ACNOTYPE' =>$row['S_APPL'] .' '. $row['S_NAME'],
        'AC_DAYS' => $row['AC_DAYS'],
        'premature_close_date' => $row['premature_close_date'],
        'scheme_total' => sprintf("%.2f" ,($GRAND_TOTAL) + 0.0),
        'amount_total'  => sprintf("%.2f" ,($GRAND_TOTAL1) + 0.0),
         'BRANCH' => $BRANCH,
         'START_DATE' => $START_DATE1,
         'END_DATE' => $END_DATE2,
        'AC_TYPE' => $AC_TYPE,
        'BRANCH' => $BRANCH,
        'BANK_NAME' => $BANK_NAME,
      
       
     ];
    
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?> 
    

