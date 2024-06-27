<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/goldsilverregister.jrxml';

$data = [];
$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$dd="'DD/MM/YYYY'";
$bankName = $_GET['bankName'];
$branchName = $_GET['branchName'];
$branch_code = $_GET['branch_code'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$AC_TYPE = $_GET['AC_TYPE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];

// $date = $_GET['date'];
$FLAG1 = $_GET['FLAG1'];
$STATUS = "'S'";
$STATUS1 = "'R'";
$ZERO = "'0'";
$ONE="'1'";

//$FLAG2 = $_GET['FLAG2'];
//$TRAN_STATUS= $_GET['TRAN_STATUS'];
//$sr_no = $j;




$query = '';
$bankName = str_replace("'", "", $bankName);
$stdate_1 = str_replace("'", "", $stdate);
$etdate_1 = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);
// $date = str_replace("'", "", $date);
if($FLAG1==1)
{
    $subreturn = "Submission";
}else{
    $subreturn = "Return"; 
}


 if($FLAG1 == 1) 
 {$query .= 'SELECT GOLDSILVER."TRAN_STATUS",
	GOLDSILVER."AC_ACNOTYPE",
	GOLDSILVER."AC_TYPE",
	GOLDSILVER."AC_NO",
	LNMASTER."AC_NAME",
	LNMASTER."AC_SANCTION_AMOUNT",
	SCHEMAST."S_NAME",
	GOLDSILVER."RATE",
	GOLDSILVER.STATUS,
	GOLDSILVER.TRAN_DATE,
	GOLDSILVER."ITEM_TYPE",
	GOLDSILVER."TOTAL_WEIGHT_GMS",
	GOLDSILVER."CLEAR_WEIGHT_GMS",
	GOLDSILVER."TOTAL_VALUE",
	GOLDSILVER."BAG_RECEIPT_NO"
FROM
	(SELECT "AC_ACNOTYPE",
			"AC_TYPE",
			"AC_NO",
			"SUBMISSION_DATE" TRAN_DATE,
			"RATE",
			'.$STATUS.' STATUS,
			"ITEM_TYPE",
			"TOTAL_WEIGHT_GMS",
			"CLEAR_WEIGHT_GMS",
			"TOTAL_VALUE",
			"BAG_RECEIPT_NO",
			GOLDSILVER."TRAN_STATUS"
		FROM GOLDSILVER
		WHERE CAST("TRAN_STATUS" AS INTEGER) = '.$ZERO.'
			AND "BRANCH_CODE" = '.$branch_code.'
	 		AND GOLDSILVER."AC_TYPE" = '.$AC_TYPE.'
		UNION ALL SELECT "AC_ACNOTYPE",
			"AC_TYPE",
			"AC_NO",
			"RETURN_DATE" TRAN_DATE,
			"RATE",
			'.$STATUS.' STATUS,
			"ITEM_TYPE",
			"TOTAL_WEIGHT_GMS",
			"CLEAR_WEIGHT_GMS",
			"TOTAL_VALUE",
			"BAG_RECEIPT_NO",
			GOLDSILVER."TRAN_STATUS"
		FROM GOLDSILVER
		WHERE CAST("TRAN_STATUS" AS INTEGER) = '.$ONE.'
	 		AND GOLDSILVER."AC_TYPE" = '.$AC_TYPE.'
			AND "BRANCH_CODE" = '.$branch_code.'
			AND "RETURN_DATE" IS NOT NULL ) GOLDSILVER
INNER JOIN LNMASTER ON GOLDSILVER."AC_NO" = LNMASTER."BANKACNO"
LEFT OUTER JOIN SCHEMAST ON GOLDSILVER."AC_TYPE" = SCHEMAST.ID
WHERE GOLDSILVER."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
	AND GOLDSILVER."AC_TYPE" = LNMASTER."AC_TYPE"
	AND GOLDSILVER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
	AND GOLDSILVER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
	AND LNMASTER."AC_TYPE" = '.$AC_TYPE.'
	AND LNMASTER."status" = 1
	AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
	AND CAST(GOLDSILVER.TRAN_DATE AS DATE) >= TO_DATE('.$stdate.','.$dd.')
	OR CAST(GOLDSILVER.TRAN_DATE AS DATE) <= TO_DATE('.$etdate.','.$dd.')
	AND GOLDSILVER.STATUS = '.$STATUS.'';
	
 }
else
{
$query.='SELECT GOLDSILVER."AC_ACNOTYPE",
GOLDSILVER."AC_TYPE",
GOLDSILVER."AC_NO",
LNMASTER."AC_NAME",
LNMASTER."AC_SANCTION_AMOUNT",
SCHEMAST."S_NAME" ,
GOLDSILVER."RATE",
GOLDSILVER.STATUS,
GOLDSILVER.TRAN_DATE,
GOLDSILVER."ITEM_TYPE",
GOLDSILVER."TOTAL_WEIGHT_GMS",
GOLDSILVER."CLEAR_WEIGHT_GMS",
GOLDSILVER."TOTAL_VALUE",
GOLDSILVER."BAG_RECEIPT_NO"
FROM
(SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO",
        "SUBMISSION_DATE" TRAN_DATE,
        "RATE",
        '.$STATUS.' STATUS ,
        "ITEM_TYPE",
        "TOTAL_WEIGHT_GMS",
        "CLEAR_WEIGHT_GMS",
        "TOTAL_VALUE",
        "BAG_RECEIPT_NO"
    FROM GOLDSILVER
    WHERE CAST("TRAN_STATUS" AS INTEGER) = '.$ZERO.' AND "BRANCH_CODE"='.$branch_code.'
    UNION ALL SELECT "AC_ACNOTYPE",
        "AC_TYPE",
        "AC_NO",
        "RETURN_DATE" TRAN_DATE,
        "RATE",
        '.$STATUS1.' STATUS ,
        "ITEM_TYPE",
        "TOTAL_WEIGHT_GMS" ,
        "CLEAR_WEIGHT_GMS",
        "TOTAL_VALUE",
        "BAG_RECEIPT_NO"
    FROM GOLDSILVER
    WHERE "TRAN_STATUS" ='.$ONE.' AND "BRANCH_CODE"='.$branch_code.'
        AND "RETURN_DATE" IS NOT NULL ) GOLDSILVER
INNER JOIN LNMASTER ON GOLDSILVER."AC_NO"  = LNMASTER."BANKACNO"
LEFT OUTER JOIN SCHEMAST ON GOLDSILVER."AC_TYPE" = SCHEMAST.ID 
WHERE GOLDSILVER."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
AND GOLDSILVER."AC_TYPE" = LNMASTER."AC_TYPE"
AND GOLDSILVER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
AND GOLDSILVER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND GOLDSILVER."AC_TYPE" = '.$AC_TYPE.'
AND LNMASTER."status"=1 and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
AND CAST(GOLDSILVER.TRAN_DATE AS DATE) >= CAST('.$stdate.' AS DATE)
AND CAST(GOLDSILVER.TRAN_DATE AS DATE) <= CAST('.$etdate.' AS DATE)
AND GOLDSILVER.STATUS = '.$STATUS1.'';
}

//  echo $query;
$sql =pg_query($conn,$query);
$i = 0;
//$j = 1;
//$sr_no=$j;
$total_value;
$sub_total;
$grandtotal;
$tot=0;
$gtot=0;
$stot=0;
$stot1=0;
$twgms=0;
$cwgms=0;
// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
         $total_value = $row['TOTAL_WEIGHT_GMS'] * $row['RATE'];
         $sub_total = $total_value;
        //  $grandtotal = $total + $sub_total;
         $tot=$tot+ $row['TOTAL_VALUE'];
        //  $gtot=$gtot+ $row['TOTAL_VALUE'];
         $stot=$stot+ $row['AC_SANCTION_AMOUNT'];
         $stot1=$stot1+ $row['AC_SANCTION_AMOUNT'];

         $twgms=$twgms+ $row['TOTAL_WEIGHT_GMS'];
         $cwgms=$cwgms+ $row['CLEAR_WEIGHT_GMS'];



        $tmp = [
            'SR_NO'=>$row['SR_NO'],
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_SANCTION_AMOUNT' => sprintf("%.2f", ($row['AC_SANCTION_AMOUNT'] + 0.0)),
            // 'S_NAME' => $row['S_NAME'],
            'RATE' =>sprintf("%.2f", ($row['RATE'] + 0.0)),
            'status' => $row['status'],
            'TOTAL_WEIGHT_GMS' =>sprintf("%.3f", ($row['TOTAL_WEIGHT_GMS'] + 0.0)),
            'CLEAR_WEIGHT_GMS' => sprintf("%.3f", ($row['CLEAR_WEIGHT_GMS'] + 0.0)),
            'TOTAL_VALUE' => $row['TOTAL_VALUE'],
            'BAG_RECEIPT_NO' => $row['BAG_RECEIPT_NO'],
            'tran_date' => $row['tran_date'],
            'ITEM_TYPE' => $row['ITEM_TYPE'],
            
         


            'branchName' => $branchName,
            'stdate' => $stdate_1,
            'etdate' => $etdate_1,
            'bankName' => $bankName,
            'FLAG1'=> $FLAG1,
            'FLAG2'=> $FLAG2,
            'sub_total' => sprintf("%.2f", ($sub_total + 0.0)),
            'grandtotal' => sprintf("%.2f", ($grandtotal + 0.0)),
            'STATUS'=>$STATUS,
            'STATUS1'=>$STATUS1,
            'subreturn'=>$subreturn,
            'branch_code'=>$branch_code,
            'tot'=> sprintf("%.2f", ($tot + 0.0)),
            'gtot'=> sprintf("%.2f", ($gtot + 0.0)),
            'stot'=> sprintf("%.2f", ($stot + 0.0)),
            'stot1'=> sprintf("%.2f", ($stot1 + 0.0)),
            'twgms'=> sprintf("%.2f", ($twgms + 0.0)),
            'cwgms'=> sprintf("%.2f", ($cwgms + 0.0)),
           
            // 'date'=>$date,
         
           
        ];
        $data[$i] = $tmp;
        $i++;
  $j++;
        // echo '<pre>';
        // print_r($tmp);
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
    
    
//}
?>
