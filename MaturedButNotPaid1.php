<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/MaturedButNotPaid.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];
$NAME = $_GET['NAME'];
$S_APPL = $_GET['S_APPL'];
$scheme = "'TD'";


$age=explode('/', '$age');
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
$query =  'SELECT dpmaster."AC_ACNOTYPE",dpmaster."AC_TYPE",dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_SCHMAMT",dpmaster."AC_OPDATE", 									
          dpmaster."AC_MATUAMT",dpmaster."AC_EXPDT",dpmaster."AC_INTRATE",dpmaster."AC_REF_RECEIPTNO",schemast."S_NAME",									
          Case WHEN "AC_ASON_DATE" IS NULL Then "AC_OPDATE" Else "AC_ASON_DATE" End  OP_ASON_DT, 									
          Age(CAST('.$startdate.' AS DATE), CAST(dpmaster."AC_EXPDT" AS DATE)) AS  "AC_MONTHS"
          FROM dpmaster 
          LEFT OUTER JOIN  schemast ON dpmaster."AC_TYPE" = schemast."id" AND dpmaster."AC_ACNOTYPE" = schemast."S_ACNOTYPE"  									
          WHERE ( dpmaster."AC_OPDATE" IS NULL  OR CAST(dpmaster."AC_OPDATE" AS DATE)  <= '.$startdate.' ::date) 									
          AND ( dpmaster."AC_CLOSEDT" IS NULL  OR CAST(dpmaster."AC_CLOSEDT" AS DATE)  >= '.$startdate.' ::date)								
          AND dpmaster."AC_ACNOTYPE" = '.$scheme.' AND (dpmaster."AC_TYPE" ) ='.$S_APPL.'					
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
    $age=explode('/', '$age');
    $NETLEDGERBAL = $row['RECPAY_INT_AMOUNT']+ $row['PENAL_INT_AMOUNT'];
    $LEDGBALGTOT = $LEDGBALGTOT + $row['LEDGER_BALANCE'];
    $INTAMTGTOT = $INTAMTGTOT + $row['TRAN_AMOUNT']; 
    $RECEVINTGTOT = $RECEVINTGTOT + $row['RECPAY_INT_AMOUNT'];
    $PENALINTGTOT = $PENALINTGTOT + $row['PENAL_INT_AMOUNT'];
    $NETLEDGTOT = $NETLEDGTOT + $NETLEDGERBAL;

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
        'AC_MONTHS' => $age[1],
        'S_APPL' => $row['S_APPL'],
        'AC_NAME'=> $row['AC_NAME'],
        'S_NAME'=> $row['S_NAME'],
        'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'AC_MATUAMT' => $row['AC_MATUAMT'],
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        'AC_DAYS' => $age[2],
        'AC_YEARS' =>$age[0],
        'bankName' => $bankName,
        'startdate' => $startdate,
        'NAME'=>$NAME,
        'enddate' => $enddate,
        'S_APPL' =>$S_APPL,
      
       
      
        
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

