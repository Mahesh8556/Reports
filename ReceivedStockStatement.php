<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ReceivedStockStatement.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$Branch  = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName  = $_GET['BankName'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
$AC_ACNOTYPE1 = str_replace("'" , "" , $AC_ACNOTYPE);

$query= ' SELECT MASTER.*,SCHEMAST."S_APPL",SCHEMAST."S_NAME" FROM 
(SELECT LNMASTER."AC_TYPE",
LNMASTER."AC_NO",
"AC_NAME",
"AC_SANCTION_AMOUNT",
"AC_OPDATE",
"SUBMISSION_DATE",
"STATEMENT_DATE"
FROM LNMASTER,
(SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO",
        "SUBMISSION_DATE",
        "STATEMENT_DATE"
    FROM STOCKSTATEMENT
    WHERE CAST("STATEMENT_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
    UNION ALL SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO", 
        "SUBMISSION_DATE",
        "STATEMENT_DATE"
    FROM BOOKDEBTS
    WHERE CAST("STATEMENT_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
    UNION ALL SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO",
        "SUBMISSION_DATE",
        "STORAGE_DATE" STATEMENT_DATE
    FROM PLEDGESTOCK
    WHERE CAST("STORAGE_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
) STOCKSTATEMENT
WHERE LNMASTER."AC_ACNOTYPE" = STOCKSTATEMENT."AC_ACNOTYPE"
AND LNMASTER."AC_TYPE" = STOCKSTATEMENT."AC_TYPE"
AND LNMASTER."BANKACNO" = STOCKSTATEMENT."AC_NO"
AND LNMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND LNMASTER."BRANCH_CODE"='.$branch_code.' AND LNMASTER.STATUS=1 AND LNMASTER."SYSCHNG_LOGIN"  IS NOT NULL
AND LNMASTER."AC_TYPE"='.$AC_TYPE.'
                        ORDER BY CAST("AC_OPDATE" AS DATE) ) MASTER left join schemast on schemast.id=MASTER."AC_TYPE"';

                        // echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row["AC_SANCTION_AMOUNT"];

    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_NO" => $row["AC_NO"],
        "S_APPL" => $row["S_APPL"],
        "S_NAME" => $row["S_NAME"],
        "AC_NAME"=> $row["AC_NAME"],
        "STATEMENT_DATE"=> $row["STATEMENT_DATE"],
        "SUBMISSION_DATE" => $row["SUBMISSION_DATE"],
        "AC_SANCTION_AMOUNT"=>sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))),

        "SCHEMEWISE_TOTAL" =>sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "TOTAL_AMT" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "BankName" => $BankName1,
        "sdate" => $sdate1,
        "edate" => $edate2,
        "AC_ACNOTYPE" => $row['S_APPL'].'  '.$row['S_NAME'],
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
    
// }   
?>
