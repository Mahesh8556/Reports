<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/InsuranceRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$enddate = $_GET['enddate'];
$BRANCH_CODE =$_GET['BRANCH_CODE'];
$ACOPEN = $_GET['ACOPEN'];
$branchName = $_GET['branchName'];

$AC_TYPE = $_GET['AC_TYPE'];

$bankName = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
$enddate_ = str_replace("'", "", $enddate);
$branchName = str_replace("'", "", $branchName);

// $query = ' SELECT firepolicy."AC_NO", firepolicy."SUBMISSION_DATE", firepolicy."POLICY_NO",
//            firepolicy."POLICY_AMT", firepolicy."POLICY_DUE_DATE", firepolicy."PREMIUM",
//            firepolicy."PREMIUM_DUE_DATE", lnmaster."AC_NAME", ownbranchmaster."NAME",
//            firepolicy."AC_ACNOTYPE",firepolicy."AC_TYPE"
//            from lnmaster
//            Inner Join firepolicy on
//            lnmaster."id" = firepolicy."id"
//            Inner Join ownbranchmaster on
//            lnmaster."BRANCH_CODE" = ownbranchmaster."id"
//            where
//            cast("SUBMISSION_DATE" as date) 
//            between CAST('.$startDate.' as date) and CAST('.$enddate.' as date) ';
          


$query='SELECT  SCHEMAST."S_APPL",
SCHEMAST."S_NAME", 
 firepolicy."AC_NO",
firepolicy."SUBMISSION_DATE", 
firepolicy."POLICY_NO",
firepolicy."POLICY_AMT", 
firepolicy."POLICY_DUE_DATE", 
firepolicy."PREMIUM", 
firepolicy."PREMIUM_DUE_DATE", 
lnmaster."AC_NAME",
lnmaster."AC_TYPE",
lnmaster."BANKACNO",
ownbranchmaster."NAME",
firepolicy."AC_ACNOTYPE",
firepolicy."AC_TYPE" ,
GOVT."PAIDUP_AMT" AS "PAID_INSTALLMENT"
from lnmaster Inner Join firepolicy on lnmaster."BANKACNO"= firepolicy."AC_NO" 
INNER JOIN SCHEMAST  ON LNMASTER."AC_TYPE"=SCHEMAST."id"
LEFT JOIN ( select "AC_ACNOTYPE","AC_TYPE","AC_NO",COALESCE("PAIDUP_AMT",0) AS "PAIDUP_AMT"
from GOVTSECULIC) GOVT ON lnmaster."BANKACNO"=GOVT."AC_NO"
Inner Join ownbranchmaster on lnmaster."BRANCH_CODE" = ownbranchmaster."id" 
where 	cast("SUBMISSION_DATE" as date) between CAST('.$startDate.' as date) and CAST('.$enddate.' as date) AND lnmaster."AC_TYPE"='.$AC_TYPE.' ';
	


        //   echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['POLICY_AMT'];

    $tmp=[
        // 'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_TYPE'=> $row['S_APPL'].' '.$row['S_NAME'],
        'NAME'=> $row['NAME'],
        'SUBMISSION_DATE' => $row['SUBMISSION_DATE'],
        'POLICY_NO' => $row['POLICY_NO'],
        'POLICY_AMT'=> $row['POLICY_AMT'],
        'PAID_INSTALLMENT'=> $row['PAID_INSTALLMENT'],
        'POLICY_DUE_DATE'=> $row['POLICY_DUE_DATE'],
        'PREMIUM' => $row['PREMIUM'],
        'PREMIUM_DUE_DATE' => $row['PREMIUM_DUE_DATE'],
        'totalpolicyamt'=>sprintf("%.2f", (abs($GRAND_TOTAL) + 0.0)),

        'bankName' => $bankName,
        'BRANCH_CODE' => $BRANCH_CODE,
        'ACOPEN' => $ACOPEN,
        'enddate_' => $enddate_,
        'startDate_' => $startDate_,
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

}
?>  