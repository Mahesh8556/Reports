<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LockerRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//$conn = pg_connect("host=127.0.0.1 dbname=bank user=postgres password=tushar");


 //variables
$PRINT_DATE = $_GET['PRINT_DATE'];
$scheme = $_GET['scheme'];
$branch = $_GET['BRANCH'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
// $AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
// $AC_TYPE = $_GET['AC_TYPE'];
$BANK_NAME = $_GET['BANK_NAME'];
// $NAME = $_GET['NAME'];

$startDate = $_GET['START_DATE'];
$endDate = $_GET['END_DATE'];


$dateformate = "'DD/MM/YYYY'";


$PRINT_DATE1 = str_replace("'", "", $PRINT_DATE);
$branch1 = str_replace("'", "", $branch);
$BANK_NAME1 = str_replace("'", "", $BANK_NAME);


// $query = ' SELECT DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE",DPMASTER."AC_NO",DPMASTER."AC_NAME",
//            DPMASTER."AC_SCHMAMT",DPMASTER."AC_MATUAMT",DPMASTER."AC_EXPDT",DPMASTER."AC_OPDATE",
//            DPMASTER."AC_REF_RECEIPTNO",DPMASTER."AC_DAYS",DPMASTER."AC_MONTHS",SCHEMAST."S_APPL",
//            SCHEMAST."S_NAME",OWNBRANCHMASTER."NAME",
//            Case When DPMASTER."AC_ASON_DATE" = null  Then DPMASTER."AC_OPDATE" 
//            Else DPMASTER."AC_ASON_DATE" End as OP_ASON_DT
//            FROM DPMASTER
//            INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
//            INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
//            WHERE (DPMASTER."AC_OPDATE" IS NULL  OR cast(DPMASTER."AC_OPDATE" as date) <= '.$PRINT_DATE.' ::date) 				
//            AND (DPMASTER."AC_CLOSEDT" IS NULL  OR cast(DPMASTER."AC_CLOSEDT" as date)  >= '.$PRINT_DATE.' ::date)
//            AND cast(DPMASTER."AC_OPDATE" as date)  >= '.$PRINT_DATE.' ::date
//            AND cast(DPMASTER."AC_OPDATE" as date)  <= '.$PRINT_DATE.' ::date 	
//            AND DPMASTER."AC_TYPE"  = '.$scheme.' 
//            AND DPMASTER."BRANCH_CODE" = '.$branch.'
//            Order By DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" , DPMASTER."AC_NO" ';

// $query='SELECT DPMASTER."AC_ACNOTYPE",DPMASTER."AC_TYPE",DPMASTER."AC_NO",DPMASTER."AC_NAME",
// DPMASTER."AC_SCHMAMT",DPMASTER."AC_MATUAMT",DPMASTER."AC_EXPDT",DPMASTER."AC_OPDATE",
// DPMASTER."AC_REF_RECEIPTNO",DPMASTER."AC_DAYS",DPMASTER."AC_MONTHS",SCHEMAST."S_APPL", 
// SCHEMAST."S_NAME",OWNBRANCHMASTER."NAME", Case When DPMASTER."AC_ASON_DATE" = null 
// Then DPMASTER."AC_OPDATE" Else DPMASTER."AC_ASON_DATE" End as OP_ASON_DT
// FROM DPMASTER INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id" 
// INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id" 
// WHERE (DPMASTER."AC_OPDATE" IS NULL OR cast(DPMASTER."AC_OPDATE" as date) <= '.$PRINT_DATE.' ::date) 
// AND (DPMASTER."AC_CLOSEDT" IS NULL OR cast(DPMASTER."AC_CLOSEDT" as date) >= '.$PRINT_DATE.' ::date)
// AND cast(DPMASTER."AC_OPDATE" as date) >= '.$PRINT_DATE.' ::date 
// AND cast(DPMASTER."AC_OPDATE" as date) <= '.$PRINT_DATE.' ::date 
// AND DPMASTER."AC_TYPE" = '.$AC_TYPE.' AND DPMASTER."BRANCH_CODE" = '.$BRANCH_CODE.' 
// Order By DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" , DPMASTER."AC_NO"';


$query='SELECT LOCKERTRAN."TRAN_DATE",LOCKERTRAN."TRAN_ACNOTYPE", 
LOCKERTRAN."TRAN_ACTYPE",LOCKERTRAN."TRAN_ACNO" , LOCKERTRAN."LOCKER_OPENING_TIME", 
LOCKERTRAN."LOCKER_CLOSING_TIME",LOCKERTRAN."NARRATION"  , DPMASTER."AC_NAME" 
FROM LOCKERTRAN  LEFT OUTER JOIN DPMASTER   
ON LOCKERTRAN."TRAN_ACNOTYPE" = DPMASTER."AC_ACNOTYPE"      
WHERE CAST(LOCKERTRAN."TRAN_ACTYPE" AS INTEGER) = DPMASTER."AC_TYPE"      
AND LOCKERTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"  
AND CAST(LOCKERTRAN."TRAN_DATE" AS DATE) >= CDATE('.$startDate.') 
AND CAST(LOCKERTRAN."TRAN_DATE" AS DATE) <= CDATE('.$endDate.') 
Order By LOCKERTRAN."TRAN_DATE",LOCKERTRAN."LOCKER_OPENING_TIME" ';

//  echo $query; 
           

          
$sql =  pg_query($conn,$query);

$i = 0;
while($row = pg_fetch_assoc($sql))
{ 

     $tmp=[
      //   'AC_NO' => $row['AC_NO'],
      //   'AC_NAME' => $row['GAC_NAME'],
      //   'AC_WARD' => $row['AC_WARD'],
      //   'AC_ADDR' => $row['AC_ADDR'],
      //   'CITY_NAME'=> $row['CITY_NAME'],
      //   'GAC_NAME'=> $row['GAC_NAME'],
      //   'S_NAME' => $row['S_NAME'],
        // 'BRANCH' => $BRANCH,
      //   'AC_ACNOTYPE' => $AC_ACNOTYPE,
      //   'AC_TYPE' => $AC_TYPE,
      //   'bankName'=> $bankName,
      //   'NAME' => $NAME,
        'BANK_NAME' => $BANK_NAME1,
        'BRANCH_CODE' => $BRANCH_CODE,
        'PRINT_DATE' => $PRINT_DATE1,
        'BRANCH' => $branch1,
       'startDate' => $startDate,
       'endDate' => $endDate,
        'tranDate' => $row['TRAN_DATE'],
        'lkopening' => $row['LOCKER_OPENING_TIME'],
        'lkclosing' => $row['LOCKER_CLOSING_TIME'],
        'lkAccName' => $row['AC_NAME'],
     ];
    
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?> 

