<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CategoryWiseDetailBalList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName = $_GET['bankName'];
$startdate = $_GET['startdate'];
$enddate = $_GET['enddate'];
$sdate = $_GET['sdate'];
$amount = $_GET['amount'];
$branch = $_GET['branch'];
$Rdio = $_GET['Rdio'];
$acno=$_GET['actype'];
$actype=$_GET['acno'];
// $sdate = "'16/07/2021'";
// $startdate = "'17/07/2021'";

// $enddate = "'27/09/2021'";
$dateformate = "'DD/MM/YYYY'";
$bcode = "'1'";
$zero="'0'";
$one="'1'";



$query = 'SELECT "ACNOTYPE",CAST(SUBSTR("AC_NO",7,3) AS INTEGER) AS "S_APPL",
(SELECT "S_NAME" FROM SCHEMAST WHERE "S_APPL"=CAST(SUBSTR("AC_NO",7,3) AS INTEGER))AS "S_NAME",
	"AMOUNT_FROM",
	"AMOUNT_TO",
	"AC_ACNOTYPE",
	"AC_TYPE",
	"AC_NO",
	"AC_NAME",
	"BRANCH_CODE",0
	"BNAME",
CASE "LEDGER_BALANCE" WHEN '.$zero.' THEN '.$zero.' ELSE '.$one.' END "LEDGER_ACCOUNTS", "LEDGER_BALANCE"  
FROM SIZEWISEBALANCE, 
 ( 
     SELECT VWALLMASTER."ac_acnotype" as "AC_ACNOTYPE" , VWALLMASTER."ac_type" as "AC_TYPE", VWALLMASTER."ac_no" as "AC_NO", 
VWALLMASTER."ac_name" as "AC_NAME", LEDGERBALANCE(CAST(fn_get_product_code(VWALLMASTER."ac_type") AS CHARACTER VARYING) ,  VWALLMASTER."ac_no",'.$enddate.',1,1,0) AS "LEDGER_BALANCE" 
	 , OWNBRANCHMASTER."NAME" AS "BNAME", VWALLMASTER."branch_code" AS "BRANCH_CODE"
     FROM VWALLMASTER  
	 INNER JOIN OWNBRANCHMASTER ON VWALLMASTER."branch_code"= OWNBRANCHMASTER.id
           WHERE LEDGERBALANCE(CAST(fn_get_product_code(VWALLMASTER."ac_type") AS CHARACTER VARYING) ,  VWALLMASTER."ac_no",'.$enddate.',1,1,0) <> '.$zero.' 
           AND ( CAST(VWALLMASTER."ac_opdate" AS DATE) IS NULL OR CAST(VWALLMASTER."ac_opdate" AS DATE) <= CDATE('.$enddate.'))
           AND ( CAST(VWALLMASTER."ac_closedt" AS DATE) IS NULL OR CAST(VWALLMASTER."ac_closedt" AS DATE) > CDATE('.$enddate.'))
 AND VWALLMASTER."ac_acnotype" = '.$acno.' AND VWALLMASTER."ac_type" = '.$actype.' 
	 And CAST(VWALLMASTER."ac_opdate" AS DATE)  >=  To_DATE('.$startdate.','.$dateformate.')
	 And CAST(VWALLMASTER."ac_opdate" AS DATE) <=  To_DATE('.$enddate.','.$dateformate.') 
	 And (CAST(VWALLMASTER."ac_closedt" AS DATE) IS Null  OR   CAST(VWALLMASTER."ac_closedt" AS DATE) >  To_DATE('.$enddate.','.$dateformate.')) 
	 and VWALLMASTER."branch_code"='.$branch.'
 ) TMP  
	 WHERE SIZEWISEBALANCE."SLAB_TYPE" = '.$amount .' AND SIZEWISEBALANCE."ACNOTYPE" = '.$acno.'
  AND (ABS(cast(TMP."LEDGER_BALANCE" as float)) >= SIZEWISEBALANCE."AMOUNT_FROM" 
	   AND ABS(cast(TMP."LEDGER_BALANCE" as float)) <=  SIZEWISEBALANCE."AMOUNT_TO" )';
          
        //    echo $query;

        //string replacements
