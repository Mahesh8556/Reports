<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

header('Access-Control-Allow-Origin: http://localhost:4200');
//  header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding');
header('Access-Control-Max-Age: 1000');  
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');

use simitsdk\phpjasperxml\PHPJasperXML;

// $filename = __DIR__.'/ODRegister.jrxml';

$filename = __DIR__.'/MinorList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");

// connection message
if($conn)
{
    echo 'Open Database Succesfully';
 
}else
{
    echo 'fail';
}
//variables
$print_date = $_GET['print_date'];
$ac_type = $_GET['ac_type'];
$branch_name = $_GET['branch_name'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$BANK_NAME = $_GET['BANK_NAME'];
$BRANCH_CODE =$_GET['BRANCH_CODE'];
$dateformate = "'DD/MM/YYYY'";
$status = '1';

$AC_ACNOTYPE1 = str_replace("'", "" , $AC_ACNOTYPE);
$print_date1 = str_replace("'", "" , $print_date);
$branch_name = str_replace("'", "" , $branch_name);
$ac_type = str_replace("'", "" ,$ac_type);
$BANK_NAME = str_replace("'", "" , $BANK_NAME);
// $print_date = str_replace("'" , "" , $print_date);


//get data from table

// echo $sql1;

$query ='SELECT DPMASTER."AC_ACNOTYPE", DPMASTER."AC_TYPE", DPMASTER."AC_NO", DPMASTER."AC_NAME",
DPMASTER."AC_MBDATE", DPMASTER."AC_GRDNAME" , DPMASTER."AC_GRDRELE", SCHEMAST."S_NAME",SCHEMAST."S_APPL", 
(CASE WHEN "AC_MBDATE" IS NULL THEN null ELSE (select add_months(DPMASTER."AC_EXPDT",(18*12))) END ) as months ,
AGE(CAST(DPMASTER."AC_EXPDT" AS date), CAST(DPMASTER."AC_MBDATE" AS date) ) AS "AGE"
FROM DPMASTER LEFT OUTER JOIN SCHEMAST ON DPMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
AND DPMASTER."AC_TYPE" = SCHEMAST."id" WHERE CAST(DPMASTER."AC_MINOR" AS INTEGER) <> 0 AND
( DPMASTER."AC_OPDATE" IS NULL OR CAST(DPMASTER."AC_OPDATE" AS DATE) <= DATE('.$print_date.' )) 
AND ( DPMASTER."AC_CLOSEDT" IS NULL OR CAST(DPMASTER."AC_CLOSEDT" AS DATE) > DATE('.$print_date.'))
AND DPMASTER."AC_ACNOTYPE" ='.$AC_ACNOTYPE.' AND DPMASTER."AC_TYPE" =' .$ac_type.'
 AND DPMASTER."BRANCH_CODE"= '.$BRANCH_CODE.' AND DPMASTER."status"='.$status.' and 
DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
ORDER BY DPMASTER."AC_ACNOTYPE", DPMASTER."AC_TYPE", DPMASTER."AC_NO"';

    //    echo $query;

$sql =  pg_query($conn,$query);

$i = 0;
 
while($row = pg_fetch_assoc($sql)){
// print_r($row);
    $tmp=[

        'AC_No'=> $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
      
        'AC_ACNOTYPE' =>$row['S_APPL'] .' '. $row['S_NAME'],
        'age'=> $row['AGE'],
        'AC_MBDATE' => $row['AC_MBDATE'], 
        'AC_GRDNAME' => $row['AC_GRDNAME'],     
        'AC_GRDRELE' => $row['AC_GRDRELE'],  
       
        'ac_type' => $ac_type,
        'print_date'=> $print_date1,
        // 'branch_name' => $branch_name,
        // 'BANK_NAME' => $BANK_NAME,
        'BRANCH_CODE' => $BRANCH_CODE,

        'Branch' => $branch_name,
        'bankName'=>$BANK_NAME,
        
    ];    
    $data[$i]=$tmp;
    $i++;   

 
}

//  echo $d;

// for clean previous execution
ob_end_clean();
// $pdf->Output($file, 'I');
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
//  print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    // $PHPJasperXML->arrayParameter=array("parameter1"=>$value);
