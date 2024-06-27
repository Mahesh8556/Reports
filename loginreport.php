<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/loginreport.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$var="' '";
$branchName = $_GET['branchName'];
$date = $_GET['date'];
$asondate = $_GET['asondate'];
$BRANCH_CODE= $_GET['BRANCH_CODE'];
$bankName = $_GET['bankName'];


$bankName = str_replace("'", "", $bankName);
// $stdate_ = str_replace("'", "", $stdate);
$asondate = str_replace("'", "", $asondate);
$branchName = str_replace("'", "", $branchName);


$query='Select concat(USERDEFINATION."F_NAME",'.$var.',USERDEFINATION."L_NAME") AS NAME,"USERID" USER_CODE , "LOGIN_TIME" , 
"LOGOFF_TIME" OFF_TIME, "COMP_NAME" COMPUTER_NAME ,USERDEFINATION."EMAIL"
FROM LOGINHISTORY 
left outer JOIN USERDEFINATION ON cast(LOGINHISTORY."USERID" as integer)=USERDEFINATION.id
Where cast("LOGIN_DATE" as date) = cast('.$date.'as date)
AND "BRANCH_CODE"='.$BRANCH_CODE.' AND USERDEFINATION."branchId"=1
Order by "USERID"';


echo $query;

$sql =pg_query($conn,$query);
$i = 0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
            
            'user_code' => $row['user_code'],
            'name' => $row['name'],
            'LOGIN_TIME' => $row['LOGIN_TIME'],
            'off_time' => $row['off_time'],
            'computer_name' => $row['computer_name'],


            'BRANCH_CODE' => $BRANCH_CODE,
            'date' => $date,
            'asondate' => $asondate,
            'branchName' => $branchName,

            // 'revoke' => $revoke,
             'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
    
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
// ob_end_clean();

// //print_r($data);
// // echo $query;
//  $config = ['driver' => 'array', 'data' => $data];
// $repandt = new PHPJasperXML();
// $repandt->load_xml_file($filename)    
//     ->setDataSource($config)
//     ->export('Pdf');
    
    
// //}
?>
