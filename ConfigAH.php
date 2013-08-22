<?php
	switch(ENVIRONMENT) {
	  case 'DEV':
	  case 'TST':
  		$ahServer = 'SQL2008'.ENVIRONMENT;
  		break;
  	case 'STG':
  		$ahServer = 'SQL2008UAT';
  		break;  	
    case 'PRD':     
  		$ahServer = 'MEL0DBS08LP';
  		break;
  	default:
  		$ahServer = 'SQL2008DEV';
  }
  $ahDatabase = 'AtHome_HQ';
	$ahUsername = 'AtHome_HQ_Web_User';
	$ahPassword = 'HxvssaLNpIti';
	$_SESSION['db_dets'.APPLICATION_NAME]['alias'] = "AtHome HQ Connection";
  
	if(!($ahConn = mssql_connect($ahServer,$ahUsername,$ahPassword))){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database server: $ahServer"));
		die;
	}
	if(!mssql_select_db($ahDatabase,$ahConn)){
    print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the master database: $ahDatabase"));
		die;
	} 
?>