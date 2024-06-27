<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/AgentwsPigmyBalBook.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

$date = $_GET['date'];
$scheme = $_GET['scheme'];
$branch = $_GET['branch'];
$schemeAccountNo = $_GET['schemeAccountNo'];
$bankName = $_GET['bankName'];
// $dateFrom = $_GET['dateFrom'];
$edate = $_GET['edate'];

// echo $edate;

$bankName = str_replace("'", "", $bankName);

// $sdate = date('d/m/Y', strtotime('last day of previous month'));
// $date = date('d-m-Y', strtotime('last day of '.date('M', strtotime(request()->query_date_to))));
// $date = date("d/m/Y", strtotime("last day of previous month")); 
// fetch from db, user input etc   
// $sdate = '';
// if (strtotime($sdate) <= $date) {
//     echo $sdate = date("d-m-Y", strtotime("last day of previous month"));
// } else {
//     echo 0;
// }

// $date_ = str_replace("'", "", $date);
// $date="02/10/2020";
// $branchName = str_replace("'", "", $branchName);


$c = "'C'";
$d = "'D'";
$o="'0'";
$dateformat = "'DD/MM/YYYY'";

$query = 'SELECT 
          ledgerbalance(cast (schemast."S_APPL" as character varying),
          pgmaster."BANKACNO",'.$edate.',0,1)as ledgerbal,
          coalesce(case when pigmytran."TRAN_DRCR"='.$c.' Then 
          cast(pigmytran."TRAN_AMOUNT" as integer) else 0 end, 0)+
          cast(pigmychartmaster."TRAN_AMOUNT" as integer) as cramt,
          coalesce(case when pigmytran."TRAN_DRCR"='.$d.' Then 
          cast(pigmytran."TRAN_AMOUNT" as integer) else 0 end, 0)+
          cast(pigmychartmaster."TRAN_AMOUNT" as integer) as dramt,
          pgmaster."AC_ACNOTYPE",pgmaster."AC_NAME",pgmaster."AC_NO",
          pgmaster."AGENT_ACNO", dpmaster."AC_NAME" as AgentName,ownbranchmaster."NAME"
          FROM pgmaster
          Inner Join dpmaster on 
          pgmaster."idmasterID" = dpmaster."idmasterID"
          Inner Join pigmytran on 
          pgmaster."BANKACNO" = pigmytran."TRAN_ACNO"
          Inner Join pigmychartmaster on
          cast(pgmaster."BANKACNO" as bigint) = pigmychartmaster."TRAN_ACNO"
          Inner Join ownbranchmaster on 
          pgmaster."BRANCH_CODE" = ownbranchmaster."id"
          Inner Join schemast on 
          pgmaster."AC_TYPE" = schemast."id"
          where cast(pgmaster."AGENT_ACNO" as bigint)= dpmaster."AC_NO"
          And cast(dpmaster."AC_NO" as integer) = '.$schemeAccountNo.'
          and cast(dpmaster."AC_TYPE" as integer) = '.$scheme.'
          and pgmaster."AC_CLOSEDT" is null 
          and pgmaster."BRANCH_CODE" = '.$branch.'
          and cast(pgmaster."AC_OPDATE" as date) 
          between cast('.$edate.' as date) and cast('.$date.' as date)
          Order By pgmaster."AGENT_ACNO" ';
          
        //   echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_DTOTAL = 0;
$GRAND_CTOTAL = 0;
$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $netbalance = '';
if ($row['ledgerbal'] > 0)
    {$netbalance = $row['ledgerbal'] + $row['dramt'] - $row['cramt']; }
if ($row['ledgerbal'] == 0)
    {$netbalance = $row['ledgerbal'];}
if ($row['ledgerbal'] < 0)
    {$netbalance = $row['ledgerbal'] + $row['cramt'] - $row['dramt'];}


    $GRAND_DTOTAL = $GRAND_DTOTAL + $row['dramt'];
    $GRAND_CTOTAL = $GRAND_CTOTAL + $row['cramt'];
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['ledgerbal'] ;
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $netbalance ;

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['dramt'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $GROUP_TOTAL = 0;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['dramt'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $SCHEME_TOTAL = $SCHEME_TOTAL + $row['cramt'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $SCHEME_TOTAL = 0;
        $SCHEME_TOTAL = $SCHEME_TOTAL + $row['cramt'];
    }
 
    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $OPENSCHM_TOTAL = $OPENSCHM_TOTAL + $row['ledgerbal'] ;
    }else{
        $type = $row['AC_ACNOTYPE'];
        $OPENSCHM_TOTAL = 0;
        $OPENSCHM_TOTAL = $OPENSCHM_TOTAL + $row['ledgerbal'] ;
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $NETSCHM_TOTAL = $NETSCHM_TOTAL + $netbalance ;
    }else{
        $type = $row['AC_ACNOTYPE'];
        $NETSCHM_TOTAL = 0;
        $NETSCHM_TOTAL = $NETSCHM_TOTAL + $netbalance ;
    }

    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $OPENAGNT_TOTAL = $OPENAGNT_TOTAL + $row['ledgerbal'] ;
    }else{
        $type = $row['agentname'];
        $OPENAGNT_TOTAL = 0;
        $OPENAGNT_TOTAL = $OPENAGNT_TOTAL + $row['ledgerbal'] ;
    }

    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $OPENAGNT_DTOTAL = $OPENAGNT_DTOTAL + $row['dramt'] ;
    }else{
        $type = $row['agentname'];
        $OPENAGNT_DTOTAL = 0;
        $OPENAGNT_DTOTAL = $OPENAGNT_DTOTAL + $row['dramt'] ;
    }

    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $OPENAGNT_CTOTAL = $OPENAGNT_CTOTAL + $row['cramt'] ;
    }else{
        $type = $row['agentname'];
        $OPENAGNT_CTOTAL = 0;
        $OPENAGNT_CTOTAL = $OPENAGNT_CTOTAL + $row['cramt'] ;
    }

    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $NETAGNT_TOTAL = $NETAGNT_TOTAL + $netbalance ;
    }else{
        $type = $row['agentname'];
        $NETAGNT_TOTAL = 0;
        $NETAGNT_TOTAL = $NETAGNT_TOTAL + $netbalance ;
    }
    $tmp=[
        'AGENT_ACNO' => $row['AGENT_ACNO'],
        'AGENT_ACTYPE' => $row['AGENT_ACTYPE'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'ledgerbal'=> $row['ledgerbal'],
        'NetBalance'=> $netbalance,
        'dramt'=> $row['dramt'],
        'cramt'=> $row['cramt'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'NAME'=> $row['NAME'],
        'agentname'=> $row['agentname'],
        'granddrtotal'=> $GRAND_DTOTAL,
        'grandcrtotal'=> $GRAND_CTOTAL,
        'schmdrtotal'=> $GROUP_TOTAL,
        'schmcrtotal'=> $SCHEME_TOTAL,
        'grandopntot'=> $GRAND_TOTAL1,
        'grandnettot'=> $GRAND_TOTAL2,
        'schmopentot'=> $OPENSCHM_TOTAL,
        'schmnettot'=> $NETSCHM_TOTAL,
        'agntopntot'=> $OPENAGNT_TOTAL,
        'agntdrtot'=> $OPENAGNT_DTOTAL,
        'agntcrtot'=> $OPENAGNT_CTOTAL,
        'agntnettot'=> $NETAGNT_TOTAL,

        'date' => $date,
        'edate' => $edate,
        'scheme' => $scheme,
        'branch' => $branch,
        'schemeAccountNo' => $schemeAccountNo,
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
?> 

    

