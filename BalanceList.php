<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');

// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/BalanceList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $date = "'31/03/2020'";
$int = "'0'";

$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
// $sdate = $_GET['sdate'];
$scheme = $_GET['scheme'];
$Rstartingacc = $_GET['Rstartingacc'];
$Rendingacc = $_GET['Rendingacc'];
$branch = $_GET['branch'];
$Rdio = $_GET['Rdio'];
$Rdiosort = $_GET['Rdiosort'];

// echo $sdate;
// $bankName = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
// $scheme = str_replace("'", "", $scheme);
// $Rdio = str_replace("'", "", $Rstartingacc);
// $Rdiosort = str_replace("'", "", $Rdiosort);

// echo $sdate;

$query1 = 'select * from ownbranchmaster where id = ' . $branch.'';
// echo $query1;
// echo $conn;

$result = pg_query($conn, $query1);
// print_r($result);
$branch_name = '';
$code = '0';
$GL = "'GL'";

while ($row = pg_fetch_assoc($result)) {
    $code = $row['CODE'];
    $branch_name = "'" . $row['NAME'] . "'";
}

$query = '
SELECT ledgerbalance(cast (schemast."S_APPL" as character varying), 
LNMASTER."BANKACNO",' . $startDate . ',1,' . $code . ')as ledgerbalance, LNMASTER."AC_NO",LNMASTER."AC_NAME",OWNBRANCHMASTER."NAME",LNMASTER."AC_TYPE",SCHEMAST."S_NAME" from lnmaster
INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.id
INNER JOIN OWNBRANCHMASTER ON LNMASTER."BRANCH_CODE" = OWNBRANCHMASTER.id
where LNMASTER."BRANCH_CODE" = ' . $branch . ' AND LNMASTER."AC_TYPE" = ' . $scheme . ' AND LNMASTER."AC_NO" BETWEEN ' . $Rstartingacc . ' AND ' . $Rendingacc . '
UNION
SELECT ledgerbalance(cast (schemast."S_APPL" as character varying), 
DPMASTER."BANKACNO",' . $startDate . ',1,' . $code . ')as ledgerbalance, DPMASTER."AC_NO",DPMASTER."AC_NAME",OWNBRANCHMASTER."NAME",DPMASTER."AC_TYPE",SCHEMAST."S_NAME" from DPMASTER
INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.id
INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER.id
where DPMASTER."BRANCH_CODE" = ' . $branch . ' AND DPMASTER."AC_TYPE" = ' . $scheme . ' AND DPMASTER."AC_NO" BETWEEN ' . $Rstartingacc . ' AND ' . $Rendingacc . '

UNION
SELECT ledgerbalance(cast (schemast."S_APPL" as character varying), 
PGMASTER."BANKACNO",' . $startDate . ',1,' . $code . ')as ledgerbalance, PGMASTER."AC_NO",PGMASTER."AC_NAME",OWNBRANCHMASTER."NAME",PGMASTER."AC_TYPE",SCHEMAST."S_NAME" from PGMASTER
INNER JOIN SCHEMAST ON PGMASTER."AC_TYPE" = SCHEMAST.id
INNER JOIN OWNBRANCHMASTER ON PGMASTER."BRANCH_CODE" = OWNBRANCHMASTER.id
where PGMASTER."BRANCH_CODE" = ' . $branch . ' AND PGMASTER."AC_TYPE" = ' . $scheme . ' AND PGMASTER."AC_NO" BETWEEN ' . $Rstartingacc . ' AND ' . $Rendingacc . '
UNION

SELECT ledgerbalance(cast (schemast."S_APPL" as character varying), 
SHMASTER."BANKACNO",' . $startDate . ',1,' . $code . ')as ledgerbalance, SHMASTER."AC_NO",SHMASTER."AC_NAME",OWNBRANCHMASTER."NAME",SHMASTER."AC_TYPE",SCHEMAST."S_NAME" from SHMASTER
INNER JOIN SCHEMAST ON SHMASTER."AC_TYPE" = SCHEMAST.id
INNER JOIN OWNBRANCHMASTER ON SHMASTER."BRANCH_CODE" = OWNBRANCHMASTER.id
where SHMASTER."BRANCH_CODE" = ' . $branch . ' AND SHMASTER."AC_TYPE" = ' . $scheme . ' AND SHMASTER."AC_NO" BETWEEN ' . $Rstartingacc . ' AND ' . $Rendingacc . '


UNION 

SELECT ledgerbalance(cast(980 as character varying), CAST(ACMASTER."AC_NO" AS character varying),' . $startDate . ',1,' . $code . ')as ledgerbalance, 
ACMASTER."AC_NO",ACMASTER."AC_NAME",' . $branch_name . ' "NAME","AC_TYPE",' . $GL . ' "S_NAME" from ACMASTER 
INNER JOIN SCHEMAST ON ACMASTER."AC_TYPE" = SCHEMAST.id 
where  ACMASTER."AC_TYPE" = ' . $scheme . ' 
AND ACMASTER."AC_NO" BETWEEN ' . $Rstartingacc . ' AND ' . $Rendingacc . ' 

order by "AC_NO"';

// echo $query;

$sql =  pg_query($conn, $query);
// echo  pg_num_rows($sql);
$i = 0;
$j = 0;

$LEDGER_TOT = 0;
$ledgeType = '';

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
} else {
    // print_r(pg_fetch_assoc($sql));
    while ($row = pg_fetch_assoc($sql)) {
        // print_r($row);
        $LEDGER_TOT = $LEDGER_TOT + $row['ledgerbalance'];

        if ((int)$row['ledgerbalance'] != 0) {
            if ($row['ledgerbalance'] < 0) {
                $ledgeType = 'Cr';
            } else {
                $ledgeType = 'Dr';
            }
            $tmp = [
                'srno' => $i + 1,
                'AC_NO' => $row['AC_NO'],
                'AC_NAME' => $row['AC_NAME'],
                'ledgerbalance' => sprintf("%.2f", (abs($row['ledgerbalance']))) . ' ' . $ledgeType,
                'AC_TYPE' => $row['AC_TYPE'],
                'S_NAME' => $row['S_NAME'],
                'NAME' => $row['NAME'],
                'ledgertot' => sprintf("%.2f", (abs($LEDGER_TOT))), 
                'receipt' => '-',
                'bankName' => $bankName,
                'startDate' => $startDate,
                'startDate_' => $startDate_,
                'sdate' => $sdate,
                'scheme' => $scheme,
                'Rstartingacc' => $Rstartingacc,
                'Rendingacc' => $Rendingacc,
                'branch' => $branch,
                'Rdio' => $Rdio,
                'Rdiosort' => $Rdiosort,

            ];
            $data[$i] = $tmp;
            $i++;
        }
    }
    ob_end_clean();
    $config = ['driver' => 'array', 'data' => $data];
    $report = new PHPJasperXML();
    $report->load_xml_file($filename)
        ->setDataSource($config)
        ->export('Pdf');
}
