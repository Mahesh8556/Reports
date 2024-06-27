<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/goldsilverreceiptprint1.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
$bankname  = $_GET['bankName'];
$BranchName  = $_GET['branchName'];
$BRANCH_CODE  = $_GET['branch'];
// $ACTYPE  = $_GET['schemeCode'];
$scheme  = $_GET['scheme'];
$accountno  = $_GET['accountno'];
$sdate  = $_GET['startDate'];
$dateformate = "'DD/MM/YYYY'";

$p = "'P'";

$BranchName1 = str_replace("'", "", $BranchName);
$bankname1 = str_replace("'", "", $bankname);
$sdate1 = str_replace("'", "", $sdate);
$accountno1 = str_replace("'", "", $accountno);


     $query = 'SELECT
     GOLDSILVER."AC_ACNOTYPE", GOLDSILVER."AC_TYPE", GOLDSILVER."AC_NO",
      ROW_NUMBER () OVER (ORDER BY GOLDSILVER."AC_NO") AS "SR_NO", 
       GOLDSILVER."SUBMISSION_DATE", GOLDSILVER."ARTICLE_NAME", GOLDSILVER."TOTAL_WEIGHT_GMS", LNMASTER."AC_SANCTION_AMOUNT" 
      , GOLDSILVER."CLEAR_WEIGHT_GMS", GOLDSILVER."TOTAL_VALUE",GOLDSILVER."RATE", 
      LNMASTER."AC_COREG_NO", LNMASTER."AC_EXPIRE_DATE" 
      , GOLDSILVER."BAG_RECEIPT_NO" , GOLDSILVER."GOLD_BOX_NO",  GOLDSILVER."NOMINEE" , GOLDSILVER."NOMINEE_RELATION" 
      , GOLDSILVER."REMARK", GOLDSILVER."MARGIN", 
      SCHEMAST."S_NAME" SCHEME_NAME , LNMASTER."AC_NAME", SECURITYMASTER."SECU_NAME" ,CUSTOMERADDRESS."AC_ADDR",
	CITYMASTER."CITY_NAME"
      FROM GOLDSILVER 
      LEFT OUTER JOIN SCHEMAST ON GOLDSILVER."AC_TYPE" = SCHEMAST.ID 
      LEFT OUTER JOIN LNMASTER ON CAST(GOLDSILVER."AC_NO" AS CHARACTER VARYING) = CAST(LNMASTER."BANKACNO" AS CHARACTER VARYING) 
      LEFT JOIN CUSTOMERADDRESS On CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID" AND CUSTOMERADDRESS."AC_ADDTYPE" = '.$p.'
LEFT JOIN CITYMASTER on CITYMASTER.id = CUSTOMERADDRESS."AC_CTCODE"
      LEFT OUTER JOIN SECURITYMASTER ON GOLDSILVER."SECU_CODE" = SECURITYMASTER."SECU_CODE" 
      WHERE GOLDSILVER."AC_ACNOTYPE" ='.$scheme.'
          AND  GOLDSILVER."AC_NO" = '.$accountno.' ';

echo $query;

$sql =  pg_query($conn, $query);
$i = 0;
$itotal=0;
$ptotal=0;
$grandtotal=0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
while ($row = pg_fetch_assoc($sql)) {

    $Ratetengram = $row["RATE"] * 10;
    $totalvalue=$totalvalue+ $row['TOTAL_VALUE'];
    $sancamt=$sancamt+ $row['AC_SANCTION_AMOUNT'];
    $twgms=$twgms+ $row['TOTAL_WEIGHT_GMS'];
    $cwgms=$cwgms+ $row['CLEAR_WEIGHT_GMS'];
    $rttotal=$rttotal+ $Ratetengram;

    $tmp=[
    "Accountname"=> $row["AC_NAME"],
    // "receiptno"=> $row["BAG_RECEIPT_NO"],
    "receiptno"=> $accountno1,
    "ARTICLE_NAME"=> $row["ARTICLE_NAME"],
    "RATE"=> sprintf("%.2f", ($Ratetengram + 0.0)),
    "TOTAL_WEIGHT_GMS"=> $row["TOTAL_WEIGHT_GMS"],
    "CLEAR_WEIGHT_GMS"=> $row["CLEAR_WEIGHT_GMS"],
    "AC_SANCTION_AMOUNT"=> $row["AC_SANCTION_AMOUNT"],
    "AC_EXPIRE_DATE"=> $row["AC_EXPIRE_DATE"],
    "SUBMISSION_DATE"=> $row["SUBMISSION_DATE"],
    "TOTAL_VALUE"=> $row["TOTAL_VALUE"],
    "address"=> $row["CITY_NAME"],
    "bankname" => $bankname1,
    "BranchName" => $BranchName1 ,
    "sdate" => $sdate1,
    "TRAN_ACTYPE" => $TRAN_ACTYPE ,
   
    'totalvalue'=> sprintf("%.2f", ($totalvalue + 0.0)),
    'sancamt'=> sprintf("%.2f", ( $row["AC_SANCTION_AMOUNT"] + 0.0)),
    'rttotal'=> sprintf("%.2f", ($Ratetengram + 0.0)),
    'twgms'=> sprintf("%.3f", ($twgms + 0.0)),
    'cwgms'=> sprintf("%.3f", ($cwgms + 0.0)),


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
}
