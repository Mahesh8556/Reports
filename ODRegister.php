<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/ODRegister.jrxml';
$filename1 = __DIR__.'/odreg.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";

$bankName  = $_GET['bankName'];
$startDate = $_GET['startDate'];
$endDate   = $_GET['endDate'];
$branchCode = $_GET['branchCode'];


$startDate_ = str_replace("'", "", $startDate);
$bankName = str_replace("'", "", $bankName);
$endDate_ = str_replace("'", "", $endDate);

// echo $etdate;
// echo $Branch;
// $NAME  = $_GET['NAME'];
$branch = $_GET['branch'];
// $date2 = $_GET['date2'];
$actype = $_GET['schemecode'];
$flag   = $_GET['flag'];
$type = $_GET['type'];
$zero = "'0'";
$BANKACNO = "'101101101100004'";

$type1 = str_replace("'", "", $type);



$branchData = pg_query($conn,'select * from ownbranchmaster where id ='.$branch);
while($row = pg_fetch_assoc($branchData)){
    $branchName = $row['NAME'];
}
echo $flag;
$checktype;
 $flag == 1? $checktype='true': $checktype='false';
 echo $checktype;
if($flag === '1'){
        $query = 'SELECT DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" ,  DPMASTER."AC_NAME",
        DPMASTER."BANKACNO" ,SUM(CAST(TODTRAN."AC_ODAMT" AS float)) OD_AMT ,TODTRAN."AC_ODDAYS",TODTRAN."AC_ODDATE" FROM DPMASTER
        INNER JOIN TODTRAN ON TODTRAN."AC_NO" = DPMASTER."BANKACNO" 
        WHERE CAST(TODTRAN."AC_ODDAYS" AS integer) = 0  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) >= CAST('.$startDate.' AS DATE)  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) <= CAST('.$endDate.' AS DATE)  
        AND TODTRAN."AC_TYPE"='.$actype.'  
        AND ("AC_CLOSEDT" IS NULL OR CAST("AC_CLOSEDT" AS DATE) > CAST('.$endDate.' AS DATE)) 
        AND DPMASTER."BRANCH_CODE" = '.$branch.'
        GROUP BY TODTRAN."AC_ODDATE" , DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" ,DPMASTER."AC_NAME", DPMASTER."BANKACNO", TODTRAN."AC_ODDAYS"
        
        UNION ALL

        SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" , LNMASTER."AC_NAME",
        LNMASTER."BANKACNO" ,SUM(CAST(TODTRAN."AC_ODAMT" AS FLOAT)) OD_AMT ,TODTRAN."AC_ODDAYS",TODTRAN."AC_ODDATE" FROM LNMASTER
        INNER JOIN TODTRAN ON TODTRAN."AC_NO" = LNMASTER."BANKACNO" 
        WHERE CAST(TODTRAN."AC_ODDAYS" AS integer) = 0  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) >= CAST('.$startDate.' AS DATE)  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) <= CAST('.$endDate.' AS DATE)  
        AND TODTRAN."AC_TYPE"='.$actype.'  
        AND ("AC_CLOSEDT" IS NULL OR CAST("AC_CLOSEDT" AS DATE) > CAST('.$endDate.' AS DATE)) 
        AND LNMASTER."BRANCH_CODE" = '.$branch.'
        GROUP BY TODTRAN."AC_ODDATE" , LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" ,LNMASTER."AC_NAME", LNMASTER."BANKACNO", TODTRAN."AC_ODDAYS"
        ';
}else{
        $query = 'SELECT DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" , DPMASTER."AC_NAME",
        DPMASTER."BANKACNO" ,SUM(CAST(TODTRAN."AC_SODAMT" AS FLOAT)) OD_AMT ,TODTRAN."AC_ODDAYS",TODTRAN."AC_ODDATE",TODTRAN."RELEASE_DATE"  FROM DPMASTER
        INNER JOIN TODTRAN ON TODTRAN."AC_NO" = DPMASTER."BANKACNO" 
        WHERE CAST(TODTRAN."AC_ODDAYS" AS integer) > 0  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) >= CAST('.$startDate.' AS DATE)  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) <= CAST('.$endDate.' AS DATE)  
        AND TODTRAN."AC_TYPE"='.$actype.'  
        AND DPMASTER."BRANCH_CODE" = '.$branch.'
        AND ("AC_CLOSEDT" IS NULL OR CAST("AC_CLOSEDT" AS DATE) > CAST('.$endDate.' AS DATE)) 
        GROUP BY TODTRAN."AC_ODDATE" , DPMASTER."AC_ACNOTYPE" , DPMASTER."AC_TYPE" ,DPMASTER."AC_NAME", DPMASTER."BANKACNO", TODTRAN."AC_ODDAYS",TODTRAN."RELEASE_DATE" 
        
        UNION ALL

        SELECT LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" , LNMASTER."AC_NAME",
        LNMASTER."BANKACNO" ,SUM(CAST(TODTRAN."AC_ODAMT" AS FLOAT)) OD_AMT ,TODTRAN."AC_ODDAYS",TODTRAN."AC_ODDATE",TODTRAN."RELEASE_DATE"  FROM LNMASTER
        INNER JOIN TODTRAN ON TODTRAN."AC_NO" = LNMASTER."BANKACNO" 
        WHERE CAST(TODTRAN."AC_ODDAYS" AS integer) > 0  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) >= CAST('.$startDate.' AS DATE)  
        AND CAST(TODTRAN."AC_ODDATE" AS DATE) <= CAST('.$endDate.' AS DATE)  
        AND TODTRAN."AC_TYPE"='.$actype.'  
        AND LNMASTER."BRANCH_CODE" = '.$branch.'
        AND ("AC_CLOSEDT" IS NULL OR CAST("AC_CLOSEDT" AS DATE) > CAST('.$endDate.' AS DATE)) 
        GROUP BY TODTRAN."AC_ODDATE" , LNMASTER."AC_ACNOTYPE" , LNMASTER."AC_TYPE" ,LNMASTER."AC_NAME", LNMASTER."BANKACNO", TODTRAN."AC_ODDAYS",TODTRAN."RELEASE_DATE" 
        ';
}

