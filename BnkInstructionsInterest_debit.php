<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/BnkInstructionsInterest_debit.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $status = "'%Suceess%'";
// $Fail = "'%Fail%'";
$startDate = "'03/03/2016'";
$endDate = "'28/03/2022'"; 
$dateformate ="'DD/MM/YYYY'";
// variables
// $startDate = $_GET['startDate'];
// $enddate = $_GET['endDate'];
// $dateformate = "'DD/MM/YYYY'";
//  $status =$_GET['status'];
 $PARTICULARS='%'.$_GET['status'].'%' ;
 $parti="'$PARTICULARS'";;
// $branch =$_GET['branch'];


$query = 'SELECT ownbranchmaster."NAME",intinstructionslog."INSTRUCTION_NO",intinstructionslog."TRAN_DATE",intinstructionslog."DR_ACNOTYPE",
            intinstructionslog."DR_ACTYPE",intinstructionslog."DR_AC_NO",intinstructionslog."DAILYTRAN_TRAN_NO",intinstructionslog."EXPECTED_EXECUTION_DATE",
            intinstructionslog."LAST_INT_DATE",intinstructionslog."TRAN_AMOUNT",intinstructionslog."CR_ACNOTYPE",intinstructionslog."CR_ACTYPE",
            intinstructionslog."CR_AC_NO",intinstructionslog."PARTICULARS",intinstruction."SI_FREQUENCY"
            from intinstruction
            inner join ownbranchmaster on intinstruction."BRANCH_CODE" =ownbranchmaster."id"
            inner join intinstructionslog on intinstruction."INSTRUCTION_NO"=intinstructionslog."INSTRUCTION_NO"
            where 
            intinstructionslog."PARTICULARS" LIKE '.$parti.'
            and
            cast(intinstructionslog."TRAN_DATE" as date) 
            between to_date('.$startDate.','.$dateformate.') and to_date('.$enddate.','.$dateformate.')
            order by  intinstruction."SI_FREQUENCY" asc';
            echo $query;

//get data from table if user wants Failed To Execute Instructions records
//   $query = 'SELECT ownbranchmaster."NAME",intinstructionslog."INSTRUCTION_NO",intinstructionslog."TRAN_DATE",intinstructionslog."DR_ACNOTYPE",
//   intinstructionslog."DR_ACTYPE",intinstructionslog."DR_AC_NO",intinstructionslog."DAILYTRAN_TRAN_NO",intinstructionslog."EXPECTED_EXECUTION_DATE",
//   intinstructionslog."LAST_INT_DATE",intinstructionslog."TRAN_AMOUNT",intinstructionslog."CR_ACNOTYPE",intinstructionslog."CR_ACTYPE",
//   intinstructionslog."CR_AC_NO",intinstructionslog."PARTICULARS",intinstruction."SI_FREQUENCY"
//   from intinstructionslog,ownbranchmaster,intinstruction
//   where 
//   intinstructionslog."PARTICULARS" LIKE '.$Fail.' and
//   cast("TRAN_DATE" as date) 
//   between to_date('.$startDate.','.$dateformate.') and to_date('.$endDate.','.$dateformate.') 
//   order by  intinstruction."SI_FREQUENCY" asc';

  
//get data from table if user wants all Instructions records
// $query = 'SELECT ownbranchmaster."NAME",intinstructionslog."INSTRUCTION_NO",intinstructionslog."TRAN_DATE",intinstructionslog."DR_ACNOTYPE",
// intinstructionslog."DR_ACTYPE",intinstructionslog."DR_AC_NO",intinstructionslog."DAILYTRAN_TRAN_NO",intinstructionslog."EXPECTED_EXECUTION_DATE",
// intinstructionslog."LAST_INT_DATE",intinstructionslog."TRAN_AMOUNT",intinstructionslog."CR_ACNOTYPE",intinstructionslog."CR_ACTYPE",
// intinstructionslog."CR_AC_NO",intinstructionslog."PARTICULARS",intinstruction."SI_FREQUENCY"
// from intinstructionslog,ownbranchmaster,intinstruction 
// order by  intinstruction."SI_FREQUENCY" asc';

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0 ;

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];

    if($type == ''){
        $type = $row['DR_ACNOTYPE'];
    }
    if($type == $row['DR_ACNOTYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['TRAN_AMOUNT'];
    }else{
        $type = $row['DR_ACNOTYPE'];
        $GROUP_TOTAL = 0 ;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['TRAN_AMOUNT'];
    }

    $tmp=[
         'NAME'=> $row['NAME'],
        'INSTRUCTION_NO'=> $row['INSTRUCTION_NO'],
        'TRAN_DATE' => $row['TRAN_DATE'],
        'DR_ACNOTYPE' => $row['DR_ACNOTYPE'],
        'DR_ACTYPE' => $row['DR_ACTYPE'],
        'DR_AC_NO'=> $row['DR_AC_NO'],
        'DAILYTRAN_TRAN_NO'=> $row['DAILYTRAN_TRAN_NO'],
        'EXPECTED_EXECUTION_DATE' => $row['EXPECTED_EXECUTION_DATE'],
        'LAST_INT_DATE' => $row['LAST_INT_DATE'],
        'TRAN_AMOUNT' => $row['TRAN_AMOUNT'],
        'CR_ACNOTYPE'=> $row['CR_ACNOTYPE'],
        'CR_ACTYPE'=> $row['CR_ACTYPE'],
        'CR_AC_NO' => $row['CR_AC_NO'],
        'SI_FREQUENCY' => $row['SI_FREQUENCY'],
        'grandamt' =>  $GRAND_TOTAL ,
        'schemamt' => $GROUP_TOTAL,
        'START_DATE' => $startDate,
        'END_DATE' => $enddate,
        'PARTICULARS' => $row['PARTICULARS'],
        // 'NAME'=>$branch,
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
    

