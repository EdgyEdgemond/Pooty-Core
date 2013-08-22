<?php
	switch(ENVIRONMENT) {
	  case 'DEV':
	  case 'TST':
  		$dsServer = 'SQL2008'.ENVIRONMENT;
  		break;
  	case 'STG':
  		$dsServer = 'SQL2008UAT';
  		break;  	
    case 'PRD':     
  		$dsServer = 'MEL0DBS08LP';
  		break;
  	default:
  		$dsServer = 'SQL2008DEV';   	
  }
  $dsDatabase = 'Direct_Sales_HQ';        
	$dsUsername = 'Direct_Sales_HQ_Web_User';
	$dsPassword = '3Ct8uQCislPc';
	$_SESSION['db_dets'.APPLICATION_NAME]['alias'] = "Direct Sales HQ Connection";
  
	if(!($dsConn = mssql_connect($dsServer,$dsUsername,$dsPassword))){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database server: $dsServer"));				
		die;
	}
	if(!mssql_select_db($dsDatabase,$dsConn)){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database: $dsDatabase"));			
		die;
	} 
?>