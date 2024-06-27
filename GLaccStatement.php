<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
ini_set('MAX_EXECUTION_TIME', 3600);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/GLaccStatement.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$C = "'C'";
$D = "'D'";
// $GL = "'GL'";
$int = "'0'";
$dateformate = "'DD/MM/YYYY'";
$v = "'980'";
$id = "'3'";

 $bankName = $_GET['bankName']; 
 $startdate = $_GET['startdate']; 
 $enddate = $_GET['enddate'];
 $sdate = $_GET['sdate'];
 $branch = $_GET['branch'];
//  $branchC = $_GET['branchC'];

 
 $branchName = $_GET['branchName'];
 $startingcode = $_GET['startingcode'];
 $endingcode = $_GET['endingcode'];
 $scheme = $_GET['scheme'];
 $MonthwiseSummary =$_GET['MonthwiseSummary'];
 $AC_NO = $_GET['AC_NO'];


//  echo $sdate;

 $bankName = str_replace("'", "", $bankName);
//  echo $startdate;
$startdate_ = str_replace("'", "", $startdate);
$enddate_ = str_replace("'", "", $enddate);
$scheme = str_replace("'", "", $scheme);





$query='select ledgerbalance(cast (schemast."S_APPL" as character varying),cast(acmaster."AC_NO" as character varying),'.$sdate.',0,'.$branch.',1)as ledger_balance, 
coalesce(case when "TRAN_DRCR" ='.$C.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) as Credit , 
coalesce(case when "TRAN_DRCR" ='.$D.' Then (-1) * cast("TRAN_AMOUNT" as float) else 0 end, 0) as Debit ,
accotran."TRAN_DATE",ownbranchmaster."NAME" ,acmaster."AC_NAME", accotran."TRAN_ACNOTYPE", 
acmaster."AC_NO", accotran."NARRATION",acmaster."AC_TYPE" from acmaster
inner join 
accotran on accotran."TRAN_ACNO" = acmaster."AC_NO"
inner join schemast on cast(accotran."TRAN_ACTYPE" as integer) = schemast."id" 
inner join ownbranchmaster on accotran."BRANCH_CODE" =ownbranchmaster."id" 
where acmaster."AC_NO" ='.$AC_NO.' AND cast(accotran."TRAN_DATE" as date) between CAST('.$startdate.' as date)
and CAST('.$enddate.' as date) and accotran."BRANCH_CODE" = '.$branch.'
order by acmaster."AC_NO",CAST(accotran."TRAN_DATE" as date) ASC';



        //  echo $query;

 
$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
$stype = '';

$crtotal=0;
$drtotal=0;
$GRtotal=0;

while($row = pg_fetch_assoc($sql)){
    if($i==0){
        $tdata= ($row['ledger_balance']);
    }

    if($row['ledger_balance'] < 0)
    {
        $tdata=$tdata + $row['credit'] + $row['debit'];
    }
    else
    {
        $tdata=$tdata - $row['debit'] - $row['credit'];

    }
// echo $row['ledger_balance'] .'ledger .<br>';
//     echo $row['credit'] .' credit<br>';
//     echo $row['debit'].'debit<br>';
    
 
    if($tdata > 0){
        $stype = 'Dr';
    }else{
        $stype = 'Cr';
    }
    echo $tdata.'tdata<br>' .$stype .'<br>';

    $totalbal = sprintf("%.2f", (abs($row['credit'] + $row['debit']) + 0.0)).' '.$stype;
    $crtotal = $crtotal + $row['credit'];
    $drtotal = $drtotal + $row['debit'];
    $GRtotal = $GRtotal + $totalbal;

    $GRtotal=$row['crtotal'] - $row['drtotal'] + $row['ledger_balance'];

    $tmp=[

        'TRAN_DATE'=> $row['TRAN_DATE'],
        'NAME'=> $row['NAME'],
        $bal=$row['ledger_balance']<0 ? 'Cr':'Dr',
        'opening_blance'=> sprintf("%.2f", (abs($row['ledger_balance']) + 0.0)) .' '. $bal,

        'NARRATION' => $row['NARRATION'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'AC_TYPE' => $row['AC_TYPE'],
        'Credit_Amount'=> sprintf("%.2f", (abs($row['credit']) + 0.0)),
        'Debit_Amount' => sprintf("%.2f", (abs($row['debit']) + 0.0)),
        'totalbal' =>   sprintf("%.2f", (abs($tdata) + 0.0)).' '.$stype, 
        'startdate'=> $startdate_,
        'sdate' => $sdate,
        'enddate'=> $enddate_,
        'Branch' => $branchName,
        'startingcode'=> $startingcode,
        'endingcode'=> $endingcode,
        'scheme'=> $scheme,
        'MonthwiseSummary'=> $scheme,
        'bankName' => $bankName,

        'crtotal' => sprintf("%.2f", (abs($crtotal) + 0.0)),
        'drtotal' => sprintf("%.2f", (abs($drtotal) + 0.0)),
        'GRtotal' => sprintf("%.2f", (abs($tdata) + 0.0)).' '.$stype,

        

         

    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
// print_r($data);
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

} 
?>
    

