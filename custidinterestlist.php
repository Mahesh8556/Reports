<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/custidinterestlist.jrxml';


$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$dd="'DD/MM/YYYY'";
$stdate = $_GET['stdate'];
$branchName = $_GET['branchName'];
$etdate = $_GET['etdate'];
$bankName = $_GET['bankName'];
//$revoke = $_GET['revoke'];
$branch_code = $_GET['branch_code'];
 $AC_CUSTID = $_GET['AC_CUSTID'];
//  $close=$_GET['close'];
$var="'C'";
$var1="'D'";
$var2="'LN'";
$var3="'TD'";
$var4="'Null'";
$status="'1'";
$var5="'0'";
// $AC_CUSTID="'1'";


$bankName = str_replace("'", "", $bankName);
$stdate_1 = str_replace("'", "", $stdate);
$etdate_1 = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);


$query='SELECT TMP.*
FROM
	(SELECT
	 		DPMASTER."AC_ACNOTYPE",
			DPMASTER."AC_TYPE",
			DPMASTER."AC_NO",
			DPMASTER."AC_NAME",DPMASTER."AC_CLOSEDT","S_NAME",  "S_APPL",
			DPMASTER."AC_REF_RECEIPTNO",
			DPMASTER."AC_EXPDT",
			'.$var5.'"IS_WEAKER",
			CUSTOMERADDRESS."AC_ADDR",
	 		DPMASTER."AC_CUSTID",
			DEPOTRAN."TRAN_DATE",
			COALESCE(CASE DEPOTRAN."IS_INTEREST_ENTRY"
				WHEN 0 THEN 0
				ELSE CASE DEPOTRAN."TRAN_DRCR"
					WHEN '.$var.' THEN CAST (DEPOTRAN."TRAN_AMOUNT" AS FLOAT)
				ELSE 0
								END
												END,
				0) INTEREST_AMOUNT,
			COALESCE(CASE DEPOTRAN."TRAN_DRCR"
						WHEN '.$var1.' THEN CAST(DEPOTRAN."INTEREST_AMOUNT" AS FLOAT)
						ELSE 0
												END,
				0) RECPAY_INTEREST_AMOUNT,
			'.$var3.' MASTER_TYPE,
			SCHEMAST."S_NAME" SCHEME_NAME
		FROM DPMASTER
		INNER JOIN DEPOTRAN ON COALESCE(CAST(DPMASTER."BANKACNO" AS FLOAT),

			0) = COALESCE(CAST(DEPOTRAN."TRAN_ACNO" AS FLOAT), 0)
		LEFT OUTER JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = DPMASTER."AC_CUSTID"
		INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.ID
		WHERE CAST(DEPOTRAN."TRAN_DATE" AS DATE) >= TO_DATE('.$stdate .','.$dd.')
			AND CAST(DEPOTRAN."TRAN_DATE" AS DATE) <= TO_DATE('.$etdate.',	'.$dd.')
			AND "AC_CUSTID" = '.$AC_CUSTID.'
			AND DEPOTRAN."BRANCH_CODE" = '.$branch_code.'
			AND DPMASTER."status" = '.$status.'
			AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL
			AND DPMASTER."BRANCH_CODE" = '.$branch_code.'
	 
		UNION ALL
	 SELECT
	 		LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			LNMASTER."AC_NO",
			LNMASTER."AC_NAME",LNMASTER."AC_CLOSEDT","S_NAME",  "S_APPL",
			'.$var4.' AC_REF_RECEIPTNO,
			LNMASTER."AC_EXPIRE_DATE",
	fn_get_WeakerMast_name(LNMASTER."IS_WEAKER") AS "IS_WEAKER", 
	fn_get_cust_address(LNMASTER."AC_CUSTID") AS "AC_ADDR",
			LNMASTER."AC_CUSTID",
			LOANTRAN."TRAN_DATE",
			COALESCE(CASE LOANTRAN."TRAN_DRCR"
				WHEN '.$var1.' THEN CAST(LOANTRAN."TRAN_AMOUNT" AS FLOAT)
						ELSE 0								END,
				0) INTEREST_AMOUNT,
			COALESCE(CASE LOANTRAN."TRAN_DRCR"
					WHEN '.$var1.' THEN CAST(LOANTRAN."INTEREST_AMOUNT" AS FLOAT)
					ELSE 0
											END,
				0) RECPAY_INTEREST_AMOUNT,
			'.$var2.' MASTER_TYPE,
			SCHEMAST."S_NAME" SCHEME_NAME
		FROM LNMASTER
		INNER JOIN LOANTRAN ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
		INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.ID
		WHERE LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
			AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER)
			AND CAST(LOANTRAN."IS_INTEREST_ENTRY" AS INTEGER) <> 0
			AND CAST(LOANTRAN."TRAN_DATE" AS DATE) >= TO_DATE('.$stdate .','.$dd.')
			AND CAST(LOANTRAN."TRAN_DATE" AS DATE) <= TO_DATE('.$etdate.','.$dd.')
			AND "AC_CUSTID" = '.$AC_CUSTID.'
			AND LOANTRAN."BRANCH_CODE" = '.$branch_code.'
			AND LNMASTER."status" = '.$status.'
			AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
			AND LNMASTER."BRANCH_CODE" = '.$branch_code.'
	 
	 
		UNION ALL SELECT 
	 		LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			LNMASTER."AC_NO",
			LNMASTER."AC_NAME", LNMASTER."AC_CLOSEDT","S_NAME",  "S_APPL",
			'.$var4.' AC_REF_RECEIPTNO,
			LNMASTER."AC_EXPIRE_DATE",
			fn_get_WeakerMast_name(LNMASTER."IS_WEAKER") AS "IS_WEAKER",
	 		fn_get_cust_address(LNMASTER."AC_CUSTID") AS "AC_ADDR",
			LNMASTER."AC_CUSTID",
			LOANTRAN."TRAN_DATE",
 			0 "INTEREST_AMOUNT",
			(COALESCE(CASE LOANTRAN."TRAN_DRCR"
						WHEN '.$var.' THEN CAST(LOANTRAN."INTEREST_AMOUNT" AS FLOAT)
						ELSE 0
													END,
					0) + COALESCE(CASE LOANTRAN."TRAN_DRCR"
							WHEN '.$var.' THEN LOANTRAN."RECPAY_INT_AMOUNT"
							ELSE 0							END,
			0) + COALESCE(CASE LOANTRAN."TRAN_DRCR"
			WHEN '.$var.' THEN CAST(LOANTRAN."PENAL_INTEREST" AS FLOAT)
																	ELSE 0
			END,	0)) RECPAY_INTEREST_AMOUNT,
			'.$var4.' MASTER_TYPE,
			SCHEMAST."S_NAME" SCHEME_NAME
		FROM LOANTRAN
		INNER JOIN LNMASTER ON LNMASTER."BANKACNO" = LOANTRAN."TRAN_ACNO"
		INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.ID
		AND LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE"
		AND LNMASTER."AC_TYPE" = CAST(LOANTRAN."TRAN_ACTYPE" AS INTEGER)
		AND CAST(LOANTRAN."TRAN_DATE" AS DATE) >= TO_DATE('.$stdate .','.$dd.')
		AND CAST(LOANTRAN."TRAN_DATE" AS DATE) <= TO_DATE('.$etdate.',	'.$dd.')
		AND "AC_CUSTID" = '.$AC_CUSTID.'
		AND LOANTRAN."BRANCH_CODE" = '.$branch_code.'
		AND LNMASTER."status" = '.$status.'
		AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
		AND LNMASTER."BRANCH_CODE" = '.$branch_code.')TMP
