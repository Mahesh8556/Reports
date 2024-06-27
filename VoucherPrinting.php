<?php

ob_start(); 
include "main.php";
require_once('dbconnect.php'); 

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/voucherDefault.jrxml';
// $filename = __DIR__.'/voucherprintingVadgaon.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');


$sdate = $_GET['date'];
$voucher_type = $_GET['voucher_type'];
$tran_type = $_GET['tran_type'];
$tranno = $_GET['VoucharNo'];
// $branch = $_GET['branch'];
$branchcode = $_GET['branchcode'];

$trantype = str_replace("'", "", $tran_type);


// $date = "'12/08/2022'";
// $sdate = "'11/08/2022'";
$c = "'C'";
$d = "'D'";
$CS="'CS'";
$Cash="'CS'";
$transfer="'TR'";
$debit="'D'";
$credit="'C'";
$CashVALUE="'CASH'";
$transferVALUE="'TRANSFER'";
$Amount="'Amount'";
$JV="'JV'";
$GL="'GL'";
$Balance="'Balance'";
$ACNO="'ACNO'";
$Gross="'Gross'";
$TRAN_AMOUNT="'TRAN_AMOUNT'";
$four="'4'";
$INTEREST_AMOUNT="'INTEREST_AMOUNT'";
$RECEIVEDINT = "'RECEIVED INTEREST'";
$RECPAY_INT_AMOUNT="'RECPAY_INT_AMOUNT'";
$PENAL_INT_AMOUNT="'PENAL_INT_AMOUNT'";
$REC_PENAL_INT_AMOUNT="'REC_PENAL_INT_AMOUNT'";
$OTHER1_AMOUNT="'OTHER1_AMOUNT'";
$OTHER2_AMOUNT="'OTHER2_AMOUNT'";
$OTHER3_AMOUNT="'OTHER3_AMOUNT'";
$OTHER4_AMOUNT="'OTHER4_AMOUNT'";
$OTHER5_AMOUNT="'OTHER5_AMOUNT'";
$OTHER6_AMOUNT="'OTHER6_AMOUNT'";
$OTHER7_AMOUNT="'OTHER7_AMOUNT'";
$OTHER8_AMOUNT="'OTHER8_AMOUNT'";
$OTHER9_AMOUNT="'OTHER9_AMOUNT'";
$OTHER10_AMOUNT="'OTHER10_AMOUNT'";
$OTHER11_AMOUNT="'OTHER11_AMOUNT'";

// $tranno = "'1'";
// $branchcode = "'101'";
$TRAN_STATUS="'2'";
$dateformate = "'dd/mm/yyyy'";
$singlequote = " '' ";

$query='SELECT CASE "TRAN_TYPE" WHEN '.$CS.' THEN '.$CashVALUE.' ELSE '.$transferVALUE.' END "MAR_TRANTYPE",
SCHEMAST."S_NAME",
VWTMPTRANTABLE."TRAN_NO" AS "TRAN_NO",
VWTMPTRANTABLE."TRAN_DRCR",
VWTMPTRANTABLE."TRAN_TYPE",
VWTMPTRANTABLE."TRAN_DATE",
VWTMPTRANTABLE."TRAN_GLACTYPE",
VWTMPTRANTABLE."TRAN_AMOUNT",
VWTMPTRANTABLE."TRAN_GLACNO",
REPLACE(REPLACE(VWTMPTRANTABLE."NARRATION", CHR(10), '.$singlequote.'),CHR(13), '.$singlequote.') NARRATION,
vwallmaster."ac_name" AS "AC_NAME",
CAST(LEDGERBALANCE(CAST("TRAN_ACTYPE" AS CHARACTER VARYING), "TRAN_ACNO", CAST('.$sdate .' AS CHARACTER VARYING),0,
                        1) AS FLOAT) + CASE "TRAN_DRCR" WHEN '.$debit.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT) END "BALANCE",
fn_amttowordenglish(CAST(LEDGERBALANCE(CAST("TRAN_ACTYPE" AS CHARACTER VARYING),

							"TRAN_ACNO",
							CAST('.$sdate .' AS CHARACTER VARYING),
							0,1) AS FLOAT) + CASE "TRAN_DRCR"
				WHEN '.$debit.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
				ELSE (-1) * CAST("TRAN_AMOUNT" AS FLOAT)
				END)	"INWORDS",	

