<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;


$filename = __DIR__.'/NoDueCertificate.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');




// variables

$scheme = $_GET['Scheme']; 
$Date = $_GET['date'];
$AC_TYPE = $_GET['AC_TYPE'];
$AccountNo = $_GET['AccountNo'];
$BANKACNO = $_GET['BANKACNO'];
$AC_TYPE=$_GET['AC_TYPE'];
$branchName=$_GET['branchName'];
$bankName=$_GET['bankName'];
$Space="' '";

// echo $Date;
$branchcode = $_GET['BRANCH_CODE'];
$dd="'DD/MM/YYYY'";
$ddd="'DD Mon YYYY'";
$BANKACNO_ = str_replace("'", "", $BANKACNO);
$Date_ = str_replace("'", "", $Date);
$branchName_ = str_replace("'", "", $branchName);
$bankName_ = str_replace("'", "", $bankName);




// $TRAN_DATE="'27/08/2023'";
// $TRAN_TYPE="'IBT'";

$query='SELECT *,coborrower."AC_TYPE",coborrower."AC_ACNOTYPE",coborrower."AC_NO",coborrower."AC_NAME" "AC_NAME1", 
SCHEMAST."S_ACNOTYPE",SCHEMAST."S_APPL",SCHEMAST."S_NAME",CITYMASTER."CITY_CODE",CITYMASTER."CITY_NAME",
customeraddress."AC_PIN",
(CONCAT("AC_HONO" ,'.$Space.',"AC_WARD",'.$Space.',"AC_ADDR",'.$Space.',"AC_GALLI",'.$Space.',"AC_AREA")) AS "ADDRESS"
FROM LNMASTER 
LEFT JOIN coborrower ON LNMASTER.ID = coborrower."lnmasterID"
LEFT JOIN schemast ON LNMASTER."AC_TYPE" = schemast.ID
LEFT JOIN IDMASTER ON LNMASTER."AC_CUSTID" = IDMASTER.ID
LEFT JOIN customeraddress ON IDMASTER.ID = customeraddress."idmasterID"
LEFT JOIN CITYMASTER ON customeraddress."AC_CTCODE" = CITYMASTER."CITY_CODE" 
WHERE "AC_CLOSEDT" IS NOT NULL
and lnmaster."BANKACNO" = '.$BANKACNO.' 
AND CAST(LNMASTER."AC_CLOSEDT" AS DATE) =  TO_DATE('.$Date.', '.$ddd.')
 AND LNMASTER."BRANCH_CODE"= '.$branchcode.' 
';

// echo $query;
$sql =  pg_query($conn,$query);
// print_r($sql);
$i = 0;
while($row = pg_fetch_assoc($sql)){


    $tmp=[
        
        'Date' =>$Date_,
        'AC_NAME'=>$row['AC_NAME'],
        'Address' =>$row['ADDRESS'],
        'acc_no' =>$BANKACNO_,
        'AC_SANCTION_AMOUNT' =>$row['AC_SANCTION_AMOUNT'],
        'AC_SANCTION_DATE' =>$row['AC_SANCTION_DATE'],
        'AC_CLOSEDT' =>$row['AC_CLOSEDT'],
        'S_APPL'=>$row['S_APPL'],
        'S_NAME'=>$row['S_NAME'],
        
        'CITY_NAME'=>$row['CITY_NAME'],
        'branchName'=>$branchName_,
        'bankName'=>$bankName_,
       
    ];    
    $data[$i]=$tmp;
    $i++;  
}

// print_r($data); 

// // for clean previous execution
ob_end_clean();
// 
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>    

