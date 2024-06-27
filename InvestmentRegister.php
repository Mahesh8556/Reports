<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/InvestmentRegister.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $startDate = "'18/03/2018'";
// $enddate = "'20/03/2018'";

//$c = "'C'";
$AC_TYPE = $_GET['AC_TYPE'];  
$startDate_ = $_GET['startDate_'];
$enddate_ = $_GET['enddate_'];
$AC_ACNOTYPE=$_GET['AC_ACNOTYPE'];
$NAME = $_GET['NAME'];
$bankName =  $_GET['bankName'];
$trandrcr=$_GET['trandrcr'];
$ac_op_cd=$_GET['ac_op_cd'];
$tran_status=$_GET['tran_status'];


$dateformate = "'DD/MM/YYYY'";



$query = 'SELECT DPMASTER."AC_ACNOTYPE"  , DPMASTER."AC_TYPE" , DPMASTER."AC_NO" , DPMASTER."BANKACNO",
DPMASTER."AC_NAME" , "INVEST_BANK" , "INVEST_BRANCH" , DPMASTER."AC_CLOSEDT" , 
DPMASTER."AC_OPDATE" , DPMASTER."AC_REF_RECEIPTNO" , DPMASTER."AC_SCHMAMT" , DPMASTER."AC_INTRATE", 
DPMASTER."AC_MATUAMT" , DPMASTER."AC_EXPDT"  , VWTMPZBALANCEIV.CLOSING_BALANCE, 
DEPOTRAN.INT_AMOUNT, 0 CURRENT_INT 
From DPMASTER 
LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", SUM(INT_AMOUNT) INT_AMOUNT FROM DPMASTER,
(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
SUM(cast("TRAN_AMOUNT" As FLOAT)) + SUM(cast("RECPAY_INT_AMOUNT" As FLOAT)) INT_AMOUNT 
From DEPOTRAN 
WHERE "TRAN_DRCR" = '.$trandrcr.' AND "IS_INTEREST_ENTRY" = -1 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 	
Union All 	
SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
SUM(cast("INTEREST_AMOUNT" As integer))  INT_AMOUNT 
From DEPOTRAN 
WHERE "TRAN_DRCR" = '.$trandrcr.' AND cast("INTEREST_AMOUNT" As integer) <> 0 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
) s GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" 
) DEPOTRAN ON DEPOTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
LEFT OUTER JOIN
(SELECT "AC_ACNOTYPE", "AC_TYPE" , "AC_NO", "AC_OPDATE", "AC_CLOSEDT" , DPMASTER."BANKACNO",
(COALESCE(CASE "AC_OP_CD"  WHEN '.$ac_op_cd.' THEN  cast("AC_OP_BAL" As integer)  ELSE (-1) * cast("AC_OP_BAL" As integer) END,0) + 
COALESCE(DEPOTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE , 
(COALESCE(CASE DPMASTER."AC_OP_CD"  WHEN '.$ac_op_cd.' THEN  cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  ELSE (-1) * cast(DPMASTER."AC_PAYBLEINT_OP" As integer)  END,0)+  
COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0))  RECPAY_INT_AMOUNT 
FROM DPMASTER
LEFT OUTER JOIN
( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) TRAN_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DEPOTRAN WHERE cast("TRAN_DATE" As date) <= cast('.$enddate_.' As date)
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DEPOTRAN
ON DPMASTER."BANKACNO" =  DEPOTRAN."TRAN_ACNO"
LEFT OUTER JOIN
(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) DAILY_AMOUNT, 
SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("RECPAY_INT_AMOUNT" As FLOAT)  ELSE (-1) * cast("RECPAY_INT_AMOUNT" As FLOAT)  END) RECPAY_INT_AMOUNT 
FROM DAILYTRAN WHERE cast("TRAN_DATE" As date) <= cast('.$enddate_.' As date)
AND "TRAN_STATUS" = '.$tran_status.' 
GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
) DAILYTRAN
ON DPMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
WHERE ((DPMASTER."AC_OPDATE" IS NULL) OR (cast(DPMASTER."AC_OPDATE" As date) <= cast('.$startDate_.' As date)))
AND ((DPMASTER."AC_CLOSEDT" IS NULL) OR (cast(DPMASTER."AC_CLOSEDT" As date) > cast('.$enddate_.' As date))
))VWTMPZBALANCEIV ON VWTMPZBALANCEIV."BANKACNO" = DPMASTER."BANKACNO"
WHERE
DPMASTER."AC_ACNOTYPE" ='.$AC_ACNOTYPE.' 
AND cast(DPMASTER."AC_OPDATE" As date) >= cast('.$startDate_.' As date) 
AND cast(DPMASTER."AC_OPDATE" As date) <= cast('.$startDate_.' As date) 
AND DPMASTER."AC_CLOSEDT" IS NULL';

          

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;
$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
$GRAND_TOTAL3 = 0;

