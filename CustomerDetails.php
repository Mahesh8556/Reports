<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/CustomerDetails.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$schem = "'TD'";
$brname = "'IV'";  

$query = 'SELECT dpmaster."AC_NAME", guaranterdetails."AC_NAME" as gname,
0 shares, depotran."TRAN_AMOUNT" as deposite,0 loan,ownbranchmaster."NAME"
FROM dpmaster
Inner Join depotran on
dpmaster."BANKACNO" = depotran."TRAN_ACNO"
Inner Join guaranterdetails on
dpmaster."id" = guaranterdetails."lnmasterID"
Inner Join ownbranchmaster on 
dpmaster."BRANCH_CODE" = ownbranchmaster."id"
Union
SELECT shmaster."AC_NAME", guaranterdetails."AC_NAME" as gname,
cast(sharetran."TRAN_AMOUNT" as integer) as shares,0 as deposite,0 as loan,
ownbranchmaster."NAME"
FROM shmaster
Inner Join guaranterdetails on
shmaster."idmasterID" = guaranterdetails."lnmasterID"
Inner Join sharetran on
shmaster."id" = sharetran."id"
Inner Join ownbranchmaster on 
shmaster."BRANCH_CODE" = ownbranchmaster."id"
Union 
Select lnmaster."AC_NAME", guaranterdetails."AC_NAME" as gname,
0 shares,0 deposite,cast(loantran."TRAN_AMOUNT" as integer) as loan,
ownbranchmaster."NAME"
FROM lnmaster
Inner Join guaranterdetails on
lnmaster."idmasterID" = guaranterdetails."lnmasterID"
Inner Join loantran on
lnmaster."idmasterID" = loantran."id"
Inner Join ownbranchmaster on 
lnmaster."BRANCH_CODE" = ownbranchmaster."id"';
          

$sql =  pg_query($conn,$query);

$i = 0;

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    $DTOTAL = 0;
    $CTOTAL = 0;

    $DTOTAL = $DTOTAL + $row['loan'];
    $CTOTAL = $CTOTAL + $row['deposite'] + $row['shares'];

    $tmp=[
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'gname' => $row['gname'],
        'deposite' => $row['deposite'],
        'shares' => $row['shares'],
        'loan' => $row['loan'],
        'NAME'=> $row['NAME'],
        'totaldebit'=> $DTOTAL,
        'totalcredit'=> $CTOTAL,
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
    

