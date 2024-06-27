<?php
ob_start();
include "main.php";
require_once('dbconnect.php');


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__ . '/memberView.jrxml';


$data = [];


$schemename = $_GET['schemename'];
$branchName = $_GET['branch'];
$bankName = $_GET['bankName'];
$depositBal = $_GET['depositBal'];
$depositInt = $_GET['depositInt'];
$loanint=$_GET['loanint'];
$loanbal=$_GET['loanbal'];
$loanscheme=$_GET['loanscheme'];
$loanDepoTotal=$_GET['loanDepoTotal'];
$loanBalTotal=$_GET['loanBalTotal'];
$DepoIntTotal=$_GET['DepoIntTotal'];
$DepoBalTotal=$_GET['DepoBalTotal'];
$date=$_GET['date'];
$netdiff=$_GET['netdiff'];
$loanacno=$_GET['loanacno'];
$depoacno=$_GET['depoacno'];

$ac_name=$_GET['ac_name'];
$memno=$_GET['memno'];
$s_appl=$_GET['s_appl'];


$loanscheme = str_replace(",", "", $loanscheme);
$loanbal = str_replace(",", "", $loanbal);
$loanint = str_replace(",", "", $loanint);
$depositInt = str_replace(",", "", $depositInt);
$depositBal = str_replace(",", "", $depositBal);
$schemename = str_replace(",", "", $schemename);
$bankName = str_replace("'", "", $bankName);
$loanacno = str_replace(",", "", $loanacno);
$depoacno = str_replace(",", "", $depoacno);


if ($netdiff > 0) {
  $netType = 'Cr';
} else {
  $netType = 'Dr';
}

$lnTotal=0;
// $lnTotal=$loanBalTotal + $DepoBalTotal;
$dataset = array();
$tmp = [
  'bankName'=>$bankName,
  'schemename'=>$schemename,
  'depositBal'=>$depositBal,
  'depositInt'=>$depositInt,
  'loanint'=>$loanint,
  'loanbal'=>$loanbal,
  'loanscheme'=>$loanscheme,
  'loanDepoTotal'=>$loanDepoTotal,
  'loanBalTotal'=>sprintf("%.2f",($loanBalTotal) + 0.0 ) ,
  'ac_name'=>$ac_name,
  'memno'=>$memno,
  's_appl'=>$s_appl,
  'DepoIntTotal'=>$DepoIntTotal,
  'DepoBalTotal'=>sprintf("%.2f",($DepoBalTotal) + 0.0 ) ,
  'branchName'=>$branchName,
  'date'=>$date,
  'netdiff'=>$netdiff .' '.$netType,
  'loanacno'=>$loanacno,
  'depoacno'=>$depoacno,
// 'branchName'=>$branchName,
];
array_push($dataset, $tmp);
$ver = [];
$ver =  $dataset;
// echo "<pre>";
print_r($tmp);
// echo "</pre>";
ob_end_clean();
$config = ['driver' => 'array', 'data' => $ver];
// $config = ['driver' => 'array', 'data' => $data];
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)
  ->setDataSource($config)
  ->export('Pdf');


// }
