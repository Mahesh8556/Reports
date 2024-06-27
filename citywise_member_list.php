<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/citywise_member_list.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$BankName  = $_GET['BankName'];
$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$edate = $_GET['edate'];
$AC_TYPE = $_GET['AC_TYPE'];
$CITY_CODE = $_GET['CITY_CODE'];



$edate2 = str_replace("'" , "" , $edate);
$AC_TYPE1 = str_replace("'" , "" , $AC_TYPE);
$CITY_CODE2 = str_replace("'" , "" , $CITY_CODE);
$Branch = str_replace("'" , "" , $Branch);
$BankName = str_replace("'" , "" , $BankName);


$query ='SELECT SHMASTER."AC_NO",
SHMASTER."AC_NAME",
SHMASTER."AC_CATG",
CITYMASTER."CITY_CODE",
CITYMASTER."CITY_NAME",
CUSTOMERADDRESS."AC_HONO",
CUSTOMERADDRESS."AC_WARD",
CUSTOMERADDRESS."AC_ADDR",
CUSTOMERADDRESS."AC_GALLI",
CUSTOMERADDRESS."AC_AREA",
PREFIX."SEX",
EXTRACT(YEAR
FROM AGE(CAST("AC_BIRTH_DT" AS DATE)))AS "AC_MEM_AGE"
FROM SHMASTER
LEFT OUTER JOIN CUSTOMERADDRESS ON SHMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID"
LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID
LEFT OUTER JOIN IDMASTER ON SHMASTER."idmasterID" = IDMASTER."id"
LEFT OUTER JOIN PREFIX ON PREFIX."PREFIX" = IDMASTER."AC_TITLE"
WHERE SHMASTER."AC_TYPE" = '.$AC_TYPE.'
AND SHMASTER."BRANCH_CODE"='.$branch_code.'
AND SHMASTER."status"=1 AND SHMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND (SHMASTER."AC_OPDATE" IS NULL
                    OR CAST(SHMASTER."AC_OPDATE" AS DATE) <= CAST('.$edate.' AS DATE))
AND (SHMASTER."AC_CLOSEDT" IS NULL
                    OR CAST(SHMASTER."AC_CLOSEDT" AS DATE) > CAST('.$edate.' AS DATE))
AND CITYMASTER."CITY_CODE" = '.$CITY_CODE.'
ORDER BY CITYMASTER."CITY_CODE",
SHMASTER."AC_NO"';



          
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
        "AC_NO" => $row["AC_NO"],
        "AC_NAME"=> $row["AC_NAME"],
        "CITY_NAME" => $row["CITY_NAME"],
        "SEX"=> $row["SEX"],
        "AC_HONO" => $row["AC_HONO"],
        "AC_WARD" => $row["AC_WARD"],
        "AC_ADDR" => $row["AC_ADDR"],
        "AC_GALLI"=>$row["AC_GALLI"],
        "AC_AREA"=> $row["AC_AREA"],
        "AC_MEM_AGE"=> $row["AC_MEM_AGE"],
       
        "BankName" => $BankName,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "edate" => $edate2,
        "AC_TYPE" => $AC_TYPE1,
        "CITY_CODE" => $CITY_CODE2,
        
    ];
    if($row["SEX"]=='M'){
        $tmp['SEX']='Male';
    }
    else if($row["SEX"]=='F'){
        $tmp['SEX']='Female';
    }
    else if($row["SEX"]=='N'){
        $tmp['SEX']='Not applicable';
    }
    else {
        $tmp['SEX']='Other';
    }
    
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
