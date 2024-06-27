<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 3600);
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/PayableIntBal.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$int="'0'";
$dateformate = "'DD/MM/YYYY'";

$Date     = $_GET['Date'];
$bankName = $_GET['bankName'];
$scheme   = $_GET['scheme'];
$branch   = $_GET['branch'];
$PrintClosedAccounts = $_GET['PrintClosedAccounts'];

$bankName = str_replace("'", "", $bankName);
$Date_ = str_replace("'", "", $Date);
$scheme = str_replace("'", "", $scheme);
$scheme1 = str_replace("'", "", $scheme);
$closeAccount = true;
$D = "'D'";

if($scheme == 'TD' || $scheme =='SB'){
    $scheme = "'".$scheme."'";
    // echo $scheme;
    $query = 'SELECT SCHEMAST."S_APPL",
	SCHEMAST."S_NAME", DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" , DPMASTER."BANKACNO" , DPMASTER."AC_NAME",DPMASTER."AC_CLOSEDT" 
    , ( COALESCE(CASE DPMASTER."AC_OP_CD"  WHEN '.$D.' THEN  CAST(DPMASTER."AC_OP_BAL" AS FLOAT)  ELSE (-1) * CAST(DPMASTER."AC_OP_BAL" AS FLOAT)  END,0) +  COALESCE(DEPOTRAN.TRAN_AMOUNT,0)) CLOSING_BALANCE 
    , ( COALESCE(CASE DPMASTER."AC_OP_CD"  WHEN '.$D.' THEN  CAST(DPMASTER."AC_PAYBLEINT_OP" AS FLOAT)  ELSE (-1) * CAST(DPMASTER."AC_PAYBLEINT_OP" AS FLOAT)  END,0) +  COALESCE(DEPOTRAN.RECPAY_INT_AMOUNT,0)) RECPAY_INT_AMOUNT , 
    0 REC_PENAL_INT_AMOUNT, 0 PENAL_INTEREST, 0 OVERDUE_INT_AMOUNT 
    FROM DPMASTER LEFT JOIN SCHEMAST ON SCHEMAST.ID=DPMASTER."AC_TYPE"
    , ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("TRAN_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END) TRAN_AMOUNT 
        , SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END) RECPAY_INT_AMOUNT FROM DEPOTRAN 
        WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$Date.' AS DATE)   GROUP BY "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" 
    ) DEPOTRAN  
    WHERE DPMASTER."BANKACNO" = DEPOTRAN."TRAN_ACNO"
    AND ((DPMASTER."AC_OPDATE" IS NULL) OR (CAST(DPMASTER."AC_OPDATE" AS DATE) <= CAST('.$Date.' AS DATE)))
    AND DPMASTER."AC_ACNOTYPE" = '.$scheme.'
    AND DPMASTER."BRANCH_CODE" ='.$branch ;
    // echo $query;

    if($PrintClosedAccounts == '0'){
        $query .=' AND DPMASTER."AC_CLOSEDT" IS NULL';
    }
    else
    {
        $query .='  AND DPMASTER."AC_CLOSEDT" IS NOT NULL AND CAST(DPMASTER."AC_CLOSEDT" as date) < CAST('.$Date.' as date)'; 
    }
    $query .=' order by  SCHEMAST."S_APPL", DPMASTER."BANKACNO" ASC';
}
if($scheme == 'PG'){
    $query = 'SELECT	SCHEMAST."S_APPL",
	SCHEMAST."S_NAME", PGMASTER."AC_ACNOTYPE" , PGMASTER."AC_TYPE" , PGMASTER."BANKACNO" , PGMASTER."AC_NAME",PGMASTER."AC_CLOSEDT"
    , ( COALESCE(CASE PGMASTER."AC_OP_CD"  WHEN '.$D.' THEN  CAST(PGMASTER."AC_OP_BAL" AS FLOAT)  ELSE (-1) * CAST(PGMASTER."AC_OP_BAL" AS FLOAT)  END,0) +  COALESCE(PIGMYTRAN.TRAN_AMOUNT,0)) CLOSING_BALANCE
    , ( COALESCE(CASE PGMASTER."AC_OP_CD"  WHEN '.$D.' THEN  CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT)  ELSE (-1) * CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT)  END,0) +  COALESCE(PIGMYTRAN.RECPAY_INT_AMOUNT,0)) RECPAY_INT_AMOUNT , 0 REC_PENAL_INT_AMOUNT, 0 PENAL_INTEREST, 0 OVERDUE_INT_AMOUNT
    FROM PGMASTER LEFT JOIN SCHEMAST ON SCHEMAST.ID=PGMASTER."AC_TYPE"
    LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("TRAN_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)  END) TRAN_AMOUNT
        , SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)  END) RECPAY_INT_AMOUNT FROM PIGMYTRAN
        WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$Date.' as DATE)  GROUP BY "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO"
    ) PIGMYTRAN
    ON PGMASTER."AC_ACNOTYPE" = PIGMYTRAN."TRAN_ACNOTYPE"
    AND PGMASTER."AC_TYPE" = CAST(PIGMYTRAN."TRAN_ACTYPE" AS integer)
    AND PGMASTER."BANKACNO" = PIGMYTRAN."TRAN_ACNO" 
    WHERE ((PGMASTER."AC_OPDATE" IS NULL) OR (CAST(PGMASTER."AC_OPDATE" as date) <= CAST('.$Date.' AS DATE))) AND PGMASTER."BRANCH_CODE"='.$branch;
   
    if($PrintClosedAccounts == '0'){
        $query .=' AND PGMASTER."AC_CLOSEDT" IS NULL';
    }
    else
    {
        $query .=' AND PGMASTER."AC_CLOSEDT" IS NOT NULL AND CAST(PGMASTER."AC_CLOSEDT" as date) < CAST('.$Date.' as date)'; 
    }
    $query .=' order by  SCHEMAST."S_APPL", PGMASTER."BANKACNO" ASC';

}

