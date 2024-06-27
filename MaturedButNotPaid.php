<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/MaturedButNotPaid.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];
$branch_code = $_GET['branch'];
$NAME = $_GET['NAME'];
$S_APPL = $_GET['S_APPL'];
$scheme = "'TD'";


$sdate1 = str_replace("'" , "" , $startdate);
$edate1 = str_replace("'" , "" , $enddate);





// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");


$query =  'SELECT dpmaster."AC_ACNOTYPE",dpmaster."AC_TYPE",dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_SCHMAMT",dpmaster."AC_OPDATE", 									
          dpmaster."AC_MATUAMT",dpmaster."AC_EXPDT",dpmaster."AC_INTRATE",dpmaster."AC_REF_RECEIPTNO",schemast."S_NAME",									
          Case WHEN "AC_ASON_DATE" IS NULL Then "AC_OPDATE" Else "AC_ASON_DATE" End  OP_ASON_DT, 									
          extract( year FROM age(CAST('.$startdate.' AS DATE), CAST( dpmaster."AC_EXPDT" AS DATE)))years,extract(  MONTH FROM age(CAST('.$startdate.' AS DATE), CAST( dpmaster."AC_EXPDT"AS DATE)))months,extract(  DAYS FROM age(CAST('.$startdate.' AS DATE), CAST( dpmaster."AC_EXPDT" AS DATE)))days
          FROM dpmaster 
          LEFT OUTER JOIN  schemast ON dpmaster."AC_TYPE" = schemast."id" AND dpmaster."AC_ACNOTYPE" = schemast."S_ACNOTYPE"  									
          WHERE ( dpmaster."AC_OPDATE" IS NULL  OR CAST(dpmaster."AC_OPDATE" AS DATE)  <= '.$startdate.' ::date) 									
          AND ( dpmaster."AC_CLOSEDT" IS NULL  OR CAST(dpmaster."AC_CLOSEDT" AS DATE)  >= '.$startdate.' ::date)								
          AND dpmaster."AC_ACNOTYPE" = '.$scheme.'AND dpmaster."BRANCH_CODE"='.$branch_code.' AND (dpmaster."AC_TYPE" ) ='.$S_APPL.'					
          AND CAST(dpmaster."AC_EXPDT" AS DATE) >= '.$startdate.'::date AND CAST(dpmaster."AC_EXPDT" AS DATE) <= '.$enddate.':: date									
          Order By dpmaster."AC_ACNOTYPE" , dpmaster."AC_TYPE" , dpmaster."AC_NO"';
          
echo $query;

$sql =  pg_query($conn,$query);

$i = 0;

$NETLEDGERBAL = 0;
$LEDGBALGTOT = 0;
$INTAMTGTOT = 0;
$RECEVINTGTOT = 0;
$PENALINTGTOT = 0;
$NETLEDGTOT = 0;

if (pg_num_rows($sql) == 0) {
  include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql))  
{
  
    $NETLEDGERBAL = $row['RECPAY_INT_AMOUNT']+ $row['PENAL_INT_AMOUNT'];
    $LEDGBALGTOT = $LEDGBALGTOT + $row['LEDGER_BALANCE'];
    $INTAMTGTOT = $INTAMTGTOT + $row['TRAN_AMOUNT']; 
    $RECEVINTGTOT = $RECEVINTGTOT + $row['RECPAY_INT_AMOUNT'];
    $PENALINTGTOT = $PENALINTGTOT + $row['PENAL_INT_AMOUNT'];
    $NETLEDGTOT = $NETLEDGTOT + $NETLEDGERBAL;

    $DEPOAMT=$DEPOAMT+ $row['AC_SCHMAMT'];
    $MATUREAMT=$MATUREAMT+ $row['AC_MATUAMT'];

  // grand-total
  if($type == ''){
    $type = $row['to_char'];
  }
  if($type == $row['to_char']){
    $SCHM_MTOTAL = $SCHM_MTOTAL + $row['TRAN_AMOUNT'];
  }else{
    $type = $row['to_char'];
    $SCHM_MTOTAL = 0;
    $SCHM_MTOTAL = $SCHM_MTOTAL + $row['TRAN_AMOUNT'];
  }


    $tmp=[
         'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NO' => $row['AC_NO'],
        'AC_MONTHS' => abs($row['months']),
        'S_APPL' => $row['S_APPL'],
        'AC_NAME'=> $row['AC_NAME'],
        'S_NAME'=> $row['S_NAME'],
        'AC_SCHMAMT' => sprintf("%.2f", ($row['AC_SCHMAMT'] + 0.0)),
        'AC_MATUAMT' => sprintf("%.2f", ($row['AC_MATUAMT'] + 0.0)),
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        'AC_DAYS' =>abs($row['days']),
        'AC_YEARS' =>abs($row['years']),
        'bankName' => $bankName,
        'startdate' => $sdate1,
        'enddate' => $edate1,
        'NAME'=>$NAME,
        'S_APPL' =>$S_APPL,
      
        'DEPOAMT'=> sprintf("%.2f", ($DEPOAMT + 0.0)),
        'MATUREAMT'=> sprintf("%.2f", ($MATUREAMT + 0.0)),
      
        
    ];
    $data[$i]=$tmp;
    $i++;
}
  
//  ob_end_clean();

//  $config = ['driver'=>'array','data'=>$data];

//  $report = new PHPJasperXML();
//  $report->load_xml_file($filename)    
//     ->setDataSource($config)
//      ->export('Pdf');
    
}
?>