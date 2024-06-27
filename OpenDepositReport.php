<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/OpenDepositeReport.jrxml';
$filename1 = __DIR__.'/closeDepositeReport.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$bankName = $_GET['bankName'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$scheme = $_GET['scheme'];
$Branch = $_GET['Branch'];
$ACCLOSE = $_GET['ACCLOSE'];
// echo $ACCLOSE;
echo '<br>';

$bankName = str_replace("'", "", $bankName);
$stdate_ = str_replace("'", "", $stdate);
$etdate_ = str_replace("'", "", $etdate);
// $branchName = str_replace("'", "", $branchName);

$dateformate = "'DD/MM/YYYY'"; 
$zero= "'0'";
// $NA="'NA'";   




$query =
    'SELECT DPMASTER."AC_NO",
    DPMASTER."AC_NAME",
    DPMASTER."AC_OPDATE",
    DPMASTER."AC_ASON_DATE",
    DPMASTER."AC_SCHMAMT",
    DPMASTER."AC_MONTHS",
    DPMASTER."AC_DAYS",
    CAST(DPMASTER."AC_INTRATE" AS FLOAT) "AC_INTRATE",	
    DPMASTER."AC_EXPDT",
    DPMASTER."AC_MATUAMT",
    DPMASTER."AC_ACNOTYPE",
    OWNBRANCHMASTER."NAME",
    CUSTOMERADDRESS."AC_ADDR",
    SCHEMAST."S_APPL",
    SCHEMAST."S_NAME",
    DPMASTER."AC_CLOSEDT",
    CAST(0 AS FLOAT) "AC_SANCTION_AMOUNT"
FROM DPMASTER
INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
LEFT JOIN CUSTOMERADDRESS ON DPMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID"
INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS date) AND CAST('.$etdate.' AS date)
AND STATUS = 1 AND "SYSCHNG_LOGIN" IS NOT NULL
    AND DPMASTER."BRANCH_CODE" = '.$Branch.'
    AND DPMASTER."AC_TYPE" = '.$scheme.' ';

    if($ACCLOSE =='1'){
                $query .= 'AND dpmaster."AC_CLOSEDT" IS NULL';
            }else{
                $query .= 'AND dpmaster."AC_CLOSEDT" IS NOT NULL';
            }

    $query .= ' UNION SELECT LNMASTER."AC_NO",
        LNMASTER."AC_NAME",
        LNMASTER."AC_OPDATE",
        NULL "AC_ASON_DATE",
        CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) "AC_SCHMAMT",
        LNMASTER."AC_MONTHS",
        '.$zero.' "AC_DAYS", 
        FN_GET_LOAN_IR(LNMASTER."BANKACNO", '.$etdate.') "AC_INTRATE", 
        LNMASTER."AC_EXPIRE_DATE" AS "AC_EXPDT", 
        '.$zero.' "AC_MATUAMT", 
        LNMASTER."AC_ACNOTYPE", 
        OWNBRANCHMASTER."NAME", 
        CUSTOMERADDRESS."AC_ADDR", 
        SCHEMAST."S_APPL",
        SCHEMAST."S_NAME", 
        LNMASTER."AC_CLOSEDT", 
        CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) "AC_SANCTION_AMOUNT" 
        FROM LNMASTER 
        INNER JOIN OWNBRANCHMASTER ON LNMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id" 
        LEFT JOIN CUSTOMERADDRESS ON LNMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID" 
        INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST."id" 
        WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS date) AND CAST('.$etdate.' AS date) 
        AND STATUS = 1 AND "SYSCHNG_LOGIN" IS NOT NULL

        AND LNMASTER."BRANCH_CODE" = '.$Branch.'
        AND LNMASTER."AC_TYPE" = '.$scheme.' ';

        if($ACCLOSE =='1'){
                    $query .= 'AND lnmaster."AC_CLOSEDT" IS NULL';
                }else{
                    $query .= 'AND lnmaster."AC_CLOSEDT" IS NOT NULL';
                } 
	
        $query .= ' UNION 
            SELECT PGMASTER."AC_NO", 
            PGMASTER."AC_NAME", 
            PGMASTER."AC_OPDATE", 
            PGMASTER."AC_ASON_DATE", 
            PGMASTER."AC_SCHMAMT", 
            PGMASTER."AC_MONTHS", 
            '.$zero.' "AC_DAYS", 
            CAST(0 AS FLOAT) "AC_INTRATE", 
            PGMASTER."AC_EXPDT", 
            PGMASTER."AC_MATUAMT", 
            PGMASTER."AC_ACNOTYPE", 
            OWNBRANCHMASTER."NAME", 
            CUSTOMERADDRESS."AC_ADDR", 
            SCHEMAST."S_APPL",
            SCHEMAST."S_NAME", 
            PGMASTER."AC_CLOSEDT", 
            CAST(0 AS FLOAT) "AC_SANCTION_AMOUNT" 
            FROM PGMASTER INNER JOIN OWNBRANCHMASTER ON PGMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id" 
            LEFT JOIN CUSTOMERADDRESS ON PGMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID" 
            INNER JOIN SCHEMAST ON PGMASTER."AC_TYPE" = SCHEMAST."id" 
            WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS date) AND CAST('.$etdate.' AS date) 
            AND STATUS = 1 AND "SYSCHNG_LOGIN" IS NOT NULL

            AND PGMASTER."BRANCH_CODE" = '.$Branch.'
            AND PGMASTER."AC_TYPE" = '.$scheme.'';

            if($ACCLOSE =='1'){
                        $query .= 'AND pgmaster."AC_CLOSEDT" IS NULL';
                    }else{
                        $query .= 'AND pgmaster."AC_CLOSEDT" IS NOT NULL';
                    } 
	
            $query .= ' UNION 
                SELECT SHMASTER."AC_NO", 
                SHMASTER."AC_NAME", 
                SHMASTER."AC_OPDATE", 
                NULL "AC_ASON_DATE", 
                '.$zero.' "AC_SCHMAMT", 
                '.$zero.' "AC_MONTHS", 
                '.$zero.' "AC_DAYS", 
                '.$zero.' "AC_INTRATE", 
                SHMASTER."AC_EXPDT", 
                '.$zero.' "AC_MATUAMT", 
                SHMASTER."AC_ACNOTYPE",
                OWNBRANCHMASTER."NAME", 
                CUSTOMERADDRESS."AC_ADDR", 
                SCHEMAST."S_APPL",
                SCHEMAST."S_NAME", 
                SHMASTER."AC_CLOSEDT", 
                CAST(0 AS FLOAT) "AC_SANCTION_AMOUNT" 
                FROM SHMASTER 
                INNER JOIN OWNBRANCHMASTER ON SHMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id" 
                LEFT JOIN CUSTOMERADDRESS ON SHMASTER."idmasterID" = CUSTOMERADDRESS."idmasterID" 
                INNER JOIN SCHEMAST ON SHMASTER."AC_TYPE" = SCHEMAST."id" 
                WHERE CAST("AC_OPDATE" AS date) BETWEEN CAST('.$stdate.' AS date) AND CAST('.$etdate.' AS date) 
                AND SHMASTER."BRANCH_CODE" = '.$Branch.' AND SHMASTER."AC_TYPE" = '.$scheme.' ';

                if($ACCLOSE =='1'){
                             $query .= 'AND shmaster."AC_CLOSEDT" IS NULL order by "AC_NO"';
                         }else{
                             $query .= 'AND shmaster."AC_CLOSEDT" IS NOT NULL order by "AC_NO" ';
                         } 



    // echo $query;



        $sql =  pg_query($conn,$query);
         
