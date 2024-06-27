<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 3600);
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ReceivableInterestBal.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$branchName = $_GET['branchName'];
$Date = $_GET['Date'];
$scheme = $_GET['scheme'];
$branch = $_GET['branch'];
$PrintClosedAccounts = $_GET['PrintClosedAccounts'];
$PrintPenalInterestDetails = $_GET['PrintPenalInterestDetails'];
$PrintClosedAcPrintReceivablePenalInterestDetailscounts = $_GET['PrintClosedAcPrintReceivablePenalInterestDetailscounts'];
$PrintOverdueInterest = $_GET['PrintOverdueInterest'];
$branchCode = $_GET['branch'];

$bankName = str_replace("'", "", $bankName);
$Date_ = str_replace("'", "", $Date);
$scheme1 = str_replace("'", "", $scheme);
// $branchName = str_replace("'", "", $branchName);

$int="'0'";
$D ="'D'";
$dateformate = "'DD/MM/YYYY'";

// if($scheme == 'IV'){
    $schemes = "'IV'";
    $query = 'SELECT SCHEMAST."S_APPL",SCHEMAST."S_NAME", LNMASTER."AC_ACNOTYPE",
	LNMASTER."AC_TYPE",
	LNMASTER."BANKACNO",
	LNMASTER."AC_NAME",
	LNMASTER."AC_CLOSED",
	(COALESCE(CASE "AC_OP_CD" WHEN '.$D.' THEN CAST("AC_OP_BAL" AS float) ELSE (-1) * CAST("AC_OP_BAL" AS float) END,0) + COALESCE(LOANTRAN.TRAN_AMOUNT,0)) CLOSING_BALANCE,
	(COALESCE(CASE "AC_OP_CD" WHEN '.$D.' THEN CAST("AC_RECBLEINT_OP" AS float) ELSE (-1) * CAST("AC_RECBLEINT_OP" AS float) END, 0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT, 0)) RECPAY_INT_AMOUNT,
	(COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$D.' THEN CAST("AC_RECBLEODUEINT_OP" AS float)
	ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float) END, 0) + COALESCE(LOANTRAN.OTHER10_AMOUNT, 0)) OVERDUE_INT_AMOUNT,(COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$D.' THEN CAST("AC_PINT_OP" AS float) ELSE (-1) * CAST(LNMASTER."AC_PINT_OP" AS float) END,0) + COALESCE(LOANTRAN.PENAL_INTEREST,0)) PENAL_INTEREST,(COALESCE(LOANTRAN.REC_PENAL_INT_AMOUNT,0)) REC_PENAL_INT_AMOUNT,
	0 AC_REF_RECEIPTNO FROM LNMASTER LEFT JOIN SCHEMAST ON SCHEMAST.ID=LNMASTER."AC_TYPE",(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE","TRAN_ACNO",SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS float)ELSE (-1) * CAST("TRAN_AMOUNT" AS Float) END) TRAN_AMOUNT,SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS float) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS Float) END) RECPAY_INT_AMOUNT,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("OTHER10_AMOUNT" AS float)ELSE (-1) * CAST("OTHER10_AMOUNT" AS float) END),0) OTHER10_AMOUNT,COALESCE(SUM(CASE "TRAN_DRCR"WHEN '.$D.' THEN CAST("PENAL_INTEREST" AS float) ELSE (-1) * CAST("PENAL_INTEREST" AS float) END),0) PENAL_INTEREST,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("REC_PENAL_INT_AMOUNT" AS float) ELSE (-1) * CAST("REC_PENAL_INT_AMOUNT" AS float) END), 0) REC_PENAL_INT_AMOUNT FROM LOANTRAN WHERE CAST("TRAN_DATE" AS date) <= CAST('.$Date.' AS DATE) AND LOANTRAN."TRAN_ACNOTYPE" IN ('.$schemes.') GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO") LOANTRAN
    WHERE LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" AND ((LNMASTER."AC_OPDATE" IS NULL) OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$Date.' AS date))) AND LNMASTER."AC_ACNOTYPE" IN('.$schemes.')
	AND LNMASTER."BRANCH_CODE" = '.$branchCode.' ORDER BY CAST(LNMASTER."BANKACNO" AS BIGINT) ASC';
    // }else{
    if($scheme == 'LN'){
        $schemes = "'LN'";
    }elseif($scheme == 'CC'){
        $schemes = "'CC'";
    }else{  
        $schemes = "'DS'";
    }
    $query = 'SELECT  SCHEMAST."S_APPL",SCHEMAST."S_NAME", LNMASTER."AC_ACNOTYPE",
	LNMASTER."AC_TYPE",
	LNMASTER."BANKACNO",
	LNMASTER."AC_NAME",
	LNMASTER."AC_CLOSED",
	(COALESCE(CASE "AC_OP_CD" WHEN '.$D.' THEN CAST("AC_OP_BAL" AS float) ELSE (-1) * CAST("AC_OP_BAL" AS float) END,0) + COALESCE(LOANTRAN.TRAN_AMOUNT,0)) CLOSING_BALANCE,
	(COALESCE(CASE "AC_OP_CD" WHEN '.$D.' THEN CAST("AC_RECBLEINT_OP" AS float) ELSE (-1) * CAST("AC_RECBLEINT_OP" AS float) END, 0) + COALESCE(LOANTRAN.RECPAY_INT_AMOUNT, 0)) RECPAY_INT_AMOUNT,
	(COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$D.' THEN CAST("AC_RECBLEODUEINT_OP" AS float)
	ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS float) END, 0) + COALESCE(LOANTRAN.OTHER10_AMOUNT, 0)) OVERDUE_INT_AMOUNT,(COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$D.' THEN CAST("AC_PINT_OP" AS float) ELSE (-1) * CAST(LNMASTER."AC_PINT_OP" AS float) END,0) + COALESCE(LOANTRAN.PENAL_INTEREST,0)) PENAL_INTEREST,(COALESCE(LOANTRAN.REC_PENAL_INT_AMOUNT,0)) REC_PENAL_INT_AMOUNT,
	0 AC_REF_RECEIPTNO FROM LNMASTER LEFT JOIN SCHEMAST ON SCHEMAST.ID=LNMASTER."AC_TYPE",(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE","TRAN_ACNO",SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS float)ELSE (-1) * CAST("TRAN_AMOUNT" AS Float) END) TRAN_AMOUNT,SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS float) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS Float) END) RECPAY_INT_AMOUNT,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("OTHER10_AMOUNT" AS float)ELSE (-1) * CAST("OTHER10_AMOUNT" AS float) END),0) OTHER10_AMOUNT,COALESCE(SUM(CASE "TRAN_DRCR"WHEN '.$D.' THEN CAST("PENAL_INTEREST" AS float) ELSE (-1) * CAST("PENAL_INTEREST" AS float) END),0) PENAL_INTEREST,COALESCE(SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("REC_PENAL_INT_AMOUNT" AS float) ELSE (-1) * CAST("REC_PENAL_INT_AMOUNT" AS float) END), 0) REC_PENAL_INT_AMOUNT FROM LOANTRAN WHERE CAST("TRAN_DATE" AS date) <= CAST('.$Date.' AS DATE) AND LOANTRAN."TRAN_ACNOTYPE" IN ('.$schemes.') GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO") LOANTRAN
    WHERE LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO" AND ((LNMASTER."AC_OPDATE" IS NULL) OR (CAST(LNMASTER."AC_OPDATE" AS date) <= CAST('.$Date.' AS date))) AND LNMASTER."AC_ACNOTYPE" IN('.$schemes.')
	AND LNMASTER."BRANCH_CODE" = '.$branchCode.' ORDER BY CAST(LNMASTER."BANKACNO" AS BIGINT) ASC';

         
// }
// echo $query;
$sql =  pg_query($conn,$query);

$i = 0;
$total = 0;
$recTotal = 0;
while($row = pg_fetch_assoc($sql)){
    if($row['recpay_int_amount'] == 0){

    }else{
        $total = $total + abs($row['closing_balance']);
        $recTotal = $recTotal + abs($row['recpay_int_amount']);
        $tmp=[
            'AC_NO'           => $row['BANKACNO'],
            'AC_NAME'         => $row['AC_NAME'],
            'NAME'            => $branchName,
            'AC_TYPE'         => $row['AC_TYPE'],
            'AC_CLOSEDT'      => '',
            'ledger_balance'  => sprintf("%.2f", (abs($row['closing_balance']))),
            'payable_balance' => sprintf("%.2f", (abs($row['recpay_int_amount']))),
            'PENAL_INTEREST'  => 0,
            'OTHER10_AMOUNT'  => $row['penal_interest'],
            'S_APPL'          => $row['S_APPL'],
            'S_NAME'          => $row['S_NAME'],
            'Total'           => sprintf("%.2f", (abs($total))),
            'recTotal'        => sprintf("%.2f", (abs($recTotal))),
            'Date'            => $Date,
            'Date_'           => $Date_,
            'bankName'        => $bankName,
            'scheme'          => $row['S_APPL'].' '.$row['S_NAME'],
            'branch'          => $branch,
            'PrintClosedAccounts'=> $PrintClosedAccounts,
            'PrintPenalInterestDetails'=> $PrintPenalInterestDetails,
            'PrintClosedAcPrintReceivablePenalInterestDetailscounts'=> $PrintClosedAcPrintReceivablePenalInterestDetailscounts,
            'PrintOverdueInterest'=> $PrintOverdueInterest,

        ];
        $data[$i]=$tmp;
        $i++;
    }
    
}
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
