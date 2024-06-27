<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename1 = __DIR__.'/ReconciliationStatement.jrxml';
$filename2 = __DIR__.'/ReconciliationStatement_Flag0.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$Branch  = $_GET['Branch'];
$sdate = $_GET['sdate'];
$edate = $_GET['edate'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE  = $_GET['AC_TYPE'];
$TRAN_ACNO  = $_GET['TRAN_ACNO'];
$flag1 = $_GET['flag1'];
$trandrcr = $_GET['trandrcr'];
$trandrcrc = $_GET['trandrcrc'];
//$tran_glactype = $_GET['tran_glactype'];
$TRANSTATUS = $_GET['TRANSTATUS'];
$TRAN_TYPE = $_GET['TRAN_TYPE'];
$ENTRYTTYPE1 = $_GET['ENTRYTTYPE1'];
$ENTRYTTYPE2 = $_GET['ENTRYTTYPE2'];
$query='';

if($flag1=='1')
{
    $query.='select VWTMPGLBAL.CLOSING_BALANCE , "AC_NAME", MAIN_TABLE.* 
    FROM ACMASTER
    LEFT OUTER JOIN
    (SELECT "AC_ACNOTYPE", "AC_TYPE" , "AC_NO", "AC_OPDATE", "AC_CLOSEDT",
    (COALESCE(CASE "AC_OP_CD"  WHEN '.$trandrcr.' THEN  cast("AC_OP_BAL" As FLOAT)  ELSE (-1) * cast("AC_OP_BAL" As FLOAT) END,0) + 
    COALESCE(ACCOTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0) + 
    COALESCE(CASHAMT.CASH_AMOUNT,0) )CLOSING_BALANCE , 0 RECPAY_INT_AMOUNT
    FROM ACMASTER
    LEFT OUTER JOIN
    (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
    COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) TRAN_AMOUNT 
    FROM ACCOTRAN 
    WHERE "TRAN_ACNOTYPE" = '.$AC_ACNOTYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date) 
    AND NOT ( cast("TRAN_DATE" As date) = cast('.$edate.' As date) AND COALESCE(cast("CLOSING_ENTRY" As integer),0) <> 0 ) 
    GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
    ) ACCOTRAN 
    ON ACMASTER."AC_NO" =  ACCOTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
    (SELECT '.$AC_ACNOTYPE.' "TRAN_ACNOTYPE",tran_glactype TRAN_ACTYPE , "TRAN_GLACNO" TRAN_ACNO, 
    COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) DAILY_AMOUNT 
    from VWDETAILDAILYTRAN 
    WHERE tran_glactype = '.$AC_TYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
    AND "TRAN_STATUS" = '.$TRANSTATUS.'
    GROUP BY tran_glactype, "TRAN_GLACNO"
    ) DAILYTRAN
    ON ACMASTER."AC_ACNOTYPE"  = DAILYTRAN."TRAN_ACNOTYPE"
    LEFT OUTER JOIN
    (SELECT '.$AC_ACNOTYPE.' "TRAN_ACNOTYPE", '.$AC_TYPE.' TRAN_ACTYPE, 1 TRAN_ACNO, 
    (COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  (-1) * cast("TRAN_AMOUNT" As FLOAT)  ELSE cast("TRAN_AMOUNT" As FLOAT) END),0)) CASH_AMOUNT 
    FROM VWDETAILDAILYTRAN 
    WHERE "TRAN_TYPE" = '.$TRAN_TYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
    AND "TRAN_STATUS" = '.$TRANSTATUS.'
    ) CASHAMT ON
    ACMASTER."AC_ACNOTYPE"  = CASHAMT."TRAN_ACNOTYPE" 
    AND ((ACMASTER."AC_OPDATE" IS NULL) OR (cast(ACMASTER."AC_OPDATE" As date) <= cast('.$edate.' As date)))
    AND ((ACMASTER."AC_CLOSEDT" IS NULL) OR (cast(ACMASTER."AC_CLOSEDT" As date) > cast('.$edate.' As date)))
    AND ACMASTER."AC_ACNOTYPE"  = '.$AC_ACNOTYPE.'
    AND ACMASTER."AC_TYPE" = '.$AC_TYPE.')VWTMPGLBAL ON ACMASTER."AC_NO" = VWTMPGLBAL."AC_NO"
    
    LEFT OUTER JOIN(
    SELECT "TRAN_ACNOTYPE" AC_ACNOTYPE, "TRAN_ACTYPE" AC_TYPE, "TRAN_ACNO" AC_NO, "TRAN_DATE" ,
    "STATEMENT_DATE", ENTRYTTYPE TRAN_ENTRY_TYPE, 0 OP_DEBIT_AMOUNT,"TRAN_ACNO", 
     0 OP_CREDIT_AMOUNT,  DEBIT_AMOUNT, CREDIT_AMOUNT, "CHEQUE_NO", "NARRATION" FROM
    (SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" ,
    '.$ENTRYTTYPE1.' ENTRYTTYPE , "CHEQUE_NO" , "NARRATION"  , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM ACCOTRAN 
    WHERE ( cast("TRAN_DATE" As date) >= cast('.$sdate.' As date)
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date))
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date))  AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    
    UNION ALL 
    SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" , 
    '.$ENTRYTTYPE1.' ENTRYTTYPE , cast("CHEQUE_NO" As character varying) , "NARRATION" , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM RECOTRAN 
    WHERE (cast("TRAN_DATE" As date) >= cast('.$sdate.' As date) 
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)) AND "TRAN_ENTRY_TYPE" = '.$ENTRYTTYPE1.'
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date)) AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    
    UNION ALL 
    SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" , 
    '.$ENTRYTTYPE2.' ENTRYTTYPE , cast("CHEQUE_NO" As character varying) , "NARRATION" , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT"As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM RECOTRAN 
    WHERE (cast("TRAN_DATE" As date) >= cast('.$sdate.' As date) 
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)) AND "TRAN_ENTRY_TYPE" = '.$ENTRYTTYPE2.'
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date)) AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    )TMP_ACCOTRAN   
    )MAIN_TABLE ON ACMASTER."AC_NO" = MAIN_TABLE."TRAN_ACNO"  
    WHERE ACMASTER."AC_NO" =  '.$TRAN_ACNO.''; 

    
}
else
{
    $query.='select VWTMPGLBAL.CLOSING_BALANCE , "AC_NAME", MAIN_TABLE.* 
    FROM ACMASTER
    LEFT OUTER JOIN
    (SELECT "AC_ACNOTYPE", "AC_TYPE" , "AC_NO", "AC_OPDATE", "AC_CLOSEDT",
    (COALESCE(CASE "AC_OP_CD"  WHEN '.$trandrcr.' THEN  cast("AC_OP_BAL" As FLOAT)  ELSE (-1) * cast("AC_OP_BAL" As FLOAT) END,0) + 
    COALESCE(ACCOTRAN.TRAN_AMOUNT,0) + COALESCE(DAILYTRAN.DAILY_AMOUNT,0) + 
    COALESCE(CASHAMT.CASH_AMOUNT,0) )CLOSING_BALANCE , 0 RECPAY_INT_AMOUNT
    FROM ACMASTER
    LEFT OUTER JOIN
    (SELECT "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO", 
    COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) TRAN_AMOUNT 
    FROM ACCOTRAN 
    WHERE "TRAN_ACNOTYPE" = '.$AC_ACNOTYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date) 
    AND NOT ( cast("TRAN_DATE" As date) = cast('.$edate.' As date) AND COALESCE(cast("CLOSING_ENTRY" As integer),0) <> 0 ) 
    GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO"
    ) ACCOTRAN 
    ON ACMASTER."AC_NO" =  ACCOTRAN."TRAN_ACNO"
    LEFT OUTER JOIN
    (SELECT '.$AC_ACNOTYPE.' "TRAN_ACNOTYPE",tran_glactype TRAN_ACTYPE , "TRAN_GLACNO" TRAN_ACNO, 
    COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  cast("TRAN_AMOUNT" As FLOAT)  ELSE (-1) * cast("TRAN_AMOUNT" As FLOAT) END),0) DAILY_AMOUNT 
    from VWDETAILDAILYTRAN 
    WHERE tran_glactype = '.$AC_TYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
    AND "TRAN_STATUS" = '.$TRANSTATUS.'
    GROUP BY tran_glactype, "TRAN_GLACNO"
    ) DAILYTRAN
    ON ACMASTER."AC_ACNOTYPE"  = DAILYTRAN."TRAN_ACNOTYPE"
    LEFT OUTER JOIN
    (SELECT '.$AC_ACNOTYPE.' "TRAN_ACNOTYPE", '.$AC_TYPE.' TRAN_ACTYPE, 1 TRAN_ACNO, 
    (COALESCE(SUM(CASE "TRAN_DRCR"  WHEN '.$trandrcr.' THEN  (-1) * cast("TRAN_AMOUNT" As FLOAT)  ELSE cast("TRAN_AMOUNT" As FLOAT) END),0)) CASH_AMOUNT 
    FROM VWDETAILDAILYTRAN 
    WHERE "TRAN_TYPE" = '.$TRAN_TYPE.'
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)
    AND "TRAN_STATUS" = '.$TRANSTATUS.'
    ) CASHAMT ON
    ACMASTER."AC_ACNOTYPE"  = CASHAMT."TRAN_ACNOTYPE" 
    AND ((ACMASTER."AC_OPDATE" IS NULL) OR (cast(ACMASTER."AC_OPDATE" As date) <= cast('.$edate.' As date)))
    AND ((ACMASTER."AC_CLOSEDT" IS NULL) OR (cast(ACMASTER."AC_CLOSEDT" As date) > cast('.$edate.' As date)))
    AND ACMASTER."AC_ACNOTYPE"  = '.$AC_ACNOTYPE.'
    AND ACMASTER."AC_TYPE" = '.$AC_TYPE.')VWTMPGLBAL ON ACMASTER."AC_NO" = VWTMPGLBAL."AC_NO"
    
    LEFT OUTER JOIN(
    SELECT "TRAN_ACNOTYPE" AC_ACNOTYPE, "TRAN_ACTYPE" AC_TYPE, "TRAN_ACNO" AC_NO, "TRAN_DATE" ,
    "STATEMENT_DATE", ENTRYTTYPE TRAN_ENTRY_TYPE, "TRAN_ACNO", 
       DEBIT_AMOUNT, CREDIT_AMOUNT, "CHEQUE_NO", "NARRATION" FROM
    (SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" ,
    '.$ENTRYTTYPE1.' ENTRYTTYPE , "CHEQUE_NO" , "NARRATION"  , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM ACCOTRAN 
    WHERE ( cast("TRAN_DATE" As date) >= cast('.$sdate.' As date)
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date))
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date))  AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    
    UNION ALL 
    SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" , 
    '.$ENTRYTTYPE1.' ENTRYTTYPE , cast("CHEQUE_NO" As character varying) , "NARRATION" , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM RECOTRAN 
    WHERE (cast("TRAN_DATE" As date) >= cast('.$sdate.' As date) 
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)) AND "TRAN_ENTRY_TYPE" = '.$ENTRYTTYPE1.'
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date)) AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    
    UNION ALL 
    SELECT "TRAN_ACNOTYPE" , "TRAN_ACTYPE" , "TRAN_ACNO" , "TRAN_DATE" , "STATEMENT_DATE" , 
    '.$ENTRYTTYPE2.' ENTRYTTYPE , cast("CHEQUE_NO" As character varying) , "NARRATION" , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcr.' THEN cast("TRAN_AMOUNT"As FLOAT) ELSE 0 END,0) DEBIT_AMOUNT , 
    COALESCE(CASE "TRAN_DRCR" WHEN '.$trandrcrc.' THEN cast("TRAN_AMOUNT" As FLOAT) ELSE 0 END,0) CREDIT_AMOUNT 
    FROM RECOTRAN 
    WHERE (cast("TRAN_DATE" As date) >= cast('.$sdate.' As date) 
    AND cast("TRAN_DATE" As date) <= cast('.$edate.' As date)) AND "TRAN_ENTRY_TYPE" = '.$ENTRYTTYPE2.'
    AND ( "STATEMENT_DATE" IS NULL OR cast("STATEMENT_DATE" As date) > cast('.$edate.' As date)) AND "TRAN_ACNO" =  '.$TRAN_ACNO.'
    )TMP_ACCOTRAN   
    )MAIN_TABLE ON ACMASTER."AC_NO" = MAIN_TABLE."TRAN_ACNO"  
    WHERE ACMASTER."AC_NO" =  '.$TRAN_ACNO.''; 
}

          

