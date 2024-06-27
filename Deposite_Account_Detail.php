<?php
 ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/Deposite_Account_Detail.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$branchName = $_GET['branchName'];
$bankName = $_GET['bankName'];
$branch_code = $_GET['branch_code'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$S_APPL = $_GET['S_APPL'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$flag = $_GET['flag'];
$status="'1'";


$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
$branchName = str_replace("'", "", $branchName);
$query = '';
if ($flag == 1)
 {
  $query .= 'SELECT CONCAT(IDMAST."AC_WARD",		IDMAST."AC_ADDR",
  IDMAST."AC_GALLI",
  IDMAST."AC_AREA",
  IDMAST."AC_HONO",
  IDMAST."AC_CTCODE",
  IDMAST."AC_PIN",
  IDMAST."CITY_NAME")AS ADDRESS,
    SCHEMAST."S_NAME" SCHEME_NAME,
    SCHEMAST."S_APPL",
    DPMASTER."AC_ACNOTYPE",
    DPMASTER."AC_TYPE",
    DPMASTER."BANKACNO",
    DPMASTER."AC_NAME",
    IDMASTER."AC_PHONE_OFFICE",
    IDMASTER."AC_PHONE_RES",
    IDMASTER."AC_EMAILID",
    CATEGORYMASTER."NAME" CATG_NAME,
    IDM."NAME" CAST_NAME,
    IDMASTER."NAME" OCCUP_NAME,
    OPERATIONMASTER."NAME" OPR_NAME,
    DPMASTER."AC_CUSTID",
    IDMASTER."AC_MEMBTYPE",
    IDMASTER."AC_MEMBNO",
    DPMASTER."AC_INTRATE",
    INTCATEGORYMASTER."NAME" INTCATG_NAME,
    DPMASTER."AC_OPDATE",
    DPMASTER."AC_MINOR",
    DPMASTER."AC_MBDATE",
    DPMASTER."AC_GRDNAME",
    DPMASTER."AC_GRDRELE",
    DPMASTER."AC_INTROBRANCH",
    DPMASTER."AC_INTROID",
    DPMASTER."AC_INTRACNO",
    DPMASTER."AC_INTRNAME",
    DPMASTER."AC_MONTHS",
    DPMASTER."AC_DAYS",
    DPMASTER."AC_EXPDT",
    DPMASTER."AC_CUSTID",
    DPMASTER."AC_INTCATA",
    DPMASTER."AC_CLOSEDT",
    DPMASTER."AC_SCHMAMT",
    "AC_MATUAMT",
    IDMASTER."AC_PANNO",
    NOMINEELINK."id",
    NOMINEELINK."AC_NNAME",
    NOMINEELINK."AC_NRELA"
  FROM DPMASTER
  LEFT OUTER JOIN CATEGORYMASTER ON DPMASTER."AC_CATG" = CATEGORYMASTER."CODE"
  LEFT OUTER JOIN
    (SELECT CUSTOMERADDRESS."AC_CTCODE",
        CITYMASTER."CITY_CODE",
        CITYMASTER."CITY_NAME",
        IDMASTER."id",
        CUSTOMERADDRESS."idmasterID",
        CUSTOMERADDRESS."AC_HONO",
        CUSTOMERADDRESS."AC_WARD",
        CUSTOMERADDRESS."AC_ADDR",
        CUSTOMERADDRESS."AC_GALLI",
        CUSTOMERADDRESS."AC_AREA",
        CUSTOMERADDRESS."AC_PIN"
      FROM CUSTOMERADDRESS
      LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID
      LEFT OUTER JOIN IDMASTER ON CUSTOMERADDRESS."idmasterID" = IDMASTER."id")IDMAST ON DPMASTER."idmasterID" = IDMAST.ID
  LEFT OUTER JOIN
    (SELECT CASTMASTER."CODE",
        CASTMASTER."NAME",
        IDMASTER."AC_CAST",
        IDMASTER."id"
      FROM IDMASTER
      LEFT OUTER JOIN CASTMASTER ON IDMASTER."AC_CAST" = CASTMASTER.ID)IDM ON DPMASTER."idmasterID" = IDM.ID
  LEFT OUTER JOIN
    (SELECT "AC_NO",
        "AC_NAME",
        "AC_OCODE",
        OCCUPATIONMASTER."CODE",
        IDMASTER.ID,
        IDMASTER."AC_PHONE_OFFICE",
        IDMASTER."AC_PHONE_RES",
        IDMASTER."AC_EMAILID",
        IDMASTER."AC_MEMBTYPE",
        IDMASTER."AC_MEMBNO",
        IDMASTER."AC_PANNO",
        OCCUPATIONMASTER."NAME"
      FROM IDMASTER
      LEFT OUTER JOIN OCCUPATIONMASTER ON IDMASTER."AC_OCODE" = OCCUPATIONMASTER.ID)IDMASTER ON
      DPMASTER."idmasterID" = IDMASTER.ID
  LEFT OUTER JOIN OPERATIONMASTER ON DPMASTER."AC_OPR_CODE" = OPERATIONMASTER.ID
  LEFT OUTER JOIN INTCATEGORYMASTER ON DPMASTER."AC_INTCATA" = INTCATEGORYMASTER.ID
  INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.ID
  LEFT OUTER JOIN NOMINEELINK ON DPMASTER."idmasterID" = NOMINEELINK."DPMasterID"
  WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS DATE) AND CAST('.$etdate.' AS DATE)
    AND "AC_CLOSEDT" IS NULL 
    AND DPMASTER.STATUS='.$status.' AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL AND DPMASTER."BRANCH_CODE"='.$branch_code.'';
}
else
{$query .= 'SELECT CONCAT(IDMAST."AC_WARD",		IDMAST."AC_ADDR",
  IDMAST."AC_GALLI",
  IDMAST."AC_AREA",
  IDMAST."AC_HONO",
  IDMAST."AC_CTCODE",
  IDMAST."AC_PIN",
  IDMAST."CITY_NAME")AS ADDRESS,
    SCHEMAST."S_NAME" SCHEME_NAME,
    SCHEMAST."S_APPL",
    DPMASTER."AC_ACNOTYPE",
    DPMASTER."AC_TYPE",
    DPMASTER."BANKACNO",
    DPMASTER."AC_NAME",
    IDMASTER."AC_PHONE_OFFICE",
    IDMASTER."AC_PHONE_RES",
    IDMASTER."AC_EMAILID",
    CATEGORYMASTER."NAME" CATG_NAME,
    IDM."NAME" CAST_NAME,
    IDMASTER."NAME" OCCUP_NAME,
    OPERATIONMASTER."NAME" OPR_NAME,
    DPMASTER."AC_CUSTID",
    IDMASTER."AC_MEMBTYPE",
    IDMASTER."AC_MEMBNO",
    DPMASTER."AC_INTRATE",
    INTCATEGORYMASTER."NAME" INTCATG_NAME,
    DPMASTER."AC_OPDATE",
    DPMASTER."AC_MINOR",
    DPMASTER."AC_MBDATE",
    DPMASTER."AC_GRDNAME",
    DPMASTER."AC_GRDRELE",
    DPMASTER."AC_INTROBRANCH",
    DPMASTER."AC_INTROID",
    DPMASTER."AC_INTRACNO",
    DPMASTER."AC_INTRNAME",
    DPMASTER."AC_MONTHS",
    DPMASTER."AC_DAYS",
    DPMASTER."AC_EXPDT",
    DPMASTER."AC_CUSTID",
    DPMASTER."AC_INTCATA",
    DPMASTER."AC_CLOSEDT",
    DPMASTER."AC_SCHMAMT",
    "AC_MATUAMT",
    IDMASTER."AC_PANNO",
    NOMINEELINK."id",
    NOMINEELINK."AC_NNAME",
    NOMINEELINK."AC_NRELA"
  FROM DPMASTER
  LEFT OUTER JOIN CATEGORYMASTER ON DPMASTER."AC_CATG" = CATEGORYMASTER."CODE"
  LEFT OUTER JOIN
    (SELECT CUSTOMERADDRESS."AC_CTCODE",
        CITYMASTER."CITY_CODE",
        CITYMASTER."CITY_NAME",
        IDMASTER."id",
        CUSTOMERADDRESS."idmasterID",
        CUSTOMERADDRESS."AC_HONO",
        CUSTOMERADDRESS."AC_WARD",
        CUSTOMERADDRESS."AC_ADDR",
        CUSTOMERADDRESS."AC_GALLI",
        CUSTOMERADDRESS."AC_AREA",
        CUSTOMERADDRESS."AC_PIN"
      FROM CUSTOMERADDRESS
      LEFT OUTER JOIN CITYMASTER ON CUSTOMERADDRESS."AC_CTCODE" = CITYMASTER.ID
      LEFT OUTER JOIN IDMASTER ON CUSTOMERADDRESS."idmasterID" = IDMASTER."id")IDMAST ON DPMASTER."idmasterID" = IDMAST.ID
  LEFT OUTER JOIN
    (SELECT CASTMASTER."CODE",
        CASTMASTER."NAME",
        IDMASTER."AC_CAST",
        IDMASTER."id"
      FROM IDMASTER
      LEFT OUTER JOIN CASTMASTER ON IDMASTER."AC_CAST" = CASTMASTER.ID)IDM ON DPMASTER."idmasterID" = IDM.ID
  LEFT OUTER JOIN
    (SELECT "AC_NO",
        "AC_NAME",
        "AC_OCODE",
        OCCUPATIONMASTER."CODE",
        IDMASTER.ID,
        IDMASTER."AC_PHONE_OFFICE",
        IDMASTER."AC_PHONE_RES",
        IDMASTER."AC_EMAILID",
        IDMASTER."AC_MEMBTYPE",
        IDMASTER."AC_MEMBNO",
        IDMASTER."AC_PANNO",
        OCCUPATIONMASTER."NAME"
      FROM IDMASTER
      LEFT OUTER JOIN OCCUPATIONMASTER ON IDMASTER."AC_OCODE" = OCCUPATIONMASTER.ID)IDMASTER ON
      DPMASTER."idmasterID" = IDMASTER.ID
  LEFT OUTER JOIN OPERATIONMASTER ON DPMASTER."AC_OPR_CODE" = OPERATIONMASTER.ID
  LEFT OUTER JOIN INTCATEGORYMASTER ON DPMASTER."AC_INTCATA" = INTCATEGORYMASTER.ID
  INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST.ID
  LEFT OUTER JOIN NOMINEELINK ON DPMASTER."idmasterID" = NOMINEELINK."DPMasterID"
  WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS DATE) AND CAST('.$etdate.' AS DATE)
    AND "AC_CLOSEDT" IS NOT NULL 
    AND DPMASTER.STATUS='.$status.' AND DPMASTER."SYSCHNG_LOGIN" IS NOT NULL AND DPMASTER."BRANCH_CODE"='.$branch_code.'';}

