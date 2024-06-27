<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/usercashdenomination.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$BRANCH_CODE = $_GET['BRANCH_CODE'];
$date = $_GET['date'];
 

$bankName = str_replace("'", "", $bankName);
// $stdate_ = str_replace("'", "", $stdate);
$date= str_replace("'", "", $date);
$branchName = str_replace("'", "", $branchName);


$query='Select USERDEFINATION."USER_NAME" , "DENO_200", "DENO_2000", "DENO_1000", "DENO_500",
"DENO_100", "DENO_50",  "DENO_20", "DENO_10", "DENO_5", "DENO_2", "DENO_1", "DENO_COINS_CASH",
"DEPOSITS", "WITHDRAWAL", cast("TOTAL_AMOUNT" as float) 
from USERDENOMINATION 
lEFT OUTER JOIN USERDEFINATION on CAST(USERDENOMINATION."CASHIER_CODE" AS INTEGER)= USERDEFINATION."id"
where USERDENOMINATION."BRANCH_CODE"='.$BRANCH_CODE.'
ORDER BY "USER_NAME" ';


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$Total=0;
$var=0;
$var1=0;
$var2=0;
$var3=0;
$var4=0;
$var5=0;
$var6=0;
$var7=0;
$var8=0;
$var9=0;
$var10=0;
$var11=0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
             
             $var=$var + $row['DENO_2000'],
             $t1=2000*$var,
             $var1=$var1 + $row['DENO_1000'],
             $t2=1000*$var1,
             $var2=$var2 + $row['DENO_500'],
             $t3=500*$var2,
             $var3=$var3 + $row['DENO_200'],
             $t4=200*$var3,
             $var4=$var4 + $row['DENO_100'],
             $t5=100*$var4 ,
             $var5=$var5 + $row['DENO_50'],
             $t6=50*$var5,
             $var6=$var6 + $row['DENO_20'],
             $t7=20*$var6,
             $var7=$var7 + $row['DENO_10'],
             $t8=10*$var7,
             $var8=$var8 + $row['DENO_5'],
             $t9=5*$var8,
             $var9=$var9 + $row['DENO_2'],
             $t10=2*$var9,
             $var10=$var10 + $row['DENO_1'],
             $t11=1*$var10,
             $var11=$var11 + $row['DENO_COINS_CASH'],
             $t12=$t12+$var11,

             $rowtotal=2000*$row['DENO_2000']+1000*$row['DENO_1000']+500*$row['DENO_500']
             +200*$row['DENO_200']+100*$row['DENO_100']+50*$row['DENO_50']+20*$row['DENO_20']+
             10*$row['DENO_10']+5*$row['DENO_5']+2*$row['DENO_2']+1*$row['DENO_1']+$row['DENO_COINS_CASH'],
             $Total=$t1+$t2+$t3+$t4+$t5+$t6+$t7+$t8+$t9+$t10+$t11+$t12,

             
            'USER_NAME' => $row['USER_NAME'],
            'DENO_2000' => $row['DENO_2000'],
            'DENO_1000' => $row['DENO_1000'],
            'DENO_500' => $row['DENO_500'],
            'DENO_200' => $row['DENO_200'],
            'DENO_100' => $row['DENO_100'],
            'DENO_50' => $row['DENO_50'],
            'DENO_20'=>$row['DENO_20'],
            'DENO_10' => $row['DENO_10'],
            'DENO_5' => $row['DENO_5'],
            'DENO_2' => $row['DENO_2'],
            'DENO_1' => $row['DENO_1'],
            'DENO_COINS_CASH' =>  sprintf("%.2f", ($row['DENO_COINS_CASH'] + 0.0)),
            // 'WITHDRAWAL' => $row['WITHDRAWAL'],
            // 'DEPOSITS' => $row['DEPOSITS'],
            // 'TOTAL_AMOUNT' => sprintf("%.2f", ($row['TOTAL_AMOUNT'] + 0.0)),
            


            'BRANCH_CODE' => $BRANCH_CODE,
            'date' => $date,
            'branchName' => $branchName,
            'rowtotal' => sprintf("%.2f", ($rowtotal+ 0.0)),
            // 'Total' => $Total,
            'Total' => sprintf("%.2f", ($Total + 0.0)),
            't1'=>sprintf("%.2f", ($t1+ 0.0)),
            't2'=>sprintf("%.2f", ($t2+ 0.0)),  
            't3'=>sprintf("%.2f", ($t3+ 0.0)),
            't4'=>sprintf("%.2f", ($t4+ 0.0)),
            't5'=>sprintf("%.2f", ($t5+ 0.0)),
            't6'=>sprintf("%.2f", ($t6+ 0.0)), 
            't7'=>sprintf("%.2f", ($t7+ 0.0)),
            't8'=>sprintf("%.2f", ($t8+ 0.0)), 
            't9'=>sprintf("%.2f", ($t9+ 0.0)),
            't10'=>sprintf("%.2f", ($t10+ 0.0)),
            't11'=>sprintf("%.2f", ($t11+ 0.0)),
            't12'=>sprintf("%.2f", ($t12+ 0.0)),


            // 'revoke' => $revoke,
             'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
    // $count++;
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();

//print_r($data);
 //echo $query;
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
// //}
?>
