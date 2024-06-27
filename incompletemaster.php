<?php

ob_start();
include "main.php";
require_once('dbconnect.php'); 


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/incompletemaster.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

$bankname  = $_GET['bankname'];
$branchname  = $_GET['branchname'];
$sdate = $_GET['sdate'];
$resolution = "'Resolution'";
$Registration = "'Registration'";
$Security = "'Security'";
// $Guarantor = "'Guarantor'";
$Introducer = "'Introducer'";
$SignatureAuthority = "'Signature Authority'";
$Nominee = "' Nominee'";
$para = "''";
$one="'1'";
// $BRANCH_CODE  = $_GET['BRANCH_CODE'];


$branchname1 = str_replace("'", "", $branchname);
$bankname1 = str_replace("'", "", $bankname);
$sdate1 = str_replace("'", "", $sdate);


$query='SELECT ROW_NUMBER() OVER () AS SR_NO, XYZ.* FROM (SELECT INCOMPLETE, "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", SCHEMAST."S_APPL", SCHEMAST."S_NAME" FROM (SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN ("AC_RESO_DATE" IS NULL OR "AC_RESO_DATE" = '.$para.') THEN '.$resolution.' ELSE '.$para.' END INCOMPLETE FROM LNMASTER WHERE CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "AC_CLOSEDT" IS NULL AND "AC_RESO_DATE" IS NULL AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN ("AC_COREG_DATE" IS NULL OR "AC_COREG_DATE" = '.$para.') THEN '.$Registration.' ELSE '.$para.' END INCOMPLETE FROM LNMASTER WHERE CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "AC_CLOSEDT" IS NULL AND "AC_COREG_DATE" IS NULL AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN (SELECT COUNT(SECURITYDETAILS."SECURITY_CODE") FROM SECURITYDETAILS) = 0 THEN '.$Security.' ELSE '.$para.' END INCOMPLETE FROM LNMASTER WHERE "AC_CLOSEDT" IS NULL AND CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN (SELECT COUNT(*) FROM GUARANTERDETAILS) = 0 THEN '.$Security.' ELSE '.$para.' END INCOMPLETE FROM LNMASTER WHERE "AC_CLOSEDT" IS NULL AND CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN ("AC_INTRNAME" IS NULL OR "AC_INTRNAME" ='.$para.') THEN '.$Introducer.' ELSE '.$para.' END INCOMPLETE FROM DPMASTER WHERE "AC_CLOSEDT" IS NULL AND CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN ("SIGNATURE_AUTHORITY" IS NULL OR "SIGNATURE_AUTHORITY" ='.$para.') THEN '.$SignatureAuthority.' ELSE '.$para.' END INCOMPLETE FROM DPMASTER WHERE CAST("AC_CLOSEDT" AS date) IS NULL AND CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "BRANCH_CODE" = '.$one.' UNION ALL SELECT "AC_ACNOTYPE", "AC_TYPE", "AC_NO", "AC_NAME", CASE WHEN (SELECT COUNT(*) FROM NOMINEELINK) = 0 THEN ' .$Nominee.' ELSE '.$para.' END INCOMPLETE FROM DPMASTER WHERE "AC_CLOSEDT" IS NULL AND CAST("AC_OPDATE" AS date) = cast('.$sdate.' As date) AND "BRANCH_CODE"= '.$one.' ) TMP LEFT JOIN SCHEMAST ON SCHEMAST.ID=TMP."AC_TYPE" WHERE INCOMPLETE IS NOT NULL OR INCOMPLETE <> '.$para.' ORDER BY "AC_ACNOTYPE", "AC_TYPE", "AC_NO") XYZ';

// echo $query;

$sql =  pg_query($conn,$query);
$i = 0;
// echo $query;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

    while($row = pg_fetch_assoc($sql)) {
        $tmp=[

             "TRAN_NO" => $row['sr_no'],
            "TRAN_TIME" => $row['incomplete'],
            "TRAN_TYPE" => $row['AC_ACNOTYPE'],
            "USER_CODE" => $row['S_NAME'],
            "TRANSFER_AMOUNT" => $row['AC_NO'],
            "CASH_AMOUNT" => $row['AC_NAME'],
            "sdate" => $sdate1,
            "branchname" => $branchname1,
            // "BRANCH_CODE" => $BRANCH_CODE ,
            "bankname" => $bankname1,
            
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

