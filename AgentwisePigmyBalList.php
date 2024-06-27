<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/AgentwisePigmyBalList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
// echo $date;
$date = $_GET['date'];
$branch = $_GET['branch'];
$scheme = $_GET['scheme'];
$schemeAccountNo = $_GET['schemeAccountNo'];

$bankName = str_replace("'", "", $bankName);
$date_ = str_replace("'", "", $date);
// $branch = str_replace("'", "", $branchName);
$scheme = str_replace("'", "", $scheme);
$schemeAccountNo = str_replace("'", "", $schemeAccountNo);
$agentName = '';
$dateformate = "'DD/MM/YYYY'";
$o="'0'";
$D ="'D'";
$status = "'1'";




// $query = 'select pgmaster."BANKACNO",pgmaster."AC_NAME",CAST(pigmytran."TRAN_AMOUNT" as float),pigmytran."AGENT_ACNO" from pigmytran
//         inner join pgmaster on pgmaster."BANKACNO" = pigmytran."TRAN_ACNO"
//         where CAST(pigmytran."TRAN_DATE" as date) = CAST('.$date.' as date)
//         and pigmytran."BRANCH_CODE"='.$branch.'

//         UNION ALL

//         select pgmaster."BANKACNO",pgmaster."AC_NAME",pigmychartmaster."TRAN_AMOUNT",pigmychart."AGENT_BANKACNO" "AGENT_ACNO" from pigmychart
//         inner join pigmychartmaster on pigmychartmaster."PIGMYCHARTID" = pigmychart.id 
//         inner join pgmaster on pgmaster."BANKACNO" = pigmychartmaster."TRAN_BANKACNO"
//         where CAST(pigmychart."TRAN_DATE" as date) = CAST('.$date.' as date) 
//         AND pigmychart."BRANCH_CODE" = '.$branch.'';      

$query = '	SELECT PGMASTER."AC_ACNOTYPE", PGMASTER."AC_TYPE", PGMASTER."AC_NO", PGMASTER."AC_NAME" AC_NAME 
            , PGMASTER."AGENT_ACTYPE" , PGMASTER."AGENT_ACNO",PGMASTER."BRANCH_CODE" 
            , VWTMPZBALANCEPIGMY.CLOSING_BALANCE , DPMASTER."AC_NAME" AGENT_NAME ,VWTMPZBALANCEPIGMY."AC_CLOSEDT" 
            FROM  PGMASTER  
            LEFT OUTER JOIN DPMASTER ON CAST(PGMASTER."AGENT_ACNO" AS BIGINT) = DPMASTER."AC_NO"  
            LEFT OUTER JOIN (
            SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_OPDATE", "AC_CLOSEDT" 
            , (COALESCE(CASE "AC_OP_CD"  WHEN '.$D.' THEN  CAST("AC_OP_BAL" AS FLOAT)  ELSE (-1) * CAST("AC_OP_BAL" AS FLOAT) END,0) + 
            COALESCE(PIGMYTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE  
            ,( COALESCE(CASE PGMASTER."AC_OP_CD"  WHEN '.$D.' THEN  CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT)  ELSE (-1) * CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT) END,0) 
            + COALESCE(PIGMYTRAN.RECPAY_INT_AMOUNT,0) + COALESCE(DAILYTRAN.RECPAY_INT_AMOUNT,0)) RECPAY_INT_AMOUNT 
            FROM PGMASTER 
            LEFT OUTER JOIN(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
            COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) TRAN_AMOUNT, 
            SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("RECPAY_INT_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END) RECPAY_INT_AMOUNT 
                FROM PIGMYTRAN WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$date.' AS DATE) AND "BRANCH_CODE" = '.$branch.'
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
            ) PIGMYTRAN ON PGMASTER."BANKACNO" =  PIGMYTRAN."TRAN_ACNO" 
            LEFT OUTER JOIN
            (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$D.' THEN  CAST("TRAN_AMOUNT" AS FLOAT)  ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END),0) DAILY_AMOUNT, 
            SUM(CASE "TRAN_DRCR" WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT) ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT) END) RECPAY_INT_AMOUNT 
                From DAILYTRAN WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$date.' AS DATE)
                AND "TRAN_STATUS" = '.$status.' 
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
            ) DAILYTRAN  ON PGMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
            Where ((PGMASTER."AC_OPDATE" IS NULL) OR (CAST(PGMASTER."AC_OPDATE" AS DATE) <= CAST('.$date.' AS DATE)))
            AND ((PGMASTER."AC_CLOSEDT" IS NULL) OR (CAST(PGMASTER."AC_CLOSEDT" AS DATE) > CAST('.$date.' AS DATE)))
            AND PGMASTER."BRANCH_CODE" = '.$branch.'
            )VWTMPZBALANCEPIGMY ON PGMASTER."AC_NO" = VWTMPZBALANCEPIGMY."AC_NO" 

            WHERE 
            CAST(PGMASTER."AGENT_ACTYPE" AS integer)  = DPMASTER."AC_TYPE"
            AND VWTMPZBALANCEPIGMY."AC_TYPE" = PGMASTER."AC_TYPE"
            AND PGMASTER."AC_ACNOTYPE" = VWTMPZBALANCEPIGMY."AC_ACNOTYPE"
            AND VWTMPZBALANCEPIGMY.CLOSING_BALANCE <> 0  
            AND ( PGMASTER."AC_OPDATE" IS NULL OR CAST(PGMASTER."AC_OPDATE" as date) <= CAST('.$date.' as date)) 
            AND ( PGMASTER."AC_CLOSEDT" IS NULL OR CAST(PGMASTER."AC_CLOSEDT" as date) > CAST('.$date.' as date))
            AND DPMASTER."BRANCH_CODE" = 1
            order by "AGENT_ACNO" ASC';

        echo $query;

$sql   = pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    while($row = pg_fetch_assoc($sql)){

        if($row['BRANCH_CODE'] == $branch){
        $type = '';
        if($row['closing_balance'] >0){
            $type = 'Dr';
        }else{
            $type = 'Cr';
        }

        $tmp=[
            'SR_NO' => $i + 1,
            'AGENT_ACNO' => $row['AC_NO'],
            'AC_NAME'=> $row['ac_name'],
            'AC_CLOSEDT' => $row['AC_CLOSEDT']==null?'-':$row['AC_CLOSEDT'],
            'NAME'=>'-',
            'ledger_balance'=> sprintf("%.2f", (abs($row['closing_balance']) + 0.0)).' '.$type,
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'agentname' => $row['agent_name'],
            'grandledgrtot'=> $GRAND_TOTAL,
            'schmledgtot'=> $GROUP_TOTAL,
            'agentwisetot'=> $AGNTSCHM_TOTAL,

            'bankName' => $bankName,
            'date_' => $date_,
            'branch' => $branch,
            'scheme' => $scheme,
            'schemeAccountNo' => $schemeAccountNo,
            'date' => $date,
        ];
        $data[$i]=$tmp;
        $i++;
        }
    }
// ob_end_clean();

// $config = ['driver'=>'array','data'=>$data];

// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');

}
    
?>  
    