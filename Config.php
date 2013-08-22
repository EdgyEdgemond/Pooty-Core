<?php
	switch(ENVIRONMENT) {
	  case 'DEV':
	  case 'TST':
  		$vServer = 'SQL2005'.ENVIRONMENT;
  		break;
  	case 'STG':
  		$vServer = 'SQL2005UAT';
  		break;  	
    case 'PRD':     
  		$vServer = 'carlsqldb05';
  		break;
  	default:
  		$vServer = 'SQL2005DEV';   	
  }
  $vDatabase = 'VorlargenThree';        
	$vUsername = 'Pooty_User';
	$vPassword = 'ww3w2ejfsss4Urs';
	$_SESSION['db_dets'.APPLICATION_NAME]['alias'] = "VorlargenThree Connection";
	
	if(!($vConn = mssql_connect($vServer,$vUsername,$vPassword))){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database server: $vServer"));				
		die;
	}
	if(!mssql_select_db($vDatabase,$vConn)){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database: $vDatabase"));			
		die;
	} 
?>
