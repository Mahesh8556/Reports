<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/shares_transfer_register.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$BankName  = $_GET['BankName'];
$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_TYPE = $_GET['AC_TYPE'];
$TRAN_TYPE1 = $_GET['TRAN_TYPE1'];
$TRAN_TYPE2 = $_GET['TRAN_TYPE2'];
$TRANDRCR="'D'";


$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
$AC_TYPE1 = str_replace("'" , "" , $AC_TYPE);



$query ='SELECT SCHEMAST."S_APPL", 
SCHEMAST."S_NAME",SHARETRAN."TRAN_DATE",
SHMASTER."AC_NO",
SHARETRAN."TRAN_AMOUNT",
SHARETRAN."NO_OF_SHARES",
SHARETRAN."CERTIFICATE_NO",
SHARETRAN."SHARES_FROM_NO",
SHARETRAN."SHARES_TO_NO",
SHARETRAN."SHARES_TRANSFER_DATE",
SHARETRAN."SHARES_RETURN_DATE",
SHARETRAN."RESULATION_DATE",
SHARETRAN."RESULATION_NO",
SHARETRAN."TRAN_TYPE",
SHMASTER."AC_TYPE",
SHMASTER."AC_NAME",
SCHEMAST."S_NAME",
CITYMASTER."CITY_NAME"
FROM SHMASTER
LEFT OUTER JOIN SHARETRAN ON CAST(SHMASTER."BANKACNO" AS CHARACTER VARYING) = SHARETRAN."TRAN_ACNO"
LEFT OUTER JOIN CUSTOMERADDRESS ON SHMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID"
LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS.ID = CITYMASTER."CITY_CODE"
LEFT OUTER JOIN SCHEMAST ON SHMASTER."AC_TYPE" = SCHEMAST.ID
WHERE SHMASTER."AC_TYPE" = '.$AC_TYPE.'
AND SHARETRAN."TRAN_DRCR" = '.$TRANDRCR.'
AND (SHARETRAN."TRAN_TYPE" = '.$TRAN_TYPE1.'
      OR SHARETRAN."TRAN_TYPE" = '.$TRAN_TYPE2.')
AND SHARETRAN."BRANCH_CODE"='.$branch_code.'
AND CAST(SHARETRAN."SHARES_TRANSFER_DATE" AS DATE) >= CAST('.$sdate.' AS DATE)
AND CAST(SHARETRAN."SHARES_TRANSFER_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
ORDER BY SHARETRAN."CERTIFICATE_NO"';


// echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row["TRAN_AMOUNT"];

    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_NAME" => $row["AC_NAME"],
        "CITY_NAME"=> $row["CITY_NAME"],
        "CERTIFICATE_NO"=> $row["CERTIFICATE_NO"],
        "NO_OF_SHARES" => $row["NO_OF_SHARES"],
        "TRAN_AMOUNT"=> sprintf("%.2f", (abs($row['TRAN_AMOUNT']))),
        "SHARES_FROM_NO" => $row["SHARES_FROM_NO"],
        "SHARES_TO_NO" => $row["SHARES_TO_NO"],
        "RESULATION_DATE"=>$row["RESULATION_DATE"],
        "RESULATION_NO"=> $row["RESULATION_NO"],
       
        "TOTAL_SHARE_AMOUNT" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "BankName" => $BankName1,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "sdate" => $sdate1,
        "edate" => $edate2,
        "AC_TYPE" => $AC_TYPE1,
        "TRAN_TYPE1" => $row['S_APPL'].'  '.$row['S_NAME'],
        "TRAN_TYPE2" => $row['S_APPL'].'  '.$row['S_NAME'],
        //"TRANDRCR" => $TRANDRCR,
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
    
//}   
?>