//if (pg_num_rows($sql) == 0) {
    //include "errormsg.html";
//}else {

while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['AC_SCHMAMT'];
    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['closing_balance'] ;
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row['INTEREST_AMOUNT'] ;
    $GRAND_TOTAL3 = $GRAND_TOTAL3 + $row['RECPAY_INT_AMOUNT'] ;

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SCHMAMT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $GROUP_TOTAL = 0;
        $GROUP_TOTAL = $GROUP_TOTAL + $row['AC_SCHMAMT'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $SCHEME_TOTAL = $SCHEME_TOTAL + $row['INTEREST_AMOUNT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $SCHEME_TOTAL = 0;
        $SCHEME_TOTAL = $SCHEME_TOTAL + $row['INTEREST_AMOUNT'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $RECEIVABLE_TOTAL = $RECEIVABLE_TOTAL + $row['RECPAY_INT_AMOUNT'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $RECEIVABLE_TOTAL = 0;
        $RECEIVABLE_TOTAL = $RECEIVABLE_TOTAL + $row['RECPAY_INT_AMOUNT'];
    }

    if($type == ''){
        $type = $row['AC_ACNOTYPE'];
    }
    if($type == $row['AC_ACNOTYPE']){
        $BALNC_TOTAL = $BALNC_TOTAL + $row['closing_balance'];
    }else{
        $type = $row['AC_ACNOTYPE'];
        $BALNC_TOTAL = 0;
        $BALNC_TOTAL = $BALNC_TOTAL + $row['closing_balance'];
    }

    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'INVEST_BRANCH' => $row['INVEST_BRANCH'],
        'AC_SCHMAMT' => $row['AC_SCHMAMT'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        'AC_CLOSEDT'=> $row['AC_CLOSEDT'],
        'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
        //'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        //'AC_TYPE' => $row['AC_TYPE'],
        'closing_balance' => $row['closing_balance'],
        'INTEREST_AMOUNT' => $row['INTEREST_AMOUNT'],
        'RECPAY_INT_AMOUNT' => $row['RECPAY_INT_AMOUNT'],
        'totalamt' => $GRAND_TOTAL,
        'totalbalance' => $GRAND_TOTAL1,
        'receivdtotal' => $GRAND_TOTAL2,
        'receivbletot' => $GRAND_TOTAL3,
        'schmtotamt'=> $GROUP_TOTAL,
        'schmreceivdtot'=> $SCHEME_TOTAL,
        'schmrecevbltot'=> $RECEIVABLE_TOTAL,
        'schmbaltot'=> $BALNC_TOTAL,

        'NAME'=>$NAME,
        'bankName' => $bankName,
        'AC_ACNOTYPE'=>$AC_ACNOTYPE,
        'AC_TYPE'=>$AC_TYPE,
        'startDate_' => $startDate_,
        'enddate_' => $enddate_,
        'trandrcr'=>$trandrcr,
        'ac_op_cd'=>$ac_op_cd,
        'tran_status'=>$tran_status,
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

//}
?>  