// echo $query;
$sql =pg_query($conn,$query);
$i = 0;


// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {

    //echo $row;

    $tmp = [
            
            'scheme_name' => $row['scheme_name'],
            'BANKACNO' => $row['BANKACNO'],
            'AC_NAME' => $row['AC_NAME'],
            'address' => $row['address'],
            'catg_name' => $row['catg_name'],
            'occup_name' => $row['occup_name'],

            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'cast_name' => $row['cast_name'],
            'opr_name' => $row['opr_name'], 
            
            'intcatg_name' => $row['intcatg_name'],
            'AC_CUSTID' => $row['AC_CUSTID'], 
            'AC_INTCATA' => $row['AC_INTCATA'], 
            'AC_NNAME' => $row['AC_NNAME'],
            'AC_NRELA' => $row['AC_NRELA'],
            'AC_CLOSEDT' => $row['AC_CLOSEDT'],
            'AC_INTRATE' => $row['AC_INTRATE'],
            'AC_MONTHS' => $row['AC_MONTHS'],

            'AC_SCHMAMT' => $row['AC_SCHMAMT'],
            'AC_MATUAMT' => $row['AC_MATUAMT'],
            


            'S_APPL' => $S_APPL,
            'stdate' => $stdate_,
            'etdate' => $etdate_,
            'AC_ACNOTYPE' => $AC_ACNOTYPE,
            'branchName' => $branchName,
            'flag'=>$flag,

            // 'revoke' => $revoke,
             'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
    
        // echo '<pre>';
      //print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();

//print_r($data);
// echo $query;
 $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
// //}
?>
