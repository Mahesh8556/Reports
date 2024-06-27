<?php
ob_start();
include "main.php";
require_once('dbconnect.php');


use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/InstallmentOverDueList.jrxml';

$data = [];
$i=0;
$faker = Faker\Factory::create('en_US');
$dd="'DD/MM/YYYY'";
$schemeName=$_GET['AC_TYPE'];
$bankName  = $_GET['bankName'];
$BranchName = $_GET['BranchName'];
$schemeCode=$_GET['schemeCode'];
$BRANCH_CODE=$_GET['BRANCH_CODE'];
$Date =$_GET['date'];


$TOBE_RECOVER_AMT= 0;

//$date="'31/03/2024'";

$LN= "'LN'";
$CC ="'CC'";
$DS="'DS'";
// $date1="'30/09/2022'";
// $date1 = $_GET['date1'];
$status="'1'"; 
$Space="' '";
$D="'D'";
$P="'P'";
$no="'120'";
$DUEBAL= "' DUEBAL'";
$AC_CLOSED="'0'";
$bankName = str_replace("'", "", $bankName);
$BranchName = str_replace("'", "", $BranchName);
$Date1 = str_replace("'", "", $Date);


$ACNO1="'101101501100263'";
$ACNO2="'101101501100329'";
$ACNO3="'101101501100331'";
$ACNO4="'101101501100406'";
$ACNO5="'101101501100423'";
$ACNO6="'101101501100070'";
$ACNO7="'101101501100147'";
$ACNO8="'101101501100203'";
$ACNO9="'101101501100238'";
$ACNO10="'101101501100251'";
$ACNO11="'101101501100263'";
$ACNO12="'101101501100272'";
$ACNO13="'101101501100274'";
$ACNO14="'101101501100284'";
$ACNO15="'101101501100288'";
$ACNO16="'101101501100297'";
$ACNO17="'101101501100317'";
$ACNO18="'101101501100327'";
$ACNO19="'101101501100329'";
$ACNO20="'101101501100331'";
$ACNO21="'101101501100333'";
$ACNO22="'101101501100346'";
$ACNO23="'101101501100347'";
$ACNO24="'101101501100348'";
$ACNO25="'101101501100349'";
$ACNO26="'101101501100350'";
$ACNO27="'101101501100352'";
$ACNO28="'101101501100367'";
$ACNO29="'101101501100368'";
$ACNO30="'101101501100376'";


$checktype;
$BRANCH_CODE == '0'? $checktype='true': $checktype='false';
//  echo $checktype;

