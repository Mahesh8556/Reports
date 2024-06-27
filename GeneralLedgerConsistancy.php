<?php

ob_start();
include "main.php";
require_once('dbconnect.php');

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// ini_set('MAX_EXECUTION_TIME', 0);
// error_reporting(E_ALL);
ini_set('max_execution_time', 300);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/GenralLedgerConsistancy1.jrxml';

$dataset = array();
// $faker = Faker\Factory::create('en_US');

$bankName = $_GET['bankName'];
$Date = $_GET['sdate'];
$scheme = $_GET['schemed'];
$branch = $_GET['branch'];
$print  = $_GET['print'];
$penal = $_GET['penal'];

$scheme = str_replace("'", "", $scheme);
$branch = str_replace("'", "", $branch);
$myObj = new stdClass();
$myObj->date = $Date;
$myObj->branch = $branch;
$myObj->scheme = $scheme;
$ch = curl_init();
$arr = array();

$bankName = str_replace("'", "", $bankName);
$Date_ = str_replace("'", "", $Date);

$dates = str_replace('/', '-', $Date);
$obj = (object)$myObj;


$url = 'http://' . $IPADDD . ':' . $port . '/voucher/ledgerbalanceview ';
// echo $url;
$data = ['date' => $dates, 'branch' => $branch, 'scheme' => $scheme];

$data = json_encode($data);

$ch   = curl_init();
$curlConfig = array(
    CURLOPT_URL            => $url,
    CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $data
);

curl_setopt_array($ch, $curlConfig);
$result = curl_exec($ch);
curl_close($ch);
$result1 = (json_decode($result));
$valueData = $result1;
curl_close($ch);
//get branch master data
$query = pg_query($conn, "select * from ownbranchmaster where id= " . $branch);

while ($row = pg_fetch_assoc($query)) {
    $branchName = $row['NAME'];
}

