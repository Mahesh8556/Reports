<?php

include "main.php";

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('MAX_EXECUTION_TIME', 3600);
error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/OliveMSTCustGroup.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBS_LIVE user=postgres password=shubhangi");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/cust-group/findall');
// curl_setopt($ch, CURLOPT_URL, 'http://139.59.63.215:7276/daybook');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query());
$responseValue = curl_exec($ch);
$valueData = json_decode($responseValue, true);

curl_close($ch);

// $get_data = callAPI('GET', 'http://localhost:3000/cust-group/findall', false);
// $response = json_decode($get_data, true);
// $errors = $response['response']['errors'];

// function callAPI($method, $url, $data){
//     $curl = curl_init();
//     switch ($method){
//        case "POST":
//           curl_setopt($curl, CURLOPT_POST, 1);
//           if ($data)
//              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//           break;
//        case "PUT":
//           curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//           if ($data)
//              curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
//           break;
//        default:
//           if ($data)
//              $url = sprintf("%s?%s", $url, http_build_query($data));
//     }
  
//     // EXECUTE:
//     $result = curl_exec($curl);
//     curl_close($curl);
//     return $result;
//  }

for ($i = 0; $i <= $i++;) {
    $tmp = [
    
        'CODE' => $row['CODE'],
        'NAME' => $row['NAME'],
        'NOTE' => $row['NOTE'],
        'REMARK' => $row['REMARK'],
        'SYSADD_DATETIME' => $row['SYSADD_DATETIME'],
    ];
    $data[$i] = $tmp;
}


ob_end_clean();

$config = ['driver' => 'array', 'data' => $data];
// print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setDataSource($config)
    ->export('Pdf');