if($BRANCH_CODE == '0'){
$query='SELECT *
FROM
	(SELECT LNMASTER.ID "LNID",
			SCHEMAST."S_NAME",
			SCHEMAST."S_APPL",
			SCHEMAST."S_APPL" || ' .$Space. ' || SCHEMAST."S_NAME" "SCHEME",
			LNMASTER."AC_ACNOTYPE",
			LNMASTER."AC_TYPE",
			LNMASTER."AC_NO",
            LNMASTER."AC_MONTHS",
			LNMASTER."AC_NAME",
			LNMASTER."BANKACNO",
			LNMASTER."AC_OPDATE",
			LNMASTER."idmasterID",
			LNMASTER."AC_CLOSEDT",
            LNMASTER."AC_CLOSED",
			DIRECTORMASTER.ID || ' .$Space. ' || DIRECTORMASTER."NAME" "DIRECTORMASTER",
			"AC_RECOMMEND_BY" "DIRECTOR",
			AUTHORITYMASTER.ID || ' .$Space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER",
			"AC_AUTHORITY" "AUTHORITY",
			RECOVERYCLEARKMASTER.ID || ' .$Space. ' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER",
			"AC_RECOVERY_CLERK",
			OWNBRANCHMASTER.ID || ' .$Space. ' || OWNBRANCHMASTER."NAME" "BRANCH",
			LNMASTER."BRANCH_CODE",
			CUSTOMERADDRESS."AC_CTCODE" "CITY",
			CITYMASTER."CITY_NAME" "CITYNAME",
			CUSTOMERADDRESS."AC_ADDR" || '  .$Space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS",
			LNMASTER."AC_MORATORIUM_PERIOD",
			LNMASTER."AC_SANCTION_AMOUNT",
			LNMASTER."AC_GRACE_PERIOD",
			LNMASTER."AC_REPAYMODE",
			IDMASTER."AC_MOBILENO",
			(COALESCE(CASE LNMASTER."AC_OP_CD"
																	WHEN '.$D.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
																	ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
													END,

					0) + COALESCE(CAST(TRANTABLE."TRAN_AMOUNT" AS FLOAT),

											0)) "LEDGER_BALANCE",
			(COALESCE(CASE LNMASTER."AC_OP_CD"
																	WHEN '.$D.' THEN LNMASTER."AC_RECBLEINT_OP"
																	ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
													END,

					0) + COALESCE(CAST(TRANTABLE."RECPAY_INT_AMOUNT" AS FLOAT),

											0) + COALESCE(CASE LNMASTER."AC_OP_CD"
																													WHEN '.$D.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
																													ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
																									END,

																	0) + COALESCE(CAST(TRANTABLE."OTHER10_AMOUNT" AS FLOAT),

																							0)) "RECPAY_INT_AMOUNT",
			LNMASTER."AC_INSTALLMENT",
			OIRINTBALANCE(SCHEMAST."S_APPL",

				LNMASTER."BANKACNO",
				'.$Date.', 0) "OVERDUEINTEREST",
                (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS",
                (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),

									LNMASTER."BANKACNO",
									'.$Date.',
									'.$DUEBAL.',
									0) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"DUEINSTALLMENT",
                                    (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then 
                                    CEIL((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) - DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),

LNMASTER."BANKACNO",
'.$Date.',
'.$DUEBAL.',
0)) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT))else 0 end ) "PAIDINSTALLMENTS",
			DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),

				LNMASTER."BANKACNO",
				'.$Date.',
				'.$DUEBAL.', 0) "DUEBALANCE",
			"AC_EXPIRE_DATE",
			OVERDUEDATE(SCHEMAST."S_APPL",

				LNMASTER."BANKACNO",
				CAST('.$Date.' AS CHARACTER VARYING),
				CAST(DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),

										LNMASTER."BANKACNO",
										'.$Date.',
										'.$DUEBAL.',
										0) AS CHARACTER VARYING),
				CAST(LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING)) "OVERDUEDATE"
		FROM LNMASTER
		LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE"
		LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER)
		LEFT JOIN RECOVERYCLEARKMASTER ON RECOVERYCLEARKMASTER.ID = CAST(LNMASTER."AC_RECOVERY_CLERK" AS INTEGER)
		LEFT JOIN AUTHORITYMASTER ON AUTHORITYMASTER.ID = LNMASTER."AC_AUTHORITY"
		LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID"
		AND CUSTOMERADDRESS."AC_ADDTYPE" = '.$P.'
		LEFT JOIN CITYMASTER ON CITYMASTER.ID = CUSTOMERADDRESS."AC_CTCODE"
		LEFT JOIN IDMASTER ON IDMASTER.ID = LNMASTER."idmasterID",

			(SELECT "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO",
					COALESCE(SUM(COALESCE(CAST("TRAN_AMOUNT" AS FLOAT),

																			0) + COALESCE("DAILY_AMOUNT",

																									0)),

						0) "TRAN_AMOUNT",
					COALESCE(SUM(COALESCE(CAST("RECPAY_INT_AMOUNT" AS FLOAT),

																			0) + COALESCE("DAILY_RECPAY_INT_AMOUNT",

																									0)),

						0) "RECPAY_INT_AMOUNT",
					COALESCE(SUM(COALESCE(CAST("OTHER10_AMOUNT" AS FLOAT),

																			0) + COALESCE("DAILY_OTHER10_AMOUNT",

																									0)),

						0) "OTHER10_AMOUNT"
				FROM
					(SELECT *
						FROM
							(SELECT "TRAN_ACNOTYPE",
									"TRAN_ACTYPE",
									"TRAN_ACNO",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
																						END),

										0) "TRAN_AMOUNT",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
																						END),

										0) "RECPAY_INT_AMOUNT",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
																						END),

										0) "OTHER10_AMOUNT",
									0 "DAILY_AMOUNT",
									0 "DAILY_RECPAY_INT_AMOUNT",
									0 "DAILY_OTHER10_AMOUNT"
								FROM LOANTRAN
								WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$Date.',

																																												'.$dd.')
									AND "TRAN_ACNOTYPE" IN ('.$LN.',
																																		'.$CC.',
																																		'.$DS.')
								GROUP BY "TRAN_ACNOTYPE",
									"TRAN_ACTYPE",
									"TRAN_ACNO"
								UNION ALL SELECT "TRAN_ACNOTYPE",
									"TRAN_ACTYPE",
									"TRAN_ACNO",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
																						END),

										0) "DAILY_AMOUNT",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
																						END),

										0) "DAILY_RECPAY_INT_AMOUNT",
									COALESCE(SUM(CASE "TRAN_DRCR"
																										WHEN '.$D.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
																										ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
																						END),

										0) "DAILY_OTHER10_AMOUNT",
									0 "TRAN_AMOUNT",
									0 "RECPAY_INT_AMOUNT",
									0 "OTHER10_AMOUNT"
								FROM DAILYTRAN
								WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$Date.',

																																												'.$dd.')
									AND "TRAN_STATUS" = '.$status.'
									AND "TRAN_ACNOTYPE" IN ('.$LN.',
																																		'.$CC.',
																																		'.$DS.')
								GROUP BY "TRAN_ACNOTYPE",
									"TRAN_ACTYPE",
									"TRAN_ACNO") RS
						ORDER BY "TRAN_ACNOTYPE",
							"TRAN_ACTYPE",
							"TRAN_ACNO")AMOUNT
				GROUP BY "TRAN_ACNOTYPE",
					"TRAN_ACTYPE",
					"TRAN_ACNO") TRANTABLE
		WHERE LNMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE"
			AND LNMASTER."AC_TYPE" = CAST(TRANTABLE."TRAN_ACTYPE" AS INTEGER)
			AND LNMASTER."BANKACNO" = TRANTABLE."TRAN_ACNO"
			AND "AC_TYPE" = '.$schemeName.'
			AND LNMASTER."status" = 1
			AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
            and LNMASTER."AC_CLOSED" = '.$AC_CLOSED.'
		ORDER BY SCHEMAST."S_APPL",
			LNMASTER."BANKACNO") AS S