cast(trim(CASE "TRAN_SOURCE_TYPE" WHEN '.$JV.' THEN 	
			  	CASE "TRAN_TYPE" 	WHEN '.$Amount.'  THEN 
			  				CASE "TRAN_ACNOTYPE" WHEN '.$GL.' THEN '.$Gross.' ELSE GETACNAME(CAST("TRAN_ACTYPE" AS CHARACTER VARYING),"TRAN_ACNO") || CHR(13) END	
			    ELSE '.$Balance.' END	
	ELSE 
	  			CASE "TRAN_SOURCE_TYPE" WHEN '.$Balance.'   THEN GETACNAME(CAST("TRAN_ACTYPE" AS CHARACTER VARYING),"TRAN_ACNO")	
		  						ELSE '.$singlequote.' END || CHR(13)
	END   || ACMASTER."AC_NAME" || '.$singlequote.' || '.$ACNO.' || "TRAN_ACNO") as CHARACTER VARYING) "GLAC_NAME",

ACMASTER."AC_NAME" AS GL_NAME,"TRAN_ACNOTYPE","TRAN_ACTYPE",
"TRAN_ACNO",VWTMPTRANTABLE."TRAN_SOURCE_TYPE",VWTMPTRANTABLE."TRAN_SOURCE_NO",VWTMPTRANTABLE."TRAN_BRANCH_CODE",
GL_STATEMENT_CODE.ID AS "STATEMENT_CODE"FROM ACMASTER,
(SELECT "TRAN_NO","TRAN_DATE","TRAN_TIME","TRAN_TYPE","TRAN_MODE","TRAN_DRCR","TRAN_ACNOTYPE","IS_INTEREST_ENTRY","TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH","TRAN_ACTYPE" AS "TRAN_GLACTYPE","TRAN_GLACNO",ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE","TRAN_ACNO","CHEQUE_NO","TRAN_AMOUNT",
        "NARRATION","TRAN_STATUS",1 RECCOUNTER,DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",DAILYTRAN."DIVIDEND_ENTRY", DAILYTRAN."TRAN_SOURCE_TYPE", DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE", DAILYTRAN."AC_CLOSED",CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(CASE WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("TRAN_AMOUNT" AS float)
         ELSE 0 END,0) + COALESCE(CASE WHEN "TRAN_TYPE" = '.$JV.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
         ELSE 0 END, 0) AS TRANSFERAMT,COALESCE(CASE WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("TRAN_AMOUNT" AS FLOAT) ELSE 0
         END, 0) AS CASHAMT, COALESCE(CASE WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("TRAN_AMOUNT" AS FLOAT)
         ELSE 0 END, 0) AS CLEARINGAMT,'.$TRAN_AMOUNT.' REF_FIELD FROM DAILYTRAN,
        ACMASTER WHERE DAILYTRAN."TRAN_GLACNO" =ACMASTER."AC_NO"
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate .', '.$dateformate.')
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .' UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE","TRAN_TIME",
        "TRAN_TYPE","TRAN_MODE",
        "TRAN_DRCR","TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY","TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",CAST("TRAN_ACTYPE" AS INTEGER) AS "TRAN_GLACTYPE",
        "TRAN_GLACNO",ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),"TRAN_ACNO","CHEQUE_NO",
        "TRAN_AMOUNT","NARRATION",
        "TRAN_STATUS",1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(CASE WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("TRAN_AMOUNT" AS float) ELSE 0 END,0) + COALESCE(CASE
        WHEN "TRAN_TYPE" = '.$JV.' THEN CAST("TRAN_AMOUNT" AS float) ELSE 0 END, 0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("TRAN_AMOUNT" AS float)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("TRAN_AMOUNT" AS float)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$TRAN_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE HISTORYTRAN."TRAN_GLACNO" = ACMASTER."AC_NO"
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' AS "TRAN_GLACTYPE",
        "INTEREST_GLACNO" AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "INTEREST_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$INTEREST_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."INTEREST_GLACNO" AS CHARACTER VARYING) = CAST(ACMASTER."AC_NO" AS CHARACTER VARYING)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."INTEREST_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' "TRAN_GLACTYPE",
        "INTEREST_GLACNO" AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "INTEREST_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("INTEREST_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$INTEREST_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE HISTORYTRAN."INTEREST_GLACNO" = ACMASTER."AC_NO" 
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."INTEREST_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' "TRAN_GLACTYPE",
        CAST("RECPAY_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "RECPAY_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$RECPAY_INT_AMOUNT.' REF_FILED
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."RECPAY_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("RECPAY_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "RECPAY_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("RECPAY_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$RECPAY_INT_AMOUNT.' REF_FILED
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."RECPAY_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."RECPAY_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' "TRAN_GLACTYPE",
        CAST("PENAL_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "PENAL_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$PENAL_INT_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."PENAL_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."PENAL_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING)<> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("PENAL_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "PENAL_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$PENAL_INT_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."PENAL_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."PENAL_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("REC_PENAL_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "REC_PENAL_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$REC_PENAL_INT_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."REC_PENAL_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."REC_PENAL_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("REC_PENAL_INT_GLACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "REC_PENAL_INT_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("REC_PENAL_INT_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$REC_PENAL_INT_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."REC_PENAL_INT_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."REC_PENAL_INT_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER1_ACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER1_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER1_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER1_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER1_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER1_ACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER1_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER1_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER1_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER1_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER1_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER2_ACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER2_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER2_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER2_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER2_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER2_ACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER2_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER2_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER2_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER2_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER2_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER3_ACNO" AS INTEGER) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER3_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER3_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER3_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER3_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' "TRAN_GLACTYPE",
        CAST("OTHER3_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER3_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER3_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER3_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER3_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER3_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING)<> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER4_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER4_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER4_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER4_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER4_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER4_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER4_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER4_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER4_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER4_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER4_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER5_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER5_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER5_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER5_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER5_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER5_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER5_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER5_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER5_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER5_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER5_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER6_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER6_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER6_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER6_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER6_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER6_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER6_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER6_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER6_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER6_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER6_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER7_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER7_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER7_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER7_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER7_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER7_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER7_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER7_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER7_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER7_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER7_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER8_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER8_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER8_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER8_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER8_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER8_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER8_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER8_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER8_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER8_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER8_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING)<> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.' "TRAN_GLACTYPE",
        CAST("OTHER9_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER9_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER9_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER9_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER9_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER9_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER9_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER9_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER9_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER9_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER9_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER10_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        CAST("OTHER10_AMOUNT" AS FLOAT) AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER10_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER10_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER10_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING)<> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER10_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER10_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER10_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER10_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER10_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER10_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER11_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        "TRAN_ACTYPE",
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER11_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",
        1 RECCOUNTER,
        DAILYTRAN."USER_CODE",
        DAILYTRAN."OFFICER_CODE",
        DAILYTRAN."DIVIDEND_ENTRY",
        DAILYTRAN."TRAN_SOURCE_TYPE",
        DAILYTRAN."TRAN_SOURCE_NO",
        DAILYTRAN."TRAN_ENTRY_TYPE",
        DAILYTRAN."AC_CLOSED",
        CAST(DAILYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER11_AMOUNT.' REF_FIELD
    FROM DAILYTRAN,
        ACMASTER
    WHERE CAST(DAILYTRAN."OTHER11_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(DAILYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                    '.$dateformate.')
        AND CAST(DAILYTRAN."OTHER11_AMOUNT" AS FLOAT) > 0
        AND CAST(DAILYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND DAILYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .'
    UNION ALL SELECT "TRAN_NO",
        "TRAN_DATE",
        "TRAN_TIME",
        "TRAN_TYPE",
        "TRAN_MODE",
        "TRAN_DRCR",
        "TRAN_ACNOTYPE",
        "IS_INTEREST_ENTRY",
        "TRAN_PROCESS_YEAR",
        "TRAN_PROCESS_MONTH",
        '.$four.'"TRAN_GLACTYPE",
        CAST("OTHER11_ACNO" AS BIGINT) AS "TRAN_GLACNO",
        ACMASTER."AC_SUBSCODE",
        CAST("TRAN_ACTYPE" AS INTEGER),
        "TRAN_ACNO",
        "CHEQUE_NO",
        "OTHER11_AMOUNT" AS "TRAN_AMOUNT",
        "NARRATION",
        "TRAN_STATUS",1 RECCOUNTER,
        HISTORYTRAN."USER_CODE",
        HISTORYTRAN."OFFICER_CODE",
        HISTORYTRAN."DIVIDEND_ENTRY",
        HISTORYTRAN."TRAN_SOURCE_TYPE",
        HISTORYTRAN."TRAN_SOURCE_NO",
        HISTORYTRAN."TRAN_ENTRY_TYPE",
        HISTORYTRAN."AC_CLOSED",
        CAST(HISTORYTRAN."TRAN_BRANCH_CODE" AS INTEGER) AS "TRAN_BRANCH_CODE",
        COALESCE(COALESCE(CASE
                                                                                                WHEN "TRAN_TYPE" = '.$transfer.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                                                                ELSE 0
                                                                                END,

                                                0) + COALESCE(CASE
                                                                                                                        WHEN "TRAN_TYPE" = '.$Amount.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                                                                                        ELSE 0
                                                                                                        END,

                                                                        0),
            0)TRANSFERAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$Cash.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CASHAMT,
        COALESCE(CASE
                                                            WHEN "TRAN_TYPE" = '.$credit.' THEN CAST("OTHER11_AMOUNT" AS FLOAT)
                                                            ELSE 0
                                            END,
            0) CLEARINGAMT,
        '.$OTHER11_AMOUNT.' REF_FIELD
    FROM HISTORYTRAN,
        ACMASTER
    WHERE CAST(HISTORYTRAN."OTHER11_ACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
        AND CAST(HISTORYTRAN."TRAN_DATE" AS DATE) = TO_DATE('.$sdate.',

                                                                                                                                                                                            '.$dateformate.')
        AND CAST(HISTORYTRAN."OTHER11_AMOUNT" AS FLOAT) > 0
        AND CAST(HISTORYTRAN."TRAN_STATUS" AS CHARACTER VARYING) <> '.$TRAN_STATUS.'
        AND HISTORYTRAN."TRAN_BRANCH_CODE" = '.$branchcode .' 
) VWTMPTRANTABLE left join schemast on schemast.id=VWTMPTRANTABLE."TRAN_ACTYPE",
GL_STATEMENT_CODE, VWALLMASTER
WHERE CAST(VWTMPTRANTABLE."TRAN_GLACNO" AS BIGINT) = CAST(ACMASTER."AC_NO" AS BIGINT)
AND GL_STATEMENT_CODE.ID = CAST(ACMASTER."AC_BCD" AS INTEGER)
AND VWTMPTRANTABLE."TRAN_ACNO"=vwallmaster."ac_no"
AND CAST(VWTMPTRANTABLE."TRAN_AMOUNT" AS FLOAT) <> 0
AND ("TRAN_SOURCE_TYPE" <> '.$d .'	OR "TRAN_SOURCE_TYPE" IS NULL)
AND "TRAN_NO"='.$tranno.'';

    // echo $query;

$sql =  pg_query($conn,$query);


$i = 0;
$totalvalue = 0;
if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){
   
    $creditbal = '';
    $debitbal = '';

    // if ($row['TRAN_DRCR'] > 0){
    //     $debitbal = $row['ledgerbalance'] + $row['dramt'] - $row['cramt']; 
    // }
    if($row['TRAN_DRCR'] =='C')
    {
    $crdr="CR";
    }
    else{
    $crdr="DR";
    }

    if($trantype =='CS')
    {
    $trtype="CASH";
    }
    else{
    $trtype="TRANSFER";
    }


    $totalvalue=$totalvalue+ $row['TRAN_AMOUNT'];

    
$query1= 'SELECT
fn_amttowordenglish(
    '.$totalvalue.'
)';
$sql1 =  pg_query($conn,$query1);
$balshow='';
    while($bal = pg_fetch_assoc($sql1))  
    {
  $balshow=  $bal['fn_amttowordenglish'];
    }

    $tmp=[
        'ac_name'=> $row['AC_NAME'],
        'tranamount'=> sprintf("%.2f", ($row['TRAN_AMOUNT'] + 0.0)),
        'voucher_no' => $row['TRAN_NO'],  
        'date' => $row['TRAN_DATE'],  
        'ac_no'=> $row['TRAN_ACNO'],
        'TRAN_ACNOTYPE' => $row['S_NAME'],
        // 'AMTINWORDS' => $row['INWORDS'],
        'Narration1' => $row['gl_name'],
        'AMTINWORDS' => $balshow,

        'NARRATION' => $row['narration'],
        'crdr' => $crdr,
       'trantype' => $trtype,
       'totalvalue' => sprintf("%.2f", ($totalvalue + 0.0)),
    ];  
    
    $data[$i]=$tmp;
    $i++;   
}

// print_r($data);

// for clean previous execution
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
}
?>













