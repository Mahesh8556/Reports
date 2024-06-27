<?php
include "main.php";
ob_start(); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/MemberIncrDecrListSummery.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
//connect pgAdmin database connection 
$conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
if($conn){
    echo 'success';

}else{
    echo 'fail';
}
//get data from enquiry tableand
$startDate = "'10/01/2005' ";
$endDate = "'31/12/2006'";
$dateformate = "'DD/MM/YYYY'";
$D = "'D'";
$year = "'year'";
$month = "'MONTH'";

$query = ' SELECT date_part('.$year.', cast(shmaster."AC_OPDATE" as date)),
           to_char(cast(shmaster."AC_OPDATE" as date), '.$month.'),
           shmaster."BANKACNO",sharetran."TRAN_AMOUNT",
           shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_TYPE",
           shmaster."AC_OPDATE",shmaster."AC_CLOSED"
           from 
           (
           Select shmaster."BANKACNO",
           shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_TYPE",
           shmaster."AC_OPDATE",shmaster."AC_CLOSED",sharetran."TRAN_AMOUNT"
           From shmaster
           Inner Join sharetran on 
           shmaster."BANKACNO" = sharetran."TRAN_ACNO" 
           where cast("AC_OPDATE" as date) 
           between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.')
           )shmaster 
           Inner Join sharetran on 
           shmaster."BANKACNO" = sharetran."TRAN_ACNO" 
           Group By shmaster."AC_NO",shmaster."AC_NAME",shmaster."AC_TYPE",shmaster."BANKACNO",
           shmaster."AC_OPDATE",shmaster."AC_CLOSED",sharetran."TRAN_AMOUNT",shmaster."AC_OPDATE" ';
 

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

while($row = pg_fetch_assoc($sql))  
{
  // grand-total
  $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];

  if($type == ''){
    $type = $row['date_part'];
  }
  if($type == $row['date_part']){
    $SCHM_YTOTAL = $SCHM_YTOTAL + $row['TRAN_AMOUNT'];
  }else{
    $type = $row['date_part'];
    $SCHM_YTOTAL = 0;
    $SCHM_YTOTAL = $SCHM_YTOTAL + $row['TRAN_AMOUNT'];
  }

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
        'AC_NO' => $row['AC_NO'],
        'AC_TYPE' => $row['AC_TYPE'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_OPDATE'=> $row['AC_OPDATE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'AC_CLOSED' => $row['AC_CLOSED'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'date_part' => $row['date_part'],
        'to_char' => $row['to_char'],
        'field1' => $row['field1'],
        'grandtotamt' => $GRAND_TOTAL,
        'totschmyear' => $SCHM_YTOTAL,
        'totschmmonth' => $SCHM_MTOTAL,

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
    
 ?>

