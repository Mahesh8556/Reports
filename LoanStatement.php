<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/LoanStatement.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBS user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";

$bankName = $_GET['bankName'];
$startDate = $_GET['startDate'];
$sdate = $_GET['sdate'];
$endDate = $_GET['endDate'];
$branch = $_GET['branch'];
$startcode = $_GET['startingcode'];
$endingcode = $_GET['endingcode'];
$scheme = $_GET['scheme'];

// echo $sdate;

$bankName = str_replace("'", "", $bankName);
$startDate_ = str_replace("'", "", $startDate);
$endDate_ = str_replace("'", "", $endDate);
// $branchName = str_replace("'", "", $branchName);

$scheme = str_replace("'", "", $scheme);

$query='SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), LNMASTER."BANKACNO",

'.$startDate.',
0,
101)AS LEDGER_BALANCE,
LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), LNMASTER."BANKACNO",

'.$startDate.',
1,
101)AS CLOSING_BALANCE,
COALESCE(CASE
                        WHEN LOANTRAN."TRAN_DRCR" = '.$c.' THEN CAST(LOANTRAN."TRAN_AMOUNT" AS float)
                        ELSE 0
        END,
0) AS CRAMT,
COALESCE(CASE
                        WHEN LOANTRAN."TRAN_DRCR" = '.$d.' THEN CAST(LOANTRAN."TRAN_AMOUNT" AS float)
                        ELSE 0
        END,
0) AS DRAMT,
LNMASTER."AC_NAME",
LNMASTER."AC_MONTHS",
LNMASTER."AC_OPDATE",
LNMASTER."AC_EXPIRE_DATE",
LNMASTER."AC_TYPE",
LNMASTER."AC_SANCTION_AMOUNT",
LNMASTER."AC_CLOSED",
LNMASTER."AC_ACNOTYPE",
LNMASTER."AC_NO",
SCHEMAST."S_NAME",
LOANTRAN."NARRATION",
LOANTRAN."RECPAY_INT_AMOUNT",
LOANTRAN."INTEREST_AMOUNT",
LOANTRAN."PENAL_INTEREST",
LOANTRAN."REC_PENAL_INT_AMOUNT",
LOANTRAN."ADDED_PENAL_INTEREST",
LOANTRAN."OTHER10_AMOUNT" AS RECEIVABLEOVERDUE,
LOANTRAN."TRAN_DATE",
LOANTRAN."CHEQUE_NO",

OWNBRANCHMASTER."NAME"
FROM LNMASTER
INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST."id"
INNER JOIN LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
INNER JOIN OWNBRANCHMASTER ON LNMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"

WHERE LNMASTER."BANKACNO" BETWEEN '.$startcode.' AND '.$startcode.'
AND CAST("TRAN_DATE" AS date) BETWEEN TO_DATE('.$startDate.', '.$dateformate.') AND TO_DATE('.$endDate .',

                                                                                                                                                                                                            '.$dateformate.')
AND LNMASTER."BRANCH_CODE" = '.$branch.'
AND LNMASTER."AC_TYPE" = '.$scheme.'

ORDER BY
    LNMASTER."BANKACNO",
    CAST("TRAN_DATE" AS date) ASC';


        //  echo $query;
     $query1='SELECT to_char("EFFECT_DATE",'.$dateformate.') effectdate, * from lnacintrate where "BANKACNO"='.$endingcode.'';

            // echo $query1;


$sql =  pg_query($conn,$query);
$sql1 =  pg_query($conn, $query1);

$effectDate='';
$intRate='';
$penalRate='';
if (pg_num_rows($sql1) == 0) {
    
} else {
    while ($row = pg_fetch_assoc($sql1)) {
        
        $effectDate =  $effectDate. ' '.$row['effectdate'] .'<br>';
        $intRate= $intRate. ' '.  $row['INT_RATE'] .' <br>';
        $penalRate= $penalRate. ' '.  $row['PENAL_INT_RATE'] .' <br>';

    }
}
// echo $effectDate.'    effect';


$i = 0;

$LEDGER_BAL = 0;    
$CRTOTAL = 0;
$TOTAL_CRAMT = 0;
$GTOT_CAMT = 0;
$GTOT_DAMT = 0;
$TOT_INTAMT = 0;
$TOT_RECEAMT = 0;
$tdata=0;
$legerbal=0;
$TOTAL_ROD=0;
$TOTAL_RODINT=0;
$TOTAL_PENAL=0;
$TOTAL_RECPENAL=0;
$TOTAL_ADDPENAL=0;
$TOTAL_RECEIVEAMT=0;




// $totalcr=$legerbal + $row['ledger_balance'];
// $totalcramt=$totalcr+$gtotdamt;

