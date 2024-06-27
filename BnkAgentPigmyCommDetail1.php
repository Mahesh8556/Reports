<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

// $filename = __DIR__.'/PigmyAgentCommission.jrxml';
$filename = __DIR__.'/BnkAgentPigmyCommDetail.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');


//connect pgAdmin database connection 
// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $startDate = "'01/01/2021'";
// $endDate = "'05/01/2021'";
$dateformate = "'DD/MM/YYYY'";
$ch ="'CH'"; 
$c ="'C'"; 
// $schemeAccountNo ="'101101301100001'"; 
$acnotype =$_GET['getschemename']; 
$acnotype =$_GET['getschemename']; 
$bankName = $_GET['bankName'];
$name = $_GET['name'];
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$scheme = $_GET['scheme'];
$schemeAccountNo = $_GET['schemeAccountNo'];
$branchName = $_GET['branchName'];
$flag = $_GET['flag'];
$acno = $_GET['acnotype'];
$schemecodeno = $_GET['schemecodeno'];

$acno1 = str_replace("'", "", $acno);
$schemecodeno1 = str_replace("'", "", $schemecodeno);
$bankName1 = str_replace("'", "", $bankName);
$name1 = str_replace("'", "", $name);
$startDate1 = str_replace("'", "", $startDate);
$endDate1 = str_replace("'", "", $endDate);
$branchName1 = str_replace("'", "", $branchName);



// echo $flag;
// $checktype;
//  $flag == 1 ? $checktype='true': $checktype='false';
//  echo $checktype;


if($flag == 1)
{
    $query=' SELECT "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE" , "PIGMY_COMMISSION_PERCENTAGE" , 
    TMP."TRAN_AMOUNT" ,  Round( ( TMP."TRAN_AMOUNT"  * TMPSLAB."PIGMY_COMMISSION_PERCENTAGE" /100) ,0) "COMMISSION_AMOUNT"
    From (SELECT "SR_NO" ,MAX("EFFECT_DATE"), "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
               "COMM_AGAINST_LN_PERCENT" FROM PGCOMMISSIONMASTER 
                WHERE CAST("EFFECT_DATE" AS DATE) <= CDATE('.$endDate.') 
                GROUP BY "SR_NO" , "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
                "COMM_AGAINST_LN_PERCENT")  TMPSLAB , 
       ( SELECT  "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
                    , SUM(CASE "TRAN_DRCR"  WHEN '.$c.' THEN  "TRAN_AMOUNT" ELSE 0 END) "TRAN_AMOUNT"
                          From PIGMYTRAN  WHERE PIGMYTRAN."AGENT_ACNOTYPE" = '.$acnotype.' 
                     And PIGMYTRAN."ENTRY_TYPE" = '.$ch.' 
                     And CAST(PIGMYTRAN."TRAN_DATE" AS DATE) >= TO_DATE( '.$startDate.', '.$dateformate.')  
                     And CAST(PIGMYTRAN."TRAN_DATE" AS DATE) <= TO_DATE( '.$endDate.', '.$dateformate.') 
                     And PIGMYTRAN."AGENT_ACTYPE" ='.$scheme.'
                     And  CAST(PIGMYTRAN."AGENT_ACNO" AS CHARACTER VARYING) ='.$schemeAccountNo.'
                          GROUP BY "AGENT_ACNOTYPE", "AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
              ) TMP 
          WHERE cast("TRAN_DATE" as date) between TO_DATE( '.$startDate.', '.$dateformate.')  and TO_DATE( '.$endDate.', '.$dateformate.')
          AND "TRAN_AMOUNT" BETWEEN TMPSLAB."AMOUNT_FROM" AND TMPSLAB."AMOUNT_TO" 
       Union All
    SELECT "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , CAST("AGENT_ACNO" AS CHARACTER VARYING) , "TRAN_DATE" , "PIGMY_COMMISSION_PERCENTAGE" , 
    TMP."TRAN_AMOUNT" ,  Round( ( TMP."TRAN_AMOUNT"  * TMPSLAB."PIGMY_COMMISSION_PERCENTAGE" /100) ,0) "COMMISSION_AMOUNT"
    From (SELECT "SR_NO" ,MAX("EFFECT_DATE"), "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
               "COMM_AGAINST_LN_PERCENT" FROM PGCOMMISSIONMASTER 
                WHERE CAST("EFFECT_DATE" AS DATE) <= CDATE('.$endDate.') 
                GROUP BY "SR_NO" , "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
                "COMM_AGAINST_LN_PERCENT")  TMPSLAB , 	 
          ( SELECT  "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
                          , SUM(CASE "TRAN_DRCR"  WHEN '.$c.' THEN  "TRAN_AMOUNT" ELSE 0 END) "TRAN_AMOUNT"  
                          From PIGMYCHART  WHERE PIGMYCHART."AGENT_ACNOTYPE" = '.$acnotype.' And PIGMYCHART."ENTRY_TYPE" = '.$ch.' And  
   CAST(PIGMYCHART."TRAN_DATE" AS DATE) >= TO_DATE( '.$startDate.', '.$dateformate.')  And
   CAST(PIGMYCHART."TRAN_DATE" AS DATE) <= TO_DATE( '.$endDate.', '.$dateformate.') And
   PIGMYCHART."AGENT_ACTYPE" ='.$scheme.' And cast(PIGMYCHART."AGENT_ACNO" as CHARACTER VARYING) ='.$schemeAccountNo.'
                          GROUP BY "AGENT_ACNOTYPE", "AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
              ) TMP 
          WHERE cast("TRAN_DATE" as date) between TO_DATE( '.$startDate.', '.$dateformate.')  and TO_DATE( '.$endDate.', '.$dateformate.')
          AND "TRAN_AMOUNT" BETWEEN TMPSLAB."AMOUNT_FROM" AND TMPSLAB."AMOUNT_TO" order by "TRAN_DATE" asc
                    ';
}


