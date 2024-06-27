<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/BnkDeadbalDepr.jrxml';
$filename2 = __DIR__ . '/BnkDeadbalDeprCategory.jrxml';
$filename1 = __DIR__ . '/BnkDeadbalDeprAll.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

// $date = "'31/03/2020'";
$int = "'0'";

$bankName = $_GET['bankName'];
$branch = $_GET['branch'];
$Rdio = $_GET['radio'];
$branch_name = $_GET['branchName'];
$TO_DATE = $_GET['startDate'];
$flag = $_GET['flag'];
$getCategoryNo = $_GET['getCategoryNo'];
$getCategoryName = $_GET['getCategoryName'];

// $flag == 0 ? $checktype='true': $checktype='false';
// echo $checktype;


// echo $sdate;
$bankName1 = str_replace("'", "", $bankName);
$branch_name1 = str_replace("'", "", $branch_name);
$getCategoryName1 = str_replace("'", "", $getCategoryName);
$TO_DATE1 = str_replace("'", "", $TO_DATE);


// echo $sdate;

// $query1 = 'select * from ownbranchmaster where id = ' . $branch.'';
// echo $query1;
// echo $conn;

// $result = pg_query($conn, $query1);
// print_r($result);
$code = '0';
$GL = "'GL'";
// $TO_DATE = "'28/08/2023'";
$DD = "'DD/MM/YYYY'";
$DPRP = "'DPRP'";
$ITEM_TYPE = "'1'";
$TRAN_STATUS = "'0'";
$TRAN_STATUS1 = "'1'";

if($flag==1)
{
    
$query = ' SELECT ITEMMASTER."ITEM_CODE" ,ITEMMASTER."ITEM_TYPE", ITEMMASTER."BRANCH_CODE", ITEMMASTER."DEPR_CATEGORY", ITEMMASTER."ITEM_NAME" 
, ITEMMASTER."PURCHASE_DATE" , ITEMMASTER."SUPPLIER_NAME" ,ITEMMASTER."PURCHASE_RATE" , ITEMMASTER."PURCHASE_QUANTITY" , 
ITEMMASTER."PURCHASE_VALUE", DEPRCATEGORY."NAME"  
,itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,0) "PRVBALANCE"  
 , COALESCE(DEADSTOCK."DEPRICIATION_AMT",0) "DEPRICIATION_AMT" 
 , COALESCE(DEADSTOCK."CLOSING_BALANCE" ,0) "CLOSING_BALANCE"  
, VWTMPDEPRRATE."DEPR_RATE" 
FROM ITEMMASTER , 
(  SELECT ID AS "CODE" ,"CATEGORY" , "DEPR_RATE" FROM DEPRRATE ,  
   ( SELECT MAX("EFFECT_DATE") "EFFECT_DATE" FROM DEPRRATE 
     WHERE CAST("EFFECT_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')  ) TMP 
     WHERE TMP."EFFECT_DATE" = DEPRRATE."EFFECT_DATE" 		 
) VWTMPDEPRRATE, DEPRCATEGORY ,
( SELECT ITEMMASTER."ITEM_TYPE" , ITEMMASTER."ITEM_CODE" , 0 "TRAN_AMT" ,    
0 "PRVDEPRICIATION_AMT" ,PDEADSTOCK."CLOSING_BALANCE" ,
PDEADSTOCK."DEPRICIATION_AMT"  
From ITEMMASTER 
LEFT OUTER JOIN (SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
, DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY"
,DEADSTOCKDETAIL."TRAN_AMOUNT" AS "DEPRICIATION_AMT"
, DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
, DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
, ITEMMASTER."BRANCH_CODE" , itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,1) "CLOSING_BALANCE" 
From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
   Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
   AND DEADSTOCKDETAIL."deadstockHeader" = DEADSTOCKHEADER.id 
    AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE"  AS INTEGER)
   AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
    AND DEADSTOCKHEADER."TRAN_ENTRY_TYPE" = '.$DPRP.'
   AND DEADSTOCKHEADER."BRANCH_CODE" = '.$branch.'
   AND ((DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS1.' AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')) 
    OR (CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) = TO_DATE('.$TO_DATE.','.$DD.') and DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS.')) 
   AND DEADSTOCKDETAIL."ITEM_TYPE" = '.$ITEM_TYPE.'
   AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')	
   ORDER BY "ITEM_CODE"			  
)PDEADSTOCK ON ITEMMASTER."ITEM_CODE" = PDEADSTOCK."ITEM_CODE" AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = PDEADSTOCK."ITEM_TYPE" ) DEADSTOCK
WHERE CAST(DEPRCATEGORY."CODE" AS INTEGER) = CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER)
AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = CAST(DEADSTOCK."ITEM_TYPE" AS INTEGER)
AND ITEMMASTER."ITEM_CODE" = DEADSTOCK."ITEM_CODE"
AND CAST(ITEMMASTER."PURCHASE_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER) = VWTMPDEPRRATE."CODE" 
AND ITEMMASTER."BRANCH_CODE"= '.$branch.'
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER)<>0
order by CAST("PURCHASE_DATE" AS DATE) 

