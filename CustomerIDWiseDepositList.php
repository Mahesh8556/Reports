<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CustomerIDWiseDepositList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//$conn = pg_connect("host=127.0.0.1 dbname=bank user=postgres password=tushar");

// variables
 $BRANCH  = $_GET['BRANCH'];
//$AC_EXPDT = $_GET['AC_EXPDT'];
 $AC_TYPE = $_GET['AC_TYPE'];
 $START_DATE = $_GET['START_DATE'];
 $CUST_ID =$_GET['CUST_ID'];
 $END_DATE = $_GET['END_DATE'];
 $AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
 $CUST_ID = $_GET['CUST_ID'];
 $TRAN_DRCR = $_GET['TRAN_DRCR'];
 $FLAG1 = $_GET['FLAG1'];
 $FLAG2 = $_GET['FLAG2'];
 $FLAG3 = $_GET['FLAG3'];

 
$dateformat ="'YYYY/MM/DD'";

$query = '';
if(  $FLAG1 == 0 ){
    $query.=  /* SUBMMARY */
             '(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" , SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.' 
              THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
              FROM DEPOTRAN 
              INNER JOIN PGMASTER ON DEPOTRAN."TRAN_ACNO" = PGMASTER."BANKACNO"
              WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)
              AND PGMASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE"
              AND PGMASTER."AC_TYPE" = CAST(DEPOTRAN."TRAN_ACTYPE" AS INTEGER)
              AND PGMASTER."AC_NO" = CAST(DEPOTRAN."TRAN_ACNO" AS BIGINT)
              AND PGMASTER."AC_CUSTID" = '.$CUST_ID.'
              AND ((CAST(PGMASTER."AC_OPDATE" AS DATE) IS NULL) OR (CAST(PGMASTER."AC_OPDATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)))
              AND ((CAST(PGMASTER."AC_CLOSEDT" AS DATE) IS NULL) OR (CAST(PGMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE)))
              GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" )
              UNION ALL
              (SELECT "TRAN_ACNOTYPE","TRAN_ACTYPE","TRAN_ACNO", SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.'
               THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
              FROM PIGMYTRAN 
              INNER JOIN DPMASTER ON PIGMYTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
              WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)
              AND DPMASTER."AC_ACNOTYPE" = PIGMYTRAN."TRAN_ACNOTYPE"
              AND DPMASTER."AC_TYPE" = CAST(PIGMYTRAN."TRAN_ACTYPE" AS  INTEGER)
              AND DPMASTER."AC_NO" = CAST(PIGMYTRAN."TRAN_ACNO" AS BIGINT)
              AND DPMASTER."AC_CUSTID" = '.$CUST_ID.'
              AND ((CAST(DPMASTER."AC_OPDATE" AS DATE) IS NULL) OR (CAST(DPMASTER."AC_OPDATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)))
              AND ((CAST(DPMASTER."AC_CLOSEDT" AS DATE) IS NULL) OR (CAST(DPMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE)))
              GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" )';

            //  echo $query;

}elseif ( $FLAG2 == 1){
    $query .= /* DETAILS */

            '(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ,SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.' 
            THEN cast("TRAN_AMOUNT" as float) ELSE (-1) * cast("TRAN_AMOUNT" as float) END,0)) TRAN_AMOUNT 
             FROM DEPOTRAN 
             INNER JOIN DPMASTER ON DEPOTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
             WHERE cast(DEPOTRAN."TRAN_DATE" as date) <= cast('.$START_DATE.' as date)
            AND DPMASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE"
             AND DPMASTER."AC_TYPE" = CAST(DEPOTRAN."TRAN_ACTYPE" AS INTEGER)
             AND DPMASTER."AC_CUSTID" = '.$CUST_ID.'
             AND ((cast(DPMASTER."AC_OPDATE" as DATE) IS NULL) OR (cast(DPMASTER."AC_OPDATE" as DATE) < cast('.$START_DATE.' as DATE)))
             AND ((cast(DPMASTER."AC_CLOSEDT" as DATE) IS NULL) OR (cast(DPMASTER."AC_CLOSEDT" as DATE) >= cast('.$END_DATE.' as DATE)))
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" )
              UNION ALL 
            (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.'
             THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
             FROM PIGMYTRAN
              INNER JOIN PGMASTER ON PIGMYTRAN."TRAN_ACNO" = PGMASTER."BANKACNO"
             WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)
             AND PGMASTER."AC_ACNOTYPE" = PIGMYTRAN."TRAN_ACNOTYPE"
             AND PGMASTER."AC_TYPE" = CAST(PIGMYTRAN."TRAN_ACTYPE" AS INTEGER)
             AND PGMASTER."AC_NO" = CAST(PIGMYTRAN."TRAN_ACNO" AS BIGINT)
             AND PGMASTER."AC_CUSTID" = '.$CUST_ID.'
             AND ((PGMASTER."AC_OPDATE" IS NULL) OR (CAST(PGMASTER."AC_OPDATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)))
             AND ((PGMASTER."AC_CLOSEDT" IS NULL) OR (CAST(PGMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE)))
             GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" )';

             // echo $query;
}else{
    $query .= /*SHOW ONLY TOP 20 */
    
            '(SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ,SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.'
            THEN cast("TRAN_AMOUNT" as float) ELSE (-1) * cast("TRAN_AMOUNT" as float) END,0)) TRAN_AMOUNT 
            FROM DEPOTRAN
            INNER JOIN PGMASTER ON DEPOTRAN."TRAN_ACNO" = PGMASTER."BANKACNO" 
            WHERE cast("TRAN_DATE" as date) <= cast('.$START_DATE.' as date)
            AND PGMASTER."AC_ACNOTYPE" = DEPOTRAN."TRAN_ACNOTYPE"
            AND PGMASTER."AC_TYPE" = cast(DEPOTRAN."TRAN_ACTYPE" as integer)
            AND PGMASTER."AC_NO" = cast(DEPOTRAN."TRAN_ACNO" as bigint)
            AND PGMASTER."AC_CUSTID" = '.$CUST_ID.' 
            AND ((cast(PGMASTER."AC_OPDATE" as date) IS NULL) OR (cast(PGMASTER."AC_OPDATE" as date) <= cast('.$START_DATE.' as date)))
            AND ((cast(PGMASTER."AC_CLOSEDT" as date) IS NULL) OR (cast(PGMASTER."AC_CLOSEDT" as date) > cast('.$END_DATE.' as date)))
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" ) 
             UNION ALL
            (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"  , SUM(COALESCE(CASE "TRAN_DRCR" WHEN '.$TRAN_DRCR.' 
            THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END,0)) TRAN_AMOUNT 
            FROM PIGMYTRAN
            INNER JOIN DPMASTER ON PIGMYTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
            WHERE CAST("TRAN_DATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)
            AND DPMASTER."AC_ACNOTYPE" = PIGMYTRAN."TRAN_ACNOTYPE"
            AND DPMASTER."AC_TYPE" = CAST(PIGMYTRAN."TRAN_ACTYPE" AS INTEGER)
            AND DPMASTER."AC_NO" = CAST(PIGMYTRAN."TRAN_ACNO" AS BIGINT)
            AND DPMASTER."AC_CUSTID" = '.$CUST_ID.' 
            AND ((CAST(DPMASTER."AC_OPDATE" AS DATE)  IS NULL) OR (CAST(DPMASTER."AC_OPDATE" AS DATE) <= CAST('.$START_DATE.' AS DATE)))
            AND ((CAST(DPMASTER."AC_CLOSEDT" AS DATE) IS NULL) OR (CAST(DPMASTER."AC_CLOSEDT" AS DATE) > CAST('.$END_DATE.' AS DATE)))
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO" )';

               //echo $query;  

}

  echo $query;           

$sql =  pg_query($conn,$query);

 $i = 0;

while($row = pg_fetch_assoc($sql))
{ 
    
    $tmp=[
        'AC_NAME'=> $row['AC_NAME'],
        'AC_OPDATE'=> $row['AC_OPDATE'],
        'AC_SCHMAMMT'=> $row['AC_SCHMAMT'],
        'AC_CLOSEDT' => $row['AC_CLOSEDT'],
        'AC_REF_RECEPITNO' =>  $row['AC_REF_RECEPITNO'],
        'AC_INTRATE' => $row['AC_INTRATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'tran_amount' => $row['tran_amount'],
        'DEPO_AMOUNT' => $row['DEPO_AMOUNT'],
        'CUST_ID' => $row['CUST_ID'],
        'CUST_IDNAME' => $row['CUST_IDNAME'],
        'S_NAME' => $row['S_NAME'],
        'AC_NO'=> $row['AC_NO'],
        'AC_OP_CD' => $row['AC_OP_CD'],
        'TRAN_ACNO' => $row['TRAN_ACNO'],
        'TRAN_ACTYPE' => $row['TRAN_ACTYPE'],
        'AC_TYPE' => $AC_TYPE,
        'TRAN_ACNOTYPE' => $TRAN_ACNOTYPE,
        'START_DATE' => $START_DATE,
        'END_DATE'  => $END_DATE,
        'TRAN_DRCR' => $TRAN_DRCR,
        'CUST_ID' => $CUST_ID,
        'AC_ACNOTYPE' => $AC_ACNOTYPE,
        'BRANCH' => $BRANCH,
        // 'schemecode'=> $schemecode,
    ];
    $data[$i]=$tmp;
    $i++;  
}
// ob_end_clean();
// // echo $query;

// $config = ['driver'=>'array','data'=>$data];
// // echo $filename;
// //  print_r($data)
// $report = new PHPJasperXML();
// $report->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');
    
?>