<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/loan analysis query.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');


$Branch  = $_GET['Branch'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$trandr = "'D'";
$transtatus = $_GET['transtatus'];
$trancr = "'C'";
$bankname = $_GET['bankname'];



$query = '
SELECT TMP."AC_ACNOTYPE" , TMP."AC_TYPE", SUM(TMP.OPENING_ACCOUNTS) OPENING_ACCOUNTS, SUM(TMP.OPENING_LEDGER_BALANCE) OPENING_LEDGER_BALANCE
, SUM(TMP.SANCTION_ACCOUNTS) SANCTION_ACCOUNTS, SUM(TMP.SANCTION_AMOUNT) SANCTION_AMOUNT 
, SUM(RECOVERD_ACCOUNTS) RECOVERD_ACCOUNTS, SUM(TMP.RECOVERD_AMOUNT) RECOVERD_AMOUNT 
, SUM(CLOSING_ACCOUNTS) CLOSING_ACCOUNTS , SUM(CLOSING_LEDGER_BALANCE) CLOSING_LEDGER_BALANCE 
From 
( 
SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE", SUM(CASE WHEN VWTMPZBALANCEFORM2.CLOSING_BALANCE=0 THEN 0  ELSE 1 END ) OPENING_ACCOUNTS 
, SUM(VWTMPZBALANCEFORM2.CLOSING_BALANCE) OPENING_LEDGER_BALANCE 
, 0 SANCTION_ACCOUNTS, 0 SANCTION_AMOUNT, 0 RECOVERD_ACCOUNTS, 0 RECOVERD_AMOUNT , 0 CLOSING_ACCOUNTS , 0 CLOSING_LEDGER_BALANCE 
FROM lnmaster
LEFT OUTER JOIN (    


SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT"
                , (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN  cast(LNMASTER."AC_OP_BAL" as float)  ELSE (-1) * cast(LNMASTER."AC_OP_BAL" as float) END ,0) + coalesce(LOANTRAN.TRAN_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE
                ,  (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN LNMASTER."AC_RECBLEINT_OP"  ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END ,0) + coalesce(LOANTRAN.RECPAY_INT_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 
                      + coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN cast(LNMASTER."AC_RECBLEODUEINT_OP" as float)  ELSE (-1) * cast(LNMASTER."AC_RECBLEODUEINT_OP" as float)END ,0) + coalesce(LOANTRAN.OTHER10_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT 
FROM lnmaster


LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)   ELSE (-1) * cast("TRAN_AMOUNT" as float) END ),0) TRAN_AMOUNT 
               , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  "RECPAY_INT_AMOUNT"   ELSE (-1) * "RECPAY_INT_AMOUNT" END ),0) RECPAY_INT_AMOUNT 
               , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  "OTHER10_AMOUNT"   ELSE (-1) * "OTHER10_AMOUNT" END ),0) OTHER10_AMOUNT  FROM LOANTRAN 
                WHERE cast("TRAN_DATE" as date) <= cast('.$sdate.' As date)
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) loantran ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as Bigint))



LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)  ELSE (-1) * cast("TRAN_AMOUNT" as float) END ),0) DAILY_AMOUNT 
                , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("RECPAY_INT_AMOUNT" as float)   ELSE (-1) * cast("RECPAY_INT_AMOUNT" as float) END ),0) DAILY_RECPAY_INT_AMOUNT  
                , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("OTHER10_AMOUNT" as float)    ELSE (-1) * cast("OTHER10_AMOUNT" as float)  END ),0) DAILY_OTHER10_AMOUNT  
                FROM DAILYTRAN WHERE  cast("TRAN_DATE" as date) <= cast('.$sdate.' As date)
                AND "TRAN_STATUS" = '.$transtatus.' 
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
          ) dailytran ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(DAILYTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast( DAILYTRAN."TRAN_ACNO" as float))
WHERE ((LNMASTER."AC_OPDATE" IS NULL) OR (cast(LNMASTER."AC_OPDATE" as date) <= cast('.$sdate.' As date))) AND ((LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date)  > cast('.$sdate.' As date)))

)vwtmpzbalanceform2 ON (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = VWTMPZBALANCEFORM2."AC_TYPE" AND LNMASTER."AC_NO" = VWTMPZBALANCEFORM2."AC_NO")
WHERE LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2."AC_ACNOTYPE" AND VWTMPZBALANCEFORM2.CLOSING_BALANCE <> 0 AND (( LNMASTER."AC_OPDATE" IS NULL) OR (cast(LNMASTER."AC_OPDATE" as date) <= cast('.$sdate.' As date))) AND (( LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date) >= cast('.$sdate.' As date))) GROUP BY LNMASTER."AC_ACNOTYPE" ,LNMASTER."AC_TYPE" 

Union All

SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE", 0 OPENING_ACCOUNTS, 0 OPENING_LEDGER_BALANCE 
, SUM(CASE WHEN LNMASTER.AC_SANCTION_AMOUNT=0 THEN 0  ELSE 1 END ) SANCTION_ACCOUNTS, coalesce(SUM(LNMASTER.AC_SANCTION_AMOUNT),0) SANCTION_AMOUNT, 0 RECOVERD_ACCOUNTS, 0 RECOVERD_AMOUNT 
, 0 CLOSING_ACCOUNTS , 0 CLOSING_LEDGER_BALANCE  
FROM ( SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" , LNMASTER."AC_NO"  
         ,( coalesce(LOANTRAN.TRAN_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_AMOUNT,0))AC_SANCTION_AMOUNT 
         FROM lnmaster
LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO"    , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)   ELSE 0 END ),0) TRAN_AMOUNT 
         FROM LOANTRAN WHERE cast("TRAN_DATE" as date)>= cast('.$sdate.' As date) AND cast("TRAN_DATE" as date) <= cast('.$edate.' As date) 
         GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"   ) loantran ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as Bigint))
LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE" , "TRAN_ACNO" 
         , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)  ELSE 0 END ),0) DAILY_AMOUNT FROM DAILYTRAN WHERE cast("TRAN_DATE" as date) >= cast('.$sdate.' As date) 
         AND cast("TRAN_DATE" as date) <= cast('.$edate.' As date) AND "TRAN_STATUS" = '.$transtatus.'  GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) dailytran ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(DAILYTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast(DAILYTRAN."TRAN_ACNO" as float))
WHERE cast(LNMASTER."AC_OPDATE" as date) <= cast('.$edate.' As date) AND ( LNMASTER."AC_CLOSEDT" IS NULL OR cast(LNMASTER."AC_CLOSEDT" as date) >= cast('.$sdate.' As date))  ) LNMASTER 
          WHERE LNMASTER.AC_SANCTION_AMOUNT <> 0 
          GROUP BY LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" 

Union All

SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE", 0 OPENING_ACCOUNTS, 0 OPENING_LEDGER_BALANCE 
, 0 SANCTION_ACCOUNTS, 0 SANCTION_AMOUNT, SUM(CASE WHEN LNMASTER.RECOVERD_AMOUNT=0 THEN 0  ELSE 1 END ) RECOVERD_ACCOUNTS, coalesce(SUM(LNMASTER.RECOVERD_AMOUNT),0) RECOVERD_AMOUNT 
, 0 CLOSING_ACCOUNTS , 0 CLOSING_LEDGER_BALANCE 
FROM ( SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" , LNMASTER."AC_NO"  
         ,( coalesce(LOANTRAN.TRAN_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_AMOUNT,0))RECOVERD_AMOUNT 
         FROM lnmaster
LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO"    , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trancr.' THEN   cast("TRAN_AMOUNT" as float)  ELSE 0 END ),0) TRAN_AMOUNT 
         FROM LOANTRAN WHERE cast("TRAN_DATE" as date) >= cast('.$sdate.' As date) AND cast("TRAN_DATE" as date) <= cast('.$edate.' As date) 
         GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"   ) loantran ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as Bigint))
LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE" , "TRAN_ACNO" 
         , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trancr.' THEN   cast("TRAN_AMOUNT" as float)  ELSE 0 END ),0) DAILY_AMOUNT FROM DAILYTRAN WHERE cast("TRAN_DATE" as date) >= cast('.$sdate.' As date) 
         AND cast("TRAN_DATE" as date) <= cast('.$edate.' As date) AND "TRAN_STATUS" = '.$transtatus.'  GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) dailytran ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(DAILYTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" =  cast(DAILYTRAN."TRAN_ACNO" as float))
WHERE cast(LNMASTER."AC_OPDATE" as date) <= cast('.$edate.' As date) AND ( LNMASTER."AC_CLOSEDT" IS NULL OR cast(LNMASTER."AC_CLOSEDT" as date) >= cast('.$sdate.' As date))   )LNMASTER WHERE LNMASTER.RECOVERD_AMOUNT<>0 
GROUP BY LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE"

Union All

SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE", 0 OPENING_ACCOUNTS, 0 OPENING_LEDGER_BALANCE 
, 0 SANCTION_ACCOUNTS, 0 SANCTION_AMOUNT, 0 RECOVERD_ACCOUNTS, 0 RECOVERD_AMOUNT 
, SUM(CASE WHEN VWTMPZBALANCEFORM2B.CLOSING_BALANCE=0 THEN 0  ELSE 1 END ) CLOSING_ACCOUNTS , SUM(VWTMPZBALANCEFORM2B.CLOSING_BALANCE) CLOSING_LEDGER_BALANCE 
FROM lnmaster
LEFT OUTER JOIN (    


SELECT LNMASTER."AC_ACNOTYPE", LNMASTER."AC_TYPE", LNMASTER."AC_NO", LNMASTER."AC_OPDATE", LNMASTER."AC_CLOSEDT"
                , (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN  cast(LNMASTER."AC_OP_BAL" as float)  ELSE (-1) * cast(LNMASTER."AC_OP_BAL" as float) END ,0) + coalesce(LOANTRAN.TRAN_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_AMOUNT,0)) CLOSING_BALANCE
                ,  (coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN LNMASTER."AC_RECBLEINT_OP"  ELSE (-1) * LNMASTER."AC_RECBLEINT_OP" END ,0) + coalesce(LOANTRAN.RECPAY_INT_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_RECPAY_INT_AMOUNT,0) 
                      + coalesce(CASE WHEN LNMASTER."AC_OP_CD"='.$trandr.' THEN cast(LNMASTER."AC_RECBLEODUEINT_OP" as float)  ELSE (-1) * cast(LNMASTER."AC_RECBLEODUEINT_OP" as float)END ,0) + coalesce(LOANTRAN.OTHER10_AMOUNT,0) + coalesce(DAILYTRAN.DAILY_OTHER10_AMOUNT,0)) RECPAY_INT_AMOUNT 
