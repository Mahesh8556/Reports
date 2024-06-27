<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Pending_Stock_Statement.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$BankName  = $_GET['BankName'];
$Branch  = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
//$AC_ACNOTYPE1 = str_replace("'" , "" , $AC_ACNOTYPE);



$query='SELECT SCHEMAST."S_APPL", 
SCHEMAST."S_NAME",LNMASTER."AC_ACNOTYPE",
LNMASTER."AC_TYPE",
LNMASTER."AC_NO",
"AC_NAME",
"AC_SANCTION_AMOUNT",
"AC_OPDATE",
STATEMENT_DATE
FROM LNMASTER
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE",
          "AC_TYPE",
          "AC_NO"
     FROM
          (SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO"
               FROM SECURITYDETAILS
               WHERE CAST("SECURITY_CODE" AS INTEGER) IN
                         (SELECT "SECU_CODE"
                              FROM SECURITYMASTER
                              WHERE CAST("BOOK_DEBTS" AS INTEGER) <> 0
                                   OR CAST("PLEDGE_STOCK" AS INTEGER) <> 0
                                   OR CAST("STOCK_STATEMENT" AS INTEGER) <> 0) ) S
     EXCEPT SELECT "AC_ACNOTYPE",
          CAST("AC_TYPE" AS CHARACTER varying),
          "AC_NO"
     FROM
          (SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO"
               FROM BOOKDEBTS
               WHERE CAST("STATEMENT_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
               UNION ALL SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO"
               FROM PLEDGESTOCK
               WHERE CAST("STORAGE_DATE" AS DATE) BETWEEN CAST ('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
               UNION ALL SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO"
               FROM STOCKSTATEMENT
               WHERE CAST("STATEMENT_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
          ) A) DETAILS ON LNMASTER."BANKACNO" = DETAILS."AC_NO"
          LEFT JOIN SCHEMAST ON SCHEMAST.ID= LNMASTER."AC_TYPE"
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE",
          "AC_TYPE",
          "AC_NO",
          MAX("STATEMENT_DATE") STATEMENT_DATE
     FROM
          (SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO",
                    "STATEMENT_DATE"
               FROM STOCKSTATEMENT
               WHERE CAST("STATEMENT_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
               UNION ALL SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO",
                    "STATEMENT_DATE"
               FROM BOOKDEBTS
               WHERE CAST("STATEMENT_DATE" AS DATE) <= CAST('.$edate.' AS DATE)
               UNION ALL SELECT "AC_ACNOTYPE",
                    "AC_TYPE",
                    "AC_NO",
                    "STORAGE_DATE" STATEMENT_DATE
               FROM PLEDGESTOCK
               WHERE CAST("STORAGE_DATE" AS DATE) <= CAST('.$edate.' AS DATE) ) S
     GROUP BY "AC_ACNOTYPE",
          "AC_TYPE",
          "AC_NO") LAST_HISTORY ON LNMASTER."BANKACNO" = LAST_HISTORY."AC_NO" 
WHERE (LNMASTER."AC_CLOSEDT" IS NULL
                              OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) >= CAST('.$edate.' AS DATE))
AND LNMASTER."BRANCH_CODE"='.$branch_code.' AND LNMASTER.STATUS=1 AND LNMASTER."SYSCHNG_LOGIN"  IS NOT NULL
AND LNMASTER."AC_ACNOTYPE"='.$AC_ACNOTYPE.' AND LNMASTER."AC_TYPE"='.$AC_TYPE.'
                              ORDER BY SCHEMAST."S_APPL",LNMASTER."AC_NO"
                              ';

  
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
        "AC_NAME" => $row["AC_NAME"],
        'AC_ACNOTYPE' => $AC_ACNOTYPE,
        'AC_TYPE' => $AC_TYPE,

        "STATEMENT_DATE"=> $row["STATEMENT_DATE"],
        "AC_OPDATE" => $row["AC_OPDATE"],
        "AC_SANCTION_AMOUNT"=>sprintf("%.2f", (abs($row['AC_SANCTION_AMOUNT']))),

        "SCHEME_TOTAL" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "TOTAL_AMT" => sprintf("%.2f",($GRAND_TOTAL) + 0.0 ) ,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "BankName" => $BankName1,
        "sdate" => $sdate1,
        "edate" => $edate2,
       
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
