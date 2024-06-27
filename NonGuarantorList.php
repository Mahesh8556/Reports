<?php
include "main.php";
ob_start(); 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/NonGuarantorList.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');


$conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
if($conn){
    echo 'success';
    

}else{
    echo 'fail';
}

$query = ' SELECT shmaster."AC_NAME", ownbranchmaster."NAME",
           citymaster."CITY_NAME" FROM citymaster,shmaster
           Inner Join ownbranchmaster on
           shmaster."BRANCH_CODE" = ownbranchmaster."id"
           where 
           SHMASTER."AC_NO"  NOT IN ( SELECT cast("MEMBER_NO" as integer) 
           FROM SCHEMAST, SHMASTER, GUARANTERDETAILS, LNMASTER Where 
           SCHEMAST."S_ACNOTYPE" = SHMASTER."AC_ACNOTYPE" 
           AND SCHEMAST."S_APPL" = SHMASTER."AC_TYPE" AND SHMASTER."AC_EXPDT" = null 
           AND cast(SHMASTER."AC_TYPE" as character varying) = GUARANTERDETAILS."MEMBER_TYPE" 
           AND SHMASTER."AC_NO" = cast(GUARANTERDETAILS."MEMBER_NO" as integer) 
           AND LNMASTER."AC_ACNOTYPE" = GUARANTERDETAILS."AC_ACNOTYPE" 
           AND cast(LNMASTER."AC_TYPE" as character varying) = GUARANTERDETAILS."AC_TYPE" 
           AND LNMASTER."AC_NO" = cast(GUARANTERDETAILS."AC_NO" as integer) )
           Union
           SELECT lnmaster."AC_NAME",  ownbranchmaster."NAME",
           citymaster."CITY_NAME" FROM citymaster, lnmaster
           Inner Join ownbranchmaster on
           lnmaster."BRANCH_CODE" = ownbranchmaster."id"
           where 
           lnmaster."AC_CLOSEDT" is null AND
           lnmaster."AC_NO"  NOT IN ( SELECT cast("MEMBER_NO" as integer) 
           FROM SCHEMAST, SHMASTER, GUARANTERDETAILS, LNMASTER Where 
           SCHEMAST."S_ACNOTYPE" = SHMASTER."AC_ACNOTYPE" 
           AND SCHEMAST."S_APPL" = SHMASTER."AC_TYPE" AND SHMASTER."AC_EXPDT" = null 
           AND cast(SHMASTER."AC_TYPE" as character varying) = GUARANTERDETAILS."MEMBER_TYPE" 
           AND SHMASTER."AC_NO" = cast(GUARANTERDETAILS."MEMBER_NO" as integer) 
           AND LNMASTER."AC_ACNOTYPE" = GUARANTERDETAILS."AC_ACNOTYPE" 
           AND cast(LNMASTER."AC_TYPE" as character varying) = GUARANTERDETAILS."AC_TYPE" 
           AND LNMASTER."AC_NO" = cast(GUARANTERDETAILS."AC_NO" as integer) )';

$sql =  pg_query($conn,$query);
         
$i = 0;
while($row = pg_fetch_assoc($sql)){
    $tmp=[
        'SR_NO' => $row['SR_NO'],
        'AC_NAME'=> $row['AC_NAME'],
        'CITY_NAME'=> $row['CITY_NAME'],
        'MEMBER_NO'=> $row['MEMBER_NO'],
        'AC_ADDR'=> $row['AC_ADDR'],
        'NAME'=> $row['NAME']
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
    
?>