while($row = pg_fetch_assoc($sql)){

    if($i==0){
        $tdata= ($row['ledger_balance']);
    }

    if($row['ledger_balance'] > 0)
    {
        $tdata=$tdata - $row['cramt'] + $row['dramt'];
    }
    else
    {
        $tdata=$tdata - $row['dramt'] + $row['cramt'];

    }
    echo $row['ledger_balance'] .'ledger .<br>';
    echo $row['cramt'] .' credit<br>';
    echo $row['dramt'].'debit<br>';
// echo $tdata .'tdata <br>';

    $LEDGER_BAL =  $row['ledger_balance'] ;
    $CRTOTAL = $row['cramt'] +  $row['ledger_balance'] ;


    $TOTAL_CRAMT = $TOTAL_CRAMT + $row['ledger_balance'] ;

    $TOTAL_ROD = $TOTAL_ROD + $row['receivableoverdue'] ;
    $TOTAL_RODINT = $TOTAL_RODINT + $row['RECPAY_INT_AMOUNT'] ;
    $TOTAL_PENAL = $TOTAL_PENAL + $row['PENAL_INTEREST'] ;
    $TOTAL_RECPENAL = $TOTAL_RECPENAL + $row['REC_PENAL_INT_AMOUNT'] ;
    $TOTAL_ADDPENAL = $TOTAL_ADDPENAL + $row['ADDED_PENAL_INTEREST'] ;
    // $TOTAL_OTHERAMT = $TOTAL_OTHERAMT + $row['receivableoverdue'] ;
    $TOTAL_RECEIVEAMT = $TOTAL_RECEIVEAMT + $TOT_RECEAMT ;


$TOTAL_CRAMT=$GTOT_DAMT+$CRTOTAL;

    $GTOT_CAMT = $GTOT_CAMT + $row['cramt'];
    $GTOT_DAMT = $GTOT_DAMT + $row['dramt'];
    $TOT_INTAMT = $TOT_INTAMT + $row['INTEREST_AMOUNT'];
    $TOT_RECEAMT = $row['ADDED_PENAL_INTEREST'] + $row['REC_PENAL_INT_AMOUNT'] + 
                   $row['PENAL_INTEREST'] + $row['INTEREST_AMOUNT'];

    $tmp=[
        'AC_NAME' => $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPIRE_DATE' => $row['AC_EXPIRE_DATE'],
        'AC_SANCTION_AMOUNT'=> $row['AC_SANCTION_AMOUNT'],
        'AC_MONTHS' => $row['AC_MONTHS'],
        'S_NAME' => $row['S_NAME'],
        'NARRATION' => $row['NARRATION'],
        'EFFECT_DATE' => $effectDate,
        'INT_RATE' => $intRate,
        'PENAL_INT_RATE' => $penalRate,
        'cramt' => $row['cramt'],
        'dramt' => sprintf("%.2f",($row['dramt'] + 0.0)),
        'RECPAY_INT_AMOUNT' => $row['RECPAY_INT_AMOUNT'],
        'INTEREST_AMOUNT' => $row['INTEREST_AMOUNT'],
        'PENAL_INTEREST' => $row['PENAL_INTEREST'],
        'REC_PENAL_INT_AMOUNT' => $row['REC_PENAL_INT_AMOUNT'],
        'ADDED_PENAL_INTEREST' => $row['ADDED_PENAL_INTEREST'],
        'receivableoverdue' => $row['receivableoverdue'],
        'CHEQUE_NO' => $row['CHEQUE_NO'],
        'TRAN_DATE' => $row['TRAN_DATE'],

        'NAME' => $row['NAME'],
        'ledger_balance' => sprintf("%.2f", (abs($tdata) + 0.0)),
        'totalcr' => sprintf("%.2f",($CRTOTAL + 0.0)),
        'totalcr' => sprintf("%.2f",($row['ledger_balance'] + 0.0)),
        'totalcramt' => sprintf("%.2f",(abs($tdata) + 0.0)),
        'gtotcamt' => sprintf("%.2f",($GTOT_CAMT + 0.0)),
        'gtotdamt' => sprintf("%.2f",($GTOT_DAMT + 0.0)),
        'totintamt' => sprintf("%.2f",($TOT_INTAMT + 0.0)),
        'totreceivamt' => $TOT_RECEAMT,

        'TOTAL_ROD' => sprintf("%.2f",($TOTAL_ROD + 0.0)),
        'TOTAL_RODINT' => sprintf("%.2f",($TOTAL_RODINT + 0.0)),
        'TOTAL_PENAL' => sprintf("%.2f",($TOTAL_PENAL + 0.0)),
        'TOTAL_RECPENAL' => sprintf("%.2f",($TOTAL_RECPENAL + 0.0)),
        'TOTAL_ADDPENAL' => sprintf("%.2f",($TOTAL_ADDPENAL + 0.0)),
        'TOTAL_RECEIVEAMT' => sprintf("%.2f",($TOTAL_RECEIVEAMT + 0.0)),
    



        'startDate_' => $startDate_,
        'sdate' => $sdate,
        'endDate_' => $endDate_,
        'branch' => $branch,
        'startingcode' => $startingcode,
        'endingcode' => $endingcode,
        'scheme' => $scheme,
        'no' => $no,
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
    
?>

