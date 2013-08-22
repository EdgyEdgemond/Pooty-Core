<?php
/************************************************************
 *
 *    There should be no real reason to change this.
 *    If you want some more validate functions they can
 *    be added at the bottom... But please contact Edgy
 *    with these as they will probably be useful to add
 *    the core library :)
 *
 **********************************************************/

$server = $_SERVER["SERVER_NAME"];
switch ($server) {
	case "taxidermy":
		$environment = "DEV";
		break;
	case "ragamuffintest":
		$environment = "TST";
		break;
	case "ragamuffinstage":
		$environment = "STG";
		break;
	case "ragamuffin":
		$environment = "PRD";
		break;
	case "pilgrim.salmat.com.au":
		$environment = "PRD";
		break;
	default:
		$environment = "DEV";
}

require_once("adLDAP.php");

define("ENVIRONMENT", $environment);

// Include all the models in the models directory
$path_to_includes = "models/";

$dir_handle = @opendir($path_to_includes) or die("Unable to open $path_to_includes");

$files = array();

while ($file = readdir($dir_handle)) {

  $files[] = "$file";
  if ($file!=".." && $file!="." && $file!=".svn") {  //ignore . .. and svn files
    require "$path_to_includes/$file";
  }

} // end while loop

switch(ENVIRONMENT) {
  case 'DEV':
    error_reporting(E_ALL);
    break;
  case 'TST':
    error_reporting(E_ALL);
    break;
  case 'STG':
    error_reporting(E_ALL);
    break;
  case 'PRD':
    error_reporting(E_ERROR);
    break;
}

Class Controller extends Validatable {

	public function __construct(){
    Security::getSecurity();
  }

}
?>