WHERE 
	 CAST(S."DUEINSTALLMENT" AS INTEGER) <= 120 AND
	CAST(S."AC_EXPIRE_DATE" AS DATE) <= TO_DATE('.$Date.','.$dd.') 
	AND (S."AC_OPDATE" IS NULL OR 
	CAST(S."AC_OPDATE" AS DATE) <= TO_DATE('.$Date.','.$dd.'))
	AND 
	(S."AC_CLOSEDT" IS NULL OR
	CAST(S."AC_CLOSEDT" AS DATE) > 
	TO_DATE('.$Date.','.$dd.'))';
}
else
{
    $query='SELECT *
    FROM
        (SELECT LNMASTER.ID "LNID",
                SCHEMAST."S_NAME",
                SCHEMAST."S_APPL",
                SCHEMAST."S_APPL" || ' .$Space. ' || SCHEMAST."S_NAME" "SCHEME",
                LNMASTER."AC_ACNOTYPE",
                LNMASTER."AC_TYPE",
                LNMASTER."AC_NO",
                LNMASTER."AC_MONTHS",
                LNMASTER."AC_NAME",
                LNMASTER."BANKACNO",
                LNMASTER."AC_OPDATE",
                LNMASTER."idmasterID",
                LNMASTER."AC_CLOSEDT",
                LNMASTER."AC_CLOSED",
                DIRECTORMASTER.ID || ' .$Space. ' || DIRECTORMASTER."NAME" "DIRECTORMASTER",
                "AC_RECOMMEND_BY" "DIRECTOR",
                AUTHORITYMASTER.ID || ' .$Space. ' || AUTHORITYMASTER."NAME" "AUTHORITYMASTER",
                "AC_AUTHORITY" "AUTHORITY",
                RECOVERYCLEARKMASTER.ID || ' .$Space. ' || RECOVERYCLEARKMASTER."NAME" "RECOVERYCLEARKMASTER",
                "AC_RECOVERY_CLERK",
                OWNBRANCHMASTER.ID || ' .$Space. ' || OWNBRANCHMASTER."NAME" "BRANCH",
                LNMASTER."BRANCH_CODE",
                CUSTOMERADDRESS."AC_CTCODE" "CITY",
                CITYMASTER."CITY_NAME" "CITYNAME",
                CUSTOMERADDRESS."AC_ADDR" || '  .$Space.' || CUSTOMERADDRESS."AC_AREA" "ADDRESS",
                LNMASTER."AC_MORATORIUM_PERIOD",
                LNMASTER."AC_SANCTION_AMOUNT",
                LNMASTER."AC_GRACE_PERIOD",
                LNMASTER."AC_REPAYMODE",
                IDMASTER."AC_MOBILENO",
                (COALESCE(CASE LNMASTER."AC_OP_CD"
                                                                        WHEN '.$D.' THEN CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                                                        ELSE (-1) * CAST(LNMASTER."AC_OP_BAL" AS FLOAT)
                                                        END,
    
                        0) + COALESCE(CAST(TRANTABLE."TRAN_AMOUNT" AS FLOAT),
    
                                                0)) "LEDGER_BALANCE",
                (COALESCE(CASE LNMASTER."AC_OP_CD"
                                                                        WHEN '.$D.' THEN LNMASTER."AC_RECBLEINT_OP"
                                                                        ELSE (-1) * LNMASTER."AC_RECBLEINT_OP"
                                                        END,
    
                        0) + COALESCE(CAST(TRANTABLE."RECPAY_INT_AMOUNT" AS FLOAT),
    
                                                0) + COALESCE(CASE LNMASTER."AC_OP_CD"
                                                                                                                        WHEN '.$D.' THEN CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                                                                                                        ELSE (-1) * CAST(LNMASTER."AC_RECBLEODUEINT_OP" AS FLOAT)
                                                                                                        END,
    
                                                                        0) + COALESCE(CAST(TRANTABLE."OTHER10_AMOUNT" AS FLOAT),
    
                                                                                                0)) "RECPAY_INT_AMOUNT",
                LNMASTER."AC_INSTALLMENT",
                OIRINTBALANCE(SCHEMAST."S_APPL",
    
                    LNMASTER."BANKACNO",
                    '.$Date.', 0) "OVERDUEINTEREST",
                    (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then  CEIL(CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"TOTALINSTALLMENTS",
                    (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL(DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),
    
                                        LNMASTER."BANKACNO",
                                        '.$Date.',
                                        '.$DUEBAL.',
                                        0) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT)) else 0 end )"DUEINSTALLMENT",
                                        (case when CAST (LNMASTER."AC_INSTALLMENT" as FLOAT) <> 0  then CEIL((CAST(LNMASTER."AC_SANCTION_AMOUNT" AS FLOAT) - DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),LNMASTER."BANKACNO",'.$Date.','.$DUEBAL.',0)) / CAST(LNMASTER."AC_INSTALLMENT" AS FLOAT))else 0 end ) "PAIDINSTALLMENTS",
                DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),
    
                    LNMASTER."BANKACNO",
                    '.$Date.',
                    '.$DUEBAL.', 0) "DUEBALANCE",
                "AC_EXPIRE_DATE",
                OVERDUEDATE(SCHEMAST."S_APPL",
    
                    LNMASTER."BANKACNO",
                    CAST('.$Date.' AS CHARACTER VARYING),
                    CAST(DUEBALANCE(CAST(SCHEMAST."S_APPL" AS CHARACTER VARYING),
    
                                            LNMASTER."BANKACNO",
                                            '.$Date.',
                                            '.$DUEBAL.',
                                            0) AS CHARACTER VARYING),
                    CAST(LNMASTER."AC_INSTALLMENT" AS CHARACTER VARYING)) "OVERDUEDATE"
            FROM LNMASTER
            LEFT JOIN SCHEMAST ON SCHEMAST.ID = LNMASTER."AC_TYPE"
            LEFT JOIN DIRECTORMASTER ON DIRECTORMASTER.ID = CAST(LNMASTER."AC_RECOMMEND_BY" AS INTEGER)
            LEFT JOIN RECOVERYCLEARKMASTER ON RECOVERYCLEARKMASTER.ID = CAST(LNMASTER."AC_RECOVERY_CLERK" AS INTEGER)
            LEFT JOIN AUTHORITYMASTER ON AUTHORITYMASTER.ID = LNMASTER."AC_AUTHORITY"
            LEFT JOIN OWNBRANCHMASTER ON OWNBRANCHMASTER.ID = LNMASTER."BRANCH_CODE"
            LEFT JOIN CUSTOMERADDRESS ON CUSTOMERADDRESS."idmasterID" = LNMASTER."idmasterID"
            AND CUSTOMERADDRESS."AC_ADDTYPE" = '.$P.'
            LEFT JOIN CITYMASTER ON CITYMASTER.ID = CUSTOMERADDRESS."AC_CTCODE"
            LEFT JOIN IDMASTER ON IDMASTER.ID = LNMASTER."idmasterID",
    
                (SELECT "TRAN_ACNOTYPE",
                        "TRAN_ACTYPE",
                        "TRAN_ACNO",
                        COALESCE(SUM(COALESCE(CAST("TRAN_AMOUNT" AS FLOAT),
    
                                                                                0) + COALESCE("DAILY_AMOUNT",
    
                                                                                                        0)),
    
                            0) "TRAN_AMOUNT",
                        COALESCE(SUM(COALESCE(CAST("RECPAY_INT_AMOUNT" AS FLOAT),
    
                                                                                0) + COALESCE("DAILY_RECPAY_INT_AMOUNT",
    
                                                                                                        0)),
    
                            0) "RECPAY_INT_AMOUNT",
                        COALESCE(SUM(COALESCE(CAST("OTHER10_AMOUNT" AS FLOAT),
    
                                                                                0) + COALESCE("DAILY_OTHER10_AMOUNT",
    
                                                                                                        0)),
    
                            0) "OTHER10_AMOUNT"
                    FROM
                        (SELECT *
                            FROM
                                (SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "TRAN_AMOUNT",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "RECPAY_INT_AMOUNT",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "OTHER10_AMOUNT",
                                        0 "DAILY_AMOUNT",
                                        0 "DAILY_RECPAY_INT_AMOUNT",
                                        0 "DAILY_OTHER10_AMOUNT"
                                    FROM LOANTRAN
                                    WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$Date.',
    
                                                                                                                                                                                    '.$dd.')
                                        AND "TRAN_ACNOTYPE" IN ('.$LN.',
                                                                                                                                            '.$CC.',
                                                                                                                                            '.$DS.')
                                    GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO"
                                    UNION ALL SELECT "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "DAILY_AMOUNT",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "DAILY_RECPAY_INT_AMOUNT",
                                        COALESCE(SUM(CASE "TRAN_DRCR"
                                                                                                            WHEN '.$D.' THEN CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                                            ELSE (-1) * CAST ("OTHER10_AMOUNT" AS FLOAT)
                                                                                            END),
    
                                            0) "DAILY_OTHER10_AMOUNT",
                                        0 "TRAN_AMOUNT",
                                        0 "RECPAY_INT_AMOUNT",
                                        0 "OTHER10_AMOUNT"
                                    FROM DAILYTRAN
                                    WHERE CAST("TRAN_DATE" AS date) <= TO_DATE('.$Date.',
    
                                                                                                                                                                                    '.$dd.')
                                        AND "TRAN_STATUS" = '.$status.'
                                        AND "TRAN_ACNOTYPE" IN ('.$LN.',
                                                                                                                                            '.$CC.',
                                                                                                                                            '.$DS.')
                                    GROUP BY "TRAN_ACNOTYPE",
                                        "TRAN_ACTYPE",
                                        "TRAN_ACNO") RS
                            ORDER BY "TRAN_ACNOTYPE",
                                "TRAN_ACTYPE",
                                "TRAN_ACNO")AMOUNT
                    GROUP BY "TRAN_ACNOTYPE",
                        "TRAN_ACTYPE",
                        "TRAN_ACNO") TRANTABLE
            WHERE LNMASTER."AC_ACNOTYPE" = TRANTABLE."TRAN_ACNOTYPE"
                AND LNMASTER."AC_TYPE" = CAST(TRANTABLE."TRAN_ACTYPE" AS INTEGER)
                AND LNMASTER."BANKACNO" = TRANTABLE."TRAN_ACNO"
                AND "AC_TYPE" = '.$schemeName.'
                AND LNMASTER."BRANCH_CODE" IN (2)
                AND LNMASTER."status" = 1
                AND LNMASTER."SYSCHNG_LOGIN" IS NOT NULL
                and LNMASTER."AC_CLOSED" = '.$AC_CLOSED.'
            ORDER BY SCHEMAST."S_APPL",
                LNMASTER."BANKACNO") AS S
    WHERE 
         CAST(S."DUEINSTALLMENT" AS INTEGER) <= 120 AND
        CAST(S."AC_EXPIRE_DATE" AS DATE) <= TO_DATE('.$Date.','.$dd.') 
        AND (S."AC_OPDATE" IS NULL OR 
        CAST(S."AC_OPDATE" AS DATE) <= TO_DATE('.$Date.','.$dd.'))
        AND 
        (S."AC_CLOSEDT" IS NULL OR
        CAST(S."AC_CLOSEDT" AS DATE) > 
        TO_DATE('.$Date.','.$dd.'))';

}
	
