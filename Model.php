<?php

      /**********************************************************
       *
       *        There should be no reason to change anything
       *       below this point. Please see Edgy if you think
       *       something needs to be added..
       *
       *********************************************************/

Abstract Class Model extends Validatable {
	static protected $conn = false; //this is the connection object.. one connection per page load

	public function __construct($alias="Default"){
		self::connect($alias);
	}

	protected static function connect($alias){

    $oldAlias = @$_SESSION['db_dets'.APPLICATION_NAME]['alias'];

    if (!isset($_SESSION['db_dets'.APPLICATION_NAME]) ||
          (isset($_SESSION['PROJECT_NAME'.APPLICATION_NAME]) &&
          @$_SESSION['db_dets'.APPLICATION_NAME]['project'] != $_SESSION['PROJECT_NAME'.APPLICATION_NAME]) ||
          (@$_SESSION['db_dets'.APPLICATION_NAME]['alias'] != $alias)
        ||false) {

        $pootyProjectName = isset($_SESSION['PROJECT_NAME'.APPLICATION_NAME])?$_SESSION['PROJECT_NAME'.APPLICATION_NAME]:PROJECT_NAME;

        include("Config.php");

        $sql = "EXEC XSP_Get_Project_Application_Mappings_For_Pooty @projectName = '$pootyProjectName', @alias = '$alias', @applicationName = '".APPLICATION_NAME."'";

        try {
          if (!$result = mssql_query($sql)) {
            throw new Exception(mssql_get_last_message()."\n".$sql);
          }
        } catch (Exception $e) {
          self::fatal_error($e->getMessage());
        }
        $result_set = array();
        while($row = mssql_fetch_assoc($result)) {
          array_push($result_set, $row);
        }

        if(!empty($result_set)) {
          $server = $result_set[0]['vcSERVER_NAME'];
          $database = $result_set[0]['vcSCHEMA_NAME'];
          $username = $result_set[0]['vcLOGIN_NAME'];
          $password = $result_set[0]['vcPASSWORD'];

        } else {
          print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Alfred could not find a mapping for $pootyProjectName to ".APPLICATION_NAME." with alias ".$alias));
  				die;
        }
//       try {
//   		  list($server, $database, $username, $password) = $project_ws->GetConnectionDetailsWithAlias(APPLICATION_NAME,
//                                                                                                             $projectName,
//                                                                                                             $alias);
//       }
//       catch (Exception $e) {
//         list($err, $msg) = explode(":", $e->GetMessage(), 2);
//         switch($err) {
//           case "ProjectDoesNotExistException":
//             print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Alfred could not find the project ".PROJECT_NAME));
//     				die;
//           case "ApplicationDoesNotExistException":
//             print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Alfred could not find the application ".APPLICATION_NAME));
//     				die;
//           case "ApplicationMappingDoesNotExistException":
//             print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Alfred could not find a mapping for ".PROJECT_NAME." to ".APPLICATION_NAME." with alias ".$alias));
//     				die;
//           default:
//             add_message($err,"Unexpected exception - ".$err);
//             false;
//         }
//       }
      $_SESSION['db_dets'.APPLICATION_NAME]['project'] = $pootyProjectName;
      $_SESSION['db_dets'.APPLICATION_NAME]['server'] = $server;
      $_SESSION['db_dets'.APPLICATION_NAME]['database'] = $database;
      $_SESSION['db_dets'.APPLICATION_NAME]['username'] = $username;
      $_SESSION['db_dets'.APPLICATION_NAME]['password'] = $password;
      $_SESSION['db_dets'.APPLICATION_NAME]['alias'] = $alias;
    } else {
      $server = $_SESSION['db_dets'.APPLICATION_NAME]['server'];
      $database = $_SESSION['db_dets'.APPLICATION_NAME]['database'];
      $username = $_SESSION['db_dets'.APPLICATION_NAME]['username'];
      $password = $_SESSION['db_dets'.APPLICATION_NAME]['password'];
    }

		if(!self::$conn || ($alias != $oldAlias)){
      if(isset($_GET['debug'])) {
       add_debug('Alias in use:'.date('H:i:s'), $alias);
  	  }
			if(!(self::$conn = mssql_connect($server,$username,$password))){
        print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the database server: $server"));
				die;
			}
			if(!mssql_select_db($database,self::$conn)){
        print(PootyError::GetBasicErrorString("I CAN HAZ HELPDESK?", "Could not connect to the database: $database"));
				die;
			}
		}
	}

  protected function fatal_error($msg) {
    $str =  PROJECT_NAME.":".APPLICATION_NAME."\n\nError!: $msg\n\n";
    $str .= "URL: ".$_SERVER['URL']."\n\n";
    $bt = debug_backtrace();
    foreach($bt as $line) {
      $args = var_export($line['args'], true);
      $str .= "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
    }

    PootyError::Log($str);

    if (ENVIRONMENT == "PRD") {
      $str = "Error occured accessing the database. Please contact your administrator.";
    }

    print (PootyError::GetBasicErrorString('I CAN HAZ HELPDESK?', $str));
    die();
  }

  protected static function delete($opts=array(), $table_name, $primary_key, $alias, $schema) {

	  $where = (isset($opts['id']))?"where $primary_key={$opts['id']}":'';
	  $where = (isset($opts['where']))?"where {$opts['where']}":$where;

    if (isset($opts['id']) && !isset($opts['where'])) {
      if (!is_numeric($opts['id'])) {
        self::fatal_error("The delete method has been called on $table_name with a non numeric id");
      }
    }

    if (!isset($opts['id']) && !isset($opts['where'])) {
      $msg = "The delete method has been called on $table_name with no conditions<br>";
      $msg .= "This will empty the $table_name table. If this is correct please log a job with helpdesk.";
      self::fatal_error($msg);
    }

	  $sql = "delete from $schema.$table_name $where";

    if(isset($_GET['debug'])) {
	   add_debug($table_name.':Delete:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    return true;
  }

  protected static function insert($columns=array(), $table_name, $primary_key, $alias, $schema) {

    $keys = '';
    $values = '';
	  foreach($columns as $key=>$value) {
	    if ($key != $primary_key) {
	      $columnDetails = self::describe_column($table_name, $key, $alias, $schema);
	  switch ($columnDetails[0]['TYPE_NAME']) {
      		case "varchar":
      		case "nvarchar":
      		case "char":
      		case "nchar":
      		case "text":
      		case "ntext":
      		case "time":
    				$keys .= "$key, ";
    				$values .= "'".str_replace("'", "''", $value)."', ";
    			break;
    			case "date":
      		case "datetime":
      		case "smalldatetime":
            if(preg_match('/^(0[1-9]|[12][0-9]|3[01])(0[1-9]|1[012])(19|20)\d\d$/', $value)) {
              $months = array("01"=>"Jan", "02"=>"Feb","03"=>"Mar", "04"=>"Apr",
                              "05"=>"May", "06"=>"Jun","07"=>"Jul", "08"=>"Aug",
                              "09"=>"Sep", "10"=>"Oct","11"=>"Nov", "12"=>"Dec");
              $value = substr($value, 0, 2).'-'.$months[substr($value, 2, 2)].'-'.substr($value, 4, 4);
            }
    				$keys .= "$key, ";
            $values .= $value==""?"NULL, ":"convert(datetime,convert(datetime,'".str_replace("'", "''", $value)."',120),103), ";
      			break;
      		case "int":
      		case "bigint":
      		case "decimal":
      		case "numeric":
      		case "float":
      		case "bit":
            $keys .= "$key, ";
            $values .= $value==""&&$value!==0?"NULL, ":"$value, ";
            break;
      		default:
            $keys .= "$key, ";
            $values .= "$value, ";
      	}
      }
	  }
	  $keys = substr($keys, 0, strlen($keys) - 2);
    $values = substr($values, 0, strlen($values) - 2);
	  $sql = "insert into $schema.$table_name ($keys) values ($values);SELECT @@IDENTITY as insertId;";

    if(isset($_GET['debug'])) {
	   add_debug($table_name.':Insert:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    list ($id) = mssql_fetch_row($result);
    return $id;
  }

  protected static function mass_insert($columns, $data, $table_name, $primary_key, $alias, $schema) {

    $keys = '';
    $select = '';
	  foreach($data as $row) {
      $select .= 'SELECT ';
      $values = '';
      foreach($row as $key=>$value) {
  	    if ($key != $primary_key) {
          if(is_null($value)) {
            $values .= "NULL, ";
          } else {
      		  $values .= "'".str_replace("'", "''", $value)."', ";
          }
        }
  	  }
	     $values = substr($values, 0, strlen($values) - 2);
	     $select .= $values.' UNION ALL ';
     }
     foreach($columns as $c) {
  	  if ($key != $primary_key) {
        $keys .= $c.", ";
      }
     }
    $keys = substr($keys, 0, strlen($keys) - 2);
    $select = substr($select, 0, strlen($select) - 11);
	  $sql = "insert into $schema.$table_name ($keys) $select;";

    if(isset($_GET['debug'])) {
	   add_debug($table_name.':Insert:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    return $result;
  }
  
  protected static function load($opts=array(), $table_name, $primary_key, $alias, $schema) {

	  $id = (isset($opts['id']))?"{$opts['id']}":'';

    $select = (isset($opts['select']))?$opts['select']:'*';

	  $where = (isset($opts['id']))?"where $primary_key={$opts['id']}":'';
	  $where = (isset($opts['where']))?"where {$opts['where']}":$where;

	  $order = (isset($opts['order']))?"order by {$opts['order']}":'';

    $group = (isset($opts['group']))?"group by {$opts['group']}":'';

	  $first = (isset($opts['first']))?$opts['first']:false;
	  $date = (isset($opts['date']))?$opts['date']:false;
	  $map_to_key = (isset($opts['map_to_key']))?$opts['map_to_key']:false;

	  if($date) {
	    $where = $where?"$where AND '$date' between dSTART and coalesce(dEND, getdate())":"where '$date' between dSTART and coalesce(dEND, getdate())";
	  }

    $sql = "SELECT $select FROM $schema.$table_name $where $group $order";

    if(isset($_GET['debug'])) {
	   add_debug($table_name.':Select:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    if(!empty($result_set)) {
      if($first) {
        $result_set = $result_set[0];
      }
      if($map_to_key) {
        $temp = array();
        foreach($result_set as $r) {
          $temp[$r[$primary_key]] = $r;
        }
        $result_set = $temp;
      }
    }
    return $result_set;
  }

  protected static function update($opts=array(), $columns=array(), $table_name, $primary_key, $alias, $schema) {

    $set = '';
	  foreach($columns as $key=>$value) {
	    if ($key != $primary_key) {
	      $columnDetails = self::describe_column($table_name, $key, $alias, $schema);
      	switch ($columnDetails[0]['TYPE_NAME']) {
      		case "varchar":
      		case "nvarchar":
      		case "char":
      		case "nchar":
      		case "text":
      		case "ntext":
      		case "time":
    				$set .= "$key = '".str_replace("'", "''", $value)."', ";
      			break;
      		case "date":
      		case "datetime":
          case "smalldatetime":
            if(preg_match('/^(0[1-9]|[12][0-9]|3[01])(0[1-9]|1[012])(19|20)\d\d$/', $value)) {
              $months = array("01"=>"Jan", "02"=>"Feb","03"=>"Mar", "04"=>"Apr",
                              "05"=>"May", "06"=>"Jun","07"=>"Jul", "08"=>"Aug",
                              "09"=>"Sep", "10"=>"Oct","11"=>"Nov", "12"=>"Dec");
              $value = substr($value, 0, 2).'-'.$months[substr($value, 2, 2)].'-'.substr($value, 4, 4);
            }
            $value = $value==""?"NULL":"convert(datetime,convert(datetime,'".str_replace("'", "''", $value)."',120),103)";
            $set .= "$key = $value, ";
            break;
      		case "int":
      		case "bigint":
      		case "decimal":
      		case "numeric":
      		case "float":
            $value = $value==""&&$value!==0?"NULL":"$value";
            $set .= "$key = $value, ";
            break;
      		default:
            $set .= "$key = $value, ";
      	}
      }
	  }
	  $set = substr($set, 0, strlen($set) - 2).' ';

	  $where = (isset($opts['id']))?"where $primary_key={$opts['id']}":'';
	  $where = (isset($opts['where']))?"where {$opts['where']}":$where;

    if (!isset($opts['id']) && !isset($opts['where'])) {
      $msg = "The update method has been called on $table_name with no conditions<br>";
      $msg .= "This will update every record in the $table_name table. If this is correct please log a job with helpdesk.<br>";
      $msg .= "SQL: update $schema.$table_name SET $set";

      self::fatal_error($msg);
    }

	  $sql = "update $schema.$table_name SET $set $where";

    if(isset($_GET['debug'])) {
	   add_debug($table_name.':Update:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    return true;
  }

  protected static function audit_details($new, $orig, $id, $audit_columns, $table_name) {

    foreach($audit_columns as $ac) {
      $columnDetails = self::describe_column($table_name, $ac[0], $alias, $schema = "dbo");
      $auditCheck = false;
      switch ($columnDetails[0]['TYPE_NAME']) {
    		case "datetime":
    		case "smalldatetime":
          if(isset($new[$ac[0]]) && display_date($new[$ac[0]]) != display_date(@$orig[$ac[0]])) {
            $auditCheck = true;
            $value = display_date(@$orig[$ac[0]]);
          }
          break;
        default:
          #if(isset($new[$ac[0]]) && $new[$ac[0]] != @$orig[$ac[0]]) {
          if(isset($new[$ac[0]]) && trim($new[$ac[0]]) != trim(@$orig[$ac[0]]) ) {
            $auditCheck = true;
            $value = @$orig[$ac[0]];
          }
          break;
      }
      if($auditCheck) {
        $audit['iID'] = $id;
        $audit['vcCOLUMN'] = $ac[0];
        $audit['vcDISPLAY'] = $ac[1];
        $audit['vcTABLE'] = $table_name;
        $audit['vcVALUE'] = $value;
        $audit['dMODIFIED'] = date('d-M-Y H:i:s');
        $audit['vcMODIFIED_BY'] = $_SERVER['AUTH_USER'];
        self::insert($audit, "XTB_Audit", "iAUDIT_ID", "Default", "dbo");
      }
    }

    return true;
  }

  public static function stored_proc($sp, $params=array(), $opts=array(), $alias="Default", $schema="dbo") {

    $first = (isset($opts['first']))?$opts['first']:false;
	  $map_to_key = (isset($opts['map_to_key']))?$opts['map_to_key']:false;
    $throw_exceptions = (isset($opts['throw_exceptions']))?$opts['throw_exceptions']:false;
    
    $param_str = '';
    foreach($params as $key=>$value) {
      $param_str .= "@$key = '".str_replace("'", "''", $value)."', ";
    }
	  $param_str = substr($param_str, 0, strlen($param_str) - 2).' ';

    $sql = "EXEC $schema.$sp $param_str";

    if(isset($_GET['debug'])) {
	   add_debug($sp.':Stored Proc:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      if($throw_exceptions) {
        throw new Exception($e->getMessage());
      } else {
        self::fatal_error($e->getMessage());
      }
    }
    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    if(!empty($result_set)) {
      if($first) {
        $result_set = $result_set[0];
      }
      if($map_to_key) {
        $temp = array();
        foreach($result_set as $r) {
          $temp[$r[$map_to_key]] = $r;
        }
        $result_set = $temp;
      }
    }
    return $result_set;
  }

  public static function sql($sql, $opts=array(), $alias="Default", $schema="dbo") {

    $first = (isset($opts['first']))?$opts['first']:false;
	  $map_to_key = (isset($opts['map_to_key']))?$opts['map_to_key']:false;
    $throw_exceptions = (isset($opts['throw_exceptions']))?$opts['throw_exceptions']:false;

    if(isset($_GET['debug'])) {
	   add_debug('Direct SQL:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      if($throw_exceptions) {
        throw new Exception($e->getMessage());
      } else {
        self::fatal_error($e->getMessage());
      }
    }
    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    if(!empty($result_set)) {
      if($first) {
        $result_set = $result_set[0];
      }
      if($map_to_key) {
        $temp = array();
        foreach($result_set as $r) {
          $temp[$r[$map_to_key]] = $r;
        }
        $result_set = $temp;
      }
    }
    return $result_set;
  }
  
  public static function describe_table($table_name, $alias, $schema) {
    $sql = "execute. sp_columns $table_name";

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    return $result_set;
  }

  protected function describe_column($table_name, $column_name, $alias, $schema) {
    $sql = "execute. sp_columns @table_name = $table_name, @column_name = $column_name, @table_owner = $schema";

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }

    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    return $result_set;
  }

  protected function run_job($job_name, $server_name, $alias="Default", $schema="dbo") {
    $sql = "sp_start_job @job_name = $job_name, @server_name = $server_name";

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }

    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }
    return $result_set;
  }
  
  protected function check_table($table_name, $alias, $schema) {
    $sql = "select id = object_id(N'$schema.$table_name')";

    if(isset($_GET['debug'])) {
	   add_debug('check_table:'.$alias, $sql);
	  }

    try {
      self::connect($alias);
      if (!$result = mssql_query($sql)) {
        throw new Exception(mssql_get_last_message()."\n".$sql);
      }
    } catch (Exception $e) {
      self::fatal_error($e->getMessage());
    }
    $result_set = array();
    while($row = mssql_fetch_assoc($result)) {
      array_push($result_set, $row);
    }

    if($result_set[0]['id']) {
      return true;
    } else {
      return false;
    }
  }

  public abstract function add($columns=array());

  public abstract function find($opts=array());

  public abstract function modify($opts=array(), $columns=array());

  public abstract function remove($opts=array());

  public abstract function table_details();

  public abstract function audit($new, $orig, $id);
}
?>
