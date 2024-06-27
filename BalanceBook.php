<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');

// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/BalanceBook.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$c = "'C'";
$d = "'D'";
$int = "'0'";
$TRAN_STATUS="'1'";



$bankName = $_GET['bankName'];
$date = $_GET['date'];
$sdate = $_GET['sdate'];



$scheme = $_GET['scheme'];
$Rstartingacc = $_GET['Rstartingacc'];
$EndingAccount = $_GET['EndingAccount'];
$branch = $_GET['branch'];
$Rdio = $_GET['Rdio'];
$Rdiosort = $_GET['Rdiosort'];
$year=$_GET['year'];




// echo $sdate;

$bankName = str_replace("'", "", $bankName);
$date_ = str_replace("'", "", $date);

$scheme = str_replace("'", "", $scheme);
$Rdio = str_replace("'", "", $Rstartingacc);
$Rdiosort = str_replace("'", "", $Rdiosort);
$data2 = str_split($date, 1);
$newarray = array_slice($data2, 1, -1);
$newDate = implode('', $newarray);
$old_date = explode('/', $newDate);
$new_date1 = $old_date[2] . '-' . $old_date[1] . '-' . $old_date[0];
// $date1 = $year;
$date1 = date_create($new_date1);


// echo "<br> Month: ".date_format($date,"m");
if (date_format($date1, "m") >= 4) { //On or After April (FY is current year - next year)
    $financial_year = (date_format($date1, "Y"));
} else { //On or Before March (FY is previous year - current year)
    $financial_year = (date_format($date1, "Y") - 1);
}


  
    $FstartDate = "'01/04/" . $financial_year . "'";
    $FstartDate1 = str_replace("'", "", $FstartDate);
    //get schemetype
    $checkSchemeType = pg_query($conn, 'select * from schemast where id =' . $scheme);
    $rowdata         = pg_fetch_assoc($checkSchemeType);
    $scheme_type     = $rowdata['S_ACNOTYPE'];
    // $scheme = "TRAN_ACTYPE"
    // switch ($scheme ) {
    //   case "TD"
//             scheme= "DPMASTER"
//       case "LN",
//             scheme= "LNMASTER"
//       case "PG"
//             scheme= "PGMASTER"
//       case "GL"
//             scheme= "ACMASTER"
//       case "SH"
//             scheme= "SHMASTER"
//       case Else
//             scheme= "DPMASTER"
    // }

    $c = "'C'";
    $d = "'D'";

   

$query='SELECT OPENINGBALANCE,
CLOSINGBALANCE,
"AC_NO","BANKACNO",
"AC_NAME","NAME",
"AC_TYPE",
"AC_ACNOTYPE",
"S_NAME","S_APPL",
COALESCE(CREDITBAL_CR,0) + COALESCE(DCREDITBAL,0) AS CREDITBAL,
COALESCE(DEBITBAL_DR,0) + COALESCE(DDEBITBAL,0) AS DEBITBAL
FROM
(SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), LNMASTER."BANKACNO",'.$FstartDate.',0,1)AS OPENINGBALANCE,
    LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), LNMASTER."BANKACNO",'.$sdate.',1,0,1)AS CLOSINGBALANCE,
    LNMASTER."AC_NO",LNMASTER."BANKACNO",
    LNMASTER."AC_NAME",OWNBRANCHMASTER."NAME",
    LNMASTER."AC_TYPE",LNMASTER."AC_ACNOTYPE",
    SCHEMAST."S_NAME",SCHEMAST."S_APPL",
