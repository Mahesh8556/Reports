<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/fdpayment.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$ac_acnotype="'TD'";
$TRAN_STATUS="'1'";
$branchName = $_GET['branchName'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$bankName =$_GET['bankName'];
$date=$_GET['date'];
$tran_date =$_GET['date'];


// $branch = $_GET['branch'];

$bankName = str_replace("'", "", $bankName);
// $stdate_ = str_replace(''', '', $stdate);
$date = str_replace("'", "", $date);
$branchName = str_replace("'", "", $branchName);


$query='SELECT DEPOCLOSETRAN."TRAN_DATE", DEPOCLOSETRAN."TRAN_ACNOTYPE" AC_ACNOTYPE ,SCHEMASTONE."S_APPL",SCHEMASTONE."S_NAME", 
DEPOCLOSETRAN."TRAN_ACTYPE" AC_TYPE, DEPOCLOSETRAN."TRAN_ACNO" AC_NO, 
 DPMASTER."AC_NAME", "NET_PAYABLE_AMOUNT" TRAN_AMOUNT  
 FROM DEPOCLOSETRAN 
 LEFT OUTER JOIN DPMASTER ON DEPOCLOSETRAN."TRAN_ACNO" = DPMASTER."BANKACNO" 
 left outer join schemast as schemastone on schemastone.id  = cast(DPMASTER."AC_TYPE" as integer)

 WHERE DEPOCLOSETRAN."TRAN_ACNOTYPE" ='.$ac_acnotype.'
 And DEPOCLOSETRAN."TRAN_STATUS" ='.$TRAN_STATUS.' 
 AND DEPOCLOSETRAN."BRANCH_CODE"='.$BRANCH_CODE.'
 And cast(DEPOCLOSETRAN."TRAN_DATE" as date)>= CAST('.$tran_date.' AS Date)
 Order by SCHEMASTONE."S_APPL", DEPOCLOSETRAN."TRAN_ACNOTYPE"';


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$t1=0;
$total=0;



// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
          //  $t1=$t1+$row['tran_amount'],
           $total=$total+$row['tran_amount'],
            
            'TRAN_DATE' => $row['TRAN_DATE'],
            'ac_acnotype' => $row['ac_acnotype'],
            'ac_type' => $row['ac_type'],
            'S_APPL' => $row['S_APPL'],
            'S_NAME' => $row['S_NAME'],
            'ac_no' => $row['ac_no'],
            'AC_NAME' => $row['AC_NAME'],
            'tran_amount' => sprintf("%.2f", ($row['tran_amount'] + 0.0)),

            'BRANCH_CODE' => $BRANCH_CODE,
            'date' => $date,
            'tran_date'=> $tran_date,
            'branchName' => $branchName,

            // 'revoke' => $revoke,
             'bankName' => $bankName,
             't1' => $t1,
             'total' =>sprintf("%.2f", ($total + 0.0)),


        ];
        $data[$i] = $tmp;
        $i++;
    
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();

//print_r($data);
 //echo $query;
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
// // //}
?>
