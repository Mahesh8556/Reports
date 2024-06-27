<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LockerRentRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=bank user=postgres password=tushar");

// variables

$ac_type = $_GET['ac_type'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$FLAG = $_GET['FLAG'];
$bank_name = $_GET['bank_name'];
$branch_name = $_GET['branch_name'];
$ac_acnotype = $_GET['ac_acnotype'];
$branch_code = $_GET['branch_code'];
$tran_status = "'1'";
$status = '1';
$LK='LK';

$ac_acnotype1 = str_replace("'" , "" , $ac_acnotype);
$ac_type = str_replace("'", "", $ac_type);
$FLAG = str_replace("'", "", $FLAG);
$bank_name = str_replace("'", "", $bank_name);
$sdate1 = str_replace("'", "", $sdate);
$edate2 = str_replace("'", "", $edate);
$branch_name = str_replace("'", "", $branch_name);

$dateformat ="'DD/MM/YYYY'";



if(  $FLAG== 1 )
{
     $query = 'SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", "TRAN_AMOUNT", "RENT_UPTO_DATE",
     "S_APPL", LOCKERRENTTRAN."BRANCH_CODE"
     FROM DPMASTER, LOCKERRENTTRAN 
     LEFT JOIN SCHEMAST ON 
     CAST(LOCKERRENTTRAN."TRAN_ACTYPE" AS integer) = SCHEMAST.ID
     WHERE DPMASTER."AC_ACNOTYPE" = LOCKERRENTTRAN."TRAN_ACNOTYPE"
     AND DPMASTER."AC_TYPE" = CAST(LOCKERRENTTRAN."TRAN_ACTYPE" AS integer)
     AND CAST(DPMASTER."BANKACNO" AS bigint) = CAST(LOCKERRENTTRAN."TRAN_ACNO" AS bigint)
     AND DPMASTER."AC_ACNOTYPE" = '.$ac_acnotype.' AND DPMASTER."AC_TYPE" = '.$ac_type.'
     AND CAST("RENT_UPTO_DATE" AS DATE) BETWEEN DATE('.$sdate.')
     AND DATE('.$edate.') AND CAST(LOCKERRENTTRAN."TRAN_STATUS" AS INTEGER) = '.$tran_status.'
     AND DPMASTER."BRANCH_CODE"='.$branch_code.' AND DPMASTER."status"= '.$status.' AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL';

            //    echo $query;
}
else 
{
    $query = 'SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", "RENT_UPTO_DATE" FROM DPMASTER
     LEFT OUTER JOIN (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", MAX("RENT_UPTO_DATE") "RENT_UPTO_DATE" 
     FROM LOCKERRENTTRAN WHERE "TRAN_STATUS" = '.$tran_status.' 
     AND "BRANCH_CODE" = '.$branch_code.'
     GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO") LOCKERRENTTRAN 
     ON DPMASTER."AC_ACNOTYPE" = LOCKERRENTTRAN."TRAN_ACNOTYPE" 
     AND DPMASTER."AC_TYPE" = CAST(LOCKERRENTTRAN."TRAN_ACTYPE" AS integer) 
     AND DPMASTER."BANKACNO" = LOCKERRENTTRAN."TRAN_ACNO" WHERE DPMASTER."AC_ACNOTYPE" = '.$ac_acnotype.' 
     AND DPMASTER."AC_TYPE" = '.$ac_type.' AND ("RENT_UPTO_DATE" IS NULL
      OR CAST("RENT_UPTO_DATE" AS date) < DATE('.$sdate.')) AND DPMASTER."AC_CLOSEDT" IS NULL AND DPMASTER."status" ='.$status.' 
      AND "SYSCHNG_LOGIN" IS NOT NULL AND DPMASTER."BRANCH_CODE" =  '.$branch_code.'';

                //  echo $query;
}
// echo $query;
             
$sql =  pg_query($conn,$query);

 $i = 0;

while($row = pg_fetch_assoc($sql))
{ 
    $TOTAL_RENTBAL = $TOTAL_RENTBAL + $row['TRAN_AMOUNT'] ;
    
    $tmp=[
        'AC_NO'=> $row['AC_NO'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_NAME'=> $row['AC_NAME'],
        'TRAN_AMOUNT'=> $row['TRAN_AMOUNT'],
        'RENT_UPTO_DATE'=> $row['RENT_UPTO_DATE'],
        // 'TRAN_ACNO'=> $row['TRAN_ACNO'],
        // 'TRAN_ACNOTYPE'=> $row['TRAN_ACNOTYPE'],
        // 'TRAN_TYPE'=> $row['TRAN_TYPE'],
        // 'TRAN_ACTYPE'=> $row['TRAN_ACTYPE'],
        // 'ac_acnotype' => $row['ac_acnotype1'],
        'ac_type' => $ac_type,
        'sdate' => $sdate1,
        'edate' => $edate2 ,
        'branch_code' => $branch_code,
        'branch_name'=> $branch_name,
        'bank_name'=> $bank_name,
        // 'FLAG' => $FLAG1,
        // 'FLAG' => $FLAG2, 
        'tran_status' => $tran_status,
        'TOTAL_RENTBAL' => sprintf("%.2f",($TOTAL_RENTBAL + 0.0)),
        
    ];
    $data[$i]=$tmp;
    $i++;  
}
// print_r($data)
ob_end_clean();
// echo $query;

$config = ['driver'=>'array','data'=>$data];
// echo $filename;
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>