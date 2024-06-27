<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/PigmyCollectionChart.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $Date = "'01/03/2022'";
$dateformate = "'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$date = $_GET['date'];
$scheme = $_GET['scheme'];
$branch = $_GET['branch'];
$Scheme_acc = $_GET['Scheme_acc'];
$ChartNo = $_GET['ChartNo'];

$bankName = str_replace("'", "", $bankName);
$date_ = str_replace("'", "", $date);
$scheme = str_replace("'", "", $scheme);
// $branchName = str_replace("'", "", $branchName);
// $Scheme_acc = str_replace("'", "", $Scheme_acc);

$query = ' SELECT pgmaster."AC_ACNOTYPE",pgmaster."AC_NAME",pgmaster."AC_CLOSEDT",pgmaster."AC_NO" ,
           pgmaster."AGENT_ACNO",dpmaster."AC_NAME" as AgentName,ownbranchmaster."NAME",pgmaster."AC_TYPE"
           from pgmaster 
           Inner Join dpmaster on 
           pgmaster."idmasterID" = dpmaster."idmasterID"
           Inner Join ownbranchmaster on
           pgmaster."BRANCH_CODE" = ownbranchmaster."id" 
           where cast(pgmaster."AC_OPDATE" as date) <= '.$date.'::date and
           pgmaster."AGENT_ACTYPE" = cast(dpmaster."AC_TYPE" as character varying) 
           and pgmaster."AC_CLOSEDT" is null 
           and pgmaster."AGENT_ACNO" = '.$Scheme_acc.'
           and pgmaster."BRANCH_CODE" = '.$branch.'   ';

           echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    
while($row = pg_fetch_assoc($sql)){

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AGENT_ACNO' => $row['AGENT_ACNO'],
        'AC_NAME'=> $row['AC_NAME'],
        'NAME'=> $row['NAME'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'agentname' => $row['agentname'],

        'bankName' => $bankName,
        'date_' => $date_,
        'scheme' =>$scheme,
        'branch' => $branch,
        'ChartNo' => $ChartNo,
        'Scheme_acc' => $Scheme_acc,
    ];
    $data[$i]=$tmp;
    $i++;
    
}
// ob_end_clean();

// $config = ['driver'=>'array','data'=>$data];

// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');

}
    
?>   

