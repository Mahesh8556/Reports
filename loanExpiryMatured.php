<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/loanExpiryMatured.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=hrdatabase user=postgres password=tushar");

// variables

$F_DIRECTOR = $_GET['F_DIRECTOR'];
$T_DIRECTOR = $_GET['T_DIRECTOR'];

 $BRANCH_CODE = $_GET['BRANCH_CODE'];
 $ac_type = $_GET['AC_TYPE'];
 $branch_name = $_GET['BRANCH'];
 $Sdate = $_GET['START_DATE'];
 $Edate = $_GET['END_DATE'];

 $BANK_NAME=$_GET['BANK_NAME'];

 $LN = $_GET['AC_ACNOTYPE'];
 $d = "'D'";
  $TStatus="'1'";
//  $TD="'TD'";



//  $sdate1 = str_replace("'", "", $sdate);
//  $edate2 = str_replace("'", "", $edate);
 $Sdate1 = str_replace("'", "", $Sdate);
 $Edate1 = str_replace("'", "", $Edate);

  $branch_name1 = str_replace("'", "", $branch_name);
 $BANK_NAME1 = str_replace("'", "", $BANK_NAME);

 

// $ac_type ="'4'";
// $ac_acnotype = "'TD'";
$dateformat ="'DD/MM/YYYY'";




$query=' SELECT  LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NO", LNMASTER."AC_NAME", LNMASTER."AC_SANCTION_AMOUNT" 
, LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT", CITYMASTER."CITY_NAME",  CUSTOMERADDRESS."AC_CTCODE" , LNMASTER."AC_RECOMMEND_BY", DIRECTORMASTER."NAME" "DIR_NAME" , LNMASTER."AC_EXPIRE_DATE"  
, SCHEMAST."S_NAME" AS "SCHEME_NAME" , SCHEMAST."S_APPL" AS "SCHEME_TYPE" ,
 VWTMPZBALANCEXPIRY."CLOSING_BALANCE",IDMASTER."AC_MOBILENO",IDMASTER."AC_PHONE_OFFICE",IDMASTER."AC_PHONE_RES" 
FROM  CITYMASTER,CUSTOMERADDRESS,IDMASTER, LNMASTER 
LEFT OUTER JOIN DIRECTORMASTER ON LNMASTER."AC_RECOMMEND_BY"= DIRECTORMASTER."CODE"
LEFT OUTER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST.ID  
LEFT OUTER JOIN 
(SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."BANKACNO", LNMASTER."AC_NO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT" 
            , (COALESCE(CASE LNMASTER."AC_OP_CD"  WHEN '.$d.' THEN  LNMASTER."AC_OP_BAL"  ELSE (-1) * LNMASTER."AC_OP_BAL" END,0) + COALESCE(LOANTRAN."TRAN_AMOUNT",0) + COALESCE(DAILYTRAN."DAILY_AMOUNT",0)) "CLOSING_BALANCE"
            ,  (COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN LNMASTER."AC_RECBLEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END,0) + COALESCE(LOANTRAN."RECPAY_INT_AMOUNT",0) + COALESCE(DAILYTRAN."DAILY_RECPAY_INT_AMOUNT",0) 
                  + COALESCE(CASE LNMASTER."AC_OP_CD" WHEN '.$d.' THEN "AC_RECBLEODUEINT_OP" ELSE (-1) * LNMASTER."AC_RECBLEODUEINT_OP" END,0) + COALESCE(LOANTRAN."OTHER10_AMOUNT",0) + COALESCE(DAILYTRAN."DAILY_OTHER10_AMOUNT",0)) "RECPAY_INT_AMOUNT" 
FROM LNMASTER LEFT OUTER JOIN
      ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "TRAN_AMOUNT"  ELSE (-1) * "TRAN_AMOUNT" END),0) "TRAN_AMOUNT" 
           , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "RECPAY_INT_AMOUNT"  ELSE (-1) * "RECPAY_INT_AMOUNT" END),0) "RECPAY_INT_AMOUNT" 
           , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "OTHER10_AMOUNT"  
                          ELSE (-1) * "OTHER10_AMOUNT"  END),0) "OTHER10_AMOUNT"  
       FROM LOANTRAN 
            WHERE CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$Sdate.', '.$dateformat.')
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) LOANTRAN 
            ON LNMASTER."BANKACNO" =  LOANTRAN."TRAN_ACNO" LEFT OUTER JOIN
      ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "TRAN_AMOUNT"  ELSE (-1) * "TRAN_AMOUNT" END),0) "DAILY_AMOUNT" 
            , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "RECPAY_INT_AMOUNT"  ELSE (-1) * "RECPAY_INT_AMOUNT" END),0) "DAILY_RECPAY_INT_AMOUNT"  
            , COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$d.' THEN  "OTHER10_AMOUNT"  ELSE (-1) * "OTHER10_AMOUNT"  END),0) "DAILY_OTHER10_AMOUNT"  
            FROM DAILYTRAN WHERE CAST("TRAN_DATE" AS DATE) <= TO_DATE('.$Sdate.', '.$dateformat.')
            AND "TRAN_STATUS" = '.$TStatus.' 
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
      ) DAILYTRAN  ON LNMASTER."BANKACNO" =  DAILYTRAN."TRAN_ACNO"