echo $query;

          
$sql =  pg_query($conn,$query);

$i = 0;
$srno = 0;
$grptotal = 0;
$grptotal1 = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
    $grptotal = $grptotal + $row["od_amt"];
    $grptotal1 = $grptotal1 + $row["AC_ODAMT"];
    $srno = $srno + 1;
    $tmp=[
        "SR_NO" => $srno,
        "AC_NO" => $row["BANKACNO"],
        "AC_TYPE" => $row["S_APPL"].' '.$row["S_NAME"],
        "AC_NAME"=> $row["AC_NAME"],
        "AC_ODDATE"=> $row["AC_ODDATE"],
        "AC_ODAMT" => sprintf("%.2f",((int)$row["od_amt"] + 0.0)),
        "t_amt" => sprintf("%.2f",((int)$row["AC_ODAMT"] + 0.0)),
        "grptotal" => sprintf("%.2f",($grptotal + 0.0)),
        "grp_total" => sprintf("%.2f",($grptotal1 + 0.0)),
        "startDate" => $startDate_,
        "endDate" => $endDate_,
        "NAME" => $branchName,
        "bankName" => $bankName,
        "date1"=>$endDate,
        "date2"=>$endDate,
        "actype"=>$actype,
        "type" => $type1,
        "BANKACNO" => $row['BANKACNO'],
        "till_date" => $row['RELEASE_DATE'],
    ];
    $data[$i]=$tmp;
    $i++;
}

// echo $grptotal;

ob_end_clean();

if($flag === '1')
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
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
}

    
}   
?>

<!-- AND CAST(TODTRAN."AC_ODDATE" AS DATE) >= CAST('.$startDate.' AS DATE)
    	AND CAST(TODTRAN."AC_ODDATE" AS DATE) <= CAST('.$endDate.' AS DATE)
        --	AND DPMASTER."BANKACNO"='.$BANKACNO.'
 -->