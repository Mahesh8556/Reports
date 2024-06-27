<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/penal interest list.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
$bankname  = $_GET['bankname'];
$dateformate = "'DD/MM/YYYY'";
$TRAN_ACTYPE  = $_GET['TRAN_ACTYPE'];
$BranchName  = $_GET['BranchName'];
$sdate  = $_GET['sdate'];
$edate  = $_GET['edate'];
$TRAN  = $_GET['TRAN'];
$BRANCH_CODE  = $_GET['BRANCH_CODE'];
$one="'1'";
$D="'D'";
$C="'C'";
$zero="'0'";


$BranchName1 = str_replace("'", "", $BranchName);
$bankname1 = str_replace("'", "", $bankname);
$sdate1 = str_replace("'", "", $sdate);
$edate1 = str_replace("'", "", $edate);


     $query = 'SELECT "S_APPL","S_NAME","AC_NAME","TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO",
          (SUM("INT_AMOUNT") + SUM(CAST("INTEREST_AMOUNT" AS FLOAT))) AS "INTEREST_AMOUNT", SUM(CAST("PENAL_INTEREST" AS FLOAT))
           "PENAL_INTEREST" FROM (SELECT "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO",SUM(COALESCE(CAST("TRAN_AMOUNT" AS FLOAT),  0)) 
           "INT_AMOUNT",0 "INTEREST_AMOUNT",0 "PENAL_INTEREST",SCHEMAST."S_APPL",SCHEMAST."S_NAME",MAX(LNMASTER."AC_NAME") "AC_NAME" FROM
            LOANTRAN LEFT JOIN LNMASTER ON LNMASTER."BANKACNO"=LOANTRAN."TRAN_ACNO"   LEFT JOIN SCHEMAST ON SCHEMAST.ID=CAST(LOANTRAN.
            "TRAN_ACTYPE" AS INTEGER)   WHERE CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$sdate.','.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <=
             TO_DATE('.$edate.','.$dateformate.')  AND "TRAN_ACNOTYPE" = '.$TRAN.' AND "TRAN_ACTYPE" ='.$TRAN_ACTYPE.' AND "TRAN_DRCR" = '.$D.' 
             AND "IS_INTEREST_ENTRY" <> '.$zero.'  AND LOANTRAN."BRANCH_CODE"= '.$BRANCH_CODE .'
              AND (CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE('.$edate.','.$dateformate.'))

              GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE",
             "TRAN_ACNO",SCHEMAST."S_APPL",   SCHEMAST."S_NAME" UNION SELECT "TRAN_ACNOTYPE","TRAN_ACTYPE", "TRAN_ACNO", 0 "INT_AMOUNT",SUM
             (COALESCE(CAST("INTEREST_AMOUNT" AS FLOAT),0)) "INTEREST_AMOUNT",  SUM(COALESCE(CAST("PENAL_INTEREST" AS FLOAT),0)) 
             "PENAL_INTEREST", SCHEMAST."S_APPL",  SCHEMAST."S_NAME", MAX(LNMASTER."AC_NAME") "AC_NAME" FROM LOANTRAN  LEFT JOIN 
             LNMASTER ON LNMASTER."BANKACNO"=LOANTRAN."TRAN_ACNO"        LEFT JOIN SCHEMAST ON SCHEMAST.ID=CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER) WHERE CAST("TRAN_DATE" AS DATE) >= TO_DATE('.$sdate.','.$dateformate.') AND CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$edate.',
             '.$dateformate.') AND "TRAN_ACNOTYPE" ='.$TRAN.'  AND "TRAN_ACTYPE" = '.$TRAN_ACTYPE.' AND "TRAN_DRCR" = '.$C.' AND LOANTRAN.
             "BRANCH_CODE"= '.$BRANCH_CODE .' 
             AND (CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL OR CAST(LNMASTER."AC_CLOSEDT" AS DATE) > TO_DATE('.$edate.','.$dateformate.'))
             GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO", SCHEMAST."S_APPL", SCHEMAST."S_NAME" ORDER BY 
             "TRAN_ACNO") S  GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO","S_APPL","S_NAME","AC_NAME"';

// echo $query;

$sql =  pg_query($conn, $query);
$i = 0;
$itotal=0;
$ptotal=0;
$grandtotal=0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
while ($row = pg_fetch_assoc($sql)) {
    $itotal = $itotal + $row["INTEREST_AMOUNT"];
    $ptotal = $ptotal + $row["PENAL_INTEREST"];
    $grandtotal= $grandtotal + $row["PENAL_INTEREST"]+$row["INTEREST_AMOUNT"];
    $tmp=[
    "Accountname"=> $row["AC_NAME"],
    "TRAN_ACNOTYPE"=>$row["S_APPL"]. ' ' . $row['S_NAME'],
    "TRAN_ACTYPE"=> $row["TRAN_ACTYPE"],
    "TRAN_ACNO" => $row["TRAN_ACNO"],
    "NORMAL_INTEREST" => sprintf("%.2f", ($row['INTEREST_AMOUNT'] + 0.0)),
    "PENAL_INTEREST" =>sprintf("%.2f", ($row['PENAL_INTEREST'] + 0.0)),
    "BranchName" => $BranchName1 ,
    "TRAN_ACTYPE" => $TRAN_ACTYPE ,
    "BRANCH_CODE" => $BRANCH_CODE1 ,
    "sdate" => $sdate1,
    "edate" => $edate1,
    "TRAN" => $TRAN,
    "itotal" =>  sprintf("%.2f", ($itotal+ 0.0)),
    "ptotal" =>  sprintf("%.2f", ($ptotal+ 0.0)),
    "total" =>  sprintf("%.2f", ($row["PENAL_INTEREST"]+$row["INTEREST_AMOUNT"] + 0.0)),
    "grandtotal" =>sprintf("%.2f", ($grandtotal+ 0.0)),
    "one" => $one,
    "bankname" => $bankname1,



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