else{

$query ='SELECT "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "FROM_DATE" , "TO_DATE" , "PIGMY_COMMISSION_PERCENTAGE" , round(SUM("COMMISSION_AMOUNT"),0) "COMMISSION_AMOUNT" ,
SUM("TRAN_AMOUNT")  "TRAN_AMOUNT"
    FROM ( SELECT "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE" , "PIGMY_COMMISSION_PERCENTAGE" , "TRAN_AMOUNT"
                , Round( ( "TRAN_AMOUNT"  * TMPSLAB."PIGMY_COMMISSION_PERCENTAGE" /100) ,0) "COMMISSION_AMOUNT" ,  '.$startDate.' AS "FROM_DATE" , 
          '.$endDate.' AS "TO_DATE"
                From (SELECT "SR_NO" ,MAX("EFFECT_DATE"), "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
           "COMM_AGAINST_LN_PERCENT" FROM PGCOMMISSIONMASTER 
            WHERE CAST("EFFECT_DATE" AS DATE) <= CDATE('.$endDate.') 
            GROUP BY "SR_NO" , "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
            "COMM_AGAINST_LN_PERCENT"
                ) TMPSLAB , 
          ( SELECT  "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
                , SUM(CASE "TRAN_DRCR"  WHEN '.$c.' THEN  "TRAN_AMOUNT" ELSE 0 END) "TRAN_AMOUNT"
                      From PIGMYTRAN  WHERE PIGMYTRAN."AGENT_ACNOTYPE" = '.$acnotype.' And PIGMYTRAN."ENTRY_TYPE" = '.$ch.'
And CAST(PIGMYTRAN."TRAN_DATE" AS DATE) >= TO_DATE( '.$startDate.', '.$dateformate.')  
And CAST(PIGMYTRAN."TRAN_DATE" AS DATE) <= TO_DATE( '.$endDate.', '.$dateformate.')
And PIGMYTRAN."AGENT_ACTYPE" = '.$scheme.' And PIGMYTRAN."AGENT_ACNO" ='.$schemeAccountNo.'
                      GROUP BY "AGENT_ACNOTYPE", "AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
          ) TMP 
   WHERE cast("TRAN_DATE" as date) between TO_DATE( '.$startDate.', '.$dateformate.')  and TO_DATE( '.$endDate.', '.$dateformate.')
      AND "TRAN_AMOUNT" BETWEEN TMPSLAB."AMOUNT_FROM" AND TMPSLAB."AMOUNT_TO" 	   

          Union All
          SELECT "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , CAST("AGENT_ACNO" AS CHARACTER VARYING) , "TRAN_DATE" , "PIGMY_COMMISSION_PERCENTAGE" , "TRAN_AMOUNT"
                , Round(( "TRAN_AMOUNT"  * TMPSLAB."PIGMY_COMMISSION_PERCENTAGE" /100) ,0) "COMMISSION_AMOUNT" ,  '.$startDate.' AS "FROM_DATE" , '.$endDate.' AS "TO_DATE"  
                From (SELECT "SR_NO" ,MAX("EFFECT_DATE"), "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
           "COMM_AGAINST_LN_PERCENT" FROM PGCOMMISSIONMASTER 
            WHERE CAST("EFFECT_DATE" AS DATE) <= CDATE('.$endDate.') 
            GROUP BY "SR_NO" , "SLAB_TYPE", "AMOUNT_FROM" , "AMOUNT_TO" , "PIGMY_COMMISSION_PERCENTAGE" , 
            "COMM_AGAINST_LN_PERCENT"
                ) TMPSLAB , ( SELECT  "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
                      , SUM(CASE "TRAN_DRCR"  WHEN '.$c.' THEN  "TRAN_AMOUNT" ELSE 0 END) "TRAN_AMOUNT"  
                      From PIGMYCHART  WHERE PIGMYCHART."AGENT_ACNOTYPE" = '.$acnotype.' And PIGMYCHART."ENTRY_TYPE" = '.$ch.'
And CAST(PIGMYCHART."TRAN_DATE" AS DATE) >= TO_DATE( '.$startDate.', '.$dateformate.')  
And CAST(PIGMYCHART."TRAN_DATE" AS DATE) <= TO_DATE( '.$endDate.', '.$dateformate.')
And PIGMYCHART."AGENT_ACTYPE" = '.$scheme.' And CAST(PIGMYCHART."AGENT_ACNO" AS CHARACTER VARYING) ='.$schemeAccountNo.'
                      GROUP BY "AGENT_ACNOTYPE", "AGENT_ACTYPE" , "AGENT_ACNO" , "TRAN_DATE"
          ) TMP 
              WHERE cast("TRAN_DATE" as date) between TO_DATE( '.$startDate.', '.$dateformate.')  and TO_DATE( '.$endDate.', '.$dateformate.')
      AND "TRAN_AMOUNT" BETWEEN TMPSLAB."AMOUNT_FROM" AND TMPSLAB."AMOUNT_TO" 
            ) s 
           GROUP BY "AGENT_ACNOTYPE" ,"AGENT_ACTYPE" , "AGENT_ACNO"  , "FROM_DATE" , 
           "TO_DATE" , "PIGMY_COMMISSION_PERCENTAGE"
        ';  
}

    // echo $query;