WHERE (COALESCE(TMP.INTEREST_AMOUNT, 0) + COALESCE(TMP.RECPAY_INTEREST_AMOUNT, 0)) <> 0 ';



// if($close=='1'){
// $query.=' AND "AC_CLOSEDT" IS NULL ORDER BY TMP."S_APPL",TMP."AC_NO",CAST("TRAN_DATE" AS DATE)';
// }
// else{
// 	$query.=' AND "AC_CLOSEDT" IS noT NULL ORDER BY TMP."S_APPL",TMP."AC_NO",CAST("TRAN_DATE" AS DATE)';
// }



//  echo $query;
$query =pg_query($conn,$query);
$i = 0;
$T1 = 0;
$TOTAL_INTEREST_AMOUNT=0;
$type ='';
$SCHEME_T1=0;
$SCHEME_INTAMT =0;
// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($query)) {

		// $TOTAL_INTEREST_AMOUNT=$TOTAL_INTEREST_AMOUNT+$row['interest_amount'];
		// $T1=$T1+$row['recpay_interest_amount'];
	   
		if($type == ''){
			$type = $row['S_APPL'];
			
		}
		if($type == $row['S_APPL']){
			$SCHEME_T1 = $SCHEME_T1 + $row['recpay_interest_amount'];
			$SCHEME_INTAMT = $SCHEME_INTAMT + $row['interest_amount'];
		}else{
			$type = $row['S_APPL'];
			$SCHEME_INTAMT = 0;
			$SCHEME_T1 = 0;
			$SCHEME_INTAMT = $SCHEME_INTAMT + $row['interest_amount'];
			$SCHEME_T1 = $SCHEME_T1 + $row['recpay_interest_amount'];
		}



		// if($type == ''){
		// 	$type = $row['S_APPL'];
			
		// }
		// if($type == $row['S_APPL']){
		// 	$SCHEME_T1 = $SCHEME_T1 + $row['recpay_interest_amount'];
		// }else{
		// 	$type = $row['S_APPL'];
		// 	$SCHEME_T1 = 0;
		// 	$SCHEME_T1 = $SCHEME_T1 + $row['recpay_interest_amount'];
		// }



        $tmp = [

            $TOTAL_INTEREST_AMOUNT=$TOTAL_INTEREST_AMOUNT+$row['interest_amount'],
            $CUSTID_TOTAL=$TOTAL_INTEREST_AMOUNT,
            $GRAND_TOTAL=$TOTAL_INTEREST_AMOUNT,
            $T1=$T1+$row['recpay_interest_amount'],

            'AC_ACNOTYPE' => $row['S_APPL'].' '.$row['S_NAME'],
            'AC_TYPE' => $row['AC_TYPE'],
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_REF_RECEIPTNO' => $row['AC_REF_RECEIPTNO'],
            'AC_CUSTID' => $row['AC_CUSTID'],
            'TRAN_DATE' => $row['TRAN_DATE'],
            'interest_amount' =>sprintf("%.2f", ($row['interest_amount'] + 0.0)),
            'recpay_interest_amount' => sprintf("%.2f", ($row['recpay_interest_amount'] + 0.0)),
            'AC_EXPDT' => $row['AC_EXPDT'],
            'AC_ADDR'=>$row['AC_ADDR'],
            'master_type'=>$row['master_type'],
			'S_APPL'=>$row['S_APPL'],
            // 'LAST_EXEC_DATE' => $row['LAST_EXEC_DATE'],
            // 'SYSADD_LOGIN' => $row['SYSADD_LOGIN'],
            // 'ac_name' => $row['ac_name'],
            // 'UserCode' => $row['UserCode'],
            // 'NAME' => $row['NAME'],
            // 'startDate' => $startDate,
            // 'endDate' => $endDate,
         


            'branchName' => $branchName,
			// '$AC_CUSTID' => $AC_CUSTID,
            'branch_code' => $branch_code,
            'bankName' => $bankName,
            'stdate' => $stdate_1,
            'etdate' => $etdate_1,
            'GRAND_TOTAL'=>sprintf("%.2f", ($GRAND_TOTAL+ 0.0)),
            'CUSTID_TOTAL'=>sprintf("%.2f", ($CUSTID_TOTAL+ 0.0)),
            'TOTAL_INTEREST_AMOUNT'=>sprintf("%.2f", ($TOTAL_INTEREST_AMOUNT+ 0.0)),
            'T1'=>sprintf("%.2f", ($T1+ 0.0)),
'schemctotal'=>sprintf("%.2f", ($SCHEME_INTAMT + 0.0)),
'schemt1'=>sprintf("%.2f", ($SCHEME_T1 + 0.0)),
            // 'revoke' => $revoke,
            //  'bankName' => $bankName,
            //  '

        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();


// // echo $query;
// // print_r($data);
$config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
//}
?>
