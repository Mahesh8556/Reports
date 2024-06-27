<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ln.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');


//connect pgAdmin database connection 
// $conn = pg_connect("host='.$BRANCH_CODE.'27. '.$zero.'. '.$zero.'.'.$BRANCH_CODE.' dbname=CBSBhairavnath user=postgres password=admin");

// $date = "''.$BRANCH_CODE.'2/ '.$zero.'2/2 '.$zero.'2'.$BRANCH_CODE.''";
// $status ="'false'";

//get data from table
// $query=' SELECT  owndeposit."AC_ACNOTYPE",owndeposit."AC_TYPE",owndeposit."AC_NO",
//          lnmaster."AC_NAME",owndeposit."DEPO_AC_TYPE",owndeposit."DEPO_AC_NO",
//          lnmaster."AC_NAME",owndeposit."MATURITY_DATE",owndeposit."RECEIPT_NO",
//          owndeposit."DEPOSIT_AMT",owndeposit."MARGIN",owndeposit."REMARK"
//          from owndeposit
//          inner join lnmaster on owndeposit."AC_NO" =lnmaster."AC_NO"
//          where owndeposit."IS_LIEN_MARK_CLEAR"='.$status.'
//          AND cast(lnmaster."AC_OPDATE" as date) <= '.$date.'::date  ';
         
//echo $query;
$Date=$_GET['Date'];
$branchname=$_GET['branchname'];
$scheme = $_GET['scheme'];
$code = "'LN'";
$zero="'0'";
$para = "''";
$BRANCH_CODE  = $_GET['BRANCH_CODE'];
$status="1";
$Bankname = $_GET['Bankname'];


$Bankname1 = str_replace("'", "", $Bankname);
$branchname1 = str_replace("'", "", $branchname);
$Date1 = str_replace("'", "", $Date);


$query=
'SELECT 
OWNDEPOSIT."AC_ACNOTYPE",
OWNDEPOSIT."AC_TYPE",
LNMASTER."AC_NO",
OWNDEPOSIT."SUBMISSION_DATE",
OWNDEPOSIT."AC_ACNOTYPE",
OWNDEPOSIT."DEPO_AC_TYPE",
OWNDEPOSIT."DEPO_AC_NO",
DPMASTER."AC_NO"  as  NO,
OWNDEPOSIT."MATURITY_DATE",
OWNDEPOSIT."RECEIPT_NO",
OWNDEPOSIT."DEPOSIT_AMT",
OWNDEPOSIT."MARGIN",
OWNDEPOSIT."REMARK",
LNMASTER."AC_NAME",
VWALLMASTER.AC_NAME as namevl,
SCHEMAST."S_APPL" AS DPSCHEME,
    SCHEMAST."S_NAME" AS DPNAME,
    SCHEME."S_APPL",
    SCHEME."S_NAME"
FROM LNMASTER,
OWNDEPOSIT
LEFT OUTER JOIN VWALLMASTER ON (OWNDEPOSIT."AC_ACNOTYPE" = VWALLMASTER.AC_ACNOTYPE
    AND OWNDEPOSIT."DEPO_AC_TYPE" = VWALLMASTER.AC_TYPE
    AND OWNDEPOSIT."DEPO_AC_NO" = VWALLMASTER.AC_NO)
    LEFT JOIN SCHEMAST ON SCHEMAST.ID=OWNDEPOSIT."DEPO_AC_TYPE"
    LEFT JOIN SCHEMAST AS SCHEME ON SCHEME.ID=  OWNDEPOSIT."AC_TYPE"
    LEFT JOIN DPMASTER ON DPMASTER."BANKACNO"=OWNDEPOSIT."DEPO_AC_NO"
WHERE OWNDEPOSIT."AC_NO" = LNMASTER."BANKACNO"
AND OWNDEPOSIT."AC_ACNOTYPE" = LNMASTER."AC_ACNOTYPE"
AND OWNDEPOSIT."AC_TYPE" = LNMASTER."AC_TYPE"
AND CAST(OWNDEPOSIT."IS_LIEN_MARK_CLEAR" AS integer) = '.$zero.'
AND (LNMASTER."AC_OPDATE" IS NULL
                    OR CAST(LNMASTER."AC_OPDATE" AS date) <= DATE('.$Date.'))
AND (LNMASTER."AC_CLOSEDT" IS NULL
                    OR CAST(LNMASTER."AC_CLOSEDT" AS date) > DATE('.$Date.'))
AND OWNDEPOSIT."AC_ACNOTYPE" = '.$code.'
AND OWNDEPOSIT."AC_TYPE" = '.$scheme.'
AND CAST(OWNDEPOSIT."SUBMISSION_DATE" AS date) <= DATE('.$Date.')
AND LNMASTER."status"= '.$status.' and LNMASTER."SYSCHNG_LOGIN" IS NOT NULL AND LNMASTER."BRANCH_CODE"='.$BRANCH_CODE.'';



// echo $query;


$sql =  pg_query($conn,$query);
//  echo $query;
$i =  0 ;
$GRAND_TOTAL =  0;

$type = '';
while($row = pg_fetch_assoc($sql)){
        // grand-total
        $GRAND_TOTAL = $GRAND_TOTAL + $row['DEPOSIT_AMT'];
        // group-total 
       
    $tmp=[
        'SUBMISSION_DATE' => $row['SUBMISSION_DATE'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME' => $row['AC_NAME'],
        'lnscheme' => $row["S_APPL"].' '. $row['S_NAME'],
        'dpscheme' => $row["dpscheme"].' '. $row['dpname'],
        'DEPO_AC_NO' => $row['no'],
        'dpname' => $row['namevl'],
        'MATURITY_DATE'=> $row['MATURITY_DATE'],
        'RECEIPT_NO'=> $row['RECEIPT_NO'],
        'DEPOSIT_AMT' =>sprintf("%.2f",(intval($row['DEPOSIT_AMT']))+  0.0 ),
        'MARGIN' => $row['MARGIN'],
        'REMARK' => $row['REMARK'],
        'total' => sprintf("%.2f",($GRAND_TOTAL+  0.0 ) ),
        'Date' => $Date1,
        'scheme' => $scheme,
        'Bankname' => $Bankname1,
        'branchname' => $branchname1,
      
    ];    
    $data[$i]=$tmp;
    $i++;   
}
// for clean previous execution
ob_end_clean();
// 
$config = ['driver'=>'array','data'=>$data];
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>   

