<?php
Class Security {

	public function getSecurity(){
    $atHomeServers = array('lenore');

    include("Config.php");

    list($domain, $login) = explode("\\", $_SERVER['AUTH_USER']);

    $sql = "EXEC XSP_Get_Security_User @ntlogin= '$login', @domain = '$domain', @userId = 0";

    $result = mssql_query($sql);
    $_SESSION['securityUser'] = array();
    $_SESSION['securityUserId'] = 0;
    while($row = mssql_fetch_assoc($result)) {
      $_SESSION['securityUserId'] = $row['UserId'];
      $_SESSION['securityUser']['Projects'][$row['ProjName']]['Permissions'][] = $row['PermName'];
    }
    
    //$login = $login=='edgecomd'?'josej':$login;
    if(in_array(strtolower($_SERVER['SERVER_NAME']),$atHomeServers)){
      try {
          $ldap = new adLDAP(array("domain_controllers"=>array("syd3ads01lv.athome.com.au"), "ad_username"=>"ah_ldap_read", "ad_password"=>"R34dMyLd4p!", "base_dn"=>"dc=athome,dc=com,dc=au", "account_suffix"=>"@athome.com.au"));
      }
      catch (adLDAPException $e) {
          echo $e; exit();
      }
      $guid = $ldap->username2guid($login);
      $params = "@guid = '$guid', @domain = 'ATHOME'";
    } else {
      try {
          $ldap = new adLDAP();
      }
      catch (adLDAPException $e) {
          echo $e; exit();
      }
      $guid = $ldap->username2guid($login);
      $params = "@guid = '$guid'";
    }

    $sql = "EXECUTE zim.XSP_Get_Profiles $params";

    $result = mssql_query($sql);
    $_SESSION['adSecurityUser'] = array();
    $_SESSION['adSecurityUser']['Login'] = $login;
    $_SESSION['adSecurityUser']['Guid'] = $guid;
    while($row = mssql_fetch_assoc($result)) {
      $_SESSION['adSecurityUser']['Projects'][$row['vcPROJECT_NAME']]['Permissions'][] = $row['vcPERMISSION'];
    }

    $sql = "EXECUTE zim.XSP_Get_Locations $params";

    $result = mssql_query($sql);
    while($row = mssql_fetch_assoc($result)) {
      $_SESSION['adSecurityUser']['Projects'][$row['vcPROJECT_NAME']]['Locations'][] = $row['vcLOCATION'];
    }

    if(!array_key_exists('Email', $_SESSION['adSecurityUser'])) {
     $user = $ldap->user_info($login);

     $_SESSION['adSecurityUser']['Email'] = $user[0]['mail'][0];
     $_SESSION['adSecurityUser']['Name'] = $user[0]['displayname'][0];
    }
	}
	
  function has_permission($permissionName, $projectName) {
  	$adSecurityUser = @$_SESSION['adSecurityUser'];
    if ($adSecurityUser == null) {
  		return false;
  	}
  	// Regular user.
  	else {
  	//as the session variable could be set in a 2.1 app it could also still be an object...
      if($adSecurityUser && @$adSecurityUser['Projects']) {
    		foreach($adSecurityUser['Projects'] as $project=>$permissions) {
    			// Look for the project first.
    		  if (strtolower($project) == strtolower($projectName)) {
             // Loop through all the permissions for a matching one, and return true if found.
            foreach($permissions['Permissions'] as $permission) {
    					if (strtolower($permission) == strtolower($permissionName)) {
    						return true;
    					}
            }
    		  }
    		}
      }
  	}
  	// Default to false, so if none of the above returned true, this user doesn't have permission.
  	return false;
  }
}