FROM lnmaster


LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)   ELSE (-1) * cast("TRAN_AMOUNT" as float) END ),0) TRAN_AMOUNT 
               , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  "RECPAY_INT_AMOUNT"   ELSE (-1) * "RECPAY_INT_AMOUNT" END ),0) RECPAY_INT_AMOUNT 
               , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  "OTHER10_AMOUNT"   ELSE (-1) * "OTHER10_AMOUNT" END ),0) OTHER10_AMOUNT  FROM LOANTRAN 
                WHERE cast("TRAN_DATE" as date) <= cast('.$edate.' As date)
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) loantran ON (LNMASTER."AC_ACNOTYPE" = LOANTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(LOANTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast(LOANTRAN."TRAN_ACNO" as Bigint))



LEFT OUTER JOIN ( SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("TRAN_AMOUNT" as float)  ELSE (-1) * cast("TRAN_AMOUNT" as float) END ),0) DAILY_AMOUNT 
                , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("RECPAY_INT_AMOUNT" as float)   ELSE (-1) * cast("RECPAY_INT_AMOUNT" as float) END ),0) DAILY_RECPAY_INT_AMOUNT  
                , coalesce(SUM(CASE WHEN "TRAN_DRCR"='.$trandr.' THEN  cast("OTHER10_AMOUNT" as float)    ELSE (-1) * cast("OTHER10_AMOUNT" as float)  END ),0) DAILY_OTHER10_AMOUNT  
                FROM DAILYTRAN WHERE  cast("TRAN_DATE" as date) <= cast('.$edate.' As date)
                AND "TRAN_STATUS" = '.$transtatus.' 
                GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
          ) dailytran ON (LNMASTER."AC_ACNOTYPE" = DAILYTRAN."TRAN_ACNOTYPE" AND LNMASTER."AC_TYPE" = cast(DAILYTRAN."TRAN_ACTYPE" as float) AND LNMASTER."AC_NO" = cast( DAILYTRAN."TRAN_ACNO" as float))
WHERE ((LNMASTER."AC_OPDATE" IS NULL) OR (cast(LNMASTER."AC_OPDATE" as date) <= cast('.$edate.' As date))) AND ((LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date)  > cast('.$edate.' As date)))

)vwtmpzbalanceform2b ON (LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2B."AC_ACNOTYPE" AND LNMASTER."AC_ACNOTYPE" = VWTMPZBALANCEFORM2B."AC_ACNOTYPE" AND LNMASTER."AC_TYPE" = VWTMPZBALANCEFORM2B."AC_TYPE" AND LNMASTER."AC_NO" = VWTMPZBALANCEFORM2B."AC_NO")
WHERE ((LNMASTER."AC_CLOSEDT" IS NULL) OR (cast(LNMASTER."AC_CLOSEDT" as date)> cast('.$edate.' As date))) GROUP BY LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE"
) TMP WHERE ((OPENING_LEDGER_BALANCE + SANCTION_AMOUNT + CLOSING_LEDGER_BALANCE ) - RECOVERD_AMOUNT ) <> 0 
GROUP BY "AC_ACNOTYPE" , "AC_TYPE"  
';

$sql =  pg_query($conn,$query);
$i = 0;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {
    $total_voucher = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        "SR_NO" => $row["SR_NO"],
        "AC_ACNOTYPE" => $row["AC_ACNOTYPE"],
        "AC_TYPE" => $row["AC_TYPE"],
        "OPENING_ACCOUNTS" => $row['opening_accounts'],
        "OPENING_LEDGER_BALANCE" => $row['opening_ledger_balance'],
        "SANCTION_ACCOUNTS" => $row['sanction_accounts'],
        "SANCTION_AMOUNT" => $row["sanction_amount"],
        "RECOVERD_ACCOUNTS" => $row["recoverd_accounts"],
        "RECOVERD_AMOUNT" => $row["recoverd_amount"],
        "CLOSING_ACCOUNTS" => $row['closing_accounts'],
        "CLOSING_LEDGER_BALANCE" => $row["closing_ledger_balance"],
        "sdate" => $sdate,
        "edate" => $edate,
        "Branch" => $Branch,
        "bankname" => $bankname,
        "transtatus" => $transtatus,

    ];
    $data[$i]=$tmp;
    $i++;
  
}
ob_end_clean();
   
$config = ['driver'=>'array','data'=>$data];
//  print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
    
}   
?>

