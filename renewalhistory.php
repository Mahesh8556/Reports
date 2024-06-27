<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/renewalhistory.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$stdate= $_GET['stdate'];
$etdate = $_GET['etdate'];
$date = $_GET['date'];
$AC_TYPE = $_GET['AC_TYPE'];
$branch_code = $_GET['branch'];
$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];


$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$date_= str_replace("'", "", $date);
$branchName = str_replace("'", "", $branchName);

// echo $branch_code;

$query='SELECT DPMASTER."AC_ACNOTYPE" ,DPMASTER."AC_TYPE" ,DPMASTER."BANKACNO" , DPMASTER."AC_NAME" , SCHEMAST."S_NAME" ,SCHEMAST."S_APPL" , RENEWALHISTORY."NEW_RECEIPTNO" , RENEWALHISTORY."RENEWAL_DATE" , RENEWALHISTORY."RENEWAL_AMOUNT" , COALESCE(CAST("NORMAL_INTEREST" AS INTEGER),0)+ COALESCE(CAST("PAYABLE_INTEREST" AS INTEGER),0) INTEREST_AMOUNT FROM DPMASTER INNER JOIN RENEWALHISTORY ON DPMASTER."BANKACNO" = RENEWALHISTORY."AC_NO" INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.id WHERE   CAST(RENEWALHISTORY."RENEWAL_DATE" AS DATE) <= CAST('.$stdate.'AS DATE) AND RENEWALHISTORY."AC_TYPE"='.$AC_TYPE.' AND CAST(RENEWALHISTORY."TRAN_STATUS" AS INTEGER)=1 AND RENEWALHISTORY."BRANCH_CODE"='.$branch_code.'';


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$TOTALAC = 0;
// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
        $tmp = [

            $TOTALAC=$TOTALAC+$row['AC_NO'],
            $TOTALRA=$TOTALRA+$row['RENEWAL_AMOUNT'],
            $TOTALIA=$TOTALIA+$row['interest_amount'],


            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'BANKACNO' => $row['BANKACNO'],
            'AC_NAME' => $row['AC_NAME'],
            'S_NAME' => $row['S_NAME'],
            'S_APPL' => $row['S_APPL'],
            'NEW_RECEIPTNO' => $row['NEW_RECEIPTNO'],
            'RENEWAL_DATE' => $row['RENEWAL_DATE'],
            'RENEWAL_AMOUNT' => sprintf("%.2f", ($row['RENEWAL_AMOUNT'] + 0.0)),
            'interest_amount' => sprintf("%.2f", ($row['interest_amount'] + 0.0)),
            

           
            'stdate' => $stdate,
            'etdate' => $etdate,
            'date' => $date_,
            '$AC_TYPE'=>$AC_TYPE,
            'branch_code' => $branch,
            'branchName' => $branchName,
            'TOTALAC'=>sprintf("%.2f", ($TOTALAC + 0.0)),
            'TOTALRA'=>sprintf("%.2f", ($TOTALRA + 0.0)),
            'TOTALIA'=>sprintf("%.2f", ($TOTALIA + 0.0)),
            'bankName' => $bankName,


        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();
$config = ['driver' => 'array', 'data' => $data];
//echo $query;
//print_r($data);
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    

?>
