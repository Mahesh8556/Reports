<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/TDReceiptPrintSSKOTOLI.jrxml';
// $filename = __DIR__ . '/TDReceiptPrintvitthal.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$td = "'TD'";
$acno = $_GET['BANKACNO1'];;
$acno1 = $_GET['BANKACNO2'];;

$dateformate = "'DD/MM/YYYY'";
$ATYPE="'P'";

$query =  'SELECT DPMASTER."AC_NO",
          DPMASTER.ID,
              DPMASTER."AC_NAME",
              DPMASTER."AC_OPDATE",
              COALESCE(DPMASTER."AC_ASON_DATE", DPMASTER."AC_OPDATE") AS "AC_ASON_DATE", DPMASTER."AC_EXPDT",
              DPMASTER."AC_MONTHS",
              DPMASTER."AC_DAYS",
              DPMASTER."AC_INTRATE",
              DPMASTER."AC_SCHMAMT",
              DPMASTER."AC_MATUAMT",
              DPMASTER."AC_ACNOTYPE",
              DPMASTER."AC_REF_RECEIPTNO",
              SCHEMAST."S_NAME",
              FN_AMTTOWORDENGLISH(DPMASTER."AC_SCHMAMT") "DEPAMT", FN_AMTTOWORDENGLISH(DPMASTER."AC_MATUAMT") "MATAMT",
              customeraddress."AC_ADDR",CITYMASTER."CITY_NAME"
              FROM DPMASTER
              left join customeraddress on customeraddress."idmasterID"=DPMASTER."idmasterID" 
		      AND CUSTOMERADDRESS."AC_ADDTYPE"=' . $ATYPE . '
		      LEFT JOIN CITYMASTER ON CITYMASTER.ID=CUSTOMERADDRESS."AC_CTCODE"
              INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
              WHERE DPMASTER."AC_ACNOTYPE" = ' . $td . '
              AND DPMASTER."BANKACNO" = ' . $acno . '';

// echo $query;

$sql =  pg_query($conn, $query);

$i = 0;

$nominee = '';
$symbol = "||";
$symbol1 = "')'";
$space = "'  '";
$space1 = "' '";


if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
} else {

    while ($row = pg_fetch_assoc($sql)) {

        if (isset($row['id'])) {
            //     // while($row = pg_fetch_assoc($sql)){   
            $query3 = 'SELECT string_agg( CAST(cnt ' . $symbol . $symbol1 . $symbol . ' ' . $space1 . ' ' . $symbol . '  "NOMINEE" AS CHARACTER VARYING),' . $space . ') "NAME1"  FROM(
            select ROW_NUMBER() OVER (
            ORDER BY "DPMasterID"
          ) cnt , "DPMasterID",  "AC_NNAME" "NOMINEE" from NOMINEELINK where
           NOMINEELINK."DPMasterID"=' . $row['id'] . ' )NOMINEE
            group by "DPMasterID"';

            $sql3 =  pg_query($conn, $query3);
            while ($row3 = pg_fetch_assoc($sql3)) {
                $nominee = $row3['NAME1'];
            }
        }
        $tmp = [
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_EXPDT' => $row['AC_EXPDT'],
            'AC_MONTHS' => $row['AC_MONTHS'] .'/'. $row['AC_DAYS'],
            'AC_DAYS' => $row['AC_DAYS'],
            'AC_INTRATE' => $row['AC_INTRATE'],
            'AC_SCHMAMT' => $row['AC_SCHMAMT'],
            'AC_MATUAMT' => $row['AC_MATUAMT'],
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
            'S_NAME' => $row['S_NAME'],
            'AC_ADDR' => $row['CITY_NAME'],
            'FN_AMTTOWORDENGLISH' => $row['DEPAMT'],
            'FN_AMTTOWORDENGLISH1' => $row['MATAMT'],
            'AC_ASON_DATE' => $row['AC_ASON_DATE'],
            "AC_NNAME" => $nominee,
            'bankName' => $bankName,
            'edate' => $edate,
            'stdate_' => $stdate_,
            'custid' => $custid,
            'branch' => $branch,
            'pritns' => $pritns,

        ];
        $data[$i] = $tmp;
        $i++;
    }
    ob_end_clean();
     //print_r($data);
    $config = ['driver' => 'array', 'data' => $data];

   $report = new PHPJasperXML();
   $report->load_xml_file($filename)
      ->setDataSource($config)
      ->export('Pdf');
}