';
}
else if($flag==0){
    
$query = ' SELECT ITEMMASTER."ITEM_CODE" ,ITEMMASTER."ITEM_TYPE", ITEMMASTER."BRANCH_CODE", ITEMMASTER."DEPR_CATEGORY", ITEMMASTER."ITEM_NAME" 
, ITEMMASTER."PURCHASE_DATE" , ITEMMASTER."SUPPLIER_NAME" ,ITEMMASTER."PURCHASE_RATE" , ITEMMASTER."PURCHASE_QUANTITY" , 
ITEMMASTER."PURCHASE_VALUE", DEPRCATEGORY."NAME"  
,itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,0) "PRVBALANCE"  
 , COALESCE(DEADSTOCK."DEPRICIATION_AMT",0) "DEPRICIATION_AMT" 
 , COALESCE(DEADSTOCK."CLOSING_BALANCE" ,0) "CLOSING_BALANCE"  
, VWTMPDEPRRATE."DEPR_RATE" 
FROM ITEMMASTER , 
(  SELECT ID AS "CODE" ,"CATEGORY" , "DEPR_RATE" FROM DEPRRATE ,  
   ( SELECT MAX("EFFECT_DATE") "EFFECT_DATE" FROM DEPRRATE 
     WHERE CAST("EFFECT_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')  ) TMP 
     WHERE TMP."EFFECT_DATE" = DEPRRATE."EFFECT_DATE" 		 
) VWTMPDEPRRATE, DEPRCATEGORY ,
( SELECT ITEMMASTER."ITEM_TYPE" , ITEMMASTER."ITEM_CODE" , 0 "TRAN_AMT" ,    
0 "PRVDEPRICIATION_AMT" ,PDEADSTOCK."CLOSING_BALANCE" ,
PDEADSTOCK."DEPRICIATION_AMT"  
From ITEMMASTER 
LEFT OUTER JOIN (SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
, DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY"
,DEADSTOCKDETAIL."TRAN_AMOUNT" AS "DEPRICIATION_AMT"
, DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
, DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
, ITEMMASTER."BRANCH_CODE" , itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,1) "CLOSING_BALANCE" 
From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
   Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
   AND DEADSTOCKDETAIL."deadstockHeader" = DEADSTOCKHEADER.id 
    AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE"  AS INTEGER)
   AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
    AND DEADSTOCKHEADER."TRAN_ENTRY_TYPE" = '.$DPRP.'
   AND DEADSTOCKHEADER."BRANCH_CODE" = '.$branch.'
   AND ((DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS1.' AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')) 
    OR (CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) = TO_DATE('.$TO_DATE.','.$DD.') and DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS.')) 
   AND DEADSTOCKDETAIL."ITEM_TYPE" = '.$ITEM_TYPE.'
   AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')	
   ORDER BY "ITEM_CODE"			  
)PDEADSTOCK ON ITEMMASTER."ITEM_CODE" = PDEADSTOCK."ITEM_CODE" AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = PDEADSTOCK."ITEM_TYPE" ) DEADSTOCK
WHERE CAST(DEPRCATEGORY."CODE" AS INTEGER) = CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER)
AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = CAST(DEADSTOCK."ITEM_TYPE" AS INTEGER)
AND ITEMMASTER."ITEM_CODE" = DEADSTOCK."ITEM_CODE"
AND CAST(ITEMMASTER."PURCHASE_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER) = VWTMPDEPRRATE."CODE" 
AND ITEMMASTER."BRANCH_CODE"= '.$branch.'
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER) = '.$getCategoryNo.'
order by CAST("PURCHASE_DATE" AS DATE) 