//    echo $query;
// get branch name details
$branchdata = pg_query($conn,'select * from ownbranchmaster where id = '.$branch);
while($row = pg_fetch_assoc($branchdata)){
    $branchName = $row['NAME'];
}
$sql =  pg_query($conn,$query);

$i = 0;

$grandLedbal = 0;
$grandReceivint = 0;
$payable_bal_total = 0;
$payable_Interest_total = 0;
$type = '';

while($row = pg_fetch_assoc($sql)){

    print_r($row);

    $grandLedbal = $grandLedbal + $row['closing_balance'];
    $grandReceivint = $grandReceivint + $row['recpay_int_amount'];


    if($type == ''){
        $type = $row['S_APPL'];
    }
    if($type == $row['S_APPL']){
        $payable_bal_total = $payable_bal_total + $row['closing_balance'];
        $payable_Interest_total = $payable_Interest_total + $row['recpay_int_amount'];

    }else{
        $type = $row['S_APPL'];
        $payable_bal_total = 0;
        $payable_Interest_total = 0;
        $payable_Interest_total = $payable_Interest_total + $row['recpay_int_amount'];

        $payable_bal_total = $payable_bal_total + $row['closing_balance'];
    }




    $ledgerType = '';
    if($row['closing_balance'] < 0){
        $ledgerType = 'Cr';
    }else{
        $ledgerType = 'Dr';
    }

    $recpayType = '';
    if($row['recpay_int_amount'] < 0){
        $recpayType = 'Cr';
    }else{
        $recpayType = 'Dr';
    }
    if($row['recpay_int_amount'] != 0){
        $closeDate = $row['AC_CLOSEDT'];
        $tmp=[
            'AC_NO' => $row['BANKACNO'],
            'AC_NAME' => $row['AC_NAME'],
            'NAME' => $branchName,
            'AC_TYPE' => $row['AC_TYPE'],
            'AC_CLOSEDT' =>$closeDate == ''?'-':$closeDate,
            'ledger_balance' =>sprintf("%.2f", (abs($row['closing_balance']))).' '.$ledgerType,
            'payable_balance' =>sprintf("%.2f", (abs($row['recpay_int_amount']))).' '.$recpayType,
            'S_APPL' =>$row['S_APPL'],
            'S_NAME' =>$row['S_NAME'],
            'grandLedbal' => sprintf("%.2f", (abs($grandLedbal))) ,
            'grandReceivint' => sprintf("%.2f", (abs($grandReceivint))),

            'Date_' => $Date_,
            'scheme' => $row['S_APPL'].' '.$row['S_NAME'],
            'branch' => $branch,
            'bankName' => $bankName,
            'Date' => $Date,
            'payable_bal_total' => sprintf("%.2f", (abs($payable_bal_total))),
            'payable_Interest_total' => sprintf("%.2f", (abs($payable_Interest_total))),
        ];
        $data[$i]=$tmp;
        // print_r($data);
        $i++;
    }
    
}

print_r($data);
ob_end_clean();
if (count($data) == 0) {
    include "errormsg.html";
}else {

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
}

?>