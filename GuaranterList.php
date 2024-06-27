<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/GuaranterList1.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");


//variables
$PRINT_DATE = $_GET['PRINT_DATE'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$bankName = $_GET['bankName'];
$NAME = $_GET['NAME'];

$dateformate = "'DD/MM/YYYY'";

$PRINT_DATE1 = str_replace("'", "", $PRINT_DATE);
$AC_ACNOTYPE1 = str_replace("'", "", $AC_ACNOTYPE);
$NAME = str_replace("'", "", $NAME);
$bankName = str_replace("'", "", $bankName);
$AC_TYPE = str_replace("'", "", $AC_TYPE);



$query = 'SELECT LNMASTER."AC_ACNOTYPE",LNMASTER."AC_TYPE",LNMASTER."AC_NO",LNMASTER."AC_NAME",CUSTOMERADDRESS."AC_WARD",
 CUSTOMERADDRESS."AC_ADDR", LNMASTER."AC_SANCTION_AMOUNT",LNMASTER."AC_SANCTION_DATE", 
 CITYMASTER."CITY_NAME",SCHEMAST."S_NAME", SCHEMAST."S_APPL",	LNMASTER.ID
  FROM LNMASTER 
 LEFT JOIN SCHEMAST ON  SCHEMAST.ID=LNMASTER."AC_TYPE"
  LEFT OUTER JOIN CUSTOMERADDRESS 
 ON LNMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID"
 left JOIN CITYMASTER ON CITYMASTER."id" = CUSTOMERADDRESS."AC_CTCODE"
 where
 (LNMASTER."AC_OPDATE" IS NULL
     OR CAST(LNMASTER."AC_OPDATE" AS DATE) <= DATE(' . $PRINT_DATE . ')) AND(LNMASTER."AC_CLOSEDT" IS NULL 
     OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > DATE(' . $PRINT_DATE . ')) AND LNMASTER."AC_ACNOTYPE" =' . $AC_ACNOTYPE . ' 
     AND LNMASTER."AC_TYPE" =' . $AC_TYPE . '
     AND LNMASTER."BRANCH_CODE" = ' . $BRANCH_CODE . '
     ORDER BY LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO"';


// echo $query; 


$sql =  pg_query($conn, $query);

echo (pg_num_rows($sql));
$i = 0;
while ($row = pg_fetch_assoc($sql)) {
    $AC_NO = $row['AC_NO'];

    $query1 = 'SELECT 	gcustadd."AC_ADDR" AS "GADD",
	gcustadd."AC_WARD" AS "GADDAC_WARD", GUARANTERDETAILS.*
FROM GUARANTERDETAILS
LEFT OUTER JOIN CUSTOMERADDRESS AS GCUSTADD ON CAST(GUARANTERDETAILS."GAC_CUSTID" AS INTEGER) = GCUSTADD."idmasterID"
WHERE "lnmasterID" =' . $row['id'];
    // echo $query1;
    $sql1 =  pg_query($conn, $query1);
    $gaurantor='';
    $address='';
    if (pg_num_rows($sql1) == 0)
    {

    } 
    else 
    {
        // $counter = 1;
        while ($demo = pg_fetch_assoc($sql1)) {
            $address =   ' '.$demo['AC_NAME'] . ' ' .  $demo['GADDAC_WARD'] . ' ' . $demo['GADD'].'<br>';
            $gaurantor = $gaurantor . '' . $address;
           
        }
    }
    // $i++;
    $tmp = [
        
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_WARD' => $row['AC_WARD'] . ' ' . $row['AC_ADDR'],
        'AC_ADDR' => '',
        'CITY_NAME' => $row['CITY_NAME'],
        'GAC_NAME' => $gaurantor,
        'AC_ACNOTYPE' => $row['S_APPL'] . ' ' . $row['S_NAME'],
        'AC_SANCTION_DATE' => $row['AC_SANCTION_DATE'],
        'AC_SANCTION_AMOUNT' => sprintf("%.2f", ($row['AC_SANCTION_AMOUNT'] + 0.0)),
        'AC_TYPE' => $AC_TYPE,
        'bankName' => $bankName,
        'NAME' => $NAME,
        'BRANCH_CODE' => $BRANCH_CODE,
        'PRINT_DATE' => $PRINT_DATE1,
    ];
    $data[$i] = $tmp;
    $i++;
    

}
ob_end_clean();

$config = ['driver' => 'array', 'data' => $data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    // ->
    ->export('Pdf');