Where
       ((LNMASTER."AC_OPDATE" IS NULL) 
        OR (CAST(LNMASTER."AC_OPDATE" AS DATE) <= To_DATE('.$Sdate.','.$dateformat.')))
       AND ((CAST(LNMASTER."AC_CLOSEDT" AS DATE) IS NULL) OR (CAST(LNMASTER."AC_CLOSEDT" AS DATE) > To_DATE('.$Sdate.','.$dateformat.')))
) AS VWTMPZBALANCEXPIRY 
 ON LNMASTER."BANKACNO" = vwtmpzbalancexpiry."BANKACNO" 
WHERE  VWTMPZBALANCEXPIRY."CLOSING_BALANCE" <> 0 
AND LNMASTER."AC_CUSTID"=IDMASTER."AC_NO" 
AND IDMASTER.ID = CUSTOMERADDRESS."idmasterID"
AND CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER."CITY_CODE"  
AND LNMASTER."AC_ACNOTYPE" ='.$LN.' AND LNMASTER."AC_TYPE" ='.$ac_type.'
AND CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) >= TO_DATE('.$Sdate.','.$dateformat.')  
AND CAST(LNMASTER."AC_EXPIRE_DATE" AS DATE) <= TO_DATE('.$Edate.','.$dateformat.') 
AND LNMASTER."BRANCH_CODE" = '.$BRANCH_CODE .'  ';

// AND LNMASTER."AC_RECOMMEND_BY" >= '.$F_DIRECTOR .' AND LNMASTER."AC_RECOMMEND_BY" <= '.$T_DIRECTOR .'

    //    echo $query;
          
$sql =  pg_query($conn,$query);

 $i = 0;


while($row = pg_fetch_assoc($sql))
{ 
    
    $tmp=[
        's_date' => $row['AC_OPDATE'],
        'maturity_date' => $row['AC_CLOSEDT'],
        'sanction_amt' => $row['AC_SANCTION_AMOUNT'],
        'city' => $row['CITY_NAME'],
        'closing_balance' => $row['CLOSING_BALANCE'],

        'name'=> $row['AC_NAME'],
        'AC_GRDNAME' => $row['AC_GRDNAME'],
        'AC_GRDRELE' => $row['AC_GRDRELE'],
        'AC_MBDATE' => $row['AC_MBDATE'],
        'ac_acnotype' =>$row['SCHEME_TYPE'] .'  '. $row['SCHEME_NAME'],
        'AGE' => $row['AGE'],
        'S_NAME' => $row['S_NAME'],
        'acc_no'=> $row['AC_NO'],
        'ac_type' => $ac_type,
        'print_date' =>  $print_date1,
        'BANK_NAME'=>  $BANK_NAME1,
      
        'branch_name' => $branch_name1, 
        // 'scheme' => $LN,
        'from_date' => $Sdate1,
        'to_date' => $Edate1,
    ];
    $data[$i]=$tmp;
    $i++;  
}
ob_end_clean();
// echo $query;

$config = ['driver'=>'array','data'=>$data];
// echo $filename;
// print_r($data)
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
?>