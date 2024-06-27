<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/pigmymaturedaclist.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$var = "'D'";
$AC_ACNOTYPE ="'PG'";
$AC_TYPE = "'9'";

$branchName =$_GET['branchName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$branch_code = $_GET['branch_code'];
$bankName = $_GET['bankName'];
$count;
$status="'1'";      

// $var = $_GET['var'];



$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);


$query='SELECT PGMASTER."AC_ACNOTYPE",
PGMASTER."AC_TYPE",
PGMASTER."AC_NO",
PGMASTER."AC_NAME",
PGMASTER."AGENT_ACTYPE",
PGMASTER."AGENT_ACNO",
PGMASTER."AC_OPDATE",
PGMASTER."AC_EXPDT",
SCHEMAST."S_NAME" AS SCHEME_NAME,
DPMASTER."AC_NAME" AS AGENTNAME,
DPMASTER.ID AS AGENTID,
VWTMPZBALANCEPIGMY.CLOSING_BALANCE,
SCHEMASTONE."S_NAME"
FROM PGMASTER
LEFT OUTER JOIN
(SELECT PGMASTER."AC_ACNOTYPE",
        PGMASTER."AC_TYPE",
        PGMASTER."BANKACNO" "AC_NO",
        PGMASTER."AC_OPDATE",
        PGMASTER."AC_CLOSEDT",
        CAST(COALESCE(CASE PGMASTER."AC_OP_CD"	WHEN '.$var.' THEN CAST(PGMASTER."AC_OP_BAL" AS FLOAT)
        ELSE (-1) * CAST(PGMASTER."AC_OP_BAL" AS FLOAT)	END,0) 
             + COALESCE(CAST(TRAN_AMOUNT AS FLOAT),	0) + COALESCE(DAILY_AMOUNT,	0) AS float)CLOSING_BALANCE,
        COALESCE(CASE PGMASTER."AC_OP_CD" WHEN '.$var.' THEN CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT)
        ELSE ((-1) * CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT))END, 0) 
         + COALESCE(CAST(PIGMYTRAN."RECPAY_INT_AMOUNT" AS INTEGER),0) 
         + COALESCE(CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS INTEGER),0)RECPAY_INT_AMOUNT
    FROM PGMASTER
    LEFT OUTER JOIN
        (SELECT PIGMYTRAN."TRAN_ACNOTYPE",
                PIGMYTRAN."TRAN_ACTYPE",
                PIGMYTRAN."TRAN_ACNO",
                PIGMYTRAN."RECPAY_INT_AMOUNT",
                COALESCE(SUM(CASE PIGMYTRAN."TRAN_DRCR" WHEN '.$var.' THEN CAST(PIGMYTRAN."TRAN_AMOUNT" AS FLOAT) 
                ELSE((-1) * CAST(PIGMYTRAN."TRAN_AMOUNT" AS FLOAT))	END),0)TRAN_AMOUNT,
                SUM(CASE PIGMYTRAN."TRAN_DRCR"	WHEN '.$var.' THEN CAST(PIGMYTRAN."RECPAY_INT_AMOUNT" AS FLOAT)
                ELSE ((-1) * CAST(PIGMYTRAN."RECPAY_INT_AMOUNT"AS FLOAT))END)RECPAY_INT_AMOUNT
            FROM PIGMYTRAN
            WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS date)
                AND "BRANCH_CODE" = '.$branch_code.'
            GROUP BY "TRAN_ACNOTYPE","TRAN_ACTYPE",	"TRAN_ACNO",
                PIGMYTRAN."RECPAY_INT_AMOUNT")PIGMYTRAN ON PGMASTER."BANKACNO" = PIGMYTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
        (SELECT DAILYTRAN."TRAN_ACNOTYPE",
                DAILYTRAN."TRAN_ACTYPE",
                DAILYTRAN."TRAN_ACNO",
                DAILYTRAN."RECPAY_INT_AMOUNT",
                COALESCE(SUM(CASE DAILYTRAN."TRAN_DRCR"	WHEN '.$etdate.' THEN CAST(DAILYTRAN."TRAN_AMOUNT" AS FLOAT) 
                             ELSE((-1) * CAST(DAILYTRAN."TRAN_AMOUNT" AS FLOAT))END),	0)DAILY_AMOUNT,
                SUM(CASE DAILYTRAN."TRAN_DRCR" WHEN '.$etdate.' THEN CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS FLOAT) 
                    ELSE((-1) * CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS FLOAT))END)RECPAY_INT_AMOUNT
            FROM DAILYTRAN
            WHERE CAST(DAILYTRAN."TRAN_DATE" AS DATE) <= CAST('.$etdate.' AS date)
                AND CAST(DAILYTRAN."TRAN_STATUS" AS FLOAT) = '.$status.'
                AND "BRANCH_CODE" = '.$branch_code.'
            GROUP BY DAILYTRAN."TRAN_ACNOTYPE",
                DAILYTRAN."TRAN_ACTYPE",
                DAILYTRAN."TRAN_ACNO",
                DAILYTRAN."RECPAY_INT_AMOUNT")DAILYTRAN ON PGMASTER."BANKACNO" = DAILYTRAN."TRAN_ACNO"
    WHERE (PGMASTER."AC_OPDATE" IS NULL OR CAST(PGMASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.' AS DATE))
        AND (PGMASTER."AC_CLOSEDT" IS NULL
                            OR CAST(PGMASTER."AC_CLOSEDT" AS DATE) > CAST('.$etdate.' AS DATE))
        AND PGMASTER."status" = '.$status.'
        AND PGMASTER."SYSCHNG_LOGIN" IS NOT NULL
        AND PGMASTER."BRANCH_CODE" = '.$branch_code.' )VWTMPZBALANCEPIGMY ON PGMASTER."BANKACNO" = VWTMPZBALANCEPIGMY."AC_NO"
LEFT OUTER JOIN DPMASTER ON PGMASTER."AGENT_ACNO" = DPMASTER."BANKACNO"
LEFT OUTER JOIN SCHEMAST ON PGMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
LEFT OUTER JOIN SCHEMAST AS SCHEMASTONE ON SCHEMASTONE.ID = CAST(PGMASTER."AGENT_ACTYPE" AS integer)
WHERE VWTMPZBALANCEPIGMY.CLOSING_BALANCE <> 0
AND PGMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.'
AND PGMASTER."AC_TYPE" = '.$AC_TYPE.'
AND PGMASTER."BRANCH_CODE" = '.$branch_code.'
AND CAST(PGMASTER."AC_EXPDT" AS DATE) >= CAST('.$stdate.' AS DATE)
AND CAST(PGMASTER."AC_EXPDT" AS DATE) <= CAST('.$etdate.' AS DATE)
GROUP BY PGMASTER."AC_ACNOTYPE",
PGMASTER."AC_TYPE",
PGMASTER."AC_NO",
PGMASTER."AC_NAME",
PGMASTER."AGENT_ACTYPE",
PGMASTER."AGENT_ACNO",
PGMASTER."AC_OPDATE",
PGMASTER."AC_EXPDT",
SCHEMAST."S_NAME",
DPMASTER."AC_NAME",
DPMASTER.ID,
VWTMPZBALANCEPIGMY.CLOSING_BALANCE,
SCHEMASTONE."S_NAME"
ORDER BY "AC_NO"';


// echo $query;


    
$sql =pg_query($conn,$query);
$i = 0;
$gtotal = 0;





if (pg_num_rows($sql) == 0) {
   include 'errormsg.html';
}else{




while ($row = pg_fetch_assoc($sql)) {
    //echo $row;
    if ($row['closing_balance'] < 0) {
        $netType = 'Dr';
    } else {
        $netType = 'Cr';
    }
    if ($row['gtotal'] < 0) {
        $netType = 'Dr';
    } else {
        $netType = 'Cr';
    }

    $gtotal=  $gtotal+ $row['closing_balance'];
    if(isset($varsd)){
       if($varsd == $row['AGENT_ACNO']){
           $sc[] = abs($row['closing_balance']);
           $sumVar += abs($row['closing_balance']);
       }
       else{
           //empty array before adding new
           $sumVar=0;
           $sc = array_diff( $sc, $sc);
           $varsd = $row['AGENT_ACNO'];
           $sc[] = abs($row['closing_balance']);
           $sumVar += abs($row['closing_balance']);
       }
   }else{
       $sumVar=0;
       $varsd = $row['AGENT_ACNO'];
       $sc[] =abs( $row['closing_balance']);
       $sumVar += abs($row['closing_balance']);
   }
   $result[$varsd] = $sc;
   $sumArray[$varsd] = $sumVar;

    
    

    $tmp = [
            'AC_ACNOTYPE' => $row['$AC_ACNOTYPE'],
            'AC_TYPE' => $row['$AC_TYPE'],
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AGENT_ACTYPE' => $row['AGENT_ACTYPE'],
            'AGENT_ACNO' => $row['AGENT_ACNO'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_EXPDT' => $row['AC_EXPDT'],
            'S_NAME'=>$row['S_NAME'],
            'scheme_name' => $row['scheme_name'],
            'agentname' => $row['agentname'],
            'closing_balance' =>sprintf("%.2f", (abs($row['closing_balance']))).' '.$netType,  
            
            


            'branch_code' => $branch_code,
            'stdate' => $stdate_,
            'etdate' => $etdate_,
            'scheme_code'=>$scheme_code,
            'var'=>$var,
            'count'=>$count,
            // 'field1' =>$field1,
            // 'field2' =>$GRAND_TOTAL,
            // 'GRAND_TOTAL' =>$GRAND_TOTAL,
            'gtotal'=>sprintf("%.2f", (abs($gtotal))),
            'branchName' => $branchName,
            'bankName'=>$bankName,
            // 'field1'=>$sumArray[$varsd],
            'field1'=> sprintf("%.2f", ($sumArray[$varsd] + 0.0)),

            // 'revoke' => $revoke,
            // 'bankName' => $bankName,

        ];
        // $scnm=$row["S_APPL"];
        $data[$i] = $tmp;
        $i++;
    $count++;
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}

ob_end_clean();

            // echo $scnm;
//print_r($data);
// echo $query;
// print_r($result);
//print_r($sumArray ); 
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
}
?>
