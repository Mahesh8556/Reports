<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/OpenDepositeReport.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$scheme = $_GET['scheme'];
$Branch = $_GET['Branch'];
$ACCLOSE = $_GET['ACCLOSE'];
echo $ACCLOSE;
echo '<br>';

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
// $branchName = str_replace("'", "", $branchName);

$dateformate = "'DD/MM/YYYY'"; 

// $query1 = 'SELECT dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_OPDATE",dpmaster."AC_ASON_DATE",
//           dpmaster."AC_SCHMAMT",dpmaster."AC_MONTHS",dpmaster."AC_DAYS",dpmaster."AC_INTRATE",
//           dpmaster."AC_EXPDT",dpmaster."AC_MATUAMT",dpmaster."AC_ACNOTYPE",
//           ownbranchmaster."NAME",customeraddress."AC_ADDR",schemast."S_NAME"
//           From dpmaster
//           Inner Join ownbranchmaster on
//           dpmaster."BRANCH_CODE" = ownbranchmaster."id"
//           Inner Join customeraddress on 
//           dpmaster."idmasterID" = customeraddress."idmasterID" 
//           Inner Join schemast on 
//           dpmaster."AC_TYPE" = schemast."id"
//           where cast("AC_OPDATE" as date) 
//           between cast('.$stdate.' as date) and cast('.$etdate.' as date)
//           and dpmaster."BRANCH_CODE" = '.$Branch.'
//           and dpmaster."AC_TYPE" = '.$scheme.' ';

    $query = 'SELECT dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_OPDATE",dpmaster."AC_ASON_DATE", dpmaster."AC_SCHMAMT",
    dpmaster."AC_MONTHS",dpmaster."AC_DAYS",dpmaster."AC_INTRATE", dpmaster."AC_EXPDT",dpmaster."AC_MATUAMT",
    dpmaster."AC_ACNOTYPE", ownbranchmaster."NAME",customeraddress."AC_ADDR",schemast."S_NAME",dpmaster."AC_CLOSEDT" From dpmaster 
    Inner Join ownbranchmaster on dpmaster."BRANCH_CODE" = ownbranchmaster."id" 
    left Join customeraddress on dpmaster."idmasterID" = customeraddress."idmasterID" 
    Inner Join schemast on dpmaster."AC_TYPE" = schemast."id" where cast("AC_OPDATE" as date) 
    between cast('.$stdate.' as date) and cast('.$etdate.' as date) and dpmaster."BRANCH_CODE" = '.$Branch.'
    and dpmaster."AC_TYPE" = '.$scheme.' AND dpmaster."AC_CLOSEDT" IS NOT NULL
    
    UNION

    SELECT lnmaster."AC_NO",lnmaster."AC_NAME",
    lnmaster."AC_OPDATE",null "AC_ASON_DATE", cast(0 as character varying) "AC_SCHMAMT", lnmaster."AC_MONTHS",cast(0 as character varying) "AC_DAYS",
    lnmaster."AC_INTRATE", null "AC_EXPDT",cast(0 as character varying) "AC_MATUAMT", lnmaster."AC_ACNOTYPE", ownbranchmaster."NAME",
    customeraddress."AC_ADDR",schemast."S_NAME",lnmaster."AC_CLOSEDT" From lnmaster 
    Inner Join ownbranchmaster on lnmaster."BRANCH_CODE" = ownbranchmaster."id" 
    left Join customeraddress on lnmaster."idmasterID" = customeraddress."idmasterID" 
    Inner Join schemast on lnmaster."AC_TYPE" = schemast."id" where cast("AC_OPDATE" as date) 
    between cast('.$stdate.' as date) and cast('.$etdate.' as date) and lnmaster."BRANCH_CODE" = '.$Branch.'
    and lnmaster."AC_TYPE" = '.$scheme.' AND lnmaster."AC_CLOSEDT" IS NOT NULL


    UNION

    SELECT pgmaster."AC_NO",pgmaster."AC_NAME",pgmaster."AC_OPDATE",pgmaster."AC_ASON_DATE", pgmaster."AC_SCHMAMT", 
    pgmaster."AC_MONTHS",cast(0 as character varying) "AC_DAYS",cast(0 as character varying) "AC_INTRATE", pgmaster."AC_EXPDT",pgmaster."AC_MATUAMT", 
    pgmaster."AC_ACNOTYPE", ownbranchmaster."NAME",customeraddress."AC_ADDR",schemast."S_NAME",pgmaster."AC_CLOSEDT"  From pgmaster 
    Inner Join ownbranchmaster on pgmaster."BRANCH_CODE" = ownbranchmaster."id" 
    left Join customeraddress on pgmaster."idmasterID" = customeraddress."idmasterID" 
    Inner Join schemast on pgmaster."AC_TYPE" = schemast."id" where cast("AC_OPDATE" as date) 
    between cast('.$stdate.' as date) and cast('.$etdate.' as date) and pgmaster."BRANCH_CODE" = '.$Branch.'
    and pgmaster."AC_TYPE" = '.$scheme.' AND pgmaster."AC_CLOSEDT" IS NOT NULL

    UNION

    SELECT shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_OPDATE",null "AC_ASON_DATE", cast(0 as character varying) "AC_SCHMAMT", 
    cast(0 as character varying) "AC_MONTHS",cast(0 as character varying) "AC_DAYS",cast(0 as character varying) "AC_INTRATE", shmaster."AC_EXPDT",cast(0 as character varying) "AC_MATUAMT", 
    shmaster."AC_ACNOTYPE", ownbranchmaster."NAME",customeraddress."AC_ADDR",schemast."S_NAME",shmaster."AC_CLOSEDT"  From shmaster 
    Inner Join ownbranchmaster on shmaster."BRANCH_CODE" = ownbranchmaster."id" 
    left Join customeraddress on shmaster."idmasterID" = customeraddress."idmasterID" 
    Inner Join schemast on shmaster."AC_TYPE" = schemast."id" where cast("AC_OPDATE" as date) 
    between cast('.$stdate.' as date) and cast('.$etdate.' as date) and shmaster."BRANCH_CODE" = '.$Branch.'
    and shmaster."AC_TYPE" = '.$scheme.' AND shmaster."AC_CLOSEDT" IS NOT NULL';


          
$sql =  pg_query($conn,$query);
         
$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_ADDR' => $row['AC_ADDR'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_ASON_DATE' => $row['AC_ASON_DATE'],
        'AC_SCHMAMT' => $row['AC_SCHMAMT'],
        'AC_MONTHS'=> $row['AC_MONTHS'],
        'AC_DAYS'=> $row['AC_DAYS'],
        'AC_INTRATE'=> $row['AC_INTRATE'],
        'AC_NNAME' => $row['AC_NNAME'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_MATUAMT'=> $row['AC_MATUAMT'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'NAME'=> $row['NAME'],
        'S_NAME'=> $row['S_NAME'],
        
        'stdate' => $stdate,
        'etdate' => $etdate,
        // 'ACOPEN' => $ACOPEN,
        // 'ACCLOSE' => $ACCLOSE,
        // 'GROUP_BY' => $GROUP_BY,
        'scheme' => $scheme,
        'stdate_' => $stdate_, 
        'etdate_' => $etdate_,
        'Branch' => $Branch,
        'bankName' => $bankName,
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
