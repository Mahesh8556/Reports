<?php

ob_start();

include "main.php";

require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/passbookissue.jrxml';

$data = [];
$row = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");

$dateformate = "'DD/MM/YYYY'";
$sh="'SH'";
$p="'P'";
$symbol="||";
$symbol1="')'";
$space="'  '";
$space1="' '";
// $BANKACNO="'101102901100001'"
// $BANKACNO2="'101101501100107'";
// $BANKACNO3="'101101101100253'";
$flag =$_GET['flag'];
$bankName = $_GET['bankName'];
$BRANCH_NAME = $_GET['BRANCH_NAME'];
$branch_code = $_GET['BRANCH_CODE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$AC_TYPE = $_GET['AC_TYPE'];
$BANKACNO = $_GET['BANKACNO'];
$BANKACNO2 = $_GET['BANKACNO'];
$BANKACNO3 = $_GET['BANKACNO'];
$ISSUE_DATE = $_GET['ISSUE_DATE'];

$BRANCH_NAME1 = str_replace("'" , "" , $BRANCH_NAME);
$bankName = str_replace("'" , "" , $bankName);
$AC_ACNOTYPE1 = str_replace("'" , "" , $AC_ACNOTYPE);
$AC_TYPE1 = str_replace("'" , "" , $AC_TYPE);
$BANKACNO1 = str_replace("'" , "" , $BANKACNO);
$ISSUE_DATE1 = str_replace("'" , "" , $ISSUE_DATE);


if( $flag== 0 )
{
$query='SELECT 
shmaster."AC_EXPDT", shmaster."AC_ACNOTYPE", SCHEMAST."S_APPL" "AC_TYPE", 
shmaster."BANKACNO" "AC_NO", "AC_NAME",  shmaster."idmasterID"  "CUSTID",shmaster.ID "SHAREID",
TRIM((CUSTOMERADDRESS."AC_ADDR"),("AC_AREA")) "AC_ADDR1", CITYMASTER."CITY_NAME" "CITY",
OWNBRANCHMASTER."NAME" "BRANCH_NAME", SCHEMAST."S_NAME" "SCHEME"
From 
shmaster 
LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = shmaster."idmasterID" AND
 CUSTOMERADDRESS."AC_ADDTYPE"='.$p.'
  LEFT JOIN CITYMASTER ON CITYMASTER.ID=CUSTOMERADDRESS."AC_CTCODE"
 LEFT JOIN OWNBRANCHMASTER ON OWNBRANCHMASTER.ID=shmaster."BRANCH_CODE", SCHEMAST  Where 
shmaster."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE" 
AND shmaster."AC_TYPE" = SCHEMAST.ID 
And shmaster."BANKACNO" ='.$BANKACNO.'';
}
// echo $query;
if($flag== 1 )
{
$query='SELECT  0 "AC_MATUAMT", null "AC_EXPDT", LNMASTER."AC_ACNOTYPE", SCHEMAST."S_APPL" "AC_TYPE", 
 LNMASTER."BANKACNO" "AC_NO", LNMASTER."AC_NAME", LNMASTER."idmasterID"  "CUSTID",LNMASTER.ID "LNID",
  	OWNBRANCHMASTER."NAME" "BRANCH_NAME", SCHEMAST."S_NAME" "SCHEME", '.$space1.' "AC_NNAME",
	TRIM((CUSTOMERADDRESS."AC_ADDR"),("AC_AREA")) "AC_ADDR1", CITYMASTER."CITY_NAME" "CITY"
   From LNMASTER 
   LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID" AND
   CUSTOMERADDRESS."AC_ADDTYPE"='.$p.'
    LEFT JOIN CITYMASTER ON CITYMASTER.ID=CUSTOMERADDRESS."AC_CTCODE"
   LEFT JOIN OWNBRANCHMASTER ON OWNBRANCHMASTER.ID=LNMASTER."BRANCH_CODE",SCHEMAST 
   Where LNMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
 AND LNMASTER."AC_TYPE" = SCHEMAST.ID 
  And LNMASTER."BANKACNO" ='.$BANKACNO2.'';
}
if($flag== 2 )
{
$query='SELECT 
 DPMASTER."AC_MATUAMT", DPMASTER."AC_EXPDT", DPMASTER."AC_ACNOTYPE", SCHEMAST."S_APPL" "AC_TYPE", 
 DPMASTER."BANKACNO" "AC_NO", "AC_NAME",  DPMASTER."idmasterID"  "CUSTID",DPMASTER.ID "DPID",
TRIM((CUSTOMERADDRESS."AC_ADDR"),("AC_AREA")) "AC_ADDR1", CITYMASTER."CITY_NAME" "CITY",
OWNBRANCHMASTER."NAME" "BRANCH_NAME", SCHEMAST."S_NAME" "SCHEME"
 From 
 DPMASTER 
  LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = DPMASTER."idmasterID" AND
   CUSTOMERADDRESS."AC_ADDTYPE"='.$p.'
    LEFT JOIN CITYMASTER ON CITYMASTER.ID=CUSTOMERADDRESS."AC_CTCODE"
   LEFT JOIN OWNBRANCHMASTER ON OWNBRANCHMASTER.ID=DPMASTER."BRANCH_CODE", SCHEMAST  Where 
  DPMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE" 
 AND DPMASTER."AC_TYPE" = SCHEMAST.ID 
 And DPMASTER."BANKACNO" ='.$BANKACNO3.'';
}
//  echo $query;

 $sql =  pg_query($conn,$query);

$i = 0;

while($row = pg_fetch_assoc($sql)){
$guaranter='';
    if(isset($row['LNID'])){
        $query1 ='SELECT string_agg( CAST(cnt '.$symbol.$symbol1.$symbol.$space1.$symbol.' "GUARANTER" AS CHARACTER VARYING),'.$space.') "NAME1"  FROM(
            select ROW_NUMBER() OVER (
          ORDER BY "lnmasterID"
          ) cnt , "lnmasterID",  "AC_NAME" "GUARANTER" from GUARANTERDETAILS where
           GUARANTERDETAILS."lnmasterID"='. $row['LNID'].' AND "EXP_DATE" IS NULL )GUARANTER
            group by "lnmasterID"';
            $guaranter;
            // echo $lnid;
            //  echo $query1;
        $sql =  pg_query($conn,$query1);
        while($row1 = pg_fetch_assoc($sql)){
            $guaranter=$row1['NAME1'];
                 }
    }
    $nominee='';
  
    if(isset($row['SHAREID'])){
    // while($row = pg_fetch_assoc($sql)){   
    $query2 ='SELECT string_agg( CAST(cnt'.$symbol.$symbol1.$symbol.' '.$space1.' '.$symbol.' "NOMINEE" AS CHARACTER VARYING),'.$space.') "NAME1"  FROM(
        select ROW_NUMBER() OVER (
      ORDER BY "sharesID"
      ) cnt , "sharesID",  "AC_NNAME" "NOMINEE" from NOMINEELINK where
       NOMINEELINK."sharesID"='. $row['SHAREID'].'  )NOMINEE
        group by "sharesID"';
        $nominee;

        // echo $query2;

        $sql2 =  pg_query($conn,$query2);
        while($row2 = pg_fetch_assoc($sql2)){
        $nominee1=$row2['NAME1'];
        }

        // echo $nominee1;
    }

    $nominee='';
  
    if(isset($row['DPID'])){
    // while($row = pg_fetch_assoc($sql)){   
    $query3 ='SELECT string_agg( CAST(cnt '.$symbol.$symbol1.$symbol.' '.$space1.' '.$symbol.'  "NOMINEE" AS CHARACTER VARYING),'.$space.') "NAME1"  FROM(
        select ROW_NUMBER() OVER (
      ORDER BY "DPMasterID"
      ) cnt , "DPMasterID",  "AC_NNAME" "NOMINEE" from NOMINEELINK where
       NOMINEELINK."DPMasterID"='. $row['DPID'].' )NOMINEE
        group by "DPMasterID"';
      
      
        $nominee;
        // echo $query3;   
        $sql =  pg_query($conn,$query3);
        while($row3 = pg_fetch_assoc($sql)){
        $nominee=$row3['NAME1'];
        }
    }

    $joint='';
  
    if(isset($row['DPID'])){
    // while($row = pg_fetch_assoc($sql)){   
    $query4 ='SELECT string_agg( "JOINT",'.$space.') "NAME2"  FROM(
        select ROW_NUMBER() OVER ( ORDER BY "DPMasterID") cnt , "DPMasterID",
          "JOINT_ACNAME" "JOINT" from joint_ac_link where
          joint_ac_link."DPMasterID"='. $row['DPID'].' )JOINT
        group by "DPMasterID"';
      
      
        $joint;
        // echo $query3;   
        $sql =  pg_query($conn,$query4);
        while($row4 = pg_fetch_assoc($sql)){
        $joint=$row4['NAME2'];
        }
    }
        //  echo $query4;
        //  echo $joint;
        //  }
      

    $tmp=[
        "bankName" => $bankName,
        "AC_NAME" => $joint==''?$row["AC_NAME"]:$row["AC_NAME"]  . ' / '.$joint ,
        // "AC_NNAME" => $row["NAME1"],
        "AC_ADDR" => $row["AC_ADDR1"],
        "BRANCH_NAME" => $BRANCH_NAME1 ,
        "branch_code" => $branch_code ,
        "ISSUE_DATE" => $ISSUE_DATE1 ,
        "SCHEME" => $row['SCHEME'],
        "AC_ACNOTYPE" => $row['AC_ACNOTYPE'],
        "AC_TYPE" => $row['AC_TYPE'],
        "BANKACNO" => $BANKACNO1,
        // "BANKACNO2" => $BANKACNO4,
        // "BANKACNO3" => $BANKACNO5,
        'flag'=>$flag,
        "AC_NNAME"=>$nominee1,
        "AC_NNAME1"=>$nominee,
        "guaranter"=>$guaranter,
        // "joint" => $joint,
    ];
    $data[$i]=$tmp;
    $i++;
  
    }
// }
// }

 ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
     ->setDataSource($config)
     ->export('Pdf');
    
// } 
  
?>