$i = 0; 
$total = 0;

if($ACCLOSE =='1')
{
    $Status="Open Register";
}
else{
    $Status="Close Register";
}

if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}
else {

while($row = pg_fetch_assoc($sql)){
    $total = $total + (int)$row['AC_SCHMAMT'];
    $mattotal = $mattotal + $row['AC_MATUAMT']; 
    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NO' => $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_ADDR' => $row['AC_ADDR'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_ASON_DATE' => $row['AC_ASON_DATE'],
        'AC_SCHMAMT' => sprintf("%.2f", (abs((int)$row['AC_SCHMAMT']))),
        'AC_MONTHS'=> $row['AC_MONTHS'],
        'AC_DAYS'=> $row['AC_DAYS'],
        'AC_INTRATE'=> $row['AC_INTRATE'],
        'AC_NNAME' => $row['AC_NNAME'],
        'AC_EXPDT'=> $row['AC_EXPDT'],
        // 'AC_EXPIRE_DATE' => $row['AC_EXPIRE_DATE'],
        'AC_MATUAMT'=> sprintf("%.2f", (abs((int)$row['AC_MATUAMT']))),
        'AC_MATTOTAL'=> sprintf("%.2f", (abs((int)$mattotal))),
        'AC_ACNOTYPE'=> $row['AC_ACNOTYPE'],
        'NAME'=> $row['NAME'],
        'S_NAME'=> $row['S_NAME'],
        'stdate' => $stdate,
        'etdate' => $etdate,
        'scheme' => $row['S_APPL'],
        // 'schem' => $scheme,
        'stdate_' => $stdate_, 
        'etdate_' => $etdate_,
        'Branch' => $Branch,
        'bankName' => $bankName,
        'Status' => $Status,
        'close_date' => $row['AC_CLOSEDT'],
        'Total' => sprintf("%.2f", (abs((int)$total)))
    ];
    $data[$i]=$tmp;
    $i++;

}    

if($ACCLOSE =='1')
{
    ob_end_clean(); 
    $config = ['driver'=>'array','data'=>$data];
    
    $report = new PHPJasperXML();
    $report->load_xml_file($filename)    
        ->setDataSource($config)
        ->export('Pdf');
}
else{
    ob_end_clean(); 
$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename1)    
    ->setDataSource($config)
    ->export('Pdf');
}

 }
?>