$GL_Balance = 0;
$glTotalVal = 0;
$schemeBal = 0;
$diffTotal = 0;
$schemdiffamt = 0;
$type = '';
for ($i = 0; $i < count($valueData); $i++) {
    $diff = 0;
    $glamount = isset($valueData[$i]->GL_AMT) ? $valueData[$i]->GL_AMT : 0;
    $schamount = isset($valueData[$i]->SCH_AMT) ? $valueData[$i]->SCH_AMT : 0;
    $diff = floatval($glamount) - floatval($schamount);
    $diffTotal = $diffTotal + $diff;
    $glType = '';
    if ($glamount < 0) {
        $glType = 'Cr';
    } else {
        $glType = 'Dr';
    }
    $schemeType = '';
    if ($schamount < 0) {
        $schemeType = 'Cr';
    } else {
        $schemeType = 'Dr';
    }
    $glTotalVal = $glTotalVal + $glamount;
    if ($glTotalVal < 0) {
        $glTotalType = 'Cr';
    } else {
        $glTotalType = 'Dr';
    }
    if ($type == '') {
        $type = $valueData[$i]->GL_acno;
        if ($valueData[$i]->GL_AMT < 0) {
            $glTotalType = 'Cr';
        } else {
            $glTotalType = 'Dr';
        }
        $GL_Balance =  abs($valueData[$i]->GL_AMT);
        $glamount =  abs($valueData[$i]->GL_AMT);
        $schemdiffamt = $GL_Balance;
        $schemeBal = 0;
    }
    if ($type == $valueData[$i]->GL_acno) {
        if ($valueData[$i]->GL_AMT < 0 && $valueData[$i]->SCH_AMT < 0) {
            $schemdiffamt = $schemdiffamt - abs($valueData[$i]->SCH_AMT);
        } else if ($valueData[$i]->GL_AMT > 0 && $valueData[$i]->SCH_AMT > 0) {
            $schemdiffamt = $schemdiffamt - $valueData[$i]->SCH_AMT;
        } else if ($valueData[$i]->GL_AMT > 0 && $valueData[$i]->SCH_AMT < 0) {
            $schemdiffamt = $schemdiffamt + abs($valueData[$i]->SCH_AMT);
        } else if ($valueData[$i]->GL_AMT < 0 && $valueData[$i]->SCH_AMT > 0) {
            $schemdiffamt = $schemdiffamt + $valueData[$i]->SCH_AMT;
        }
        $glamount = null;
        $schemeBal = $schemeBal + ($valueData[$i]->SCH_AMT);
        if ($schemeBal < 0) {
            $glSchemeType = 'Cr';
        } else {
            $glSchemeType = 'Dr';
        }       
    } else {
        $type = '';
        if ($type == '') {
            $type = $valueData[$i]->GL_acno;
            if ($valueData[$i]->GL_AMT < 0) {
                $glTotalType = 'Cr';
            } else {
                $glTotalType = 'Dr';
            }
            $GL_Balance =  abs($valueData[$i]->GL_AMT);
            $glamount =  abs($valueData[$i]->GL_AMT);
            $schemdiffamt = $GL_Balance;
            $schemeBal = 0;
        }
        if ($type == $valueData[$i]->GL_acno) {
            if ($valueData[$i]->GL_AMT < 0 && $valueData[$i]->SCH_AMT < 0) {
                $schemdiffamt = $schemdiffamt - abs($valueData[$i]->SCH_AMT);
            } else if ($valueData[$i]->GL_AMT > 0 && $valueData[$i]->SCH_AMT > 0) {
                $schemdiffamt = $schemdiffamt - $valueData[$i]->SCH_AMT;
            } else if ($valueData[$i]->GL_AMT > 0 && $valueData[$i]->SCH_AMT < 0) {
                $schemdiffamt = $schemdiffamt + abs($valueData[$i]->SCH_AMT);
            } else if ($valueData[$i]->GL_AMT < 0 && $valueData[$i]->SCH_AMT > 0) {
                $schemdiffamt = $schemdiffamt + $valueData[$i]->SCH_AMT;
            }
            $glamount = null;
            $schemeBal = $schemeBal + ($valueData[$i]->SCH_AMT);
            if ($schemeBal < 0) {
                $glSchemeType = 'Cr';
            } else {
                $glSchemeType = 'Dr';
            }
        }       
    }
       $tmp = [
        // We can check the condition (isset) if their is value then show otherwise display null
        ////////////////////////-------------- 
        'glname' => isset($valueData[$i]->GL_NAME) ? $valueData[$i]->GL_NAME : null,
        // 'glname' => isset($valueData[$i]->GL_NAME) ? $valueData[$i]->GL_NAME : null,
        'gltotal' => sprintf("%.2f", (abs($glamount) + 0.0)) . ' ' . $glType,
        'schemename' => isset($valueData[$i]->SCH_NAME) ? $valueData[$i]->SCH_NAME : null,
        'counts' => isset($valueData[$i]->COUNTS) ? $valueData[$i]->COUNTS : 0,
        'gl_acno' => isset($valueData[$i]->GL_acno) ? $valueData[$i]->GL_acno : 0,
        'schemtotal' => sprintf("%.2f", (abs($schamount) + 0.0)) . ' ' . $schemeType,
        'difference' => null,
        // 'difference' => sprintf("%.2f", ($diff + 0.0)),
        'diff' => sprintf("%.2f", ($GL_Balance + 0.0)),
        'schmtot' => sprintf("%.2f", (abs($GL_Balance) + 0.0)) . ' ' . $glTotalType,
        'balschm' => sprintf("%.2f", (abs($schemeBal) + 0.0)) . ' ' . $glSchemeType,
        // 'schmdiff' => sprintf("%.2f", (abs($diffTotal) + 0.0)),
        'schmdiff' => sprintf("%.2f", (abs($schemdiffamt) + 0.0)),
        'Date' => $Date,
        'sdate_' => $Date_,
        'bankName' => $bankName,
        'Branch' => $branch,
        'NAME' => $branchName,

        'S_NAME' => 'NA',
        'ac_acnotype' => 'NA',
        'ac_type' => 'NA',
        'AC_ACNOTYPE' => $scheme,
        'S_GLACNO' => 'NA',
        // 'grandschmtot' => 0,
        // 'granddifftot' => 0,
        'grandgltot' => 0,
        'schemed' => 0,
        'branch' => 0,
        'schemewise' => 0,
        'print' => $print,
        'penal' => $penal,
        'total' => $glamount,
        //'total' => $balance,

        //  'gl_acno' => $glacno1
    ];
    $dataset[$i] = $tmp;
}
//  print_r($dataset);

ob_end_clean();
$config = ['driver' => 'array', 'data' => $dataset];
//  print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');
