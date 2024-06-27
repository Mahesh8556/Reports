<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/GoldSilverSecurity.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//  $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


 //variables
// $PRINT_DATE = $_GET['PRINT_DATE'];
$START_DATE = $_GET['START_DATE'];
$END_DATE = $_GET['END_DATE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$BRANCH = $_GET['BRANCH'];
$branchCode = $_GET['branchCode'];
$BANK_NAME = $_GET['BANK_NAME'];
// $scheme = $_GET['scheme'];      
// $branch = $_GET['branch'];

$START_DATE1 = str_replace("'", "" , $START_DATE);
$END_DATE2 = str_replace("'", "" , $END_DATE);
$BRANCH1 = str_replace("'", "" , $BRANCH);
$AC_ACNOTYPE0 = str_replace("'", "" , $AC_ACNOTYPE);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);
$dateformate = "'DD/MM/YYYY'";
$dd = "'DD/MM/YYYY'";


 $query = ' SELECT "AC_NAME",
 "SUBMISSION_DATE",
 "NOMINEE_RELATION",
 "ARTICLE_NAME",
 "TOTAL_WEIGHT_GMS",
 "CLEAR_WEIGHT_GMS",
 "AC_SECURITY_AMT",
 "ac_sanction_amount",
 "RATE",
 "MARGIN",
 "S_APPL",
 "S_NAME",LNMASTER."AC_NO",
 LNMASTER."AC_EXPIRE_DATE"
FROM GOLDSILVER
LEFT OUTER JOIN LNMASTER ON GOLDSILVER."AC_NO" = LNMASTER."BANKACNO"
LEFT OUTER JOIN VWALLMASTER ON CAST(LNMASTER."BANKACNO" AS bigint) = CAST(VWALLMASTER."ac_no" AS bigint)
LEFT OUTER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.ID
WHERE LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
 AND LNMASTER."AC_TYPE" = '.$AC_TYPE.'
 AND CAST(LNMASTER."AC_OPDATE" AS DATE) >= TO_DATE('.$START_DATE.','.$dd.')
 AND CAST(LNMASTER."AC_OPDATE" AS DATE) <= TO_DATE('.$END_DATE.' ,'.$dd.')
 AND LNMASTER."BRANCH_CODE"='.$branchCode.' AND LNMASTER."status"=1 AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL';

         //   echo $query; 

          
$sql =  pg_query($conn,$query); 

$i = 0;
while($row = pg_fetch_assoc($sql))
{ 

   $stot1=$stot1+ $row['ac_sanction_amount'];
   $twaight=$twaight+ $row['TOTAL_WEIGHT_GMS'];
   $cwaight=$cwaight+ $row['CLEAR_WEIGHT_GMS'];
   $TRATE=$TRATE+ $row['RATE'];
   $TSECAMT=$TSECAMT+ $row['AC_SECURITY_AMT'];

     $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'MARGIN' => $row['MARGIN'],
        'RATE' => $row['RATE'],
        'AC_EXPIRE_DATE'  => $row['AC_EXPIRE_DATE'],
        'AC_ACNOTYPE' => $row['S_APPL'].' '. $row['S_NAME'],
        'AC_SECURITY_AMT' => sprintf("%.2f", ($row['AC_SECURITY_AMT'] + 0.0)),
        'ac_sanction_amount' => sprintf("%.2f", ($row['ac_sanction_amount'] + 0.0)),
        'TOTAL_WEIGHT_GMS' => $row['TOTAL_WEIGHT_GMS'],
        'CLEAR_WEIGHT_GMS' => $row['CLEAR_WEIGHT_GMS'],
        'NOMINEE_RELATION' => $row['NOMINEE_RELATION'],
        'ARTICLE_NAME' => $row['ARTICLE_NAME'],
        'SUBMISSION_DATE' => $row['SUBMISSION_DATE'],
        'NOMINEE_RELATION' => $row['NOMINEE_RELATION'],
        'ARTICLE_NAME' => $row['ARTICLE_NAME'],
        'TOTAL_WEIGHT_GMS' => sprintf("%.3f", ($row['TOTAL_WEIGHT_GMS'] + 0.0)),
        'CLEAR_WEIGHT_GMS' => sprintf("%.3f", ($row['CLEAR_WEIGHT_GMS'] + 0.0)),
        'RATE' => sprintf("%.2f", ($row['RATE'] + 0.0)),
        'START_DATE' => $START_DATE1,
        'END_DATE' => $END_DATE2,
        'BRANCH'  => $BRANCH,
        'AC_TYPE' => $AC_TYPE,
        // 'AC_ACNOTYPE' => $AC_ACNOTYPE0,
        'BANK_NAME' => $BANK_NAME,
        

        'stot1'=> sprintf("%.2f", ($stot1 + 0.0)),
        'twaight'=> sprintf("%.3f", ($twaight + 0.0)),
        'cwaight'=> sprintf("%.3f", ($cwaight + 0.0)),
        'TRATE'=> sprintf("%.2f", ($TRATE + 0.0)),
        'TSECAMT'=> sprintf("%.2f", ($TSECAMT + 0.0)),

       
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