(SELECT COALESCE(SUM(CAST(LOANTRAN."TRAN_AMOUNT" AS float)),0)
            FROM LOANTRAN
                WHERE LOANTRAN."TRAN_DRCR" = '.$c .'
                AND LOANTRAN."TRAN_ACNO" = LNMASTER."BANKACNO"
                 AND CAST(LOANTRAN."TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
                 AND CAST(LOANTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date)) AS CREDITBAL_CR,

(SELECT COALESCE(SUM(CAST(LOANTRAN."TRAN_AMOUNT" AS float)),0)
        FROM LOANTRAN
                WHERE LOANTRAN."TRAN_DRCR" = '.$d .'
                AND LOANTRAN."TRAN_ACNO" = LNMASTER."BANKACNO"
                AND CAST(LOANTRAN."TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
                 AND CAST(LOANTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date)) AS DEBITBAL_DR,		 

 (SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
            FROM DAILYTRAN
                WHERE "TRAN_DRCR" = '.$c .'
                AND "TRAN_ACNO" = LNMASTER."BANKACNO"
                AND "TRAN_STATUS" = '.$TRAN_STATUS.'
                AND CAST("TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
                 AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DCREDITBAL,

(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
            FROM DAILYTRAN
                WHERE "TRAN_DRCR" = '.$d .'
                AND "TRAN_ACNO" = LNMASTER."BANKACNO"
                AND "TRAN_STATUS" = '.$TRAN_STATUS.'
                AND CAST("TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
                 AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DDEBITBAL		 
 
FROM LNMASTER
INNER JOIN SCHEMAST ON LNMASTER."AC_TYPE" = SCHEMAST."id"
INNER JOIN OWNBRANCHMASTER ON LNMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
WHERE LNMASTER."BRANCH_CODE" = '.$branch.'
AND LNMASTER."AC_TYPE" ='.$scheme.'
AND LNMASTER."AC_NO" BETWEEN '.$Rstartingacc.' AND '.$EndingAccount.') S
UNION
SELECT OPENINGBALANCE,
CLOSINGBALANCE,
"AC_NO",
"BANKACNO",
"AC_NAME",
"NAME",
"AC_TYPE",
"AC_ACNOTYPE",
"S_NAME",
"S_APPL",
COALESCE(CREDITBAL_CR,0) + COALESCE(DCREDITBAL,0) AS CREDITBAL,
COALESCE(DEBITBAL_DR,0) + COALESCE(DDEBITBAL,0) AS DEBITBAL
FROM
(SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), DPMASTER."BANKACNO",'.$FstartDate.',0,1)AS OPENINGBALANCE,
    LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), DPMASTER."BANKACNO",'.$sdate.',1,0,1)AS CLOSINGBALANCE,
    DPMASTER."AC_NO",
    DPMASTER."BANKACNO",
    DPMASTER."AC_NAME",
    OWNBRANCHMASTER."NAME",
    DPMASTER."AC_TYPE",
    DPMASTER."AC_ACNOTYPE",
    SCHEMAST."S_NAME",
    SCHEMAST."S_APPL",
(SELECT COALESCE(SUM(CAST(DEPOTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DEPOTRAN
            WHERE DEPOTRAN."TRAN_DRCR" = '.$c .'
            AND DEPOTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
             AND CAST(DEPOTRAN."TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 
             AND CAST(DEPOTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date)) AS CREDITBAL_CR,
(SELECT COALESCE(SUM(CAST(DEPOTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DEPOTRAN
            WHERE DEPOTRAN."TRAN_DRCR" = '.$d .'
            AND DEPOTRAN."TRAN_ACNO" = DPMASTER."BANKACNO"
             AND CAST(DEPOTRAN."TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
             AND CAST(DEPOTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DEBITBAL_DR,

(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
            WHERE "TRAN_DRCR" = '.$c .'
            AND "TRAN_ACNO" = DPMASTER."BANKACNO"
            AND "TRAN_STATUS" = '.$TRAN_STATUS.'
             AND CAST("TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
             AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DCREDITBAL,

(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
        WHERE "TRAN_DRCR" = '.$d .'
            AND "TRAN_ACNO" = DPMASTER."BANKACNO"
            AND "TRAN_STATUS" = '.$TRAN_STATUS.'
            AND CAST("TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
             AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DDEBITBAL	 
 FROM DPMASTER
INNER JOIN SCHEMAST ON DPMASTER."AC_TYPE" = SCHEMAST."id"
INNER JOIN OWNBRANCHMASTER ON DPMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
WHERE DPMASTER."BRANCH_CODE" = '.$branch.'
    AND DPMASTER."AC_TYPE" = '.$scheme.'
    AND DPMASTER."AC_NO" BETWEEN '.$Rstartingacc.' AND '.$EndingAccount.' ) S
UNION
SELECT OPENINGBALANCE,CLOSINGBALANCE,
"AC_NO","BANKACNO","AC_NAME","NAME","AC_TYPE",
"AC_ACNOTYPE","S_NAME","S_APPL",
COALESCE(CREDITBAL_CR,0) + COALESCE(DCREDITBAL,0) AS CREDITBAL,
COALESCE(DEBITBAL_DR,0) + COALESCE(DDEBITBAL,0) AS DEBITBAL
FROM
(SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), PGMASTER."BANKACNO",'.$FstartDate.',0,1)AS OPENINGBALANCE,
    LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), PGMASTER."BANKACNO",'.$sdate.',1,0,1)AS CLOSINGBALANCE,
    PGMASTER."AC_NO",
    PGMASTER."BANKACNO",
    PGMASTER."AC_NAME",
    OWNBRANCHMASTER."NAME",
    PGMASTER."AC_TYPE",
    PGMASTER."AC_ACNOTYPE",
    SCHEMAST."S_NAME",
    SCHEMAST."S_APPL",
(SELECT COALESCE(SUM(CAST(PIGMYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM PIGMYTRAN
        WHERE PIGMYTRAN."TRAN_DRCR" = '.$c .'
            AND PIGMYTRAN."TRAN_ACNO" = PGMASTER."BANKACNO"
            AND CAST(PIGMYTRAN."TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 	
             AND CAST(PIGMYTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date)) AS CREDITBAL_CR,	 

(SELECT COALESCE(SUM(CAST(PIGMYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM PIGMYTRAN
        WHERE PIGMYTRAN."TRAN_DRCR" = '.$d .'
            AND PIGMYTRAN."TRAN_ACNO" = PGMASTER."BANKACNO"
             AND CAST(PIGMYTRAN."TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 
             AND CAST(PIGMYTRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DEBITBAL_DR,

  
(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
        WHERE "TRAN_DRCR" = '.$c .'
        AND "TRAN_ACNO" = PGMASTER."BANKACNO"
        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
        AND CAST("TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 
         AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DCREDITBAL,	 

    (SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
            WHERE "TRAN_DRCR" = '.$d .'
            AND "TRAN_ACNO" = PGMASTER."BANKACNO"
            AND "TRAN_STATUS" = '.$TRAN_STATUS.'
             AND CAST("TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
             AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DDEBITBAL

FROM PGMASTER
INNER JOIN SCHEMAST ON PGMASTER."AC_TYPE" = SCHEMAST."id"
INNER JOIN OWNBRANCHMASTER ON PGMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
WHERE PGMASTER."BRANCH_CODE" = '.$branch.'
AND PGMASTER."AC_TYPE" ='.$scheme.'
AND PGMASTER."AC_NO" BETWEEN '.$Rstartingacc.' AND '.$EndingAccount.')S
UNION
SELECT OPENINGBALANCE,
CLOSINGBALANCE,
"AC_NO","BANKACNO","AC_NAME",
"NAME","AC_TYPE","AC_ACNOTYPE",
"S_NAME","S_APPL",
COALESCE(CREDITBAL_CR,0) + COALESCE(DCREDITBAL,0) AS CREDITBAL,
COALESCE(DEBITBAL_DR,0) + COALESCE(DDEBITBAL,0) AS DEBITBAL
FROM
(SELECT LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), SHMASTER."BANKACNO",'.$FstartDate.',0,1)AS OPENINGBALANCE,
    LEDGERBALANCE(CAST (SCHEMAST."S_APPL" AS CHARACTER varying), SHMASTER."BANKACNO",'.$sdate.',1,0,1)AS CLOSINGBALANCE,
    SHMASTER."AC_NO",
    SHMASTER."BANKACNO",
    SHMASTER."AC_NAME",
    OWNBRANCHMASTER."NAME",
    SHMASTER."AC_TYPE",
    SHMASTER."AC_ACNOTYPE",
    SCHEMAST."S_NAME",
    SCHEMAST."S_APPL",

(SELECT COALESCE(SUM(CAST(SHARETRAN."TRAN_AMOUNT" AS float)),0)
        FROM SHARETRAN
            WHERE SHARETRAN."TRAN_DRCR" = '.$c .'
            AND SHARETRAN."TRAN_ACNO" = SHMASTER."BANKACNO"
            AND CAST(SHARETRAN."TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
              AND CAST(SHARETRAN."TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date)) AS CREDITBAL_CR,	  

(SELECT COALESCE(SUM(CAST(SHARETRAN."TRAN_AMOUNT" AS float)),0)
        FROM SHARETRAN
            WHERE SHARETRAN."TRAN_DRCR" = '.$d .'
            AND SHARETRAN."TRAN_ACNO" = SHMASTER."BANKACNO"
            AND CAST(SHARETRAN."TRAN_DATE" AS DATE)>= CAST('.$FstartDate.' AS DATE) 
             AND CAST(SHARETRAN."TRAN_DATE" AS DATE)<CAST('.$sdate.' AS date))AS DEBITBAL_DR,	 

(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
        WHERE "TRAN_DRCR" = '.$c.'
        AND "TRAN_ACNO" = SHMASTER."BANKACNO"
        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
           AND CAST("TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 
           AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DCREDITBAL,

(SELECT COALESCE(SUM(CAST(DAILYTRAN."TRAN_AMOUNT" AS float)),0)
        FROM DAILYTRAN
        WHERE "TRAN_DRCR" = '.$d .'
        AND "TRAN_ACNO" = SHMASTER."BANKACNO"
        AND "TRAN_STATUS" = '.$TRAN_STATUS.'
           AND CAST("TRAN_DATE" AS DATE)>=CAST('.$FstartDate.' AS DATE) 
           AND CAST("TRAN_DATE" AS DATE)< CAST('.$sdate.' AS date))AS DDEBITBAL

FROM SHMASTER
INNER JOIN SCHEMAST ON SHMASTER."AC_TYPE" = SCHEMAST."id"
INNER JOIN OWNBRANCHMASTER ON SHMASTER."BRANCH_CODE" = OWNBRANCHMASTER."id"
WHERE SHMASTER."BRANCH_CODE" = '.$branch.'
    AND SHMASTER."AC_TYPE" = '.$scheme.'
    AND SHMASTER."AC_NO" BETWEEN '.$Rstartingacc.' AND '.$EndingAccount.')S
ORDER BY "AC_NO" ASC






';
    // echo $query;

    $sql =  pg_query($conn, $query);

    $i = 0;

    $GRAND_TOTAL = 0;
    $GRAND_TOTAL1 = 0;
    $GRAND_TOTAL2 = 0;
    $SCHM_CTOTAL = 0;
    $GROUP_DTOTAL = 0;
    $SCHM_OPN = 0;
    $SCHM_NET = 0;
    $sdate = '01/04/2021';
    $ledgeType = '';
    $netbalance = '';
    if (pg_num_rows($sql) == 0) {
        include "errormsg.html";
        
    } else {
        while ($row = pg_fetch_assoc($sql)) {
            if ($row['openingbalance'] == 0 && $row['closingbalance'] == 0 && $row['creditbal'] =='' && $row['debitbal'] =='') {
            } else {
                
                $SCHM_CTOTAL = $SCHM_CTOTAL + $row['creditbal'];
                $GROUP_DTOTAL = $GROUP_DTOTAL + $row['debitbal'];
                $SCHM_NET     = $SCHM_NET + $row['closingbalance'];
                $SCHM_OPN     = $SCHM_OPN + $row['openingbalance'];
                if ($row['closingbalance'] < 0) {
                    $ledgeType = 'Cr';
                } else {
                    $ledgeType = 'Dr';
                }
                if ($row['openingbalance'] > 0) {
                    $netType = 'Dr';
                } else {
                    $netType = 'Cr';
                }
                
                if($row['closingbalance']==0){
                    $ledgeType = '';
                }
                $tmp = [
                    'AC_NO' => $row['AC_NO'],
                    'S_NAME' => $row['S_NAME'],
                    'AC_NAME' => $row['AC_NAME'],
                    'OpenBalance' =>sprintf("%.2f", (abs($row['openingbalance']))).' '.$netType,
                    'AC_TYPE' => $row['AC_TYPE'],
                    'NetBalance' =>sprintf("%.2f", (abs($row['closingbalance']))).' '.$ledgeType,
                    'creditamt' => sprintf("%.2f", ($row['creditbal'] + 0.0)),
                    // 'cramt' =>sprintf("%.2f", ($row['cramt'] + 0.0)),
                    'debitamt' => sprintf("%.2f", ($row['debitbal']  + 0.0)),
                    // 'dramt' =>sprintf("%.2f", ($row['dramt'] + 0.0)),
                    'AC_ACNOTYPE' => $row['S_APPL'].' '.$row['S_NAME'],
                    'NAME' => $row['NAME'],
                    'crtotalamt' =>sprintf("%.2f", ($SCHM_CTOTAL + 0.0)),
                      
                    'drtotalamt' =>sprintf("%.2f", ($GROUP_DTOTAL + 0.0)),
                    'schemtotal' => $GROUP_TOTAL,
                    'netgrandtotal' => sprintf("%.2f", ($GRAND_TOTAL2 + 0.0)).' '.$ledgeType,

                    'drschmamt' =>sprintf("%.2f", ($GROUP_DTOTAL + 0.0)),
                    'crschmeamt' =>sprintf("%.2f", ($SCHM_CTOTAL + 0.0)),

                    'schmopn' =>sprintf("%.2f", (abs($SCHM_OPN + 0.0))).' '.$netType,
                    'schmnet' =>sprintf("%.2f", (abs($SCHM_NET + 0.0))).' '.$ledgeType,
                    'enddate' => $date_,
                    'startdate' => $FstartDate1,

                    'bankName' => $bankName,
                    '$date_' => $date_,
                    'sdate' => $sdate,
                    'scheme' => $scheme,
                    'Rstartingacc' => $Rstartingacc,
                    'EndingAccount' => $EndingAccount,
                    'branch' => $branch,
                    'Rdio' => $Rdio,
                    'Rdiosort' => $Rdiosort,
                    'Sdate' => $sdate,

                ];
                // print_r($row);
                // echo '<br>';
                $data[$i] = $tmp;
                $i++;
            }
        }
    }
    ob_end_clean();
    // echo $query;
    // print_r($data);
    $config = ['driver'=>'array','data'=>$data];

    $report = new PHPJasperXML();
    $report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');

?>