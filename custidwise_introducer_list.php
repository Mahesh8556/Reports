<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/custidwise_introducer_list.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";


$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
// $S_APPL = $_GET['S_APPL'];
$MEMFROM = $_GET['MEMFROM'];
$MEMTO = $_GET['MEMTO'];
$date = $_GET['date'];
$AC_TYPE=$_GET['S_APPL'];
$branch_code = $_GET['branch_code'];

// $AC_OPDATE = $_GET['AC_OPDATE'];
// $AC_CLOSEDT = $_GET['AC_CLOSEDT'];



$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);
$branch_code = str_replace("'", "", $branch_code);
$date = str_replace("'", "", $date);
$status="'1'";


// $query='SELECT *
// FROM
// 	(SELECT SCHEMAST."S_APPL",
// 			SCHEMAST."S_NAME",
// 			"AC_TYPE",
// 			"AC_NO",
// 			"AC_NAME",
// 			"AC_OPDATE",
// 			SCHEME."S_APPL" AS INTROSCHEME,
// 			SCHEME."S_NAME" AS INTROSCHEMENAME,
// 			VWALLMASTER.AC_NAME "INTRONAME",
// 			"AC_INTRACNO",
// 			"AC_INTROID",
// 			"AC_CUSTID"
// 		FROM DPMASTER
// 		LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID = CAST("AC_INTROID" AS INTEGER)
// 		LEFT JOIN VWALLMASTER ON CAST(DPMASTER."AC_INTROBRANCH" AS INTEGER) = VWALLMASTER.BRANCH_CODE
// 		AND CAST(DPMASTER."AC_INTROID" AS INTEGER) = VWALLMASTER.AC_TYPE
// 		AND CAST(DPMASTER."AC_INTRACNO" AS INTEGER) = VWALLMASTER.ACNO
// 		LEFT JOIN SCHEMAST ON SCHEMAST.ID = DPMASTER."AC_TYPE"
// 		WHERE "AC_CUSTID" BETWEEN '.$MEMFROM.' AND '.$MEMTO.'
// 			AND "BRANCH_CODE" = '.$branch_code.'
// 			AND STATUS = '.$status.'
// 			AND "SYSCHNG_LOGIN" IS NOT NULL
// 		UNION SELECT SCHEMAST."S_APPL",
// 			SCHEMAST."S_NAME",
// 			"AC_TYPE",
// 			"AC_NO",
// 			"AC_NAME",
// 			"AC_OPDATE",
// 			SCHEME."S_APPL" AS INTROSCHEME,
// 			SCHEME."S_NAME" AS INTROSCHEMENAME,
// 			VWALLMASTER.AC_NAME "INTRONAME",
// 			"AC_INTRACNO",
// 			"AC_INTROID",
// 			"AC_CUSTID"
// 		FROM PGMASTER
// 		LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID = CAST("AC_INTROID" AS INTEGER)
// 		LEFT JOIN VWALLMASTER ON CAST(PGMASTER."AC_INTROBRANCH" AS INTEGER) = VWALLMASTER.BRANCH_CODE
// 		AND CAST(PGMASTER."AC_INTROID" AS INTEGER) = VWALLMASTER.AC_TYPE
// 		AND CAST(PGMASTER."AC_INTRACNO" AS INTEGER) = VWALLMASTER.ACNO
// 		LEFT JOIN SCHEMAST ON SCHEMAST.ID = PGMASTER."AC_TYPE"
// 		WHERE "AC_CUSTID" BETWEEN '.$MEMFROM.' AND '.$MEMTO.'
// 			AND "BRANCH_CODE" ='.$branch_code.'
// 			AND STATUS ='.$status.'
// 			AND "SYSCHNG_LOGIN" IS NOT NULL )MASTER
// WHERE "AC_INTRACNO" IS NOT NULL
// 	AND "AC_INTROID" IS NOT NULL
// 	AND "S_APPL" = '.$S_APPL.'
// 	AND CAST(MASTER."AC_OPDATE" AS DATE) 
//  BETWEEN CAST('.$stdate.' AS DATE)
//  AND CAST('.$etdate.' AS DATE) 