';
}
if($flag==2)
{
    
$query = ' SELECT ITEMMASTER."ITEM_CODE" ,ITEMMASTER."ITEM_TYPE", ITEMMASTER."BRANCH_CODE", ITEMMASTER."DEPR_CATEGORY", ITEMMASTER."ITEM_NAME" 
, ITEMMASTER."PURCHASE_DATE" , ITEMMASTER."SUPPLIER_NAME" ,ITEMMASTER."PURCHASE_RATE" , ITEMMASTER."PURCHASE_QUANTITY" , 
ITEMMASTER."PURCHASE_VALUE", DEPRCATEGORY."NAME"  
,itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,0) "PRVBALANCE"  
 , COALESCE(DEADSTOCK."DEPRICIATION_AMT",0) "DEPRICIATION_AMT" 
 , COALESCE(DEADSTOCK."CLOSING_BALANCE" ,0) "CLOSING_BALANCE"  
, VWTMPDEPRRATE."DEPR_RATE" 
FROM ITEMMASTER , 
(  SELECT ID AS "CODE" ,"CATEGORY" , "DEPR_RATE" FROM DEPRRATE ,  
   ( SELECT MAX("EFFECT_DATE") "EFFECT_DATE" FROM DEPRRATE 
     WHERE CAST("EFFECT_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')  ) TMP 
     WHERE TMP."EFFECT_DATE" = DEPRRATE."EFFECT_DATE" 		 
) VWTMPDEPRRATE, DEPRCATEGORY ,
( SELECT ITEMMASTER."ITEM_TYPE" , ITEMMASTER."ITEM_CODE" , 0 "TRAN_AMT" ,    
0 "PRVDEPRICIATION_AMT" ,PDEADSTOCK."CLOSING_BALANCE" ,
PDEADSTOCK."DEPRICIATION_AMT"  
From ITEMMASTER 
LEFT OUTER JOIN (SELECT DEADSTOCKDETAIL."TRAN_DATE" , DEADSTOCKDETAIL."TRAN_NO" , DEADSTOCKDETAIL."TRAN_DRCR" 
, DEADSTOCKDETAIL."ITEM_TYPE" , DEADSTOCKDETAIL."ITEM_CODE", DEADSTOCKDETAIL."ITEM_RATE" ,DEADSTOCKDETAIL."ITEM_QTY"
,DEADSTOCKDETAIL."TRAN_AMOUNT" AS "DEPRICIATION_AMT"
, DEADSTOCKHEADER."TRAN_STATUS" , DEADSTOCKHEADER."TRAN_ENTRY_TYPE", DEADSTOCKHEADER."NARRATION" 
, DEADSTOCKHEADER."RESO_NO" ,DEADSTOCKHEADER."RESO_DATE" , DEADSTOCKHEADER."TRAN_SUPPLIER_NAME" , ITEMMASTER."OP_BAL_DATE" 
, ITEMMASTER."BRANCH_CODE" , itembalance(CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) , ITEMMASTER."ITEM_CODE" ,'.$TO_DATE.',2,1) "CLOSING_BALANCE" 
From DEADSTOCKDETAIL, DEADSTOCKHEADER , ITEMMASTER 
   Where DEADSTOCKDETAIL."TRAN_DATE" = DEADSTOCKHEADER."TRAN_DATE" 
   AND DEADSTOCKDETAIL."deadstockHeader" = DEADSTOCKHEADER.id 
    AND DEADSTOCKDETAIL."ITEM_TYPE" = CAST(ITEMMASTER."ITEM_TYPE"  AS INTEGER)
   AND DEADSTOCKDETAIL."ITEM_CODE" = ITEMMASTER."ITEM_CODE" 
    AND DEADSTOCKHEADER."TRAN_ENTRY_TYPE" = '.$DPRP.'
   AND DEADSTOCKHEADER."BRANCH_CODE" = '.$branch.'
   AND ((DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS1.' AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')) 
    OR (CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) = TO_DATE('.$TO_DATE.','.$DD.') and DEADSTOCKHEADER."TRAN_STATUS" = '.$TRAN_STATUS.')) 
   AND DEADSTOCKDETAIL."ITEM_TYPE" = '.$ITEM_TYPE.'
   AND CAST(DEADSTOCKHEADER."TRAN_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')	
   ORDER BY "ITEM_CODE"			  
)PDEADSTOCK ON ITEMMASTER."ITEM_CODE" = PDEADSTOCK."ITEM_CODE" AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = PDEADSTOCK."ITEM_TYPE" ) DEADSTOCK
WHERE CAST(DEPRCATEGORY."CODE" AS INTEGER) = CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER)
AND CAST(ITEMMASTER."ITEM_TYPE" AS INTEGER) = CAST(DEADSTOCK."ITEM_TYPE" AS INTEGER)
AND ITEMMASTER."ITEM_CODE" = DEADSTOCK."ITEM_CODE"
AND CAST(ITEMMASTER."PURCHASE_DATE" AS DATE) <= TO_DATE('.$TO_DATE.','.$DD.')
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER) = VWTMPDEPRRATE."CODE" 
AND ITEMMASTER."BRANCH_CODE"= '.$branch.'
AND CAST(ITEMMASTER."DEPR_CATEGORY" AS INTEGER)<>0
order by DEPRCATEGORY."NAME",CAST("PURCHASE_DATE" AS DATE) ';

}


