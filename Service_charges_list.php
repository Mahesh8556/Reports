<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Service_charges_list.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";


$Branch = $_GET['Branch'];
$branch_code = $_GET['branch_code'];
$BankName = $_GET['BankName'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$mincharges = $_GET['mincharges'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$TRANDRCR="'D'";
$TRAN_STATUS="'1'";

$sdate1 = str_replace("'" , "" , $sdate);
$edate2 = str_replace("'" , "" , $edate);
$Branch = str_replace("'" , "" , $Branch);
$BankName1 = str_replace("'" , "" , $BankName);
$AC_ACNOTYPE1 = str_replace("'" , "" , $AC_ACNOTYPE);




$query='SELECT * FROM (SELECT SCHEMAST."S_APPL", 
SCHEMAST."S_NAME",MASTER."AC_ACNOTYPE",
MASTER."AC_TYPE",
MASTER."AC_NO",
MASTER."BANKACNO",
MASTER."AC_NAME",
(COALESCE(TRANTABLE.TRAN_TRANSACTIONS,
        0) + COALESCE(DAILYTRANTABLE.DAILY_TRANSACTIONS,
0)) NO_OF_TRANSACTIONS,
((COALESCE(TRAN_TRANSACTIONS,
0) + COALESCE(DAILY_TRANSACTIONS,
0)) * 7) CHARGES
FROM DPMASTER MASTER
LEFT OUTER JOIN
(SELECT "TRAN_ACNOTYPE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        SUM(CASE "TRAN_ACNO"
WHEN CAST(0 AS CHARACTER VARYING) THEN 0 ELSE 1 END) TRAN_TRANSACTIONS
    FROM DEPOTRAN TRANTABLE
    WHERE "TRAN_DRCR" = '.$TRANDRCR.'
        AND CAST("TRAN_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
        AND "TRAN_ACNOTYPE" = '.$AC_ACNOTYPE.'
 AND "BRANCH_CODE"='.$branch_code.'
    GROUP BY "TRAN_ACNOTYPE",
        "TRAN_ACTYPE",
        "TRAN_ACNO") TRANTABLE ON MASTER."BANKACNO" = TRANTABLE."TRAN_ACNO"
LEFT OUTER JOIN
(SELECT "TRAN_ACNOTYPE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        SUM(CASE "TRAN_ACNO"
WHEN CAST(0 AS CHARACTER VARYING) THEN 0 ELSE 1  END) DAILY_TRANSACTIONS
    FROM DAILYTRAN DAILYTRANTABLE
    WHERE "TRAN_DRCR" = '.$TRANDRCR.'
        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
 AND "BRANCH_CODE"='.$branch_code.'
        AND CAST("TRAN_DATE" AS DATE) BETWEEN CAST('.$sdate.' AS DATE) AND CAST('.$edate.' AS DATE)
        AND "TRAN_ACNOTYPE" = '.$AC_ACNOTYPE.'
    GROUP BY "TRAN_ACNOTYPE",
        "TRAN_ACTYPE",
        "TRAN_ACNO") DAILYTRANTABLE ON MASTER."BANKACNO" = DAILYTRANTABLE."TRAN_ACNO" 
        LEFT JOIN SCHEMAST ON SCHEMAST.ID= MASTER."AC_TYPE"
        WHERE(MASTER."AC_OPDATE" IS NULL 

OR CAST(MASTER."AC_OPDATE" AS DATE) <= CAST('.$edate.' AS DATE)) AND (MASTER."AC_CLOSEDT" IS NULL
                OR CAST(MASTER."AC_CLOSEDT" AS DATE) > CAST('.$edate.' AS DATE))
AND MASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND MASTER.STATUS=1 AND MASTER."BRANCH_CODE"='.$branch_code.' AND MASTER."SYSCHNG_LOGIN" IS NOT NULL
AND (COALESCE(TRANTABLE.TRAN_TRANSACTIONS,
        0) + COALESCE(DAILYTRANTABLE.DAILY_TRANSACTIONS,
0)) > 0 )MAIN WHERE CAST(charges AS FLOAT) < '.$mincharges.' ORDER BY "AC_NO"';

    //    echo $query;
$sql =  pg_query($conn,$query);

$i = 0;



// if (pg_num_rows($sql) == 0) {
//     include "errormsg.html";
// }else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['charges'];
    $TRAN_TOTAL = $TRAN_TOTAL + $row['no_of_transactions'];


    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_NO" => $row["AC_NO"],
        "AC_NAME"=> $row["AC_NAME"],
        "no_of_transactions"=> $row["no_of_transactions"],
        "charges" => sprintf("%.2f", (abs($row['charges']))),
        'charges_total' => sprintf("%.2f" ,($GRAND_TOTAL) + 0.0),
        'tran_total' => $TRAN_TOTAL,
        "Branch" => $Branch,
        'branch_code' => $branch_code ,
        "BankName" => $BankName1,
        "sdate" => $sdate1,
        "edate" => $edate2,
        "AC_ACNOTYPE" => $row['S_APPL'].'  '.$row['S_NAME'],
        //"TRAN_TYPE2" => $TRAN_TYPE2,
        "mincharges" => $mincharges,
      
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