$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_TOTAL1 = 0;
$GRAND_TOTAL2 = 0;
//$GRAND_TOTAL3 = 0;
//$GRAND_TOTAL4 = 0;



while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row["debit_amount"];
    $GRAND_TOTAL2 = $GRAND_TOTAL2 + $row["credit_amount"];
    //$GRAND_TOTAL3 = $GRAND_TOTAL3 + ($row["closing_balance"] + $GRAND_TOTAL2);
    //$GRAND_TOTAL4 = $GRAND_TOTAL4 + $GRAND_TOTAL1 +$row["closing_balance"];
   

    $tmp=[

        $FINAL_CREDIT_AMT=$FINAL_CREDIT_AMT+$row['TOT_CREDIT_WORD'],
        $FINAL_DEBIT_AMT=$FINAL_DEBIT_AMT+$row['TOTAL_DEBIT_WORD'],
        $C=convertNumberToWordsForIndia($FINAL_CREDIT_AMT),
        $D=convertNumberToWordsForIndia($FINAL_DEBIT_AMT),

        "TRAN_DATE" => $row["TRAN_DATE"],
        "CHEQUE_NO" => $row["CHEQUE_NO"],
        "NARRATION"=> $row["NARRATION"],
        "debit_amount"=> sprintf("%.2f", (abs($row['debit_amount']))),
        "credit_amount" => sprintf("%.2f", (abs($row['credit_amount']))),
        "STATEMENT_DATE"=>$row["STATEMENT_DATE"],
        "op_debit_amount" => sprintf("%.2f", (abs($row['op_debit_amount']))),
        "op_credit_amount" => sprintf("%.2f", (abs($row['op_credit_amount']))),
        "closing_balance" => sprintf("%.2f", (abs($row['closing_balance']))),
        "TOT_CREDIT_WORD"=> $row["TOT_CREDIT_WORD"],
        "TOTAL_DEBIT_WORD"=> $row["TOTAL_DEBIT_WORD"],

       // "TOTAL_DEBIT_AMT" => $GRAND_TOTAL1 ,
        "TOTAL_DEBIT_AMT" =>sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        "TOTAL_CREDIT_AMOUNT" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        "FINAL_DEBIT_AMT" =>  ($row["closing_balance"] + $GRAND_TOTAL1) ,
        "FINAL_CREDIT_AMT" => ($row["closing_balance"] + $GRAND_TOTAL2) ,
        "Branch" => $Branch,
        "sdate" => $sdate,
        "edate" => $edate,
        "AC_TYPE" => $AC_TYPE,
        "TRAN_ACNO" => $TRAN_ACNO,
        "flag1" => $flag1,
        "trandrcr" => $trandrcr,
        "trandrcrc" => $trandrcrc,
        //"tran_glactype" => $tran_glactype,
        "TRANSTATUS" => $TRANSTATUS,
        "AC_ACNOTYPE" => $AC_ACNOTYPE,
        "TRAN_TYPE" => $TRAN_TYPE,
        "ENTRYTTYPE1" => $ENTRYTTYPE1,
        "ENTRYTTYPE2" => $ENTRYTTYPE2,
        
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
//print_r($data);
$report = new PHPJasperXML();
if($flag1=='1')
{
    $report->load_xml_file($filename1)    
     ->setDataSource($config)
     ->export('Pdf');
}
else
{
    $report->load_xml_file($filename2)    
     ->setDataSource($config)
     ->export('Pdf');

}

 
function convertNumberToWordsForIndia($number){
    echo "IN FUNCTION";
    //A function to convert numbers into Indian readable words with Cores, Lakhs and Thousands.
    $words = array(
    '0'=> 'zero' ,'1'=> 'one' ,'2'=> 'two' ,'3' => 'three','4' => 'four','5' => 'five',
    '6' => 'six','7' => 'seven','8' => 'eight','9' => 'nine','10' => 'ten',
    '11' => 'eleven','12' => 'twelve','13' => 'thirteen','14' => 'fouteen','15' => 'fifteen',
    '16' => 'sixteen','17' => 'seventeen','18' => 'eighteen','19' => 'nineteen','20' => 'twenty',
    '30' => 'thirty','40' => 'fourty','50' => 'fifty','60' => 'sixty','70' => 'seventy',
    '80' => 'eighty','90' => 'ninty');
    
    //First find the length of the number
    $number_length = strlen($number);
    //Initialize an empty array
    $number_array = array(0,0,0,0,0,0,0,0,0);        
    $received_number_array = array();
    
    //Store all received numbers into an array
    for($i=0;$i<$number_length;$i++){    
      $received_number_array[$i] = substr($number,$i,1);    
    }
  
    //Populate the empty array with the numbers received - most critical operation
    for($i=9-$number_length,$j=0;$i<9;$i++,$j++){ 
        $number_array[$i] = $received_number_array[$j]; 
    }
  
    $number_to_words_string = "";
    //Finding out whether it is teen ? and then multiply by 10, example 17 is seventeen, so if 1 is preceeded with 7 multiply 1 by 10 and add 7 to it.
    for($i=0,$j=1;$i<9;$i++,$j++){
        //"01,23,45,6,78"
        //"00,10,06,7,42"
        //"00,01,90,0,00"
        if($i==0 || $i==2 || $i==4 || $i==7){
            if($number_array[$j]==0 || $number_array[$i] == "1"){
                $number_array[$j] = intval($number_array[$i])*10+$number_array[$j];
                $number_array[$i] = 0;
            }
               
        }
    }
  
    $value = "";
    for($i=0;$i<9;$i++){
        if($i==0 || $i==2 || $i==4 || $i==7){    
            $value = $number_array[$i]*10; 
        }
        else{ 
            $value = $number_array[$i];    
        }            
        if($value!=0)         {    $number_to_words_string.= $words["$value"]." "; }
        if($i==1 && $value!=0){    $number_to_words_string.= "Crores "; }
        if($i==3 && $value!=0){    $number_to_words_string.= "Lakhs ";    }
        if($i==5 && $value!=0){    $number_to_words_string.= "Thousand "; }
        if($i==6 && $value!=0){    $number_to_words_string.= "Hundred "; }            
  
    }
    if($number_length>9){ $number_to_words_string = "Sorry This does not support more than 99 Crores"; }
    return ucwords(strtolower($number_to_words_string." Only."));
  }
  
?>