// echo $query;

 $sql =  pg_query($conn,$query);

 $GRAND_TOTAL1=0;
 $GRAND_TOTAL2=0;
 $GRAND_TOTAL3=0;
 $GRAND_TOTAL4=0;
 $GRAND_TOTAL5=0;
 $GRAND_TOTAL6=0;
 $GRAND_TOTAL7=0;
 $GRAND_TOTAL8=0;
 $GRAND_TOTAL9=0;
 $GRAND_TOTAL10=0;
 $GRAND_TOTAL11=0;
 while($row = pg_fetch_assoc($sql))
{
            
    // Schemewsie Total
    // Sanction Amount


   
   
    if(isset($varsd1)){
        if($varsd1 == $row['S_APPL']){
            $sc1[] = $row['AC_SANCTION_AMOUNT']; 
            $sumVar1 += $row['AC_SANCTION_AMOUNT'];
           // echo "if part";
        }
        else{
            $sumVar1=0;
            $sc1= array_diff($sc1, $sc1);
            $varsd1= $row['S_APPL'];
            $sc1[] = $row['AC_SANCTION_AMOUNT'];
            $sumVar += $row['AC_SANCTION_AMOUNT'];
         //   echo "else1 part";
        }
    }else{
        $sumVar1=0;
        $varsd1 = $row['S_APPL'];
        $sc1[] = $row['AC_SANCTION_AMOUNT'];
        $sumVar1 += $row['AC_SANCTION_AMOUNT'];
       // echo "2nd else part";
    }
    $result1[$varsd1] = $sc1;
    $sumArray1[$varsd1] = $sumVar1;

    // Installment Amount
    
    if(isset($varsd2)){
        if($varsd2 == $row['S_APPL']){
            $sc2[] = $row['AC_INSTALLMENT']; 
            $sumVar2 += $row['AC_INSTALLMENT'];
           // echo "if part";
        }
        else{
            $sumVar2=0;
            $sc2= array_diff($sc2, $sc2);
            $varsd2= $row['S_APPL'];
            $sc2[] = $row['AC_INSTALLMENT'];
            $sumVar2 += $row['AC_INSTALLMENT'];
         //   echo "else1 part";
        }
    }else{
        $sumVar2=0;
        $varsd2 = $row['S_APPL'];
        $sc2[] = $row['AC_INSTALLMENT'];
        $sumVar2 += $row['AC_INSTALLMENT'];
       // echo "2nd else part";
    }
    $result2[$varsd2] = $sc2;
    $sumArray2[$varsd2] = $sumVar2;

    //Receivable interest

    
    if(isset($varsd3)){
        if($varsd3 == $row['S_APPL']){
            $sc3[] = $row['RECPAY_INT_AMOUNT']; 
            $sumVar3 += $row['RECPAY_INT_AMOUNT'];
           // echo "if part";
        }
        else{
            $sumVar3=0;
            $sc3= array_diff($sc3, $sc3);
            $varsd3= $row['S_APPL'];
            $sc3[] = $row['RECPAY_INT_AMOUNT'];
            $sumVar3 += $row['RECPAY_INT_AMOUNT'];
         //   echo "else1 part";
        }
    }else{
        $sumVar3=0;
        $varsd3 = $row['S_APPL'];
        $sc3[] = $row['RECPAY_INT_AMOUNT'];
        $sumVar3 += $row['RECPAY_INT_AMOUNT'];
       // echo "2nd else part";
    }
    $result3[$varsd3] = $sc3;
    $sumArray3[$varsd3] = $sumVar3;

    //Outstanding Balance
    
    if(isset($varsd4)){
        if($varsd4 == $row['S_APPL']){
            $sc4[] = $row['CLOSING_BALANCE']; 
            $sumVar4 += $row['CLOSING_BALANCE'];
           // echo "if part";
        }
        else{
            $sumVar4=0;
            $sc4= array_diff($sc4, $sc4);
            $varsd4= $row['S_APPL'];
            $sc4[] = $row['CLOSING_BALANCE'];
            $sumVar4 += $row['CLOSING_BALANCE'];
         //   echo "else1 part";
        }
    }else{
        $sumVar4=0;
        $varsd4 = $row['S_APPL'];
        $sc4[] = $row['CLOSING_BALANCE'];
        $sumVar4 += $row['CLOSING_BALANCE'];
       // echo "2nd else part";
    }
    $result4[$varsd4] = $sc4;
    $sumArray4[$varsd4] = $sumVar4;

    // Due Balance
    
    if(isset($varsd5)){
        if($varsd5 == $row['S_APPL']){
            $sc5[] = $row['DUEBALANCE']; 
            $sumVar5 += $row['DUEBALANCE'];
           // echo "if part";
        }
        else{
            $sumVar5=0;
            $sc5= array_diff($sc5, $sc5);
            $varsd5= $row['S_APPL'];
            $sc5[] = $row['DUEBALANCE'];
            $sumVar5 += $row['DUEBALANCE'];
         //   echo "else1 part";
        }
    }else{
        $sumVar5=0;
        $varsd5 = $row['S_APPL'];
        $sc5[] = $row['DUEBALANCE'];
        $sumVar5 += $row['DUEBALANCE'];
       // echo "2nd else part";
    }
    $result5[$varsd5] = $sc5;
    $sumArray5[$varsd5] = $sumVar5;

    // Grand Total
    $GRAND_TOTAL1= $GRAND_TOTAL1 + $row["AC_SANCTION_AMOUNT"];
    $GRAND_TOTAL2= $GRAND_TOTAL2 + $row["AC_INSTALLMENT"];
    $GRAND_TOTAL3= $GRAND_TOTAL3 + $row["RECPAY_INT_AMOUNT"];
    $GRAND_TOTAL4= $GRAND_TOTAL4 + $row["LEDGER_BALANCE"];
    $GRAND_TOTAL5= $GRAND_TOTAL5 + $row["DUEBALANCE"];
   
    



   
    
    
    $DUEINSTALLMENT=$row['DUEINSTALLMENT'];
   
    if ( $DUEINSTALLMENT>=1 && $DUEINSTALLMENT<= 5) 
    {
        $a= $row['DUEBALANCE'];
        $j=' ';

        $k=' ';
        $l=' ';
        $m=' ';
        $n=' ';
       
    } 
    elseif( $DUEINSTALLMENT>=6 && $DUEINSTALLMENT<= 9) 
    {
        
        $a=' ';
        $j=$row['DUEBALANCE'];
        $k=' ';
        $l=' ';
        $m=' ';
        $n=' ';
       
    } 
    elseif ($DUEINSTALLMENT >= 10 && $DUEINSTALLMENT<= 12) 
    {
        $a=' ';
       $j=' ';
       $k = $row['DUEBALANCE'];
       $l=' ';
       $m=' ';
       $n=' ';
       
     } 
    elseif ($DUEINSTALLMENT >= 13 && $DUEINSTALLMENT<= 24) 
    {  // echo $DUEINSTALLMENT;
        $a=' ';
        $j=' ';
        $k=' ';
        $l = $row['DUEBALANCE'];
        $m=' ';
        $n=' ';
        
    } 
    elseif ($DUEINSTALLMENT >= 25 && $DUEINSTALLMENT<= 36) 
    {
       // echo $DUEINSTALLMENT;
        $a=' ';
        $j=' ';
        $k=' ';
        $l=' ';
        $m = $row['DUEBALANCE'];
        $n=' ';
     
    } 
    else 
    {
        $a=' ';
        $j=' ';
        $k=' ';
        $l=' ';
        $m=' ';
        $n = $row['DUEBALANCE'];
        
    }
    //echo "Due Installment Slab:" .$DueBalance;

     // Grand total Range wise 1 to 36

     $GRAND_TOTAL6= $GRAND_TOTAL6 + (float)$a;
     $GRAND_TOTAL7= $GRAND_TOTAL7 + (float)$j;
     $GRAND_TOTAL8= $GRAND_TOTAL8 + (float)$k;
     $GRAND_TOTAL9= $GRAND_TOTAL9 + (float)$l;
     $GRAND_TOTAL10= $GRAND_TOTAL10 + (float)$m;
     $GRAND_TOTAL11= $GRAND_TOTAL11 + (float)$n;
    $temp =
    [
        // 'Scheme' =>$row['SCHEME'],
        'schemeNo' => $row['S_APPL'],
        'schemeName' => $row['S_NAME'],       
        'ACNO' =>$row['AC_NO'],
        'bankac' =>$row['BANKACNO'],
        'AccountName'=>$row['AC_NAME'],
        'OpeningDate'=>$row['AC_OPDATE'],
        'ExpiryDate'=>$row['AC_EXPIRE_DATE'],
        'Periods_Month'=>$row['AC_MONTHS'],
        'SanctionAmount'=>$row['AC_SANCTION_AMOUNT'],
        'InstallmentAmount'=>$row['AC_INSTALLMENT'],
        'ReceivableInterest' =>$row['RECPAY_INT_AMOUNT'],
        'OutstandingBalance' => $row['LEDGER_BALANCE'],
        'DueBalance' => $row['DUEBALANCE'],
        'DueInstallment'=>$row['DUEINSTALLMENT'],
        'OverDueOn'=>$row['OVERDUEDATE'],
        "bankName"  => $bankName,
        "BranchName1"=>$BranchName,
        "BRANCHCODE"=>$BRANCH_CODE,
        "date"=>$Date1,

        'a'=>$a,
        'j'=>$j,
        'k'=>$k,
        'l'=>$l,
        'm'=>$m,
        'n'=>$n,   
       
        // Scehmewise Total

        'SAmount' =>sprintf("%.2f", ($sumArray1[$varsd1])).' '.$netType,
        'InstAmt' =>sprintf("%.2f", ($sumArray2[$varsd2])).' '.$netType,
        'ReceivableIntr'=>sprintf("%.2f", ($sumArray3[$varsd3])).' '.$netType,
        'OutstandingBal'=>sprintf("%.2f", ($sumArray4[$varsd4])).' '.$netType,
        'DueBal'=>sprintf("%.2f", ($sumArray5[$varsd5])).' '.$netType,

        // Grand Total
        "SAmt" => sprintf("%.2f",($GRAND_TOTAL1) + 0.0 ) ,
        "InstAmount" => sprintf("%.2f",($GRAND_TOTAL2) + 0.0 ) ,
        "RecevIntr" => sprintf("%.2f",($GRAND_TOTAL3) + 0.0 ) ,
        "OutBal" => sprintf("%.2f",($GRAND_TOTAL4) + 0.0 ) ,
        "DuBal" => sprintf("%.2f",($GRAND_TOTAL5) + 0.0 ) ,

        "a1" => sprintf("%.2f",($GRAND_TOTAL6) + 0.0 ) ,
        "j1" => sprintf("%.2f",($GRAND_TOTAL7) + 0.0 ) ,
        "k1" => sprintf("%.2f",($GRAND_TOTAL8) + 0.0 ) ,
        "l1" => sprintf("%.2f",($GRAND_TOTAL9) + 0.0 ) ,
        "m1" => sprintf("%.2f",($GRAND_TOTAL10) + 0.0 ) ,
        "n1" => sprintf("%.2f",($GRAND_TOTAL11) + 0.0 ) ,

                    
        ];
         $data[$i]=$temp;
        $i++;
}
  ob_end_clean();
$config = ['driver'=>'array','data'=>$data];
//  print_r($data);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
->setDataSource($config)
->export('Pdf');

?>
		