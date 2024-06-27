<?php
include "main (1).php";

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/pigmymaturedaclist.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');
require_once('dbconnect.php');
//connect mysql database connection 
//$conn = mysqli_connect('localhost','root','','book');
//get data from enquiry table
$date ="'02/08/2021'";
$sql =pg_query($conn,'SELECT COUNT(pgmaster."AC_NO") as count,
ledgerbalance(cast (schemast."S_APPL" as character varying),
pgmaster."BANKACNO",'.$date.',0,1)as ledgerbalance,
dpmaster."AC_NAME" as agentname,pgmaster."AC_NO", pgmaster."AC_EXPDT",pgmaster."AC_OPDATE",
pgmaster."AC_NAME",schemast."S_APPL",schemast."S_NAME"
FROM dpmaster 
inner join pgmaster on dpmaster."idmasterID" = pgmaster."idmasterID"
Inner Join schemast on pgmaster."AC_TYPE" = schemast."id"
WHERE pgmaster."AGENT_ACTYPE" = cast(dpmaster."AC_TYPE" as character varying)  									
AND pgmaster."AGENT_ACNO" = cast(dpmaster."AC_NO" as character varying)
and dpmaster."BRANCH_CODE" = 1 and cast(pgmaster."AC_OPDATE" as date) <= '.$date.'::date
GROUP BY schemast."S_APPL", schemast."S_NAME", pgmaster."BANKACNO", dpmaster."AC_NAME", pgmaster."AC_NO", pgmaster."AC_EXPDT", pgmaster."AC_OPDATE", pgmaster."AC_NAME";
');
$i = 0;
$GRAND_TOTAL = 0;
$schemeledger = 0;
$type = 0;
while($row = pg_fetch_assoc($sql)){

    $GRAND_TOTAL = $GRAND_TOTAL + $row['ledgerbalance'];
    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $schemeledger = $schemeledger + $row['ledgerbalance'];
    }else{
        $type = $row['agentname'];
        $schemeledger = 0;
        $schemeledger = $schemeledger + $row['ledgerbalance'];
    }


    $tmp=[
        'AC_NO' => $row['AC_NO'],  
        'AC_NAME' => $row['AC_NAME'],
        'AC_OPDATE' => $row['AC_OPDATE'],
        'AC_EXPDT' => $row['AC_EXPDT'],
        'ledgerbalance' => $row['ledgerbalance'],
        'agentname' => $row['agentname'],
        'S_APPL' => $row['S_APPL'],
        'field1' => $GRAND_TOTAL,
        'count' => $row['count'],
        'field2' => $schemeledger,
    ];
    $data[$i]=$tmp;
    $i++;
}


$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