//  ORDER BY SCHEME."S_APPL",
//  "AC_NO"

$query='SELECT *
FROM
	(SELECT SCHEMAST."S_APPL",
			SCHEMAST."S_NAME",
			"AC_TYPE",
			"AC_NO",
			"AC_NAME",
			"AC_OPDATE",
			SCHEME."S_APPL" AS INTROSCHEME,
			SCHEME."S_NAME" AS INTROSCHEMENAME,
			VWALLMASTER.AC_NAME "INTRONAME",
			"AC_INTRACNO",
			CAST("AC_INTROID" AS INTEGER),
			"AC_CUSTID"
		FROM DPMASTER
		LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID = CAST("AC_INTROID" AS INTEGER)
		LEFT JOIN VWALLMASTER ON CAST(DPMASTER."AC_INTROBRANCH" AS INTEGER) = VWALLMASTER.BRANCH_CODE
		AND CAST(DPMASTER."AC_INTROID" AS INTEGER) = VWALLMASTER.AC_TYPE
		AND CAST(DPMASTER."AC_INTRACNO" AS INTEGER) = VWALLMASTER.ACNO
		LEFT JOIN SCHEMAST ON SCHEMAST.ID = DPMASTER."AC_TYPE"
		WHERE "AC_CUSTID" BETWEEN  '.$MEMFROM.' AND '.$MEMTO.'
			AND "BRANCH_CODE" = '.$branch_code.'
			AND STATUS = '.$status.'
	 		AND "AC_TYPE"='.$AC_TYPE.'
			AND "SYSCHNG_LOGIN" IS NOT NULL
		UNION SELECT SCHEMAST."S_APPL",
			SCHEMAST."S_NAME",
			"AC_TYPE",
			"AC_NO",
			"AC_NAME",
			"AC_OPDATE",
			SCHEME."S_APPL" AS INTROSCHEME,
			SCHEME."S_NAME" AS INTROSCHEMENAME,
			VWALLMASTER.AC_NAME "INTRONAME",
			"AC_INTRACNO",
			CAST("AC_INTROID" AS INTEGER),
			"AC_CUSTID"
		FROM PGMASTER
		LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID = CAST("AC_INTROID" AS INTEGER)
		LEFT JOIN VWALLMASTER ON CAST(PGMASTER."AC_INTROBRANCH" AS INTEGER) = VWALLMASTER.BRANCH_CODE
		AND CAST(PGMASTER."AC_INTROID" AS INTEGER) = VWALLMASTER.AC_TYPE
		AND CAST(PGMASTER."AC_INTRACNO" AS INTEGER) = VWALLMASTER.ACNO
		LEFT JOIN SCHEMAST ON SCHEMAST.ID = PGMASTER."AC_TYPE"
		WHERE "AC_CUSTID" BETWEEN '.$MEMFROM.' AND '.$MEMTO.' 
			AND "BRANCH_CODE" = '.$branch_code.'
			AND STATUS = '.$status.'
	 		AND "AC_TYPE" = '.$AC_TYPE.'
			AND "SYSCHNG_LOGIN" IS NOT NULL )MASTER
WHERE "AC_INTRACNO" IS NOT NULL
	AND "AC_INTROID" IS NOT NULL
ORDER BY MASTER."S_APPL","AC_NO"';
// echo $query;
$sql =pg_query($conn,$query);
$i = 0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
            
            'BANKACNO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'ac_introname'=>$row['INTRONAME'],
            


            'stdate' => $stdate_,
            'etdate' => $etdate_,
            'AC_CLOSEDT'=>$AC_CLOSEDT,
            'S_APPL'=>$S_APPL,
            'branchName' => $branchName,
            'branch_code' => $branch_code,
            'date' => $date,

            // 'revoke' => $revoke,
            'bankName' => $bankName,
            'MEMFROM' => $MEMFROM,
            'MEMTO' => $MEMTO,

        ];
        $data[$i] = $tmp;
        $i++;
    
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();

//print_r($data);
// echo $query;
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
// //}
?>
