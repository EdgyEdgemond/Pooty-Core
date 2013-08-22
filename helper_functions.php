<?php

/**************************************************************
 *
 *      This should also be a stable library. Contact Edgy
 *      if you have any suggestions/tweaks to this file.
 *
 *************************************************************/

function detect_ie()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) &&
    (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
        return true;
    else
        return false;
}

//functions defined here are available in the rest of the framework
  function render($pview,$controller,$vars=array(),$options=array()){
    $view = "views\\$controller\\$pview.php";
    /* Grab the Project name if set otherwise default to app config */
    $pootyProjectName = isset($_SESSION['PROJECT_NAME'.APPLICATION_NAME])?$_SESSION['PROJECT_NAME'.APPLICATION_NAME]:PROJECT_NAME;

    /*
    * Special case security level "None" allow access to specific page regardless of all security measures
    * This allows for multi project apps to set PROJECT_NAME to check security against (Joker etc)
    */
    if(@$options['securityLevel'] != 'None') {
      /*
      * Application level security
      */
      /* If Security "Access" level perm has been set then check for access and deny entry if not */
      if(@$_SESSION['securityAccess'.APPLICATION_NAME]) {
       if(!Security::has_permission($_SESSION['securityAccess'.APPLICATION_NAME], $pootyProjectName)) {
         print(PootyError::GetBasicErrorString("Access Denied: ".$_SESSION['securityAccess'.APPLICATION_NAME], "You do not have sufficient permissions to view this application."));
         die();
       }
      }

      /*
      * Page level security
      */
      /* If the security heirachy has been set and the page has a security level check for access and deny entry if not */
      if(isset($_SESSION['securityHeirarchy'.APPLICATION_NAME]) && isset($options['securityLevel'])) {
        $pootyHeirarchy = $_SESSION['securityHeirarchy'.APPLICATION_NAME];
        $pootySecurityLevel = $options['securityLevel'];
        foreach($pootyHeirarchy as $k=>$h) {
          if($h == $pootySecurityLevel) {
            break;
          } else {
            unset($pootyHeirarchy[$k]);
          }
        }
        $pootyAccess = false;
        foreach($pootyHeirarchy as $h) {
          $pootyAccess = Security::has_permission($h, $pootyProjectName)?true:$pootyAccess;
        }
        if(!$pootyAccess) {
          print(PootyError::GetBasicErrorString("Access Denied: $pootySecurityLevel", "You do not have sufficient permissions to view this page."));
          die();
        }
      }
    }
    foreach($vars as $key=>$value) {
      $$key = $value;
    }
    $errors = get_errors();
    if(isset($options['partial']) && $options['partial'] === true){
      include($view);
    }else if(isset($options['layout']) && file_exists("views\\layouts\\{$options['layout']}.php")){
      include("views\\layouts\\{$options['layout']}.php");
    }else if(file_exists("views\\layouts\\$controller.php")){
      include("views\\layouts\\{$controller}.php");
  	} else if(file_exists("views\\layouts\\application.php")){
      include('views\\layouts\\application.php');
  	} else{
  	  include($view);
  	}
  }

  function link_to($method,$controller=false,$get_data=array()){
    $controller = ($controller)?$controller:$_GET['c'];
    //$controller = (isset($_GET['c']))?$_GET['c']:$controller;
  	$link = $_SERVER['PHP_SELF'].'?';
  	$link .= ($controller)?'c='.$controller.'&p='.$method:'p='.$method;
  	foreach($get_data as $key => $value){
  		$link .= "&$key=".htmlentities($value);
  	}
  	return $link;
  }

  function paginate_links($current, $total, $method, $controller=false,$get_data=array()){
    $str = '';
    $mid = $current;
    if($current < 6) {
      $mid = 6;
    }
    if($current > $total - 5) {
      $mid = $total - 5;
    }
    if ($current > 1) {
      $get_data['page'] = 1;
      $str .= '<a href="'.link_to($method, $controller, $get_data).'"><<</a>  ';
      $get_data['page'] = $current - 1;
      $str .= '<a href="'.link_to($method, $controller, $get_data).'"><</a>  ';
      $get_data['page'] = 1;
      if($current > 7) {
        $str .= '<a href="'.link_to($method, $controller, $get_data).'">1</a> ...  ';
      }
    }
    $lower = $mid-5<1?1:$mid-5;
    $upper = $mid+5>$total?$total:$mid+5;
    for($i=$lower;$i<=$upper;$i++){
      $get_data['page'] = $i;
      $class=$i==$current?"style=\"text-decoration:none\"":"";
      $str .= '<a '.$class.' href="'.link_to($method, $controller, $get_data).'">'.$i.'</a>  ';
    }
    if ($current < $total) {
      $get_data['page'] = $total;
      if($current < $total - 6) {
        $str .= ' ... <a href="'.link_to($method, $controller, $get_data).'">'.$total.'</a>  ';
      }
      $get_data['page'] = $current + 1;
      $str .= '<a href="'.link_to($method, $controller, $get_data).'">></a>  ';
      $get_data['page'] = $total;
      $str .= '<a href="'.link_to($method, $controller, $get_data).'">>></a>  ';
    }
    return $str;
  }

  function paginate_alpha_links($current, $method, $controller=false){
    $str = '';
    if ($current != 'A') {
      $str .= '<a href="'.link_to($method, $controller, array("page" => 'A')).'"><<</a> ';
      $str .= '<a href="'.link_to($method, $controller, array("page" => chr(ord($current)-1))).'"><</a> ';
    }
    for($i=65;$i<=90;$i++){
      $str .= '<a href="'.link_to($method, $controller, array("page"=>chr($i))).'">'.chr($i).'</a> ';
    }
    if (ord($current) < ord('Z')) {
      $str .= '<a href="'.link_to($method, $controller, array("page" => chr(ord($current)+1))).'">></a> ';
      $str .= '<a href="'.link_to($method, $controller, array("page" => 'Z')).'">>></a> ';
    }
    return $str;
  }

  function redirect_to($method,$controller=false,$get_data=array()){
    $controller = ($controller)?$controller:$_GET['c'];
  	//$controller = (isset($_GET['c']))?$_GET['c']:$controller;
  	$link = $_SERVER['PHP_SELF'].'?';
  	$link .= ($controller)?'c='.$controller.'&p='.$method:'p='.$method;
  	foreach($get_data as $key => $value){
  		$link .= "&$key=$value";
  	}
  	header('location:'.$link);
  	die;
  }

  function add_message($id,$message, $type="message"){
  	if(!isset($_SESSION['message'.APPLICATION_NAME])) $_SESSION['message'.APPLICATION_NAME] = array();
    if(!isset($_SESSION['messages'.APPLICATION_NAME])) $_SESSION['messages'.APPLICATION_NAME] = array();
  	$_SESSION['message'.APPLICATION_NAME][$id]["message"] = $message;
  	$_SESSION['message'.APPLICATION_NAME][$id]["type"] = $type;
    $_SESSION['messages'.APPLICATION_NAME][$type][$id] = $message;
  }

  function count_messages(){
  	if(!isset($_SESSION['message'.APPLICATION_NAME])) return 0;
  	return count($_SESSION['message'.APPLICATION_NAME]);
  }

  function get_messages(){
  	if(!isset($_SESSION['message'.APPLICATION_NAME])) $_SESSION['message'.APPLICATION_NAME] = array();
  	$ret =  $_SESSION['message'.APPLICATION_NAME];
  	$_SESSION['message'.APPLICATION_NAME] = array();
  	return $ret;
  }

  function get_messages_new(){
  	if(!isset($_SESSION['messages'.APPLICATION_NAME])) $_SESSION['messages'.APPLICATION_NAME] = array();
  	$ret =  $_SESSION['messages'.APPLICATION_NAME];
  	$_SESSION['messages'.APPLICATION_NAME] = array();
  	return $ret;
  }

  function add_error($id,$error){
  	if(!isset($_SESSION['error'.APPLICATION_NAME])) $_SESSION['error'.APPLICATION_NAME] = array();
  	$_SESSION['error'.APPLICATION_NAME][$id] = $error;
  }

  function count_errors(){
  	if(!isset($_SESSION['error'.APPLICATION_NAME])) return 0;
  	return count($_SESSION['error'.APPLICATION_NAME]);
  }

  function get_errors(){
  	if(!isset($_SESSION['error'.APPLICATION_NAME])) $_SESSION['error'.APPLICATION_NAME] = array();
    $ret = $_SESSION['error'.APPLICATION_NAME];
  	$_SESSION['error'.APPLICATION_NAME] = array();
  	return $ret;
  }

  function has_error($key, $errors) {
    $ret = "";
    if(@$errors[$key]) {
      $ret = "class=\"error\"";
    }
    return $ret;
  }

  function td_has_error($key, $errors) {
    $ret = "";
    if(@$errors[$key]) {
      $ret = " error";
    }
    return $ret;
  }

  function show_error($key, $errors) {
    $ret = @$errors[$key];
    return $ret;
  }

  function add_debug($id,$debug){
  	if(!isset($_SESSION['debug'.APPLICATION_NAME])) $_SESSION['debug'.APPLICATION_NAME] = array();
  	$_SESSION['debug'.APPLICATION_NAME][] = array($id,$debug);
  }

  function display_debugs(){
  	if(!isset($_SESSION['debug'.APPLICATION_NAME])) $_SESSION['debug'.APPLICATION_NAME] = array();
  	$retval = "";
    foreach($_SESSION['debug'.APPLICATION_NAME] as $debug) {
  		$retval .= "<div style=\"background:#fff;color:#333;width:50%;margin:10px auto;font-family:Arial,sans-serif;border:5px solid #a13;\">"
  		. "<h1 style=\"background:#a13;color:#fff;padding:5px;margin:0px;\">{$debug[0]}</h1>"
  		. "<span style=\"display:block;padding:10px;\">{$debug[1]}</span>"
  		. "</div><br>";
  	}
  	$_SESSION['debug'.APPLICATION_NAME] = array();
  	return $retval;
  }

  function p($string) {
    $string = htmlentities($string);
    print $string;
  }

  function extract_post_data() {
    foreach($_POST as $key=>$value) {
      switch (gettype($value)) {
        case "array":
          $data[$key] = $value;
          break;
        default:
          $data[$key] = trim($value);
      }
      if (stristr($key,"_")) {
        list($model, $column) = explode("_", $key, 2);
        $data[$model][$column] = trim($value);
      }
    }
    return $data;
  }

  function update_from_post($original, $new) {
    foreach($new as $key=>$value) {
      $original[$key] = $value;
    }
    return $original;
  }

  function display_date($date, $default = "", $format="%d-%b-%Y") {
    $retval = strtotime($date)?strftime($format, strtotime($date)):$default;
    return $retval;
  }

  function data_changed($ary, $aryOrig) {
    foreach($ary as $key=>$value) {
      if(trim(@$aryOrig[$key]) != trim($value)) {
        return true;
      }
    }
    return false;
  }

  function GetDSAgentsByPermission($permission, $projectName, $sort="name") {
     include("ConfigDS.php");

        $sql = "EXEC ASP_Get_Agents_By_Permission @permission = '$permission', @project = '$projectName'";

        try {
          if (!$result = mssql_query($sql)) {
            throw new Exception(mssql_get_last_message()."\n".$sql);
          }
        } catch (Exception $e) {
          self::fatal_error($e->getMessage());
        }
        $agentList = array();
        while($row = mssql_fetch_assoc($result)) {
          array_push($agentList, $row);
        }

//     $permission = urlencode($permission);
//     $projectName = urlencode($projectName);
//
//     $test = new RESTclient("http://bottleloader/grr/agents?permission=$permission&project=$projectName");
//     $test->createRequest("GET");
//     $test->sendRequest();
//     $agentList = $test->getResponse();
//     $agentList = convertResult($agentList);

    $tempagentList = array();

    foreach($agentList as $agent) {
     switch($sort) {
       case "name":
         $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
         $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
         $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
         break;
      case "salescode":
        $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
        $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
        $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
        break;
      case "payroll":
        $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
        $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
        $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
        break;
     }

      $tempagentList[$agent['iPROFILE_ID']]["id"] = $agent['iPROFILE_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["userId"] = $agent['iUSER_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["scdisplay"] = ($agent['vcSALES_CODE']==""?"Unknown":$agent['vcSALES_CODE']).' - '.ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["pndisplay"] = $agent['vcPAYROLL_NUMBER'].' - '.ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["displaysc"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME'])).' - '.($agent['vcSALES_CODE']==""?"Unknown":$agent['vcSALES_CODE']);
      $tempagentList[$agent['iPROFILE_ID']]["displaypn"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME'])).' - '.$agent['vcPAYROLL_NUMBER'];
      $tempagentList[$agent['iPROFILE_ID']]["location"] = $agent['vcLOCATION'];
      $tempagentList[$agent['iPROFILE_ID']]["role"] = $agent['vcROLE'];
      $tempagentList[$agent['iPROFILE_ID']]["hired"] = $agent['dSTART'];
      $tempagentList[$agent['iPROFILE_ID']]["lost"] = $agent['dEND'];
      $tempagentList[$agent['iPROFILE_ID']]["login"] = $agent['vcLOGIN'];
      $tempagentList[$agent['iPROFILE_ID']]["sex"] = $agent['vcSEX'];
      $tempagentList[$agent['iPROFILE_ID']]["uniform_size"] = $agent['vcUNIFORM_SIZE'];
    }

    $agentList = $tempagentList;
    asort($agentList);
    return $agentList;
  }

  function GetDSAgentsByRole($role, $projectName, $sort="name") {
     include("ConfigDS.php");

        $sql = "EXEC ASP_Get_Agents_By_Role @role = '$role', @project = '$projectName'";

        try {
          if (!$result = mssql_query($sql)) {
            throw new Exception(mssql_get_last_message()."\n".$sql);
          }
        } catch (Exception $e) {
          self::fatal_error($e->getMessage());
        }
        $agentList = array();
        while($row = mssql_fetch_assoc($result)) {
          array_push($agentList, $row);
        }

//     $role = urlencode($role);
//     $projectName = urlencode($projectName);
//     $test = new RESTclient("http://bottleloader/grr/agents?role=$role&project=$projectName");
//     $test->createRequest("GET");
//     $test->sendRequest();
//     $agentList = $test->getResponse();
//     $agentList = convertResult($agentList);

     $tempagentList = array();

     foreach($agentList as $agent) {
       switch($sort) {
         case "name":
           $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
           $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
           $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
           break;
        case "salescode":
          $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
          $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
          $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
          break;
        case "payroll":
          $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
          $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
          $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
          break;
       }

      $tempagentList[$agent['iPROFILE_ID']]["id"] = $agent['iPROFILE_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["userId"] = $agent['iUSER_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["scdisplay"] = ($agent['vcSALES_CODE']==""?"Unknown":$agent['vcSALES_CODE']).' - '.ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["pndisplay"] = $agent['vcPAYROLL_NUMBER'].' - '.ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["displaysc"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME'])).' - '.($agent['vcSALES_CODE']==""?"Unknown":$agent['vcSALES_CODE']);
      $tempagentList[$agent['iPROFILE_ID']]["displaypn"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME'])).' - '.$agent['vcPAYROLL_NUMBER'];
      $tempagentList[$agent['iPROFILE_ID']]["location"] = $agent['vcLOCATION'];
      $tempagentList[$agent['iPROFILE_ID']]["hired"] = $agent['dSTART'];
      $tempagentList[$agent['iPROFILE_ID']]["lost"] = $agent['dEND'];
      $tempagentList[$agent['iPROFILE_ID']]["login"] = $agent['vcLOGIN'];      
     }

     $agentList = $tempagentList;
     asort($agentList);
     return $agentList;
  }

  function GetDSAgentByUserId($id) {
     include("ConfigDS.php");

        $sql = "EXEC ASP_Get_Agents_By_User_Id @userId = '$id'";

        try {
          if (!$result = mssql_query($sql)) {
            throw new Exception(mssql_get_last_message()."\n".$sql);
          }
        } catch (Exception $e) {
          self::fatal_error($e->getMessage());
        }
        $agentList = array();
        while($row = mssql_fetch_assoc($result)) {
          array_push($agentList, $row);
        }

//     $test = new RESTclient("http://bottleloader/grr/agents?uid=$id");
//     $test->createRequest("GET");
//     $test->sendRequest();
//     $agentList = $test->getResponse();
//     $agentList = convertResult($agentList);

    $tempagentList = array();

    foreach($agentList as $agent) {
      $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["salescode"] = $agent['vcSALES_CODE'];
      $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
      $tempagentList[$agent['iPROFILE_ID']]["id"] = $agent['iPROFILE_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["userId"] = $agent['iUSER_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["location"] = $agent['vcLOCATION'];
      $tempagentList[$agent['iPROFILE_ID']]["hired"] = $agent['dHIRED'];
      $tempagentList[$agent['iPROFILE_ID']]["lost"] = $agent['dTERMINATED'];
      $tempagentList[$agent['iPROFILE_ID']]["address"] = $agent['vcADDRESS'];
      $tempagentList[$agent['iPROFILE_ID']]["suburb"] = $agent['vcSUBURB'];
      $tempagentList[$agent['iPROFILE_ID']]["state"] = $agent['vcSTATE'];
      $tempagentList[$agent['iPROFILE_ID']]["postcode"] = $agent['vcPOSTCODE'];
      $tempagentList[$agent['iPROFILE_ID']]["mobile"] = $agent['vcMOBILE_NUMBER'];
      $tempagentList[$agent['iPROFILE_ID']]["email"] = $agent['vcEMAIL'];
      $tempagentList[$agent['iPROFILE_ID']]["dob"] = $agent['dDOB'];
    }
    $agentList = @array_pop($tempagentList);
    return $agentList;
  }

  function FilterDSAgents($list, $filterType, $filter) {
    switch($filterType) {
      case 'date':
        $templist = array();
          foreach($list as $key=>$value) {
              if(strtotime($value['hired']) <= strtotime($filter) &&
                (strtotime($value['lost']) >= strtotime($filter) ||
                  is_null($value['lost']))) {
                $templist[$key] = $value;
            }
          }
        break;
      case 'terminated':
        $templist = array();
          foreach($list as $key=>$value) {
              if(strtotime($value['hired']) <= strtotime($filter) && $value['lost']) {
                $templist[$key] = $value;
            }
          }
        break;
      case 'locations':
        $templist = array();
        foreach($list as $key=>$value) {
          //Check GIR Location. If a match keep it
          if(stripos($filter, $value['location'])!==false) {
            $templist[$key] = $value;
          } else {
            //If no match in GIR check if additional locations have been manually entered (aka NAB) and check against those.
            if(isset($value['locations']) && !empty($value['locations'])) {
              foreach($value['locations'] as $l) {
                if(stripos($filter, $l)!==false) {
                  $templist[$key] = $value;
                }
              }
            }
          }
        }
        break;
      case 'hired_date':
        $templist = array();
        foreach($list as $key=>$value) {
          if(strtotime($value['hired']) >= strtotime($filter)){
            $templist[$key] = $value;
          }
        }
        break;
       case 'login':
         $templist = array();
         foreach($list as $key=>$value) {
           if($value['login'] == $filter){
             $templist[$key] = $value;
           }
         }
         break;        
      default:
        break;
    }

    $list = $templist;
    return $list;
  }

  function GetAHAgentsByPermission($permission, $projectName, $sort="name") {
     include("ConfigAH.php");

        $sql = "EXEC ASP_Get_Agents_By_Permission @permission = '$permission', @project = '$projectName'";

        try {
          if (!$result = mssql_query($sql)) {
            throw new Exception(mssql_get_last_message()."\n".$sql);
          }
        } catch (Exception $e) {
          self::fatal_error($e->getMessage());
        }
        $agentList = array();
        while($row = mssql_fetch_assoc($result)) {
          array_push($agentList, $row);
        }

//     $permission = urlencode($permission);
//     $projectName = urlencode($projectName);
//
//     $test = new RESTclient("http://bottleloader/grr/agents?permission=$permission&project=$projectName");
//     $test->createRequest("GET");
//     $test->sendRequest();
//     $agentList = $test->getResponse();
//     $agentList = convertResult($agentList);

    $tempagentList = array();    

    foreach($agentList as $agent) {
     switch($sort) {
       case "name":
         $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
         $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
         break;
      case "payroll":
        $tempagentList[$agent['iPROFILE_ID']]["payroll"] = $agent['vcPAYROLL_NUMBER'];
        $tempagentList[$agent['iPROFILE_ID']]["name"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
        break;
     }

      $tempagentList[$agent['iPROFILE_ID']]["id"] = $agent['iPROFILE_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["userId"] = $agent['iUSER_ID'];
      $tempagentList[$agent['iPROFILE_ID']]["pndisplay"] = $agent['vcPAYROLL_NUMBER'].' - '.ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME']));
      $tempagentList[$agent['iPROFILE_ID']]["displaypn"] = ucwords(strtolower($agent['vcFIRST_NAME'].' '.$agent['vcSURNAME'])).' - '.$agent['vcPAYROLL_NUMBER'];
      $tempagentList[$agent['iPROFILE_ID']]["location"] = $agent['vcLOCATION'];
      $tempagentList[$agent['iPROFILE_ID']]["role"] = $agent['vcROLE'];
      $tempagentList[$agent['iPROFILE_ID']]["hired"] = $agent['dSTART'];
      $tempagentList[$agent['iPROFILE_ID']]["lost"] = $agent['dEND'];
    }

    $agentList = $tempagentList;
    asort($agentList);
    return $agentList;
  }
  
  function convertResult($array) {
    $tempArray = array();
    foreach($array as $k=>$v) {
      $temp = array();
      foreach($v as $col=>$val) {
        $temp[$col] = $val;
      }
      $tempArray[$k] = $temp;
    }
    return $tempArray;
  }

  function pooty_send_mail($to, $subject, $message, $opts=array()) {

	  $cc = (isset($opts['cc']))?$opts['cc']:array();
	  $bcc = (isset($opts['bcc']))?$opts['bcc']:array();
      $from = (isset($opts['from']))?$opts['from']:"noreply@pooty.com.au";
      $reply = (isset($opts['reply']))?$opts['reply']:$from;

    $send_to_role = (isset($opts['send_to_role']))?$opts['send_to_role']:false;
    $send_to_role_ad = (isset($opts['send_to_role_ad']))?$opts['send_to_role_ad']:false;
    $files = (isset($opts['files']))?$opts['files']:array();

    if($send_to_role) {
      $user_ws = UserWS::getInstance();
      $users = $user_ws->SearchForUsersByRoleAndProject($to, $send_to_role);
      $to = "";
      foreach($users as $u) {
        $email = $u->EmailPrimary?$u->EmailPrimary:($u->EmailSecondary?$u->EmailSecondary:false);
        if($email) {
          $to .= $email.", ";
        } else {
          //missing emails.. add message? throw exception?
        }
      }
      $to = substr($to, 0, strlen($to) - 2);
      if(!$to) {
        //no emails found.. add message? throw exception?
      }
    }

    if($send_to_role_ad) {
      include("Config.php");
      
      $sql = "EXECUTE zim.XSP_Get_Users_By_Role_In_Project @role= '$to', @project='$send_to_role_ad'";

      $result = mssql_query($sql);
      try {
          $ldap = new adLDAP();
      }
      catch (adLDAPException $e) {
          echo $e; exit();
      }
      $to = "";

      while($row = mssql_fetch_assoc($result)) {
        $login = $row['vcAD_LOGIN'];

        $user = $ldap->user_info($login);
        $email = $user[0]['mail'][0];
        if($email) {
          $to .= $email.", ";
        } else {
          //missing emails.. add message? throw exception?
        }
      }
      $to = substr($to, 0, strlen($to) - 2);
      if(!$to) {
        //no emails found.. add message? throw exception?
      }
    }
    
    //end of message
    $headers  = "From: $from\r\n";
    $headers .= "Reply-To: $reply\r\n";
    $headers .= "Content-type: text/html\r\n";

    //options to send to cc+bcc
    if(!empty($cc)) {
      $headers .= "Cc: ";
      foreach($cc as $email) {
        $headers .= "$email, ";
      }
      $headers = substr($headers, 0, strlen($headers) - 2);
      $headers .= "\r\n";
    }
    if(!empty($bcc)) {
      $headers .= "Bcc: ";
      foreach($bcc as $email) {
        $headers .= "$email, ";
      }
      $headers = substr($headers, 0, strlen($headers) - 2);
      $headers .= "\r\n";
    }

     if(!empty($files)) {
      // boundary
      $semi_rand = md5(time());
      $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

      // headers for attachment
      $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

      // multipart boundary
      $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
      "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

      // preparing attachments
      for($i=0;$i<count($files);$i++){
          if(is_file($files[$i])){
              $message .= "--{$mime_boundary}\n";
              $fp =    @fopen($files[$i],"rb");
          $data =    @fread($fp,filesize($files[$i]));
                      @fclose($fp);
              $data = chunk_split(base64_encode($data));
              $message .= "Content-Type: application/octet-stream; name=\"".basename($files[$i])."\"\n" .
              "Content-Description: ".basename($files[$i])."\n" .
              "Content-Disposition: attachment;\n" . " filename=\"".basename($files[$i])."\"; size=".filesize($files[$i]).";\n" .
              "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
              }
          }
      $message .= "--{$mime_boundary}--";
     }
//PHP Manual warns about > 70 char lines. not sure if needed but below code will wordwrap at 70 chars and should be the solution
//     // In case any of our lines are larger than 70 characters, we should use wordwrap()
//     $message = wordwrap($message, 70);
    // now lets send the email.
    mail($to, $subject, $message, $headers);
  }

    function GetZimUsersByRole($role, $project) {
        include("Config.php");

        $sql = "EXECUTE zim.XSP_Get_Users_By_Role_In_Project @role= '$role', @project='$project'";

        $result = mssql_query($sql);
        try {
            $ldap = new adLDAP();
        }
        catch (adLDAPException $e) {
            echo $e; exit();
        }
        $to = "";

        while($row = mssql_fetch_assoc($result)) {
          $login = $row['vcAD_LOGIN'];

          $user = $ldap->user_info($login);
          $name = $user[0]['displayname'][0];
          $users[$row['vcAD_LOGIN']]['name'] = $name;
        }
        return $users;
    }

?>