$sql =  pg_query($conn,$query);

$i = 0;
$GRAND_TOTAL = 0 ;
$Percentage = 0 ;
$totalcommission = 0;
$type = '';


while($row = pg_fetch_assoc($sql)){
    $GRAND_TOTAL = $GRAND_TOTAL + $row['TRAN_AMOUNT'];
    $Percentage = ($row['PIGMY_COMMISSION_PERCENTAGE']/100)*$GRAND_TOTAL ; 
    $totalcommission = ($GRAND_TOTAL * $row['PIGMY_COMMISSION_PERCENTAGE'] )/100;
    $tmp=[
        'bankName' => $bankName1,
        'from_date' => $startDate1,
        'to_date' => $endDate1,
        'branchName' => $branchName1,
        'name' => $name1,
        'totalamount'=> sprintf("%.2f",($row['TRAN_AMOUNT'] + 0.0)),
        'commissionAmt' => sprintf("%.2f",($row['COMMISSION_AMOUNT'] + 0.0)),
        'tran_date' => $row['TRAN_DATE'],
        'grandtotal' => sprintf("%.2f",($GRAND_TOTAL + 0.0)),
        'totalcommission' => sprintf("%.2f",($totalcommission + 0.0)),
        'percentage' => $row['PIGMY_COMMISSION_PERCENTAGE'],
        'flag' => $flag,
        'acno' => $acno1,
        'schemecodeno' => $schemecodeno1,
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