$startdate1 = str_replace("'", "", $startdate);

$enddate1 = str_replace("'", "", $enddate);

$sql =  pg_query($conn,$query);

$i = 0;

$ledgertotal = 0;
$schemledger=0;
$total=0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $ledgertotal = $ledgertotal + $row['LEDGER_BALANCE'];
    // $schemledger = $schemledger + $row['ledgerbalance'];
    
    

    if($type == ''){
        $type = $row['S_APPL']; 
    }
    if($type == $row['S_APPL']){
        $schemeledger = $schemeledger + $row['ledgerbalance'];
    }else{
        $type = $row['S_APPL'];
        $schemeledger = 0;
        $schemeledger = $schemeledger + $row['ledgerbalance'];
    }

    

    $tmp=[
        'ledgerbalance' => abs($row['LEDGER_BALANCE']),
        'ac_no'=> $row['AC_NO'],
        'ac_name' => $row['AC_NAME'],
        'S_NAME' => $row['S_NAME'],
        'bname' => $row['BNAME'],
        // 'ac_opdate' => $row['ac_opdate'],
        'S_APPL' => $row['S_APPL'],
        'amount_from' => $row['AMOUNT_FROM'],
        'amount_to' => $row['AMOUNT_TO'],
        'ac_type' => $row['ac_type'],
        'schemledger' => $schemeledger,
        'ledgertotal' => abs($ledgertotal),
        'AC_ACNOTYPE'=>$row['S_APPL'].' '.$row['S_NAME'],
        'bankName' => $bankName,
        'startdate' => $startdate1,
        'enddate' => $enddate1,
        'sdate' => $sdate,
        // 'scheme' => $scheme,
        'branch' => $branch,
        'Rdio' => $Rdio,
        'one' =>$one,
        'actype' =>$actype,
        'acno' =>$acno,
        'zero' =>$zero,
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
    
}   
?>


<!-- Balance Certificate 

SELECT 
ledgerbalance(cast (SCHEMAST."S_APPL" as character varying),
VWALLMASTER."ac_no",'10/08/2022',0,1)as ledgerbalance,
VWALLMASTER."ac_name",VWALLMASTER."ac_no",VWALLMASTER."ac_acnotype"
FROM VWALLMASTER
INNER JOIN SCHEMAST ON 
VWALLMASTER."ac_type" = SCHEMAST."id"
where cast(VWALLMASTER."ac_opdate" as date) = '12/08/2022'::date 
and vwallmaster."ac_acnotype" = 'TD'
and cast(ac_no as bigint) between 101101202103224 and 101101202103226

-->

<!-- SELECT 
sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",sizewisebalance."count"
FROM 
( 
    SELECT count(cast(vwallmaster."ac_no" as bigint)) as count,
    ledgerbalance(cast (SCHEMAST."S_APPL" as character varying),
    vwallmaster."ac_no",'12/08/2022',0,1)as ledgerbalance,
    vwallmaster."ac_name" as name,sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",
    sizewisebalance."SLAB_TYPE"
    FROM vwallmaster
    INNER JOIN schemast ON vwallmaster."ac_type" = schemast."id"
    INNER JOIN sizewisebalance ON vwallmaster."ac_acnotype" = sizewisebalance."ACNOTYPE"
    WHERE cast(vwallmaster."ac_opdate" as date) 
    between to_date('17/07/2021','DD/MM/YYYY') and to_date('27/09/2021','DD/MM/YYYY')
    and vwallmaster."branch_code" = 1
    GROUP BY sizewisebalance."AMOUNT_FROM",sizewisebalance."AMOUNT_TO",vwallmaster."ac_name",
    sizewisebalance."SLAB_TYPE",SCHEMAST."S_APPL",vwallmaster."ac_no"
)
sizewisebalance 
WHERE sizewisebalance."SLAB_TYPE" = 'AMOUNT' -->