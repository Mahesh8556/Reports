<?php
    
    ob_start(); 
    include "main.php";
    require_once('dbconnect.php');
    
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    use simitsdk\phpjasperxml\PHPJasperXML;
    
    $filename = __DIR__.'/InterestExecutionListCredit.jrxml';
    
    $data = [];
    $faker = Faker\Factory::create('en_US');
    
    //connect pgAdmin database connection 
    // $conn = pg_connect("host=127.0.0.1 dbname=CBSBhairavnath user=postgres password=admin");
   
    // $date = "'31/12/2021'";
    $dateformat = "'DD/MM/YYYY'";

    //variable
   $bankName = $_GET['bankName'];
   $date = $_GET['date'];  
   $status = $_GET['status'];
   $branch = $_GET['branch'];
   $frequency = $_GET['frequency'];
   $PrintClosedAccounts = $_GET['PrintClosedAccounts'];

   $bankName = str_replace("'", "", $bankName);
   $date_ = str_replace("'", "", $date);
   $frequency = str_replace("'", "", $frequency);
//    $branchName = str_replace("'", "", $branchName);
    
    $query = 'SELECT intinstruction."INSTRUCTION_NO", intinstruction."CR_AC_NO", intinstruction."DR_AC_NO",
              intinstruction."LAST_EXEC_DATE", intinstruction."CR_ACTYPE", intinstruction."CR_ACTYPE", 
              dpmaster."AC_INTRATE", dpmaster."AC_SCHMAMT", dpmaster."AC_OPDATE", dpmaster."AC_NAME",
              dpmaster."AC_EXPDT", dpmaster."AC_REF_RECEIPTNO",ownbranchmaster."NAME"
              from intinstruction, dpmaster
              Inner Join ownbranchmaster on
              dpmaster."BRANCH_CODE" = ownbranchmaster."id" where
              intinstruction."REVOKE_DATE" is null and
              AND dpmaster."BRANCH_CODE" = '.$branch.' 
              cast("INSTRUCTION_DATE" as date) = '.$date.'::date
              ORDER BY "CR_ACTYPE" ASC ';

    $sql =  pg_query($conn,$query);
    
    $i = 0;

    $GRAND_TOTAL = 0;
    $GRAND_TOTAL1 = 0;

    if (pg_num_rows($sql) == 0) {
        include "errormsg.html";
    }else {

    while($row = pg_fetch_assoc($sql)){

        $GRAND_TOTAL = $GRAND_TOTAL + 15000;
        $GRAND_TOTAL1 = $GRAND_TOTAL1 + $row['AC_SCHMAMT'];

        if($type == ''){
            $type = $row['CR_ACTYPE'];
        }
        if($type == $row['CR_ACTYPE']){
            $GROUP_TOTAL = $GROUP_TOTAL + 15000;
        }else{
            $type = $row['CR_ACTYPE'];
            $GROUP_TOTAL = 0;
            $GROUP_TOTAL = $GROUP_TOTAL + 15000;
        }
    
        if($type == ''){
            $type = $row['CR_ACTYPE'];
        }
        if($type == $row['CR_ACTYPE']){
            $SCHEME_TOTAL = $SCHEME_TOTAL + $row['AC_SCHMAMT'];
        }else{
            $type = $row['CR_ACTYPE'];
            $SCHEME_TOTAL = 0;
            $SCHEME_TOTAL = $SCHEME_TOTAL + $row['AC_SCHMAMT'];
        }

        $tmp=[
            'INSTRUCTION_NO' => $row['INSTRUCTION_NO'],
            'CR_AC_NO'=> $row['CR_AC_NO'],
            'DR_AC_NO' => $row['DR_AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'LAST_EXEC_DATE' => $row['LAST_EXEC_DATE'],
            'AC_INTRATE'=> $row['AC_INTRATE'],
            'INTREST_AMT'=> 15000 ,
            'AC_SCHMAMT'=> $row['AC_SCHMAMT'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_EXPDT'=> $row['AC_EXPDT'],
            'AC_REF_RECEIPTNO'=> $row['AC_REF_RECEIPTNO'],
            'CR_ACTYPE'=> $row['CR_ACTYPE'],
            'NAME'=> $row['NAME'],
            'totalintamt'=> $GRAND_TOTAL,
            'totaldepoamt'=> $GRAND_TOTAL1,
            'schemdepoamt'=> $SCHEME_TOTAL,
            'schemintamt'=> $GROUP_TOTAL,
            'date_' => $date_,
            'branch' => $branch,
            'status' => $status,
            'frequency' => $frequency,
            'PrintClosedAccounts' => $PrintClosedAccounts,
            'bankName' => $bankName,

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

    }
?>    
    
    