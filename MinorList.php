<?php
include "main.php";
ob_start(); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: http://localhost:4200');
//  header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding');
header('Access-Control-Max-Age: 1000');  
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');

use simitsdk\phpjasperxml\PHPJasperXML;


$filename = __DIR__.'/MinorList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

//connect pgAdmin database connection 
$conn = pg_connect("host=127.0.0.1 dbname=cbsdb_live user=postgres password=admin");


$startDate = "'01/01/2000'";
// $scheme = $_GET['scheme'];
// $Branch = $_GET['branch'];
$dateformate = "'DD/MM/YYYY'";

$query =' SELECT dpmaster."AC_NO",dpmaster."AC_NAME",dpmaster."AC_ACNOTYPE",
          dpmaster."AC_MBDATE",dpmaster."AC_GRDNAME",dpmaster."AC_GRDRELE",
          extract(year from age(to_date('.$startDate.','.$dateformate.'),
          cast (dpmaster."AC_MBDATE" as TIMESTAMP WITHOUT TIME ZONE ))),
          ownbranchmaster."NAME"
          from dpmaster 
          Inner Join ownbranchmaster on 
          dpmaster."BRANCH_CODE" = ownbranchmaster."id"
          order by  dpmaster."AC_ACNOTYPE" asc';

        // if($query == 0){
        //     echo "Report have no data";
        // }else{
        //     echo "Data Have been loaded";
        // }

$sql =  pg_query($conn,$query);

// if($sql == 0){
//     echo "Report have no data";
// }else{
//     echo "Data Have been loaded";
// }
    
$i = 0;
 
while($row = pg_fetch_assoc($sql)){

    if($row == 0){
        echo "Report have no data";
    }else{
        echo "Data Have been loaded";
    }

    $tmp=[

        'AC_NO'=> $row['AC_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
        'AC_MBDATE' => $row['AC_MBDATE'],
        'AC_GRDNAME' => $row['AC_GRDNAME'],     
        'AC_GRDRELE' => $row['AC_GRDRELE'],  
        'age' => $row['extract'],
        'USER_DATE' => $startDate,
        'NAME' => $row['NAME'],
        
    ];    
    $data[$i]=$tmp;
    $i++;   
}

// print_r($data);
// for clean previous execution
ob_end_clean();


$config = ['driver'=>'array','data'=>$data];    
// for pdf conversion of report
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    $PHPJasperXML->arrayParameter=array("parameter1"=>$value);

?>