//  echo $query;

$sql =  pg_query($conn, $query);
// echo  pg_num_rows($sql);
$i = 0;
$j = 0;

$LEDGER_TOT = 0;
$ledgeType = '';
$TotalOpeningBal = 0;
$TotalCloseBal = 0;
$TotalDeprAmt = 0;
$openbal = 0;
$closebal = 0;
$dpramt = 0;


if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
} else {
    // print_r(pg_fetch_assoc($sql));
    while ($row = pg_fetch_assoc($sql)) {
        // print_r($row);
        // $LEDGER_TOT = $LEDGER_TOT + $row['ledgerbalance'];

        // if ((int)$row['ledgerbalance'] != 0) {
        //     if ($row['ledgerbalance'] < 0) {
        //         $ledgeType = 'Cr';
        //     } else {
        //         $ledgeType = 'Dr';
        //     }

       
          

           
 // SchemeWise Total for PRVBALANCE
if(isset($varsd1)){
    if($varsd1 == $row['NAME']){
        $sc1[] = $row['PRVBALANCE']; 
        $sumVar1 += $row['PRVBALANCE'];
    }
    else{
        $sumVar1=0;
        $sc1 = array_diff($sc1, $sc1);
        $varsd1 = $row['NAME'];
        $sc1[] = $row['PRVBALANCE'];
        $sumVar1 += $row['PRVBALANCE'];
    }
}else{
    $sumVar1=0;
    $varsd1 = $row['NAME'];
    $sc1[] = $row['PRVBALANCE'];
    $sumVar1 += $row['PRVBALANCE'];
}
$result1[$varsd1] = $sc1;
$sumArray1[$varsd1] = $sumVar1;



// SchemeWise Total for DEPRICIATION_AMT
if(isset($varsd2)){
    if($varsd2 == $row['NAME']){
        $sc2[] = $row['DEPRICIATION_AMT']; 
        $sumVar2 += $row['DEPRICIATION_AMT'];
    }
    else{
        $sumVar2=0;
        $sc2 = array_diff($sc2, $sc2);
        $varsd2 = $row['NAME'];
        $sc2[] = $row['DEPRICIATION_AMT'];
        $sumVar2 += $row['DEPRICIATION_AMT'];
    }
}else{
    $sumVar2=0;
    $varsd2 = $row['NAME'];
    $sc2[] = $row['DEPRICIATION_AMT'];
    $sumVar2 += $row['DEPRICIATION_AMT'];
}
$result2[$varsd2] = $sc2;
$sumArray2[$varsd2] = $sumVar2;




/// SchemeWise Total for CLOSING_BALANCE
if(isset($varsd3)){
    if($varsd3 == $row['NAME']){
        $sc3[] = $row['CLOSING_BALANCE']; 
        $sumVar3 += $row['CLOSING_BALANCE'];
    }
    else{
        $sc3 = []; // Resetting the array
        $varsd3 = $row['NAME'];
        $sc3[] = $row['CLOSING_BALANCE'];
        $sumVar3 = $row['CLOSING_BALANCE']; 
    }
} else {
    $varsd3 = $row['NAME'];
    $sc3[] = $row['CLOSING_BALANCE'];
    $sumVar3 = $row['CLOSING_BALANCE'];
}

$result3[$varsd3] = $sc3;
$sumArray3[$varsd3] = $sumVar3;



/// SchemeWise Total for PURCHASE_VALUE
if(isset($varsd4)){
    if($varsd4 == $row['NAME']){
        $sc4[] = $row['PURCHASE_VALUE']; 
        $sumVar4 += $row['PURCHASE_VALUE'];
    }
    else{
        $sc4 = []; // Resetting the array
        $varsd4 = $row['NAME'];
        $sc4[] = $row['PURCHASE_VALUE'];
        $sumVar4 = $row['PURCHASE_VALUE']; 
    }
} else {
    $varsd4 = $row['NAME'];
    $sc4[] = $row['PURCHASE_VALUE'];
    $sumVar4 = $row['PURCHASE_VALUE'];
}

$result4[$varsd4] = $sc4;
$sumArray4[$varsd4] = $sumVar4;



/// SchemeWise Total for PURCHASE_RATE
if(isset($varsd5)){
    if($varsd5 == $row['NAME']){
        $sc5[] = $row['PURCHASE_RATE']; 
        $sumVar5 += $row['PURCHASE_RATE'];
    }
    else{
        $sc5 = []; 
        $varsd5 = $row['NAME'];
        $sc5[] = $row['PURCHASE_RATE'];
        $sumVar5 = $row['PURCHASE_RATE']; 
    }
} else {
    $varsd5 = $row['NAME'];
    $sc5[] = $row['PURCHASE_RATE'];
    $sumVar5 = $row['PURCHASE_RATE'];
}

$result5[$varsd5] = $sc5;
$sumArray5[$varsd5] = $sumVar5;



/// SchemeWise Total for DEPR_RATE
if(isset($varsd6)){
    if($varsd6 == $row['NAME']){
        $sc6[] = $row['DEPR_RATE']; 
        $sumVar6 += $row['DEPR_RATE'];
    }
    else{
        $sc6 = []; 
        $varsd6 = $row['NAME'];
        $sc6[] = $row['DEPR_RATE'];
        $sumVar6 = $row['DEPR_RATE']; 
    }
} else {
    $varsd6 = $row['NAME'];
    $sc6[] = $row['DEPR_RATE'];
    $sumVar6 = $row['DEPR_RATE'];
}

$result6[$varsd6] = $sc6;
$sumArray6[$varsd6] = $sumVar6;


//Grand Total
$openbal += $row['PRVBALANCE'];
$closebal += $row['CLOSING_BALANCE'];
$dpramt += $row['DEPRICIATION_AMT'];
$purchaseval += $row['PURCHASE_VALUE'];
$rate += $row['PURCHASE_RATE'];
$dpr += $row['DEPR_RATE'];

$totall+=$row['PRVBALANCE'];
$totall1+=$row['CLOSING_BALANCE'];
$totall2+=$row['DEPRICIATION_AMT'];
$totall3+=$row['PURCHASE_VALUE'];
$totall4+=$row['PURCHASE_RATE'];
$totall5+=$row['DEPR_RATE'];



        $tmp=[
          
                'ITEM_CODE' => $row['ITEM_CODE'],
                'ITEM_NAME' => $row['ITEM_NAME'],
                'PURCHASE_DATE'=> $row['PURCHASE_DATE'],
                'SUPPLIER_NAME'=> $row['SUPPLIER_NAME'],
                'PURCHASE_VALUE'=> $row['PURCHASE_VALUE'],
                'PURCHASE_RATE'=> $row['PURCHASE_RATE'],
                'DEPR_RATE'=> $row['DEPR_RATE'],
                'NAME'=> $row['NAME'],
                'PURCHASE_QUANTITY'=> $row['PURCHASE_QUANTITY'],
                'PRVBALANCE'=> sprintf("%.2f",($row['PRVBALANCE'])),
                'DEPRICIATION_AMT'=> sprintf("%.2f",($row['DEPRICIATION_AMT'])),
                'CLOSING_BALANCE'=> sprintf("%.2f",($row['CLOSING_BALANCE'])),
                'BRANCH_CODE' => $branch,
                // 'purchasetotal' =>  $PURCHASE_Total ,
                // 'DepramtTotal' =>  $DEPRAMT_Total ,
        
                'date'=> $TO_DATE1,
                'TotalOpeningBal' => sprintf("%.2f",($sumArray1[$varsd1]+ 0.0)),
                'TotalCloseBal' => sprintf("%.2f",($sumArray3[$varsd3]+ 0.0)),
                'TotalDeprAmt' => sprintf("%.2f",($sumArray2[$varsd2]+ 0.0)),
                'Totalpurchaseval' => sprintf("%.2f",($sumArray4[$varsd4]+ 0.0)),
                'Totalrateval' => sprintf("%.2f",($sumArray5[$varsd5]+ 0.0)),
                'Totaldprate' => sprintf("%.2f",($sumArray6[$varsd6]+ 0.0)),
                'branch' => $branch_name1,
                'bankName' => $bankName1,
                'flag' => $flag,
                'getCategoryName' => $getCategoryName1,
                'schtot'=>  sprintf("%.2f",($openbal)),
                'dramt'=>  sprintf("%.2f",($dpramt)),
                'close'=>  sprintf("%.2f",($closebal)),
                'purval'=>  sprintf("%.2f",($purchaseval)),
                'rate'=>  sprintf("%.2f",($rate)),
                'dpr'=>  sprintf("%.2f",($dpr)),
                'totall' => sprintf("%.2f",($totall)),
                'totall1' => sprintf("%.2f",($totall1)),
                'totall2' => sprintf("%.2f",($totall2)),
                'totall3' => sprintf("%.2f",($totall3)),
                'totall4' => sprintf("%.2f",($totall4)),
                'totall5' => sprintf("%.2f",($totall5)),

            ];
            $data[$i]=$tmp;
            $i++;
        }
    }
    ob_end_clean();

    if($flag === '0')
    {
        $config = ['driver'=>'array','data'=>$data];
        // print_r($data);
        $report = new PHPJasperXML();
        $report->load_xml_file($filename)    
             ->setDataSource($config)
             ->export('Pdf');
    }
    else if($flag === '1')
    {
        $config = ['driver'=>'array','data'=>$data];
    // print_r($data);
    $report = new PHPJasperXML();
    $report->load_xml_file($filename1)    
         ->setDataSource($config)
         ->export('Pdf');
    } 
    else
    {
        $config = ['driver'=>'array','data'=>$data];
    // print_r($data);
    $report = new PHPJasperXML();
    $report->load_xml_file($filename2)    
         ->setDataSource($config)
         ->export('Pdf');
    } 
    ?>

