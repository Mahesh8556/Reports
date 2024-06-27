<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/PigmyAgentwiseCollection.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$c = "'C'";
$entry = "'CH'";

$bankName = $_GET['bankName'];
$date = $_GET['date'];
$scheme = $_GET['scheme'];
$schemeAccountNo = $_GET['schemeAccountNo'];
$branchName = $_GET['branchName'];
$branch = $_GET['branch'];
$ChartNo = $_GET['ChartNo'];

$bankName = str_replace("'", "", $bankName);
$date_ = str_replace("'", "", $date);

$AG = "'AG'";
$CH = "'CH'";
$branchName = str_replace("'", "", $branchName);


$query = 'SELECT TMP.*,pgmaster."AC_NAME",DPMASTER."AC_NAME" ACNAME FROM (  
    select "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", "AGENT_ACNOTYPE", "AGENT_ACTYPE" , "AGENT_ACNO", "TRAN_DATE", "ENTRY_TYPE",                
    pigmychartmaster."TRAN_AMOUNT" , "AUTO_VOUCHER_NO"  from pigmychart
    inner join pigmychartmaster on pigmychartmaster."PIGMYCHARTID" = pigmychart.id
    where PIGMYCHART."AGENT_ACNOTYPE" = '.$AG.' And CAST(PIGMYCHART."AGENT_ACTYPE" as integer) ='.$scheme.' AND PIGMYCHART."ENTRY_TYPE" = '.$CH.'
    AND CAST(PIGMYCHART."TRAN_DATE" as date) = cast( '.$date.' as date) AND pigmychartmaster."CHART_NO"='.$ChartNo.' ANd PIGMYCHART."BRANCH_ID" = '.$branch.'
    AND TRIM(CAST("AGENT_ACNO" AS CHARACTER VARYING)) = '.$schemeAccountNo.'
                         
                         
    UNION ALL 
    
    SELECT "TRAN_ACNOTYPE", CAST("TRAN_ACTYPE" as integer), CAST("TRAN_ACNO" as bigint), "AGENT_ACNOTYPE" 
    , CAST("AGENT_ACTYPE" as integer) , cast("AGENT_ACNO" as bigint), "TRAN_DATE", "ENTRY_TYPE", cast("TRAN_AMOUNT" as float) , cast("AUTO_VOUCHER_NO" as float)
    FROM PIGMYTRAN WHERE PIGMYTRAN."AGENT_ACNOTYPE" = '.$AG.' And PIGMYTRAN."ENTRY_TYPE" = '.$CH.' 
	And CAST(PIGMYTRAN."TRAN_DATE" as date) = CAST( '.$date.' as date) 
	And CAST(PIGMYTRAN."AGENT_ACTYPE" as integer) ='.$scheme.' And CAST(PIGMYTRAN."CHART_NO" as integer) ='.$ChartNo.' AND PIGMYTRAN."BRANCH_CODE" = '.$branch.'
    AND TRIM(CAST(PIGMYTRAN."AGENT_ACNO" AS CHARACTER VARYING)) = '.$schemeAccountNo.'
    ) TMP 
    inner join pgmaster on pgmaster."BANKACNO" = CAST(TMP."TRAN_ACNO" AS character varying)
    INNER JOIN DPMASTER ON DPMASTER."BANKACNO" = CAST(TMP."AGENT_ACNO" AS character varying)
    WHERE "TRAN_AMOUNT" <> 0  ORDER BY "TRAN_ACNO" ASC';

    // echo $query;
          
    $sql =  pg_query($conn,$query);

    $i = 0;

    $GRAND_TOTAL = 0;

    if (pg_num_rows($sql) == 0) {
        include "errormsg.html";
    }else {
        while($row = pg_fetch_assoc($sql)){ 

            $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];

            $tmp=[
                'AGENT_ACNO' => $row['AGENT_ACNO'],
                'AgentName' => $row['acname'],
                'AC_NO' => $row['TRAN_ACNO'],
                'AGENT_ACTYPE' => $row['AGENT_ACTYPE'],
                'AC_NAME'=> $row['AC_NAME'],
                'depositeamt' => sprintf("%.2f", ($row['TRAN_AMOUNT'] + 0.0)),
                'AGENT_ACNOTYPE' => $row['AGENT_ACNOTYPE'],
                'grandtotal' => sprintf("%.2f", ($GRAND_TOTAL + 0.0)), 
                'NAME' => $branchName,
                'date_' => $date_,
                'scheme' => $scheme,
                'schemeAccountNo' => $schemeAccountNo,
                'branch' => $branch,
                'ChartNo' => $ChartNo,
                'bankName' => $bankName,
            ];
            $data[$i]=$tmp;
            // print_r($data[$i]);
            $i++;
        }

    ob_end_clean();

    $config = ['driver'=>'array','data'=>$data];
    $report = new PHPJasperXML();
    $report->load_xml_file($filename)    
        ->setDataSource($config)
        ->export('Pdf');

}
