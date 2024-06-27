<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/DepositAmountWiseBalancelist.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$date = "'08/04/2016'";
$sdate = "'01/04/2016'";
$samount = "'10000'";
$eamount = "'30000'";
 
// variables
// $startDate = $_GET['startDate'];
// $endDate = $_GET['endDate'];
// $dateformate = "'DD/MM/YYYY'";


$query = ' SELECT 
           ledgerbalance(cast (schemast."S_APPL" as character varying),
           DPMASTER."BANKACNO",'.$date.',0,1)as ledgerbalance,
           DPMASTER."AC_NO",DPMASTER."AC_NAME",DPMASTER."AC_OPDATE",DPMASTER."AC_EXPDT",
           DPMASTER."AC_SCHMAMT",DPMASTER."AC_INTRATE",DPMASTER."AC_MEMBTYPE",OWNBRANCHMASTER."NAME"
           FROM DPMASTER
           INNER JOIN SCHEMAST ON 
           DPMASTER."AC_TYPE" = SCHEMAST."id"
           INNER JOIN OWNBRANCHMASTER ON
           DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
           WHERE cast(DPMASTER."AC_OPDATE" AS date) = '.$sdate.'::date 
           AND DPMASTER."AC_SCHMAMT" BETWEEN '.$samount.' AND '.$eamount.' ';
          
$sql =  pg_query($conn,$query);

$i = 0;

$DEPOSIT_AMT = 0;
$LEDGER_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $DEPOSIT_AMT = $DEPOSIT_AMT + $row['AC_SCHMAMT'];
    $LEDGER_TOTAL = $LEDGER_TOTAL + $row['ledgerbalance'];
   
    // if($type == ''){
    //     $type = $row['AC_ACNOTYPE'];
    // }
    // if($type == $row['AC_ACNOTYPE']){
    //     $SCHM_RANG = $SCHM_RANG + $row['AC_SCHMAMT'];
    // }else{
    //     $type = $row['AC_ACNOTYPE'];
    //     $SCHM_RANG = 0;
    //     $SCHM_RANG = $SCHM_RANG + $row['AC_SCHMAMT'];
    // }

    $tmp=[
        'ledgerbalance' => $row['ledgerbalance'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'AC_OPDATE'=> $row['AC_OPDATE'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'AC_INTRATE'=> $row['AC_INTRATE'],
        'AC_MEMBTYPE' => $row['AC_MEMBTYPE'],
        'NAME' => $row['NAME'],
        'deposit_amt' => $DEPOSIT_AMT,
        'ledger_total' => $LEDGER_TOTAL,

        'bankName' => $bankName,
        'date' => $date,
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

